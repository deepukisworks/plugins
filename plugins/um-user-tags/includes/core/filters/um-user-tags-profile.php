<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Save custom tags submitted by user
 *
 * @param array $to_update
 *
 * @return array
 */
function um_user_tags_save_custom_tags( $to_update ) {
	$form_id = absint( $_REQUEST['form_id'] );

	$filters = get_option( 'um_user_tags_filters', array() );
	$fields = UM()->query()->get_attr( 'custom_fields', $form_id );

	foreach ( $fields as $metakey => $value ) {
		if ( $value['type'] == 'user_tags' && in_array( $metakey, array_keys( $filters ) ) ) {

			$data = UM()->fields()->get_field( $metakey );
			if ( ! um_can_edit_field( $data ) || ! um_can_view_field( $data ) ) {
				continue;
			}

			/*newly added*/
			if ( um_user( 'user_tags_can_add' ) && isset( $value['tag_source'] ) && ! empty( $to_update[ $metakey ] ) ) {
				for ( $i = 0; $i < count( $to_update[ $metakey ] ); $i++ ) {
					if ( ! is_numeric( $to_update[ $metakey ][ $i ] ) ) {

						$new_tag = wp_insert_term( $to_update[ $metakey ][ $i ], 'um_user_tag', array(
							'parent' => $value['tag_source']
						) );

						if ( ! is_wp_error( $new_tag ) ) {
							$to_update[ $metakey ][ $i ] = $new_tag['term_id'];

							//update count +1
							$term = get_term_by( 'id', $new_tag['term_id'], 'um_user_tag' );
							global $wpdb;
							$wpdb->update(
								$wpdb->term_taxonomy,
								array( 'count' => $term->count + 1 ),
								array( 'term_id' => $term->term_id )
							);
						}
					}
				}
			}
		}
	}

	return $to_update;
}
add_filter( 'um_user_pre_updating_profile_array', 'um_user_tags_save_custom_tags' );