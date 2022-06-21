<?php

/**
 * Class for populating section file lists
 * @since 1.0.3
 */

namespace WPSynchro\Files;

use WPSynchro\Files\SyncList;
use WPSynchro\Installation;
use WPSynchro\Job;
use WPSynchro\CommonFunctions;
use WPSynchro\Logger\LoggerTrait;
use WPSynchro\Transport\Destination;
use WPSynchro\Transport\RemoteTransport;

class PopulateListHandler
{
    // Traits
    use LoggerTrait;

    // Constants
    const STATUS_EVERY_X_REQUEST = 3;

    // Data objects
    public $job = null;
    public $installation = null;
    public $sync_list = null;
    public $timer = null;

    /**
     *  Constructor
     *  @since 1.0.3
     */
    public function __construct()
    {
    }

    /**
     *  Initialize class
     *  @since 1.0.3
     */
    public function init(SyncList &$sync_list, Installation &$installation, Job &$job)
    {
        $this->sync_list = $sync_list;
        $this->installation = $installation;
        $this->job = $job;
    }

    /**
     * Populate File List
     * @since 1.0.3
     */
    public function populateFilelist()
    {
        // Timer
        global $wpsynchro_container;
        $this->timer = $wpsynchro_container->get('class.SyncTimerList');

        /**
         *  Validate file sections (for overlapping sections etc.)
         */
        if (!$this->job->files_population_sections_validated) {
            $this->job->files_population_sections_validated = true;
            if (!$this->validateFileSections()) {
                return;
            }
        }

        /**
         *  Run populate on both sites
         */
        // Find the current section we are working on
        $current_section = null;
        foreach ($this->job->files_sections as $key => &$section) {
            if (!$section->source_files_population_complete) {
                $current_section = $section;
                break;
            }
            if (!$section->target_files_population_complete) {
                $current_section = $section;
                break;
            }
        }

        // If all sections are done for both source and target
        if ($current_section == null) {
            $this->job->files_population_source = true;
            $this->job->files_population_target = true;
            $this->job->request_full_timeframe = true;
            return;
        }

        // Do source population
        if (!$current_section->source_files_population_complete) {
            $source_destination = new Destination(Destination::SOURCE);
            $current_section->source_request_count++;
            $this->doSectionPopulate(
                $source_destination,
                $current_section,
                $current_section->source_request_count,
                $current_section->source_is_remote_complete
            );
        }

        // Do target populate
        if (!$current_section->target_files_population_complete) {
            $target_destination = new Destination(Destination::TARGET);
            $current_section->target_request_count++;
            $this->doSectionPopulate(
                $target_destination,
                $current_section,
                $current_section->target_request_count,
                $current_section->target_is_remote_complete
            );
        }


        $this->job->request_full_timeframe = true;
        return;
    }

    /**
     *  Figure out if we are doing status request or just kick the remote into action
     *  @since 1.6.0
     */
    public function doSectionPopulate($destination, $section, $request_count, $is_remote_complete)
    {
        $do_status_request = false;
        if (
            $is_remote_complete === true ||
            $request_count % self::STATUS_EVERY_X_REQUEST == 0
        ) {
            $do_status_request = true;
        }

        // Do either status request or normal kick-into-action request
        $get_file_data_timer = $this->timer->startTimer('filessync', 'population', 'servicecall');
        $this->log('INFO', sprintf(
            'Call file populate on %s doing status request: %s and remote completed: %s on section with id: %s',
            $destination->getDestination(),
            $do_status_request ? "yes" : "no",
            $is_remote_complete ? "yes" : "no",
            $section->id
        ));
        if ($do_status_request) {
            $this->updateSectionStatus($destination, $section);
        } else {
            $this->triggerRemoteFilePopulation($destination, $section);
        }
        $this->log('INFO', sprintf(
            'Got response from remote file population in %f seconds',
            $this->timer->getElapsedTimeToNow($get_file_data_timer)
        ));

        // Wait a bit, to not hammer the remote constantly if still running.
        // If we are just doing status requests (when population is done), just smash away
        if (!$do_status_request) {
            sleep(1);
        }
    }

    /**
     *  Validate file sections before starting population
     *  @since 1.2.0
     */
    public function validateFileSections()
    {
        $valid = true;

        /**
         *  Check if there is overlapping full paths
         */
        $fullpath_sections = [];
        foreach ($this->job->files_sections as $key => $section) {
            foreach ($section->temp_locations_in_basepath as $basepath => $notused) {
                $fullpath_sections[] = trailingslashit(trailingslashit($section->source_basepath) . trim($basepath, '/'));
            }
        }

        foreach ($fullpath_sections as $fullpath1) {
            foreach ($fullpath_sections as $fullpath2) {
                if (substr($fullpath1, 0, strlen($fullpath2)) === $fullpath2 && $fullpath1 != $fullpath2) {
                    $errormsg = sprintf(__('Found overlapping filepaths to synchronize: %s and %s. Please remove one of them before starting again.', 'wpsynchro'), $fullpath2, $fullpath1);
                    $this->job->errors[] = $errormsg;
                    $this->log('CRITICAL', $errormsg);
                    $valid = false;
                    break;
                }
            }
        }

        return $valid;
    }

    /**
     *  Handle the population of a section with a type, that can be source or target
     *  @since 1.2.0
     */
    public function updateSectionStatus($destination, $section)
    {
        // Determine URL
        $url = $destination->getFullURLForREST('wpsynchro/v1/populatefileliststatus/');

        $response = $this->getFileDataFromSource($destination, $section, $url, true);

        if (!isset($response->state)) {
            return;
        }

        // Set files found
        if ($destination->getDestination() == Destination::SOURCE) {
            $section->files_population_source_count = $response->state->files_found;
        } else {
            $section->files_population_target_count = $response->state->files_found;
        }

        // Check if REST service returned a complete state or is still populating data
        if ($response->state->state == 'completed') {
            if ($destination->getDestination() == Destination::SOURCE) {
                $section->source_is_remote_complete = true;
            } else {
                $section->target_is_remote_complete = true;
            }
        }

        // If there is files
        if (isset($response->filelist)) {
            // If set is empty, just set completed on this section
            if (count($response->filelist) == 0) {
                if ($destination->getDestination() == Destination::SOURCE && $section->source_is_remote_complete) {
                    $this->log('DEBUG', 'No more files from source - Setting source file population to completed');
                    $section->source_files_population_complete = true;
                } elseif ($destination->getDestination() == Destination::TARGET && $section->target_is_remote_complete) { {
                        $this->log('DEBUG', 'No more files from target - Setting target file population to completed');
                        $section->target_files_population_complete = true;
                    }
                }
            } else {
                // If not empty, handle the file list and add to database
                $sql_insert_result = $this->sync_list->addUpdateFilelistFromPopulation($destination->getDestination(), $section->id, $response->filelist);
                if ($sql_insert_result) {
                    $this->log('INFO', 'Populated section ' . $section->name . ' on ' . $destination->getDestination() . ' with ' . count($response->filelist) . ' files');
                }
            }
        }
    }

    /**
     *  Handle the population of a section with a type, that can be source or target
     *  @since 1.2.0
     */
    public function triggerRemoteFilePopulation($destination, $section)
    {
        // Determine URL
        $url = $destination->getFullURLForREST('wpsynchro/v1/populatefilelist/');

        // Call the remote url
        $this->getFileDataFromSource($destination, $section, $url, false);
    }

    /**
     *  Get file list data from source installation
     *  @since 1.0.3
     */
    public function getFileDataFromSource($destination, $section, $url, $blocking)
    {
        global $wpsynchro_container;

        // Gather exclusions
        $exclusions = [];
        if (strlen(trim($this->installation->files_exclude_files_match)) > 0) {
            $exclusions = array_merge($exclusions, explode(',', $this->installation->files_exclude_files_match));
        }
        if (strlen(trim($section->exclusions)) > 0) {
            $exclusions = array_merge($exclusions, explode(',', $section->exclusions));
        }

        // To prevent moving WP Synchro plugin, uploads folder and the likes
        $exclusions = array_merge($exclusions, $this->getFilePopulationExclusions(
            $destination
        ));

        // Do some fixy fixy magic on the paths
        $common = new CommonFunctions();
        array_walk($exclusions, function (&$value, $key) use ($common) {
            $value = trim($value, ' ');
            $value = $common->fixPath($value);
        });

        // Genereate request
        $body = new \stdClass();
        $body->exclusions = $exclusions;
        $body->section = $section;
        $body->type = $destination->getDestination();
        $body->allotted_time = $this->timer->getRemainingSyncTime();
        $body->requestid = $this->job->id;

        // Get remote transfer object
        $remotetransport = new RemoteTransport();
        $remotetransport->setDestination($destination);
        $remotetransport->init();
        $remotetransport->setUrl($url);
        $remotetransport->setDataObject($body);

        // Set if blocking or not
        if (!$blocking) {
            $remotetransport->setNonBlocking();
        }
        $remote_filedata_result = $remotetransport->remotePOST();

        // If not blocking, we just return true
        if (!$blocking) {
            return true;
        }

        // If blocking, we return the data.
        if ($remote_filedata_result->isSuccess()) {
            $result_body = $remote_filedata_result->getBody();
            if (is_object($result_body)) {
                return $result_body;
            } else {
                $this->job->errors[] = __('Data returned during file population is invalid, which means we can not continue the synchronization.', 'wpsynchro');
                return false;
            }
        } else {
            $this->job->errors[] = __('File population failed, which means we can not continue the synchronization.', 'wpsynchro');
            return false;
        }
    }

    /**
     * Get file exclusion paths
     * @since 1.2.0
     */
    public function getFilePopulationExclusions($destination)
    {
        $exclusion_arr = [];
        $destination_type = $destination->getDestination();

        // Add wp-admin, wp-includes
        if ($destination_type == Destination::SOURCE) {
            $files_wp_dir_b1 = basename($this->job->from_files_wp_dir);
        } else {
            $files_wp_dir_b1 = basename($this->job->to_files_wp_dir);
        }

        $exclusion_arr[] = $files_wp_dir_b1 . '/wp-admin';
        $exclusion_arr[] = $files_wp_dir_b1 . '/wp-includes';

        // Add plugin location
        if ($destination_type == Destination::SOURCE) {
            $plugin_basename = basename($this->job->from_files_plugins_dir);
            $wpcontent_basename = basename($this->job->from_files_wp_content_dir);
        } else {
            $plugin_basename = basename($this->job->to_files_plugins_dir);
            $wpcontent_basename = basename($this->job->to_files_wp_content_dir);
        }
        $exclusion_arr[] = $wpcontent_basename . '/' . $plugin_basename . '/wpsynchro';

        // Add uploads location
        if ($destination_type == Destination::SOURCE) {
            $uploads_basename = basename($this->job->from_files_uploads_dir);
        } else {
            $uploads_basename = basename($this->job->to_files_uploads_dir);
        }
        $exclusion_arr[] = $wpcontent_basename . '/' . $uploads_basename . '/wpsynchro';

        // Add .htaccess in web root, to prevent troubles with https redirects and other stuff
        if ($destination_type == Destination::SOURCE) {
            $exclusion_arr[] = basename($this->job->from_files_home_dir) . '/.htaccess';
        } else {
            $exclusion_arr[] = basename($this->job->to_files_home_dir) . '/.htaccess';
        }

        // Add system generated source exclusions
        if ($destination_type == Destination::SOURCE) {
            $exclusion_arr = array_merge($exclusion_arr, $this->job->files_population_source_excludes);
        } else {
            $exclusion_arr = array_merge($exclusion_arr, $this->job->files_population_target_excludes);
        }

        return $exclusion_arr;
    }
}
