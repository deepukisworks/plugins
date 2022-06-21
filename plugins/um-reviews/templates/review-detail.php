<?php
/**
 * Template for the UM User Reviews, The "Overview rating" detail block.
 *
 * Page: "Profile", tab "Reviews"
 * Caller: Reviews_Main_API->get_details() method
 * Parent template: review-overview.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/review-detail.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<span class="um-reviews-detail">
	<span class="um-reviews-d-s">
	<?php
	if($star_rating_text=="5 Star"){
		$reaction=site_url()."/wp-content/uploads/2022/03/score-5.png";
		$style="background: #4fc761; height: 20px;";
	}else if($star_rating_text=="4 Star"){
		$reaction=site_url()."/wp-content/uploads/2022/03/score-4.png";
		$style="background: #b6d97a; height: 20px;";
	}
	else if($star_rating_text=="3 Star"){
		$reaction=site_url()."/wp-content/uploads/2022/03/score-3.png";
		$style="background: #fedd60; height: 20px;";
	}
	else if($star_rating_text=="2 Star"){
		$reaction=site_url()."/wp-content/uploads/2022/03/score-2.png";
		$style="background: #fb9964;height: 20px;";
	}else{
		$reaction=site_url()."/wp-content/uploads/2022/03/score-1.png";
		$style="background: #fc595a;height:20px;";
	}
	?>
	<?php //echo esc_url( $star_rating_url ); ?>
		<a href="#"><img src="<?php echo $reaction;?>"></a>
	</span>
	<a href="#" class="um-reviews-d-p um-tip-n"  style="height: 20px;" title="<?php echo sprintf( __( '%s reviews (%s)', 'um-reviews' ), $count_of_reviews, $progress . '%' ); ?>">
		<span data-width="<?php echo $progress; ?>" style="<?php echo $style;?>"></span>
	</a>
	<span class="um-reviews-d-n"><?php echo $count_of_reviews; ?></span>
</span>