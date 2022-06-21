<?php
/**
 * Template for the UM myCRED.
 * Used on Profile page, Badges tab, All Badges subtab.
 *
 * Caller: method myCRED->show_badges_all()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-mycred/badges.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mycred_types = mycred_get_types();
?>

<div class="um-badges">
	<?php
	foreach ( $all_badges as $badge_id ) {

		$badge = mycred_get_badge( $badge_id );
		$badge_title = strtoupper( $badge->title );
		$badge_level_reached = intval( mycred_badge_level_reached( um_profile_id(), $badge_id ) );
		$image_identification = get_post_meta( $badge_id, 'main_image', true );
		$image_url = wp_get_attachment_url( $image_identification );
		$class = '';
		?>

		<span class="um-badge-leaderboard title">
			<strong><?php printf( __( '%s', 'um-mycred' ), $badge->title ); ?></strong>
		</span>
		<div class="um-clear"></div>

		<?php
		if ( ! empty( $image_url ) ) {
			$title = strip_tags( apply_filters( 'um_mycred_badge_loop_title', $badge_title, $badge ) );
			?>
			<span class="the-badge">
				<img src="<?php echo esc_url( $image_url ) ?>"
						 title="<?php echo esc_attr( $title ) ?>"
						 alt="<?php echo esc_attr( $title ) ?>"
						 width="<?php echo esc_attr( $size ) ?>"
						 height="<?php echo esc_attr( $size ) ?>"
						 class="mycred-badge earned um-tip-n <?php echo esc_attr( $class ) ?>" />
			</span>
		<?php } ?>

		<?php
		foreach ( $badge->levels as $level_key => $level ) {
			$base_requirements = array();
			$req_count = count( $level['requires'] );
			$lvl_count = count( $badge->levels );

			if ( empty( $level['attachment_id'] ) ) {
				continue;
			}

			$image_url = wp_get_attachment_url( $level['attachment_id'] );
			if ( empty( $image_url ) && ! empty( $level['image_url'] ) ) {
				$image_url = $level['image_url'];
			}
			if ( empty( $image_url ) ) {
				continue;
			}

			foreach ( $level['requires'] as $requirement_row => $requirement ) {
				if ( $requirement['type'] == '' ) {
					$requirement['type'] = MYCRED_DEFAULT_TYPE_KEY;
				}
				if ( ! array_key_exists( $requirement['type'], $point_types ) ) {
					continue;
				}

				$base_requirements[$requirement_row] = array(
					'type'      => $requirement['type'],
					'ref'       => array_key_exists( $requirement['reference'], $references ) ? $references[ $requirement['reference'] ] : $requirement['reference'],
					'amount'    => $requirement['amount'],
					'by'        => $requirement['by']
				);
			}

			if ( ! empty( $level['label'] ) ) {
				$badge_title = "{$badge->title} - {$level['label']}";
			} else {
				$badge_title = sprintf( __( '%s - Level %s', 'um-mycred' ), $badge->title, $level_key + 1 );
			}
			$badge_title = strtoupper( $badge_title );

			foreach ( $base_requirements as $requirement ) {
				if ( empty( $requirement['amount'] ) || empty( $requirement['by'] ) ) {
					continue;
				}

				switch ( $requirement['by'] ) {
					case 'sum':
						$format = _x( '%1$s %2$s "%3$s" for "<span class="um-points-wrap">%4$s</span>"', 'Gained|Lost x "point_type" for "reference"', 'um-mycred' );
						break;

					case 'count':
						$format = _x( '%1$s "%3$s" for "<span class="um-points-wrap">%4$s</span>" %5$s times', 'Gained|Lost "point_type" for "reference" x times', 'um-mycred' );
						break;

					default:
						continue 2;
				}

				$badge_title .= sprintf( "&#013;&#10;$format",
					/* 1 */ ( $requirement['amount'] < 0 ? __( 'Lost', 'um-mycred' ) : ( $level_key > $badge_level_reached ? __( 'Gained', 'um-mycred' ) : __( 'Gain', 'um-mycred' ) ) ),
					/* 2 */ $mycred->format_creds( $requirement['amount'] ),
					/* 3 */ $mycred_types[$requirement['type']],
					/* 4 */ $requirement['ref'],
					/* 5 */ $requirement['amount']
				);
			}

			if ( $level_key > $badge_level_reached ) {
				$class = 'um-mycred-locked-badge';
			}

			if ( $image_url ) {
				$title = strip_tags( apply_filters( 'um_mycred_badge_loop_title', $badge_title, $badge ) );
				?>

				<span class="the-badge um-badge-attachment-id-<?php echo esc_attr( $level['attachment_id'] ) ?>">
					<img src="<?php echo esc_url( $image_url ) ?>"
							 title="<?php echo esc_attr( $title ) ?>"
							 alt="<?php echo esc_attr( $title ) ?>"
							 width="<?php echo esc_attr( $size ) ?>"
							 height="<?php echo esc_attr( $size ) ?>"
							 class="mycred-badge earned um-tip-n <?php echo esc_attr( $class ) ?>" />
				</span>

				<?php
			}
		}
		?>

		<div class="um-clear"></div>
	<?php } ?>
</div>