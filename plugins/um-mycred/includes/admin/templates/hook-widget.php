<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( empty( $hook_callback->um_hooks ) ) {
	_e( 'No options available', 'um-mycred' );
} else {

	$prefs = $hook_callback->prefs;

	foreach ( $hook_callback->um_hooks as $hook => $k ) { ?>

		<div class="hook-instance">

			<h3>
				<?php if ( ! empty( $k['icon'] ) ) { ?><i class="<?php echo esc_attr( $k['icon'] ); ?>"></i>&nbsp;<?php } ?>
				<?php echo $k['title']; ?>
			</h3>

			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'creds' ) ) ); ?>">
							<?php if ( isset( $hook_callback->um_hooks[ $hook ]['deduct'] ) ) {
								printf( __( 'Deduct %s', 'um-mycred' ), $hook_callback->core->plural() );
							} else {
								printf( __( 'Award %s', 'um-mycred' ), $hook_callback->core->plural() );
							} ?>
						</label>
						<input type="text" name="<?php echo esc_attr( $hook_callback->field_name( array( $hook, 'creds' ) ) ); ?>"
						       id="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'creds' ) ) ); ?>"
						       value="<?php echo esc_attr( $hook_callback->core->format_number( $prefs[ $hook ]['creds'] ) ); ?>" size="8" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'limit' ) ) ); ?>">
							<?php _e( 'Limit', 'um-mycred' ); ?>
						</label>
						<div class="h2">
							<?php echo $hook_callback->hook_limit_setting(
								$hook_callback->field_name( array( $hook, 'limit' ) ),
								$hook_callback->field_id( array( $hook, 'limit' ) ),
								$prefs[ $hook ]['limit']
							); ?>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'log' ) ) ); ?>">
							<?php _e( 'Log template', 'um-mycred' ); ?>
						</label>
						<input type="text" name="<?php echo esc_attr( $hook_callback->field_name( array( $hook, 'log' ) ) ); ?>"
						       id="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'log' ) ) ); ?>"
						       value="<?php echo esc_attr( $prefs[ $hook ]['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $hook_callback->core->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>

			<?php do_action( 'um_mycred_hooks_option_extended', $hook, $k, $prefs, $hook_callback ); ?>

			<input type="hidden" name="<?php echo esc_attr( $hook_callback->field_name( array( $hook, 'um_hook' ) ) ); ?>"
			       id="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'limit' ) ) ); ?>"
			       value="<?php echo esc_attr( $hook ); ?>" />
		</div>
	<?php }
}