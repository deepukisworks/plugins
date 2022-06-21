<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications
 */
class Notifications {


	/**
	 * @var string
	 */
	var $action = '';


	/**
	 * Notifications constructor.
	 */
	function __construct() {
		add_filter( 'um_notifications_core_log_types', array( &$this, 'extends_notifications' ), 10, 1 );
		add_filter( 'um_notifications_get_icon', array( &$this, 'notification_icons' ), 10, 2 );

		add_filter( 'um_allowed_user_tags_patterns', array( &$this, 'um_mycred_allowed_user_tags' ), 10, 1 );
		add_filter( 'um_profile_tag_hook__mycred_balance', array( &$this, 'um_profile_tag_hook__mycred_balance' ), 10, 2 );

		add_filter( 'um_notification_modify_entry_mycred_award', array( &$this, 'replace_notification_template' ), 10, 2 );
		add_filter( 'um_notification_modify_entry_mycred_deduct', array( &$this, 'replace_notification_template' ), 10, 2 );
	}


	/**
	 * @param $content
	 * @param $vars
	 *
	 * @return mixed
	 */
	function replace_notification_template( $content, $vars ) {
		if ( ! empty( $vars['mycred_run_array']['data']['notification_tpl'] ) ) {
			$mycred = mycred( $vars['mycred_run_array']['type'] );
			$content = $mycred->template_tags_general( $mycred->template_tags_amount( $vars['mycred_run_array']['data']['notification_tpl'], $vars['mycred_run_array']['amount'] ) );
		}

		return $content;
	}


	/**
	 * Add UM: Notifications
	 *
	 * @param array $notifications_log
	 *
	 * @return array
	 */
	function extends_notifications( $notifications_log ) {

		$mycred_types = mycred_get_types();
		if ( count( $mycred_types ) > 1 ) {
			$notifications_log['mycred_award'] = array(
				'title'         => __( 'User awarded points for action', 'um-mycred' ),
				'account_desc'  => __( 'When I receive points by completing an action', 'um-mycred' ),
			);

			$notifications_log['mycred_deduct'] = array(
				'title'         => __( 'User deducted points for action', 'um-mycred' ),
				'account_desc'  => __( 'Points deducted when incompleted an action', 'um-mycred' ),
			);

			$notifications_log['mycred_points_sent'] = array(
				'title'         => __( 'User receives points from another person', 'um-mycred' ),
				'account_desc'  => __( 'When I receive points balance from another member', 'um-mycred' ),
			);
		} else {
			$types_keys = array_keys( $mycred_types );
			$mycred = mycred( $types_keys[0] );

			$notifications_log['mycred_award'] = array(
				'title'         => $mycred->template_tags_general( __( 'User awarded <span class="um-points-wrap">%_plural%</span> for action', 'um-mycred' ) ),
				'account_desc'  => $mycred->template_tags_general( __( 'When I receive <span class="um-points-wrap">%_plural%</span> by completing an action', 'um-mycred' ) ),
			);

			$notifications_log['mycred_deduct'] = array(
				'title'         => $mycred->template_tags_general( __( 'User deducted <span class="um-points-wrap">%_plural%</span> for action', 'um-mycred' ) ),
				'account_desc'  => $mycred->template_tags_general( __( '<span class="um-points-wrap">%plural%</span> deducted when incompleted an action', 'um-mycred' ) ),
			);

			$notifications_log['mycred_points_sent'] = array(
				'title'         => $mycred->template_tags_general( __( 'User receives <span class="um-points-wrap">%_plural%</span> from another person', 'um-mycred' ) ),
				'account_desc'  => $mycred->template_tags_general( __( 'When I receive <span class="um-points-wrap">%_plural%</span> balance from another member', 'um-mycred' ) ),
			);
		}

		return $notifications_log;
	}


	/**
	 * @param $output
	 * @param $type
	 *
	 * @return string
	 */
	function notification_icons( $output, $type ) {

		switch ( $type ) {
			case 'mycred_award':
			case 'mycred_points_sent':
				$output = '<i class="um-faicon-plus-circle" style="color:#dfb250"></i>';
				break;
			case 'mycred_deduct':
				$output = '<i class="um-faicon-minus-circle" style="color:#ff7772"></i>';
				break;
		}

		return $output;
	}


	/**
	 * @param $tags
	 *
	 * @return array
	 */
	function um_mycred_allowed_user_tags( $tags ) {
		$tags[] = '{mycred_balance}';
		return $tags;
	}


	/**
	 * @param $value
	 * @param $user_id
	 *
	 * @return mixed|null|string
	 */
	function um_profile_tag_hook__mycred_balance( $value, $user_id ) {
		$output = '';
		$mycred_types = mycred_get_types();

		if ( ! empty( $mycred_types ) ) {
			foreach ( $mycred_types as $point_type => $point_title ) {
				$output .= sprintf( __( '%s: %s', 'um-mycred' ), $point_title, UM()->myCRED()->points()->get_points( $user_id, $point_type ) );
			}
		}

		return $output;
	}
}