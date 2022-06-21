<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return mixed
 */
function um_profile_completeness_add_field( $fields ) {
	$fields['completeness_bar'] = array(
		'title'             => __( 'Profile Completeness', 'um-profile-completeness' ),
		'metakey'           => 'completeness_bar',
		'type'              => 'text',
		'label'             => __( 'Profile Completeness', 'um-profile-completeness' ),
		'required'          => 0,
		'public'            => 1,
		'editable'          => 0,
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	return $fields;
}
add_filter( 'um_predefined_fields_hook', 'um_profile_completeness_add_field', 100 );


/**
 * Display the progress bar
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_profile_field_filter_hook__completeness_bar( $value, $data ) {
	if ( UM()->is_ajax() ) {
		if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
			return do_shortcode('[ultimatemember_profile_progress_bar user_id="' . um_user('ID') . '" who="admin"]' );
		} else {
			return apply_shortcodes('[ultimatemember_profile_progress_bar user_id="' . um_user('ID') . '" who="admin"]' );
		}
	} else {
		if ( um_is_user_himself() || UM()->roles()->um_user_can('can_edit_everyone') ) {
			wp_enqueue_script( 'um_profile_completeness' );
			wp_enqueue_style( 'um_profile_completeness' );

			if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
				return do_shortcode('[ultimatemember_profile_progress_bar user_id="' . um_profile_id() . '" who="admin"]' );
			} else {
				return apply_shortcodes('[ultimatemember_profile_progress_bar user_id="' . um_profile_id() . '" who="admin"]' );
			}
		}
	}

	return $value;
}
add_filter( 'um_profile_field_filter_hook__completeness_bar', 'um_profile_field_filter_hook__completeness_bar', 99, 2 );


/**
 * @param $html
 * @param $field_data
 * @param $form_data
 *
 * @return string
 */
function um_completeness_fields( $html, $field_data, $form_data ) {
	wp_enqueue_script( 'um_profile_completeness' );
	wp_enqueue_style( 'um_profile_completeness' );
	ob_start(); ?>

	<script type="text/html" id="tmpl-um_completeness_fields">
		<#
			var total = 0;
			if ( _.size(data.fields) > 0 ) {
				total = _.reduce(data.fields, function(memo, num){ return memo + parseInt(num);  }, 0);
			}
			var remaining_progress = 100 - total;
		#>
		<input type="hidden" id="role_um_allocated_progress" name="role[_um_allocated_progress]" value="{{{total}}}" />

		<div class="profilec-setup">

			<h3><?php _e( 'Setup Fields','um-profile-completeness' ); ?></h3>

			<div>
				<?php _e( 'Remaining progress:', 'um-profile-completeness'); ?>
				<strong><span class="profilec-ajax">{{{remaining_progress}}}</span>%</strong>
			</div>

			<span class="profilec-data">


				<# var object = data.select; #>


				<# if ( _.size(data.fields) > 0 ) { #>
					<# _.each( data.fields, function(value, key) {
					var field_key = key.replace('_um_progress_', ''); #>
						<p data-key="{{{key}}}">
							<span class="profilec-key alignleft" title="{{{data.select[field_key]['title']}}}">{{{data.select[field_key]['title']}}}</span>
							<span class="profilec-progress alignright">
								<strong><ins>{{{value}}}</ins>%</strong>
								<span class='profilec-edit' data-id="{{{key}}}"><i class='um-faicon-pencil'></i></span>
								<span class='profilec-remove' data-id="{{{key}}}"><i class="um-faicon-remove"></i></span>
							</span>
							<span class="profilec-field-form role{{{key}}}" style="display: none;">
								<label for="role{{{key}}}"><?php _e( 'Edit allocated progress (%)', 'um-profile-completeness' ); ?></label>
								<input type="number" min="0" max="100" id="role{{{key}}}" name="role[{{{key}}}]" value="{{{value}}}" data-prev="{{{value}}}" />
								<span class="profilec-field-form-buttons"></span>
							</span>
						</p>
						<div class="clear"></div>
					<# }); #>
				<# } #>

			</div>

			<span class="profilec-field-form-hidden" style="display: none;">
				<span style="display: none;" class="profilec-field-validation"><?php _e( 'Total progress should be less than 100%', 'um-profile-completeness' ); ?></span><br>
				<a href="javascript:void(0);" class="profilec-update button-primary" data-id=""><?php _e('Update','um-profile-completeness'); ?></a>
				<a href="javascript:void(0);" class="profilec-cancel-edit button" data-id=""><?php _e('Cancel','um-profile-completeness'); ?></a>
			</span>

			<p <# if ( remaining_progress === 0 ) { #> style="display: none" <# } #> >
				<button class="profilec-add button"><?php _e( 'Add field', 'um-profile-completeness' ); ?></button>
			</p>
		</div>

		<div class="profilec-field" style="display: none;">
			<p>
				<select name="progress_field" id="progress_field" class="um-forms-field um-long-field" readonly disabled>
					<# _.each( data.select, function(value, key) { #>
						<option value="{{{key}}}">
							<# if ( value.title ) { #>
								{{{value.title}}}
							<# } else { #>
								{{{key}}}
							<# } #>
						</option>
					<# }) #>
				</select>
			</p>

			<p>
				<label for="progress_value">
					<?php _e( 'How much (%) this field should attribute to profile completeness?', 'um-profile-completeness') ?>
				</label>
				<input type="number" max="100" min="0" name="progress_value" id="progress_value" value="" placeholder="<?php esc_attr_e( 'Completeness value (%)', 'um-profile-completeness' ) ?>" class="um-forms-field um-long-field" readonly disabled />
				<span style="display: none;" class="profilec-new-field-percent-validation"><?php _e( 'Total progress should be less than 100%', 'um-profile-completeness' ); ?></span>
				<span style="display: none;" class="profilec-new-field-validation"><?php _e( 'You have already set this field', 'um-profile-completeness' ); ?></span>
				<span style="display: none;" class="profilec-empty-field-validation"><?php _e( 'Field cannot be empty', 'um-profile-completeness' ); ?></span>
			</p>

			<p>
				<a href="javascript:void(0);" class="profilec-save button-primary"><?php _e( 'Save', 'um-profile-completeness' ) ?></a>
				<a href="javascript:void(0);" class="profilec-cancel button"><?php _e( 'Cancel', 'um-profile-completeness') ?></a>
			</p>
		</div>
	</script>
	<div class="compl-fields">

	</div>

	<?php return ob_get_clean();
}
add_filter( 'um_render_field_type_completeness_fields', 'um_completeness_fields', 10, 3 );


/**
 * Rewrite core id's
 *
 * @param $field_id
 * @param $data
 * @param $args
 *
 * @return string
 */
function um_completeness_field_id( $field_id, $data, $args ) {
	if ( ! empty( $args['profile_completeness'] ) ) {
		$field_id = 'um_completeness_widget_' . $field_id;
	}

	return $field_id;
}
add_filter( 'um_completeness_field_id', 'um_completeness_field_id', 0, 3 );


/**
 * Integration between "Ultimate Member - MailChimp" and "Ultimate Member - Profile Completeness"
 * @param  array  $merge_vars
 * @param  int    $user_id
 * @param  string $list_id
 * @param  array  $_um_merge
 * @return array
 */
function um_completeness_mailchimp_single_merge_fields( $merge_vars, $user_id, $list_id, $_um_merge ) {
	if ( in_array( 'completeness_bar', $_um_merge ) ) {
		$key = current( array_keys( $_um_merge, 'completeness_bar' ) );
		$merge_vars[ $key ] = (int) get_user_meta( $user_id, '_completed', true );
	}
	return $merge_vars;
}
add_filter( 'um_mailchimp_single_merge_fields', 'um_completeness_mailchimp_single_merge_fields', 10, 4 );