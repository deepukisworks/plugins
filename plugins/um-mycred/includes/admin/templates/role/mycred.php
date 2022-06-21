<?php if ( ! defined( 'ABSPATH' ) ) exit;

$mycred_types = mycred_get_types();
if ( count( $mycred_types ) > 1 ) {
	$label1 = __( 'Can transfer points to other members?', 'um-mycred' );
	$label2 = __( 'Can not receive points from other members?', 'um-mycred' );
} else {
	$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
	$mycred = mycred( $point_type );

	$label1 = $mycred->template_tags_general( __( 'Can transfer %_plural% to other members?', 'um-mycred' ) );
	$label2 = $mycred->template_tags_general( __( 'Can not receive %_plural% from other members?', 'um-mycred' ) );
} ?>

<div class="um-admin-metabox">
	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'     => 'um-role-mycred um-half-column',
		'prefix_id' => 'role',
		'fields'    => array(
			array(
				'id'    => '_um_can_transfer_mycred',
				'type'  => 'checkbox',
				'label' => $label1,
				'value' => ! empty( $role['_um_can_transfer_mycred'] ) ? $role['_um_can_transfer_mycred'] : 0,
			),
			array(
				'id'    => '_um_cannot_receive_mycred',
				'type'  => 'checkbox',
				'label' => $label2,
				'value' => ! empty( $role['_um_cannot_receive_mycred'] ) ? $role['_um_cannot_receive_mycred'] : 0,
			),

		)
	) )->render_form(); ?>
</div>