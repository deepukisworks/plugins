<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">
	<?php $role = $object['data'];

	UM()->admin_forms( [
		'class'     => 'um-role-locations um-half-column',
		'prefix_id' => 'role',
		'fields'    => [
			[
				'id'    => '_um_locations_media_icon',
				'type'  => 'media',
				'label' => __( 'Role icon', 'um-user-locations' ),
				'value' => ! empty( $role['_um_locations_media_icon'] ) ? $role['_um_locations_media_icon'] : '',
			],
		],
	] )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>