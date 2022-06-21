<?php

namespace WPSynchro\Transport;

use WPSynchro\Installation;
use WPSynchro\Job;
use WPSynchro\Utilities\Configuration\PluginConfiguration;

/**
 * Class for basic auth stuff
 *
 * @since 1.7.2
 */
class Destination
{
    private $destination = "";
    public $sync_type = "";
    private $installation = null;
    private $job = null;
    const TARGET = 'target';
    const SOURCE = 'source';
    const LOCAL = 'local';
    const REMOTE = 'remote';
    const OTHER = 'other';

    /**
     *  Constructor
     */
    public function __construct($destination = "")
    {
        $this->destination = $destination;
        if ($this->destination !== self::OTHER) {
            // Get installation
            global $wpsynchro_container;
            $sync_controller = $wpsynchro_container->get("class.SynchronizeController");
            if ($sync_controller->installation instanceof Installation) {
                $this->setInstallation($sync_controller->installation);
                $this->setJob($sync_controller->job);
            }
        }

        if($destination == self::LOCAL) {
            $this->sync_type = self::LOCAL;
        }
    }

    /**
     * Set installation, to use it out of synchronization context
     * @since 1.7.2
     */
    public function setInstallation(Installation $installation)
    {
        $this->installation = $installation;
        if (!is_null($this->installation)) {
            $this->sync_type = $this->installation->type;
        }
    }

    /**
     * Set job
     * @since 1.7.2
     */
    public function setJob(Job $job)
    {
        $this->job = $job;

    }

    /**
     * Get installation
     * @since 1.7.2
     */
    public function getInstallation()
    {
        return $this->getInstallation();
    }

    /**
     * Get destination
     * @since 1.7.2
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Get full url, given a url path without trailing slash
     * @since 1.7.2
     */
    public function getFullURL($url_path = "")
    {
        if (isset($this->installation->site_url)) {
            $remote_site_url = $this->installation->site_url;
        } else {
            $remote_site_url = "";
        }
        $base_url = "";

        if ($this->destination == self::LOCAL) {
            $base_url = get_home_url();
        } elseif ($this->destination == self::REMOTE) {
            $base_url = $remote_site_url;
        } elseif ($this->destination == self::TARGET) {
            if ($this->sync_type == 'pull') {
                $base_url = get_home_url();
            } elseif ($this->sync_type == 'push') {
                $base_url = $remote_site_url;
            }
        } elseif ($this->destination == self::SOURCE) {
            if ($this->sync_type == 'pull') {
                $base_url = $remote_site_url;
            } elseif ($this->sync_type == 'push') {
                $base_url = get_home_url();
            }
        }

        $url_path = trim($url_path, ' /\\');
        $url = untrailingslashit($base_url) . '/' . $url_path;
        return $url;
    }

    /**
     * Get full url for REST path, given a path without trailing slash
     * @since 1.7.2
     */
    public function getFullURLForREST($rest_url_path = "")
    {
        $base_rest_url = "";

        if ($this->destination == self::LOCAL) {
            $base_rest_url = get_rest_url();
        } elseif ($this->destination == self::REMOTE) {
            if ($this->sync_type == 'pull') {
                $base_rest_url = $this->job->from_rest_base_url;
            } elseif ($this->sync_type == 'push') {
                $base_rest_url = $this->job->to_rest_base_url;
            }
        } elseif ($this->destination == self::TARGET) {
            $base_rest_url = $this->job->to_rest_base_url;
        } elseif ($this->destination == self::SOURCE) {
            $base_rest_url = $this->job->from_rest_base_url;
        }

        $url_path = trim($rest_url_path, ' /\\');
        $url = untrailingslashit($base_rest_url) . '/' . $rest_url_path;
        return $url;
    }

    /**
     * Get accesskey for destination
     * @since 1.7.2
     */
    public function getAccessKey()
    {
        if ($this->destination == self::LOCAL) {
            return TransferAccessKey::getAccessKey();
        } elseif ($this->destination == self::REMOTE) {
            return $this->installation->access_key;
        } elseif ($this->destination == self::TARGET) {
            if ($this->sync_type == 'pull') {
                return TransferAccessKey::getAccessKey();
            } elseif ($this->sync_type == 'push') {
                return $this->installation->access_key;
            }
        } elseif ($this->destination == self::SOURCE) {
            if ($this->sync_type == 'pull') {
                return $this->installation->access_key;
            } elseif ($this->sync_type == 'push') {
                return TransferAccessKey::getAccessKey();
            }
        }
        return null;
    }

    /**
     * Whether to verify SSL
     * @since 1.7.2
     */
    public function shouldVerifySSL()
    {
        if ($this->destination == self::LOCAL) {
            return false;
        } elseif ($this->destination == self::REMOTE) {
            return $this->installation->verify_ssl;
        } elseif ($this->destination == self::TARGET) {
            if ($this->sync_type == 'pull') {
                return false;
            } elseif ($this->sync_type == 'push') {
                return $this->installation->verify_ssl;
            }
        } elseif ($this->destination == self::SOURCE) {
            if ($this->sync_type == 'pull') {
                return $this->installation->verify_ssl;
            } elseif ($this->sync_type == 'push') {
                return false;
            }
        }
        return true;
    }

    /**
     * Whether to use basic auth
     * @since 1.7.2
     */
    public function getBasicAuthentication()
    {
        if ($this->destination == self::LOCAL) {
            return $this->getLocalBasicAuth();
        } elseif ($this->destination == self::REMOTE) {
            return $this->getRemoteBasicAuth();
        } elseif ($this->destination == self::TARGET) {
            if ($this->sync_type == 'pull') {
                return $this->getLocalBasicAuth();
            } elseif ($this->sync_type == 'push') {
                return $this->getRemoteBasicAuth();
            }
        } elseif ($this->destination == self::SOURCE) {
            if ($this->sync_type == 'pull') {
                return $this->getRemoteBasicAuth();
            } elseif ($this->sync_type == 'push') {
                return $this->getLocalBasicAuth();
            }
        }
        return false;
    }

    /**
     * Get remote basic auth
     * @since 1.7.2
     */
    private function getRemoteBasicAuth()
    {
        // Set basic authentication if needed
        if (isset($this->installation->connection_type) && $this->installation->connection_type === 'basicauth') {
            return [
                $this->installation->basic_auth_username,
                $this->installation->basic_auth_password
            ];
        }
        return false;
    }

    /**
     * Get local basic auth
     * @since 1.7.2
     */
    private function getLocalBasicAuth()
    {
        // Check for basic auth, check in request first or alternativly, use the configuration
        if (isset($_SERVER['PHP_AUTH_USER']) && strlen($_SERVER['PHP_AUTH_USER']) > 0) {
            return [
                $_SERVER['PHP_AUTH_USER'],
                $_SERVER['PHP_AUTH_PW']
            ];
        } else {
            $plugin_configuration = new PluginConfiguration();
            $basic_auth_config = $plugin_configuration->getBasicAuthSetting();

            if (strlen($basic_auth_config['username']) > 0) {
                return [
                    $basic_auth_config['username'],
                    $basic_auth_config['password']
                ];
            }
        }
        return false;
    }
}
