<?php

/**
 * Plugin Name: User Posts Limit
 * Plugin URI: https://en.condless.com/user-posts-limit/
 * Description: Limit the number of posts user can create. Any post type.
 * Version: 1.1.1
 * Author: Condless
 * Author URI: https://www.condless.com/
 * Developer: Condless
 * Developer URI: https://www.condless.com/
 * Contributors: condless
 * Text Domain: user-posts-limit
 * Domain Path: /i18n/languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.2
 * Tested up to: 5.9
 * Requires PHP: 7.0
 */

/**
 * Exit if accessed directly
 */
defined( 'ABSPATH' ) || exit;

/**
 * User Posts Limit Class.
 */
class WP_UPL {

	/**
	 * Construct class
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * WP init
	 */
	public function init() {
		$this->init_textdomain();
		$this->init_settings();
		if ( get_option( 'upl_num_limit' ) ) {
			$this->init_limits();
		}
	}

	/**
	 * Loads text domain for internationalization
	 */
	public function init_textdomain() {
		load_plugin_textdomain( 'user-posts-limit', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * WP settings init
	 */
	public function init_settings() {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wp_update_settings_link' ] );
		add_filter( 'plugin_row_meta', [ $this, 'wp_add_plugin_links' ], 10, 4 );
		add_action( 'admin_menu', [ $this, 'wp_register_options_page' ] );
		add_action( 'admin_init', [ $this, 'wp_register_settings' ] );
		if ( is_multisite() ) {
			add_filter( 'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wp_network_update_settings_link' ] );
			add_action( 'admin_init', [ $this, 'wp_network_register_settings' ] );
			add_action( 'network_admin_menu', [ $this, 'wp_admin_menu' ] );
			add_action( 'network_admin_edit_uplaction', [ $this, 'wp_save_settings' ] );
			add_action( 'network_admin_notices', [ $this, 'wp_custom_notices' ] );
		}
	}

	/**
	 * WP limit init
	 */
	public function init_limits() {
		add_action( 'admin_init', [ $this, 'wp_add_author_support_to_posts' ] );
		add_filter( 'wp_insert_post_empty_content', [ $this, 'wp_limit_post_save' ], 999, 2 );
		add_shortcode( 'upl_hide', [ $this, 'wp_upl_hide_shortcode' ] );
		add_shortcode( 'upl_limits', [ $this, 'wp_upl_limits_shortcode' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'upl_dashboard_widgets' ] );
		if ( get_option( 'upl_stats' ) ) {
			add_filter( 'manage_users_columns', [ $this, 'wp_modify_user_table' ] );
			add_filter( 'manage_users_custom_column', [ $this, 'wp_modify_user_table_row' ], 10, 3 );
		}
	}

	/**
	 * Adds plugin links to the plugin menu
	 * @param mixed $links
	 * @return mixed
	 */
	public function wp_update_settings_link( $links ) {
		array_unshift( $links, '<a href=' . esc_url( add_query_arg( 'page', 'posts-limit', get_admin_url() . 'options-general.php' ) ) . '>' . __( 'Settings' ) . '</a>' );
		return $links;
	}

	/**
	 * Adds plugin meta links to the plugin menu
	 * @param mixed $links_array
	 * @param mixed $plugin_file_name
	 * @param mixed $plugin_data
	 * @param mixed $status
	 * @return mixed
	 */
	public function wp_add_plugin_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
		if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
			$sub_domain = 'he_IL' === get_locale() ? 'www' : 'en';
			$links_array[] = "<a href=https://$sub_domain.condless.com/user-posts-limit/>" . __( 'Documentation' ) . '</a>';
			$links_array[] = "<a href=https://$sub_domain.condless.com/contact/>" . _x( 'Contact', 'Theme starter content' ) . '</a>';
		}
		return $links_array;
	}

	/**
	 * Registers settings
	 */
	public function wp_register_settings() {
		add_option( 'upl_rules_count', '1' );
		add_option( 'upl_message', __( 'Posts limit exceeded', 'user-posts-limit' ) . ' (' . __( 'Delete permanently' ) . ': {extra_posts} {type})' );
		add_option( 'upl_notice', WP_DEBUG ? 'Fullscreen' : 'embed' );
		add_option( 'upl_priority', 'permissive' );
		add_option( 'upl_manage_cap', 'manage_options' );
		add_option( 'upl_stats', '' );
		add_option( 'upl_user_role', [ '' ] );
		add_option( 'upl_posts_type' );
		add_option( 'upl_num_limit' );
		add_option( 'upl_period' );
		register_setting( 'upl_options_group', 'upl_rules_count', 'absint' );
		register_setting( 'upl_options_group', 'upl_message', 'wp_kses_post' );
		register_setting( 'upl_options_group', 'upl_notice', 'sanitize_text_field' );
		register_setting( 'upl_options_group', 'upl_priority', 'sanitize_text_field' );
		register_setting( 'upl_options_group', 'upl_manage_cap', 'sanitize_text_field' );
		register_setting( 'upl_options_group', 'upl_stats', 'sanitize_text_field' );
		register_setting( 'upl_options_group', 'upl_user_role', [ $this, 'upl_sanitize_role' ] );
		register_setting( 'upl_options_group', 'upl_posts_type' );
		register_setting( 'upl_options_group', 'upl_num_limit' );
		register_setting( 'upl_options_group', 'upl_period' );
	}

	/**
	 * Registers options page
	 */
	public function wp_register_options_page() {
		add_options_page( 'Posts Limit', __( 'Posts Limit', 'user-posts-limit' ), get_option( 'upl_manage_cap', 'manage_options' ), 'posts-limit', [ $this, 'upl_options_page' ] );
	}

	/**
	 * Creates the options page
	 */
	public function upl_options_page() {
		if ( has_filter( 'upl_query' ) ) {
			echo '<div id="message" class="notice notice-info is-dismissible"><p>' . esc_attr__( 'Some rules were modified by code, contact your developer to make changes when required', 'user-posts-limit' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__( 'Dismiss this notice.' ) . '</span></button></div>'; 
		} ?>
		<div>
			<h2><?php esc_html_e( 'User Posts Limit', 'user-posts-limit' ); echo " "; esc_html_e( 'Settings' ); ?></h2>
			<form method="post" action="options.php">
			<?php settings_fields( 'upl_options_group' ); ?>
			<table>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Set how many rules to apply', 'user-posts-limit' ); ?>" for="upl_rules_count"><?php esc_html_e( 'Rules', 'user-posts-limit' ); ?></label></th>
					<td><input type="number" min="0" max="99" id="upl_rules_count" name="upl_rules_count" value="<?php echo get_option( 'upl_rules_count' ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Set the message which will be displayed when posts limit exceeded', 'user-posts-limit' ); echo '. {extra_posts} {limit} {count} {type} {release_date}'; ?>" for="upl_message"><?php esc_html_e( 'text' ); ?></label></th>
					<td><input type="text" id="upl_message" name="upl_message" value="<?php echo esc_html( get_option( 'upl_message' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Set the type of notification when posts limit exceeded', 'user-posts-limit' ); echo '. '; esc_html_e( 'embed' ); echo ': '; esc_html_e( 'Compatible also with frontend forms', 'user-posts-limit' ); echo '. '; esc_html_e( 'Fullscreen' ); echo ': '; esc_html_e( 'Use if the warning is not displayed as expected', 'user-posts-limit' ); echo '. '; esc_html_e( 'Redirect', 'user-posts-limit' ); echo ': '; esc_html_e( 'Use the text option to set the redirection path', 'user-posts-limit' ); echo '.'; ?>" for="upl_notice"><?php esc_html_e( 'Notifications' ); ?></label></th>
					<td><select id="upl_notice" name="upl_notice">
						<option value="embed"<?php selected( get_option( 'upl_notice' ), 'embed' ); ?>><?php esc_html_e( 'embed' ); ?></option>
						<option value="Fullscreen"<?php selected( get_option( 'upl_notice' ), 'Fullscreen' ); ?>><?php esc_html_e( 'Fullscreen' ); ?></option>
						<option value="redirect"<?php selected( get_option( 'upl_notice' ), 'redirect' ); ?>><?php esc_html_e( 'Redirect', 'user-posts-limit' ); ?></option>
					</td>
				</tr>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Permissive' ); echo ': '; esc_html_e( 'Limit when all of the user relevant rules were passed', 'user-posts-limit' ); echo '. '; esc_html_e( 'Restrictive' ); echo ': '; esc_html_e( 'Limit when any of the user relevant rules was passed', 'user-posts-limit' ); echo '. '; esc_html_e( 'For more accurate messsage data when multiple rules applied on the same user & post type, put the strictest rules at the bottom in Permissive and at the top for Restrictive', 'user-posts-limit' ); echo '.'; ?>" for="upl_priority"><?php esc_html_e( 'Priority' ); ?></label></th>
					<td><select id="upl_priority" name="upl_priority">
						<option value="permissive"<?php selected( get_option( 'upl_priority' ), 'permissive' ); ?>><?php esc_html_e( 'Permissive' ); ?></option>
						<option value="restrictive"<?php selected( get_option( 'upl_priority' ), 'restrictive' ); ?>><?php esc_html_e( 'Restrictive' ); ?></option>
					</td>
				</tr>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'You can choose a capability which besides manage_options will be required in order to manage this plugin. Limiting users that have the manage_options capability will be possible but they will be able to bypass it by creating/promoting users or edit the code.', 'user-posts-limit' ); ?>" for="upl_manage_cap"><?php esc_html_e( 'Plugin Management Capability' ); ?></label></th>
					<td><select id="upl_manage_cap" name="upl_manage_cap">
						<?php foreach ( [ 'manage_options', 'edit_plugins', 'edit_themes', 'delete_plugins', 'create_users', 'promote_users' ] as $cap ) :
							if ( current_user_can( $cap ) ) : ?>
								<option value="<?php echo esc_html( $cap ); ?>"<?php selected( get_option( 'upl_manage_cap' ), $cap ); ?>><?php echo esc_html( $cap ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Display the limits per user in the users list table', 'user-posts-limit' ); ?>" for="upl_stats"><?php esc_html_e( 'Document Statistics' ); ?></label></th>
					<td><input type="checkbox" id="upl_stats" name="upl_stats" value="1" <?php checked( 1, get_option( 'upl_stats' ), true ); ?> /></td>
				</tr>
				<?php for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) : ?>
					<th><h2><?php echo '#'; echo $i+1; ?></h2></th>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The user role to limit', 'user-posts-limit' ); ?>" for="upl_user_role[<?php echo $i; ?>]"><?php esc_html_e( 'Role' ); ?></label></th>
						<td><select id="upl_user_role[<?php echo $i; ?>]" name="upl_user_role[<?php echo $i; ?>]"><?php wp_dropdown_roles( isset( get_option( 'upl_user_role' )[ $i ] ) ? get_option( 'upl_user_role' )[ $i ] : '' ); ?></select></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The type of the posts to limit', 'user-posts-limit' ); ?>" for="upl_posts_type[<?php echo $i; ?>]"><?php esc_html_e( 'Type' ); ?></label></th>
						<td><select id="upl_posts_type[<?php echo $i; ?>]" name="upl_posts_type[<?php echo $i; ?>]">
							<?php foreach ( get_post_types( [], 'objects' ) as $post_type_obj ): ?>
								<option value="<?php echo esc_attr( $post_type_obj->name ); ?>"<?php if ( isset( get_option( 'upl_posts_type' )[ $i ] ) ) selected( get_option( 'upl_posts_type' )[ $i ], $post_type_obj->name ); ?>><?php echo esc_html( $post_type_obj->labels->name ); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The number of posts allowed', 'user-posts-limit' ); ?>" for="upl_num_limit[<?php echo $i; ?>]"><?php esc_html_e( 'Limit', 'user-posts-limit' ); ?></label></th>
						<td><input type="number" min="0" max="9999" id="upl_num_limit[<?php echo $i; ?>]" name="upl_num_limit[<?php echo $i; ?>]" value="<?php if ( isset( get_option( 'upl_num_limit' )[ $i ] ) ) echo get_option( 'upl_num_limit' )[ $i ]; ?>" /></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'In each what period to reset the count', 'user-posts-limit' ); ?>" for="upl_period[<?php echo $i; ?>]"><?php esc_html_e( 'Cycle', 'user-posts-limit' ); ?></label></th>
						<td><select id="upl_period[<?php echo $i; ?>]" name="upl_period[<?php echo $i; ?>]">
							<option value="1970"<?php if ( isset( get_option( 'upl_period' )[ $i ] ) ) selected( get_option( 'upl_period' )[ $i ], '1970' ); ?>><?php esc_html_e( 'None' ); ?></option>
							<option value="1 year ago"<?php if ( isset( get_option( 'upl_period' )[ $i ] ) ) selected( get_option( 'upl_period' )[ $i ], '1 year ago' ); ?>><?php esc_html_e( 'Year' ); ?></option>
							<option value="1 month ago"<?php if ( isset( get_option( 'upl_period' )[ $i ] ) ) selected( get_option( 'upl_period' )[ $i ], '1 month ago' ); ?>><?php esc_html_e( 'Month' ); ?></option>
							<option value="1 week ago"<?php if ( isset( get_option( 'upl_period' )[ $i ] ) ) selected( get_option( 'upl_period' )[ $i ], '1 week ago' ); ?>><?php esc_html_e( 'Week' ); ?></option>
							<option value="1 day ago"<?php if ( isset( get_option( 'upl_period' )[ $i ] ) ) selected( get_option( 'upl_period' )[ $i ], '1 day ago' ); ?>><?php esc_html_e( 'Day' ); ?></option>
						</td>
					</tr>
				<?php endfor; ?>
			</table>
			<?php submit_button(); ?>
			</form>
		</div>
	<?php }

	/**
	 * Sanitizes the user role option
	 * @param mixed $input
	 * @return mixed
	 */
	public function upl_sanitize_role( $input ) {
		foreach ( $input as $key => $role ) {
			if ( $key >= get_option( 'upl_rules_count' ) ) {
				break;
			}
			$role_obj = get_role( $role );
			if ( $role_obj->has_cap( get_option( 'upl_manage_cap' ) ) || $role_obj->has_cap( 'create_users' ) && get_site_option( 'add_new_users' ) ) {
				$wpmu_role = is_multisite() && 'create_users' !== get_option( 'upl_manage_cap' ) ? '/create_users' : '';
				if ( 'manage_options' === get_option( 'upl_manage_cap' ) || $role_obj->has_cap( 'create_users' ) && get_site_option( 'add_new_users' ) ) {
					$input[ $key ] = 'subscriber';
					add_settings_error( 'upl_user_role', 'upl_user_role', __( 'Limits can not be applied on users that have the capability', 'user-posts-limit' ) . ": manage_options$wpmu_role. #" . ( $key + 1 ) );
					continue;
				} else {
					add_settings_error( 'upl_user_role', 'upl_user_role', __( 'The limit will be applied only on users that do not have the Plugin Management Capability', 'user-posts-limit' ) . ' (' . get_option( 'upl_manage_cap' ) . ")$wpmu_role. #" . ( $key + 1 ), 'info' );
				}
			}
			if ( $role_obj->has_cap( 'edit_others_posts' ) || $role_obj->has_cap( 'edit_others_pages' ) ) {
				add_settings_error( 'upl_user_role', 'upl_user_role', __( 'To prevent bypassing the limits make sure the users do not have the capability to modify posts of others in the selected post type', 'user-posts-limit' ) . '. #' . ( $key + 1 ), 'info' );
			}
		}
		return $input;
	}

	/**
	 * Adds plugin links to the multisite plugin menu
	 * @param mixed $links
	 * @return mixed
	 */
	public function wp_network_update_settings_link( $links ) {
		array_unshift( $links, '<a href=' . esc_url( add_query_arg( 'page', 'posts-limit', network_admin_url( 'settings.php' ) ) ) . '>' . __( 'Settings' ) . '</a>' );
		return $links;
	}

	/**
	 * Adds multisite options
	 */
	public function wp_network_register_settings() {
		add_site_option( 'upl_site_rules_count', '1' );
		add_site_option( 'upl_site_user_role', [ '' ] );
		add_site_option( 'upl_site_posts_type', '' );
		add_site_option( 'upl_site_num_limit', '' );
		add_site_option( 'upl_site_period', '' );
	}

	/**
	 * Adds multisite settings page
	 */
	public function wp_admin_menu() {
		add_submenu_page( 'settings.php', __( 'Posts Limit', 'user-posts-limit' ), __( 'Posts Limit', 'user-posts-limit' ), 'manage_options', 'posts-limit', [ $this, 'upl_network_options_page' ] );
	}

	/**
	 * Creates the multisite settings page
	 */
	public function upl_network_options_page() {
		if ( has_filter( 'upl_network_query' ) ) {
			echo '<div id="message" class="notice notice-info is-dismissible"><p>' . esc_attr__( 'Some rules were modified by code, contact you developer to make changes when required', 'user-posts-limit' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__( 'Dismiss this notice.' ) . '</span></button></div>'; 
		} ?>
		<div>
			<h2><?php esc_html_e( 'User Posts Limit', 'user-posts-limit' ); echo " "; esc_html_e( 'Settings' ); ?></h2>
			<form method="post" action="edit.php?action=uplaction">
			<?php wp_nonce_field( 'upl-validate' ); ?>
			<table>
				<tr valign="top">
					<th><label title="<?php esc_html_e( 'Set how many rules to apply', 'user-posts-limit' ); echo '. '; esc_html_e( 'Network-wide rules are not triggered by the shortcodes and do not appear in the users list / dashbaord', 'user-posts-limit' ); echo '.'; ?>" for="upl_site_rules_count"><?php esc_html_e( 'Rules', 'user-posts-limit' ); ?></label></th>
					<td><input type="number" min="0" max="99" id="upl_site_rules_count" name="upl_site_rules_count" value="<?php echo get_site_option( 'upl_site_rules_count' ); ?>" /></td>
				</tr>
				<?php for ( $i = 0; $i < get_site_option( 'upl_site_rules_count' ); $i++ ) : ?>
					<th><h2><?php echo '#'; echo $i+1; ?></h2></th>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The user role to limit', 'user-posts-limit' ); ?>" for="upl_site_user_role[<?php echo $i; ?>]"><?php esc_html_e( 'Role' ); ?></label></th>
						<td><select id="upl_site_user_role[<?php echo $i; ?>]" name="upl_site_user_role[<?php echo $i; ?>]"><?php wp_dropdown_roles( isset( get_site_option( 'upl_site_user_role' )[ $i ] ) ? get_site_option( 'upl_site_user_role' )[ $i ] : '' ); ?></select></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The type of the posts to limit', 'user-posts-limit' ); ?>" for="upl_site_posts_type[<?php echo $i; ?>]"><?php esc_html_e( 'Type' ); ?></label></th>
						<td><select id="upl_site_posts_type[<?php echo $i; ?>]" name="upl_site_posts_type[<?php echo $i; ?>]">
							<?php foreach ( get_post_types( [], 'objects' ) as $post_type_obj ): ?>
								<option value="<?php echo esc_attr( $post_type_obj->name ); ?>"<?php if ( isset( get_site_option( 'upl_site_posts_type' )[ $i ] ) ) selected( get_site_option( 'upl_site_posts_type' )[ $i ], $post_type_obj->name ); ?>><?php echo esc_html( $post_type_obj->labels->name ); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'The number of posts allowed', 'user-posts-limit' ); ?>" for="upl_site_num_limit[<?php echo $i; ?>]"><?php esc_html_e( 'Limit', 'user-posts-limit' ); ?></label></th>
						<td><input type="number" min="0" max="9999" id="upl_site_num_limit[<?php echo $i; ?>]" name="upl_site_num_limit[<?php echo $i; ?>]" value="<?php if ( isset( get_site_option( 'upl_site_num_limit' )[ $i ] ) ) echo get_site_option( 'upl_site_num_limit' )[ $i ]; ?>" /></td>
					</tr>
					<tr valign="top">
						<th><label title="<?php esc_html_e( 'In each what period to reset the count', 'user-posts-limit' ); ?>" for="upl_site_period[<?php echo $i; ?>]"><?php esc_html_e( 'Cycle', 'user-posts-limit' ); ?></label></th>
						<td><select id="upl_site_period[<?php echo $i; ?>]" name="upl_site_period[<?php echo $i; ?>]">
							<option value="1970"<?php if ( isset( get_site_option( 'upl_site_period' )[ $i ] ) ) selected( get_site_option( 'upl_site_period' )[ $i ], '1970' ); ?>><?php esc_html_e( 'None' ); ?></option>
							<option value="1 year ago"<?php if ( isset( get_site_option( 'upl_site_period' )[ $i ] ) ) selected( get_site_option( 'upl_site_period' )[ $i ], '1 year ago' ); ?>><?php esc_html_e( 'Year' ); ?></option>
							<option value="1 month ago"<?php if ( isset( get_site_option( 'upl_site_period' )[ $i ] ) ) selected( get_site_option( 'upl_site_period' )[ $i ], '1 month ago' ); ?>><?php esc_html_e( 'Month' ); ?></option>
							<option value="1 week ago"<?php if ( isset( get_site_option( 'upl_site_period' )[ $i ] ) ) selected( get_site_option( 'upl_site_period' )[ $i ], '1 week ago' ); ?>><?php esc_html_e( 'Week' ); ?></option>
							<option value="1 day ago"<?php if ( isset( get_site_option( 'upl_site_period' )[ $i ] ) ) selected( get_site_option( 'upl_site_period' )[ $i ], '1 day ago' ); ?>><?php esc_html_e( 'Day' ); ?></option>
						</td>
					</tr>
				<?php endfor; ?>
			</table>
			<?php submit_button(); ?>
			</form>
		</div>
	<?php }

	/**
	 * Saves the multisite options
	 * @param mixed $input
	 * @return mixed
	 */
	public function wp_save_settings() {
		check_admin_referer( 'upl-validate' );
		update_site_option( 'upl_site_rules_count', $_POST['upl_site_rules_count'] );
		update_site_option( 'upl_site_user_role', $_POST['upl_site_user_role'] );
		update_site_option( 'upl_site_posts_type', $_POST['upl_site_posts_type'] );
		update_site_option( 'upl_site_num_limit', $_POST['upl_site_num_limit'] );
		update_site_option( 'upl_site_period', $_POST['upl_site_period'] );
		wp_redirect( add_query_arg( [ 'page' => 'posts-limit', 'updated' => true ], network_admin_url( 'settings.php' ) ) );
		exit;
	}

	/**
	 * Adds admin notices in multisite settings page
	 * @param mixed $input
	 * @return mixed
	 */
	public function wp_custom_notices() {
		if ( isset( $_GET['page'], $_GET['updated'] ) && $_GET['page'] == 'posts-limit' ) {
			if ( get_site_option( 'upl_site_rules_count' ) && get_site_option( 'upl_site_user_role' ) ) {
				foreach ( get_site_option( 'upl_site_user_role' ) as $key => $role ) {
					if ( $key >= get_site_option( 'upl_site_rules_count' ) ) {
						break;
					}
					$role_obj = get_role( $role );
					if ( $role_obj->has_cap( 'create_users' ) && get_site_option( 'add_new_users' ) ) {
						echo '<div id="message" class="notice notice-warning is-dismissible"><p>' . __( 'Limits can not be applied on users that have the capability', 'user-posts-limit' ) . ": create_users. $role" . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__( 'Dismiss this notice.' ) . '</span></button></div>'; 
					} elseif ( $role_obj->has_cap( 'edit_others_pages' ) || $role_obj->has_cap( 'edit_others_posts' ) ) {
						echo '<div id="message" class="notice notice-info is-dismissible"><p>' . __( 'To prevent bypassing the limits make sure the users do not have the capability to modify posts of others in the selected post type', 'user-posts-limit' ) . '. #' . ( $key + 1 ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__( 'Dismiss this notice.' ) . '</span></button></div>'; 
					}
				}
			}
			echo '<div id="message" class="updated notice is-dismissible"><p>' . __( 'Settings updated.' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__( 'Dismiss this notice.' ) . '</span></button></div>'; 
		}
	}

	/**
	 * Adds support for the author feature to all the post types that rules applied on
	 * @param bool $maybe_empty
	 * @param array $postarr
	 * @return bool
	 */
	public function wp_add_author_support_to_posts() {
		if ( current_user_can( get_option( 'upl_manage_cap' ) ) ) {
			if ( is_multisite() && get_site_option( 'upl_site_rules_count' ) ) {
				$posts_type = get_site_option( 'upl_site_posts_type' );
				$num_limit = get_site_option( 'upl_site_num_limit' );
				for ( $i = 0; $i < get_site_option( 'upl_site_rules_count' ); $i++ ) {
					if ( isset( $num_limit[ $i ] ) && '' !== $num_limit[ $i ] ) {
						add_post_type_support( $posts_type[ $i ], 'author' );
					}
				}
			}
			$posts_type = get_option( 'upl_posts_type' );
			$num_limit = get_option( 'upl_num_limit' );
			for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) {
				if ( isset( $num_limit[ $i ] ) && '' !== $num_limit[ $i ] ) {
					add_post_type_support( $posts_type[ $i ], 'author' );
				}
			}
		}
	}

	/**
	 * Limits the post creation by the configured rules
	 * @param bool $maybe_empty
	 * @param array $postarr
	 * @return bool
	 */
	public function wp_limit_post_save( $maybe_empty, $postarr ) {
		if ( empty( $postarr['ID'] ) && ( ! is_multisite() || is_multisite() && ! current_user_can( 'create_users' ) ) ) {
			if ( is_multisite() && get_site_option( 'upl_site_rules_count' ) ) {
				for ( $i = 0; $i < get_site_option( 'upl_site_rules_count' ); $i++ ) {
					if ( isset( get_site_option( 'upl_site_num_limit' )[ $i ] ) && '' !== get_site_option( 'upl_site_num_limit' )[ $i ] && apply_filters( 'upl_network_rule_limit_current_post_type_check', get_site_option( 'upl_site_posts_type' )[ $i ] === $postarr['post_type'], $i, $postarr['post_type'] ) && apply_filters( 'upl_network_rule_limit_current_user_role_check', current_user_can( get_site_option( 'upl_site_user_role' )[ $i ] ), $i, get_current_user_id() ) ) {
						$upl_query = new wp_query( apply_filters( 'upl_network_query', [
							'author'	=> $postarr['post_author'],
							'post_type'	=> $postarr['post_type'],
							'post_status'	=> [ 'any', 'trash', 'draft' ],
							'date_query'	=> [ 'column' => 'post_date', 'after' => get_site_option( 'upl_site_period' )[ $i ] ],
							'posts_per_page'	=> '1',
						], $i ) );
						if ( 0 <= $upl_query->found_posts - get_site_option( 'upl_site_num_limit' )[ $i ] ) {
							do_action( 'upl_network_limit_applied', $postarr, $i );
							add_action( 'admin_notices', function() {
								?><div class="error"><p><?php esc_html_e( 'Network Admin' ); echo ': '; esc_html_e( 'Posts limit exceeded', 'user-posts-limit' ); ?></p></div><?php
							} );
							return true;
						}
					}
				}
				do_action( 'upl_network_limit_not_applied', $postarr );
			}
			if ( ! current_user_can( get_option( 'upl_manage_cap' ) ) ) {
				$relevant_rule = '';
				for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) {
					$num_limit = isset( get_option( 'upl_num_limit' )[ $i ] ) ? apply_filters( 'upl_rule_num_limit', get_option( 'upl_num_limit' )[ $i ], $i, $postarr['post_author'] )  : '';
					if ( '' !== $num_limit && apply_filters( 'upl_rule_limit_current_post_type_check', get_option( 'upl_posts_type' )[ $i ] === $postarr['post_type'], $i, $postarr['post_type'] ) && apply_filters( 'upl_rule_limit_current_user_role_check', current_user_can( get_option( 'upl_user_role' )[ $i ] ), $i, get_current_user_id() ) ) {
						$message = apply_filters( 'upl_message', get_option( 'upl_message' ), $postarr, $relevant_rule );
						$release_date_used = false !== strpos( $message, '{release_date}' );
						$upl_query = new wp_query( apply_filters( 'upl_query', [
							'author'	=> $postarr['post_author'],
							'post_type'	=> $postarr['post_type'],
							'post_status'	=> [ 'any', 'trash', 'draft' ],
							'date_query'	=> [ 'column' => 'post_date', 'after' => get_option( 'upl_period' )[ $i ] ],
							'posts_per_page'	=> $release_date_used ? '15' : '1',
							'order'		=> $release_date_used ? 'ASC' : '',
						], $i ) );
						if ( 0 <= $upl_query->found_posts - $num_limit ) {
							$relevant_rule = $i;
							$applied_num_limit = $num_limit;
							if ( 'restrictive' === get_option( 'upl_priority' ) ) {
								break;
							}
						} elseif ( 'permissive' === get_option( 'upl_priority' ) ) {
							$relevant_rule = '';
							break;
						}
					}
				}
				if ( '' !== $relevant_rule ) {
					$release_date = '0' !== $applied_num_limit && '1970' !== get_option( 'upl_period' )[ $relevant_rule ] && isset( $upl_query->posts[ $upl_query->found_posts - $applied_num_limit ] ) ? date( 'Y-m-d', strtotime( get_the_date( 'Y-m-d', $upl_query->posts[ $upl_query->found_posts - $applied_num_limit ] ) . ' + ' . apply_filters( 'upl_strtotime_cycle', str_replace( ' ago', '', get_option( 'upl_period' )[ $relevant_rule ] ) ) ) ) : '';
					$prepared_message = str_replace( [ '{extra_posts}', '{limit}', '{count}', '{type}', '{release_date}' ], [ $upl_query->found_posts - $applied_num_limit + 1, $applied_num_limit, $upl_query->found_posts, get_post_type_object( get_option( 'upl_posts_type' )[ $relevant_rule ] )->labels->name, $release_date ], do_shortcode( $message ) );
					do_action( 'upl_limit_applied', $postarr, $relevant_rule, $prepared_message );
					switch ( apply_filters( 'upl_notice', get_option( 'upl_notice' ), $postarr, $relevant_rule ) ) {
						case 'embed':
							add_action( 'admin_notices', function() use ( $prepared_message ) {
								?><div class="error"><p><?php echo $prepared_message; ?></p></div><?php
							} );
							return true;
						case 'redirect':
							if ( wp_redirect( $prepared_message ) ) {
								exit;
							}
						case 'Fullscreen':
							wp_die( $prepared_message, '', [ 'back_link' => true ] );
					}
				} else {
					do_action( 'upl_limit_not_applied', $postarr );
				}
			}
		}
		return $maybe_empty;
	}

	/**
	 * Adds shortcode to hide content if limit exceeded
	 * @param mixed $atts
	 * @param mixed $content
	 * @return mixed
	 */
	public function wp_upl_hide_shortcode( $atts, $content = "" ) {
		if ( ! current_user_can( get_option( 'upl_manage_cap' ) ) && ( ! is_multisite() || is_multisite() && ! current_user_can( 'create_users' ) ) ) {
			$atts = shortcode_atts( [
				'type' => 'post',
				'message' => get_option( 'upl_message' ),
			], $atts, 'upl_hide' );
			$post_author = get_current_user_id();
			$relevant_rule = '';
			for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) {
				$num_limit = isset( get_option( 'upl_num_limit' )[ $i ] ) ? apply_filters( 'upl_rule_num_limit', get_option( 'upl_num_limit' )[ $i ], $i, $post_author )  : '';
				if ( '' !== $num_limit && apply_filters( 'upl_rule_limit_current_post_type_check', get_option( 'upl_posts_type' )[ $i ] === $atts['type'], $i, $atts['type'] ) && apply_filters( 'upl_rule_limit_current_user_role_check', current_user_can( get_option( 'upl_user_role' )[ $i ] ), $i, get_current_user_id() ) ) {
					$release_date_used = false !== strpos( $atts['message'], '{release_date}' );
					$upl_query = new wp_query( apply_filters( 'upl_query', [
						'author'	=> $post_author,
						'post_type'	=> $atts['type'],
						'post_status'	=> [ 'any', 'trash', 'draft' ],
						'date_query'	=> [ 'column' => 'post_date', 'after' => get_option( 'upl_period' )[ $i ] ],
						'posts_per_page'	=> $release_date_used ? '15' : '1',
						'order'		=> $release_date_used ? 'ASC' : '',
					], $i ) );
					if ( 0 <= $upl_query->found_posts - $num_limit ) {
						$relevant_rule = $i;
						$applied_num_limit = $num_limit;
						if ( 'restrictive' === get_option( 'upl_priority' ) ) {
							break;
						}
					} elseif ( 'permissive' === get_option( 'upl_priority' ) ) {
						$relevant_rule = '';
						break;
					}
				}
			}
			if ( '' !== $relevant_rule ) {
				$release_date = '0' !== $applied_num_limit && '1970' !== get_option( 'upl_period' )[ $relevant_rule ] && isset( $upl_query->posts[ $upl_query->found_posts - $applied_num_limit ] ) ? date( 'Y-m-d', strtotime( get_the_date( 'Y-m-d', $upl_query->posts[ $upl_query->found_posts - $applied_num_limit ] ) . ' + ' . apply_filters( 'upl_strtotime_cycle', str_replace( ' ago', '', get_option( 'upl_period' )[ $relevant_rule ] ) ) ) ) : '';
				$prepared_message = str_replace( [ '{extra_posts}', '{limit}', '{count}', '{type}', '{release_date}' ], [ $upl_query->found_posts - $applied_num_limit + 1, $applied_num_limit, $upl_query->found_posts, get_post_type_object( get_option( 'upl_posts_type' )[ $relevant_rule ] )->labels->name, $release_date ], do_shortcode( $atts['message'] ) );
				do_action( 'upl_hide_applied', $atts, $relevant_rule, $prepared_message );
				if ( class_exists( 'Elementor\Plugin' ) && Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
					add_filter( 'elementor/frontend/the_content', function( $content ) use( $prepared_message ) {
							return preg_replace( '/\[\/upl_start([^\]]*)\]([\s\S]*?)\[\/upl_hide\]/', $prepared_message, $content );
						} );
				} else {
					return $prepared_message;
				}
			} else {
				do_action( 'upl_hide_not_applied', $atts );
			}
		}
		if ( class_exists( 'Elementor\Plugin' ) && Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
			add_filter( 'elementor/frontend/the_content', function( $content ) {
				return str_replace( [ '[/upl_start]', '[/upl_hide]' ], '', $content );
				} );
		} else {
			return do_shortcode( $content );
		}
	}

	/**
	 * Adds shortcode that displays the current user posts limit
	 * @param mixed $atts
	 * @return mixed
	 */
	public function wp_upl_limits_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'format'	=> '{type} {count} / {limit}. ',
			'type'		=> '',
		], $atts, 'upl_limits' );
		$formatted_limits = '';
		foreach ( $this->current_user_limits() as $i => $count ) {
			if ( empty( $atts['type'] ) || $atts['type'] === get_option( 'upl_posts_type' )[ $i ] ) {
				$num_limit = apply_filters( 'upl_rule_num_limit', get_option( 'upl_num_limit' )[ $i ], $i, get_current_user_id() );
				$formatted_limits .= str_replace( [ '{left}', '{limit}', '{count}', '{type}' ], [ $num_limit - $count, $num_limit, $count, get_post_type_object( get_option( 'upl_posts_type' )[ $i ] )->labels->name ], $atts['format'] );
			}
		}
		return $formatted_limits;
	}

	/**
	 * Creates dashboard widget
	 */
	public function upl_dashboard_widgets() {
		wp_add_dashboard_widget( 'upl_limits_widget', __( 'Posts Limit', 'user-posts-limit' ), [ $this, 'upl_limits_dashboard_widget' ] );
	}

	/**
	 * Displays the posts count in the dashboard widget
	 */
	public function upl_limits_dashboard_widget() {
		$limits = $this->current_user_limits();
		if ( ! empty( $limits ) ) {
			foreach ( $limits as $i => $count ) {
				$num_limit = apply_filters( 'upl_rule_num_limit', get_option( 'upl_num_limit' )[ $i ], $i, get_current_user_id() );
				echo '<span style=color:' . ( $count < $num_limit ? '' : 'coral' ) . '>' . get_post_type_object( get_option( 'upl_posts_type' )[ $i ] )->labels->name . ' ' . $count . ' / ' . $num_limit . '. </span>';
			}
		} else {
			esc_attr_e( 'Unlimited', 'user-posts-limit' );
		}
	}

	/**
	 * Adds column to the admin users table
	 * @param mixed $columns
	 * @return mixed
	 */
	public function wp_modify_user_table( $columns ) {
		for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) {
			if ( isset( get_option( 'upl_num_limit' )[ $i ] ) && '' !== get_option( 'upl_num_limit' )[ $i ] ) {
				$columns[ "rule$i" ] = get_post_type_object( get_option( 'upl_posts_type' )[ $i ] )->labels->name . ' ' . __( 'Limit', 'user-posts-limit' );
			}
		}
		return $columns;
	}

	/**
	 * Sets the column in the admin users table
	 * @param mixed $columns
	 * @return mixed
	 */
	public function wp_modify_user_table_row( $row_output, $column_id_attr, $user_id ) {
		$i = str_replace( 'rule', '', $column_id_attr );
		if ( isset( get_option( 'upl_user_role' )[ $i ] ) && apply_filters( 'upl_rule_limit_current_user_role_check', in_array( get_option( 'upl_user_role' )[ $i ], get_userdata( $user_id )->roles ), $i, $user_id ) && ! user_can( $user_id, get_option( 'upl_manage_cap' ) ) && ( ! is_multisite() || is_multisite() && ! user_can( $user_id, 'create_users' ) ) ) {
			$upl_query = new wp_query( apply_filters( 'upl_query', [
				'author'	=> $user_id,
				'post_type'	=> get_option( 'upl_posts_type' )[ $i ],
				'post_status'	=> [ 'any', 'trash', 'draft' ],
				'date_query'	=> [ 'column' => 'post_date', 'after' => get_option( 'upl_period' )[ str_replace( 'rule', '', $column_id_attr ) ] ],
				'posts_per_page'	=> '1',
			], $i ) );
			$num_limit = apply_filters( 'upl_rule_num_limit', get_option( 'upl_num_limit' )[ $i ], $i, $user_id );
			return '<span style=color:' . ( $upl_query->found_posts < $num_limit ? '' : 'coral' ) . '>' . $upl_query->found_posts . ' / ' . $num_limit . '</span>';
		}
		return $row_output;
	}

	/**
	 * Checks the current user posts limits
	 * @return mixed
	 */
	public function current_user_limits() {
		$limits = [];
		if ( ! current_user_can( get_option( 'upl_manage_cap' ) ) && ( ! is_multisite() || is_multisite() && ! current_user_can( 'create_users' ) ) ) {
			for ( $i = 0; $i < get_option( 'upl_rules_count' ); $i++ ) {
				if ( isset( get_option( 'upl_num_limit' )[ $i ] ) && '' !== get_option( 'upl_num_limit' )[ $i ] && apply_filters( 'upl_rule_limit_current_user_role_check', current_user_can( get_option( 'upl_user_role' )[ $i ] ), $i, get_current_user_id() ) ) {
					$upl_query = new wp_query( apply_filters( 'upl_query', [
						'author'	=> get_current_user_id(),
						'post_type'	=> get_option( 'upl_posts_type' )[ $i ],
						'post_status'	=> [ 'any', 'trash', 'draft' ],
						'date_query'	=> [ 'column' => 'post_date', 'after' => get_option( 'upl_period' )[ $i ] ],
						'posts_per_page'	=> '1',
					], $i ) );
					$limits[ $i ] = $upl_query->found_posts;
				}
			}
		}
		return apply_filters( 'upl_current_user_limits', $limits );
	}
}

/**
 * Instantiate class
 */
$user_posts_limit = new WP_UPL();
