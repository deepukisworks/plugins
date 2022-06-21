<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin:20px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666;">

	<div style="color: #444444;font-weight: normal;">
		<div style="text-align: center;font-weight:600;font-size:26px;padding: 10px 0;border-bottom: solid 3px #eeeeee;">{site_name}</div>
		<div style="clear:both"></div>
	</div>

	<div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee;">

		<div style="margin: 30px 0px;font-size: 24px;text-align: center;line-height: 30px;">{reviewer} has just written a review on {site_name}</div>

		<div style="margin: 15px 0px;font-size: 16px;line-height: 30px;">
			The review status is <strong>Pending</strong>.<br /><br />
			To see and publish this review please click the following link:<br />
			<a href="{review_admin_link}" style="color: #3ba1da;text-decoration: none;">{review_admin_link}</a><br /><br />
			Click the following link to see the review at profile page:<br />
			<a href="{reviews_link}" style="color: #3ba1da;text-decoration: none;">{reviews_link}</a><br /><br />
		</div>

		<div style="margin: 15px 0px;font-size: 16px;text-align: center;line-height: 30px;">Here is the review content</div>

		<div style="background: #eee; color: #444; padding: 12px 15px; border-radius: 3px; font-weight: bold; font-size: 16px;">{review_content}</div>
	</div>

	<div style="color: #999;padding: 20px 30px">

		<div style="">Thank you!</div>
		<div style="">The <a href="{site_url}" style="color: #3ba1da;text-decoration: none;">{site_name}</a> Team</div>

	</div>

</div>