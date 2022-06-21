<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style>
.user_icon {
  display: flex;
  justify-content: end;
  position: relative;
}
.um-cover.has-cover {
  transform: translate(0, 50px);
}
</style>
<div class="um <?php echo esc_attr( $this->get_class( $mode ) ); ?> um-<?php echo esc_attr( $form_id ); ?> um-role-<?php echo esc_attr( um_user( 'role' ) ); ?> ">

	<div class="um-form" data-mode="<?php echo esc_attr( $mode ) ?>">

		<?php
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_profile_before_header
		 * @description Some actions before profile form header
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_profile_before_header', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_profile_before_header', 'my_profile_before_header', 10, 1 );
		 * function my_profile_before_header( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action( 'um_profile_before_header', $args );

		if ( um_is_on_edit_profile() ) { ?>
			<form method="post" action="">
		<?php }
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_profile_header_cover_area
		 * @description Profile header cover area
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_profile_header_cover_area', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_profile_header_cover_area', 'my_profile_header_cover_area', 10, 1 );
		 * function my_profile_header_cover_area( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action( 'um_profile_header_cover_area', $args );
			
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_profile_header
		 * @description Profile header area
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_profile_header', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_profile_header', 'my_profile_header', 10, 1 );
		 * function my_profile_header( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		?>
	
		<?php
		do_action( 'um_profile_header', $args );
		?> 

		<?php
		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_profile_navbar_classes
		 * @description Additional classes for profile navbar
		 * @input_vars
		 * [{"var":"$classes","type":"string","desc":"UM Posts Tab query"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'um_profile_navbar_classes', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'um_profile_navbar_classes', 'my_profile_navbar_classes', 10, 1 );
		 * function my_profile_navbar_classes( $classes ) {
		 *     // your code here
		 *     return $classes;
		 * }
		 * ?>
		 */
		$classes = apply_filters( 'um_profile_navbar_classes', '' ); ?>

		<div class="um-profile-navbar <?php echo esc_attr( $classes ); ?>">
			<?php
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_profile_navbar
			 * @description Profile navigation bar
			 * @input_vars
			 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_profile_navbar', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_profile_navbar', 'my_profile_navbar', 10, 1 );
			 * function my_profile_navbar( $args ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			// do_action( 'um_profile_navbar', $args );
			 ?>
			
			<div class="um-clear"></div>
		</div>

		<?php
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_profile_menu
		 * @description Profile menu
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_profile_menu', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_profile_menu', 'my_profile_navbar', 10, 1 );
		 * function my_profile_navbar( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action( 'um_profile_menu', $args );

		if ( um_is_on_edit_profile() || UM()->user()->preview ) {

			$nav = 'main';
			$subnav = UM()->profile()->active_subnav();
			$subnav = ! empty( $subnav ) ? $subnav : 'default'; ?>

			<div class="um-profile-body <?php echo esc_attr( $nav . ' ' . $nav . '-' . $subnav ); ?>">

				<?php
				/**
				 * UM hook
				 *
				 * @type action
				 * @title um_profile_content_{$nav}
				 * @description Custom hook to display tabbed content
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'um_profile_content_{$nav}', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'um_profile_content_{$nav}', 'my_profile_content', 10, 1 );
				 * function my_profile_content( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action("um_profile_content_{$nav}", $args);

				/**
				 * UM hook
				 *
				 * @type action
				 * @title um_profile_content_{$nav}_{$subnav}
				 * @description Custom hook to display tabbed content
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'um_profile_content_{$nav}_{$subnav}', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'um_profile_content_{$nav}_{$subnav}', 'my_profile_content', 10, 1 );
				 * function my_profile_content( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action( "um_profile_content_{$nav}_{$subnav}", $args ); ?>

				<div class="clear"></div>
			</div>

			<?php if ( ! UM()->user()->preview ) { ?>

			</form>
            
			<?php }
		} else {
			$menu_enabled = UM()->options()->get( 'profile_menu' );
			$tabs = UM()->profile()->tabs_active();

			$nav = UM()->profile()->active_tab();
			$subnav = UM()->profile()->active_subnav();
			$subnav = ! empty( $subnav ) ? $subnav : 'default';

			if ( $menu_enabled || ! empty( $tabs[ $nav ]['hidden'] ) ) { ?>

				<div class="um-profile-body <?php echo esc_attr( $nav . ' ' . $nav . '-' . $subnav ); ?>">

					<?php
					// Custom hook to display tabbed content
					/**
					 * UM hook
					 *
					 * @type action
					 * @title um_profile_content_{$nav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'um_profile_content_{$nav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'um_profile_content_{$nav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action("um_profile_content_{$nav}", $args);

					/**
					 * UM hook
					 *
					 * @type action
					 * @title um_profile_content_{$nav}_{$subnav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'um_profile_content_{$nav}_{$subnav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'um_profile_content_{$nav}_{$subnav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action( "um_profile_content_{$nav}_{$subnav}", $args ); ?>

					<div class="clear"></div>
				</div>

			<?php }
		}

		do_action( 'um_profile_footer', $args ); ?>
		</div>
</div>	
<?php if ( um_is_on_edit_profile() ) { ?>	
<?php if ( is_user_logged_in() ) { ?>

	<?php if( current_user_can('career') || current_user_can('administrator') ) {  ?>


<?php echo do_shortcode('[wpcfu_form]'); ?>
<button class="downloadpdfs">Download Resume</button>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
  $("button.downloadpdfs").click(function(){
    $("body").toggleClass("pops-resume");
	$(".resume-down").css("display", "block")
  });
});
</script>

 <div class="resume-down" style="display:none;"> 
<button class="close-resume">X</button>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   <script>
$(document).ready(function(){
  $("button.close-resume").click(function(){
    $("body").removeClass("pops-resume");
	$(".resume-down").css("display", "none")
  });
});
</script>
        <?php
$user = wp_get_current_user();
$um_profile = UM()->profile();
$um_user = UM()->user();
// 17.01.2022
$full_name = $um_user->id;
$user_email = $um_user->user_email;
global $wpdb;

// !17.01
//$post_id = get_the_ID();
	$post_id = get_current_user_id();

$all_meta_for_users = get_user_meta( $post_id );
$country = $all_meta_for_users['country'][0];
$meta = get_post_meta($post_id);
$UserPostMeta  = $wpdb->prefix . 'userpostdata';
$SelectPostMeta = $wpdb->get_row("SELECT * FROM $UserPostMeta WHERE post_id = '$post_id' ");

$PostUserID = $SelectPostMeta->userid;
$UserLanguage = $SelectPostMeta->language;
$wpcfu_file = $SelectPostMeta->wpcfu_file;


$the_user = get_user_by( 'id', $PostUserID );
$AuthorName =  $the_user->display_name;
$UserCurrentLocation = $SelectPostMeta->address;
$work_position = $all_meta_for_users['position'][0];
$work_company = $all_meta_for_users['work_company'][0];
$work_period = $all_meta_for_users['work_period'][0];
$work_periods = $all_meta_for_users['work_periods'][0];
$languages = $all_meta_for_users['languages'][0];
$wservices = $all_meta_for_users['services'][0];
$employments = $all_meta_for_users['employments'][0];
$University = $all_meta_for_users['institute'][0];
$GraduationDate = $all_meta_for_users['graduation_date'][0];
$qualification = $all_meta_for_users['qualification'][0];
$carrer_location = $all_meta_for_users['carrer_location'][0];
$month_salary = $all_meta_for_users['month_salary'][0];
$year = preg_split("#/#", $work_period);
		$seyear = preg_split("#/#", $work_periods);
		$years = $year[0];
		$setyears = $seyear[0];
        $yearcal = $years - $setyears;
		$seyears = preg_split("#-#", $yearcal);
		$calyears = $seyears[1];
		$decoded = unserialize($employments);  
		$parttime = $decoded['0']; 
		$parttimez = $decoded['1']; 
		$parttimes = $decoded['2']; 
		$parttimen = $decoded['3']; 


$country_new = $wpdb->get_results( "SELECT * FROM country WHERE countryname = '$country'");
/* echo "<pre>";
		print_r($employments);
		echo "</pre>";
		die('123'); */

 ?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://unpkg.com/html2canvas@1.4.0/dist/html2canvas.js"></script>
	<style>
     .countryy i {background: url(https://dl.dropboxusercontent.com/s/izcyieh1iatr4n5/flags.png) no-repeat;display: inline-block;width: 16px;height: 11px;}
		 
	</style>
	<div id="html-content-holder" class="" style="float:left;width: 100%;">
		<div class="main-resume">
		<div class="main_bodyresume">
		<div class="headerresume">
			<div class="header_left_content">
			 <img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/Beesmart-menu.png">
			 <h2>BEE<span>SMART</span></h2>
			</div>
			<div class="header_right">
			  <h3>@2022.All rights Reserved</h3>
			</div>
		</div>
		<div class="header_profile_section">
            <div class="header_account_section">
			  <?php if ( ! $default_size || $default_size == 'original' ) {
										$profile_photo = UM()->uploader()->get_upload_base_url() . um_user( 'ID' ) . "/" . um_profile( 'profile_photo' );

										$data = um_get_user_avatar_data( um_user( 'ID' ) );
										echo $overlay . sprintf( '<img src="%s" class="%s" alt="%s" data-default="%s" onerror="%s" />',
											esc_url( $profile_photo ),
											esc_attr( $data['class'] ),
											esc_attr( $data['alt'] ),
											esc_attr( $data['default'] ),
											'if ( ! this.getAttribute(\'data-load-error\') ){ this.setAttribute(\'data-load-error\', \'1\');this.setAttribute(\'src\', this.getAttribute(\'data-default\'));}'
										);
									} else {
										echo $overlay . get_avatar( um_user( 'ID' ), $default_size );
									} ?>
                <div class="header_account_section2">
                    <h3 style="text-transform: capitalize;">
					    <?php $user = wp_get_current_user();
							if ($user): ?>
									<?php $loginuser = wp_get_current_user(); ?>
									<?php if($loginuser->user_login){ ?>
									 <?php echo $loginuser->user_login; ?>
									<?php } ?>
						<?php endif; ?>
					</h3>
					<h4><?php echo $work_position; ?></h4>
					<h5>$<?php echo $month_salary; ?> | <?php if ( $parttime ) { print $parttime; ?>, &nbsp; <?php } ?> <?php if ( $parttimez ) { print $parttimez; ?> &nbsp; <?php } ?> <br> <?php if ( $parttimes ) { print $parttimes;?>, &nbsp; <?php } ?> <br> <?php if ( $parttimen ) { print $parttimen; ?>&nbsp; <?php } ?></h5>
				</div>
            </div>
            <div class="header_scanner">
			 <?php global $current_user; 
					  get_currentuserinfo();    ?>
			    <img id='barcode' src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo "https://beesm.art/user/$current_user->display_name"; ?>&amp;size=50x50" alt="<?php echo $current_user->display_name; ?>" title="<?php echo $current_user->display_name; ?>" width="50" height="50" />
            </div>
	    </div>
		<div class="About_your_business">
		    <p><?php if($loginuser->About_your_business){ ?><?php echo $loginuser->About_your_business; ?><?php } ?></p>
		</div>
        <div class="contact_section">
            <h3>Contacts</h3>
			<div class="main-cont-set">
				<div class="mail_profile">
					<div class="mail_icon">
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2022/01/mail-1.png">
						<span><?php if($loginuser->user_email){ ?><?php echo $loginuser->user_email; ?><?php } ?></span>
					</div>
					<div class="mail_link_icon">
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/url-3-1.png">
						<span><?php if($loginuser->user_url){ ?><?php echo $loginuser->user_url; ?><?php } ?></span>
				    </div>
				</div>
				<div class="contact_language">
				    <div class="contact_country_logo">
						<input type='hidden' id='code_cn' value='<?php print_r($country_new[0]->code); ?>'>
						<div class='cont-flg um-field'><span class='countryy'><i style='background-position: 0px -300px;'></i></span></div>
						<span><?php if($loginuser->Currency_picker){ ?><?php echo $loginuser->Currency_picker; ?><?php } ?></span>
					</div>
					<div class="contact_language_2">
						<h2>Languages: <span class="languages"><?php if($loginuser->languages[0]){ ?><?php echo $loginuser->languages[0]; ?><?php } ?>&nbsp;<?php echo $loginuser->languages[1]; ?>&nbsp;<?php echo $loginuser->languages[2]; ?><?php echo $loginuser->languages[3]; ?></span></h2>
					</div>
				</div>
			</div>
	    </div>
    <div class="Work Experience">
        <div class="experince_section">
            <span>Work Experience</span>
              <div class="teamsection">
            <div class="work_img_section">
                <img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/12/Work_Experience.png">
            </div>
            <div class="team_lead_section">
                <h3><?php echo $work_position; ?><br><?php echo $work_company; ?><br><?php echo $years; ?>-<?php echo $setyears; ?> • <?php echo $calyears; ?>yrs</h3>
            </div>
        </div>
           <div class="teamsection">
            <div class="work_img_section">
                <img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/12/Work_Experience.png">
            </div>
            <div class="team_lead_section">
			    <h3><?php echo $work_position; ?><br><?php echo $work_company; ?><br><?php echo $years; ?>-<?php echo $setyears; ?> • <?php echo $calyears; ?>yrs</h3>
                
            </div>
        </div>
        </div>
    </div>
    <div class="Work Experience">
        <div class="experince_section">
            <span>Eduction</span>
              <div class="teamsection">
            <div class="work_img_section">
                <img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/12/cil_education.png">
            </div>
            <div class="team_lead_section">
                <h3><?php echo $University; ?><br> <?php echo $GraduationDate; ?><br> <?php echo $qualification; ?><br> <?php echo $carrer_location; ?></h3>
            </div>
        </div>
    </div>	
    </div>
     <div class="skill_section">
        <span>Skills</span>
            <ul>
                <li><?php echo $wservices; ?></li>    
            </ul>
     </div>
     <div class="skill_section">
        <span>Employment</span>
            <ul>
				<?php if ( $parttime ) { ?>
				<li><?php print $parttime; ?></li>
				<?php } ?>
				<?php if ( $parttimez ) { ?>
				<li><?php print $parttimez; ?></li>
				<?php } ?>
                <?php if ( $parttimes ) { ?>				
				<li><?php print $parttimes; ?></li> 
				<?php } ?>
                <?php if ( $parttimen ) { ?>				
				<li><?php print $parttimen; ?></li>  
				<?php } ?>
            </ul>
     </div>
   </div>
   </div>
	 	
	</div>
	<div class="">
        <input style="text-align:center;" id="btn-Preview-Image" type="button" value="Download Resume" /> 
        <div class="upload-dwn"> </div>

	</div>
	<div id="previewImage" style="display: none;"></div>	
	
	<script>
		$(document).ready(function() {
			// Global variable
			var element = $("#html-content-holder");
			// Global variable
			var getCanvas;

			$("#btn-Preview-Image").on('click', function() {

				html2canvas(document.getElementById("html-content-holder"),{
					allowTaint: true,
					useCORS: true
				}).then(function (canvas) {                   
                   var anchorTag = document.createElement("a");
                    document.body.appendChild(anchorTag);
                    document.getElementById("previewImage").appendChild(canvas);
                    anchorTag.download = "filename.jpg";
                    anchorTag.href = canvas.toDataURL();
                    anchorTag.target = '_blank';
                    anchorTag.click();
                });
			});
		});
	</script>
	<script>
(function ($) {
    // size = flag size + spacing
    var default_size = {
        w: 20,
        h: 15
    };

    function calcPos(letter, size) {
        return -(letter.toLowerCase().charCodeAt(0) - 97) * size;
    }

    $.fn.setFlagPosition = function (iso, size) {
        size || (size = default_size);
        
        var x = calcPos(iso[1], size.w),
            y = calcPos(iso[0], size.h);

        return $(this).css('background-position', [x, 'px ', y, 'px'].join(''));
    };
})(jQuery);

// USAGE:

(function ($) {
    $(function () {
        var $target = $('.countryy');
        
        // on load:
        //$target.find('i').setFlagPosition('es');

        var cn_code = $('input#code_cn').val();
        console.log(cn_code);
            $target.find('i').setFlagPosition(cn_code);    
    });
})(jQuery);

</script>

</div>				

<?php } ?>
	<?php } ?>
<?php } ?>