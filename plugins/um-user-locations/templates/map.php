<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-member-directory-map<?php if ( $args['must_search'] && $not_searched ) { ?> um-member-directory-hidden-map<?php } ?>"
     data-zoom="<?php echo esc_attr( $zoom ) ?>" data-lat="<?php echo esc_attr( $lat ) ?>" data-lng="<?php echo esc_attr( $lng ) ?>"
     data-dynamic-search="<?php echo esc_attr( $dynamic_search ) ?>"<?php if ( $dynamic_search ) { ?> data-sw="<?php echo esc_attr( $map_sw ) ?>" data-ne="<?php echo esc_attr( $map_ne ) ?>"<?php } ?>
     <?php if ( $search ) { ?>data-search-lat="<?php echo esc_attr( $search_lat ) ?>" data-search-lng="<?php echo esc_attr( $search_lng ) ?>"<?php } ?>
     style="height: <?php echo esc_attr( $height ) ?>"></div>

<?php if ( ! empty( $args['map_search_by_moving'] ) ) { ?>

	<div class="um-member-directory-map-controls um-member-directory-map-moving-search">
		<div class="um-member-directory-map-controls-half">
			<div class="um-field um-field-location um-field-user-location" data-key="location">
				<div class="um-field-area">
					<input class="um_user_location_g_autocomplete" type="text"
					       name="location" id="location" value="" data-key="location" />
					<a href="javascript:void(0);" class="um_current_user_location"><i class="um-faicon-map-marker" aria-hidden="true"></i></a>
				</div>
			</div>
		</div>
	</div>

<?php }