<?php

   require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');

	if(isset($_GET['location_update'])){

		 $postid = $_GET['postid'];
		 $Latitude = $_GET['Latitude'];
		 $Longitude = $_GET['Longitude'];
		 $userid = $_GET['userid'];
		 $id = $_GET['id'];
		 $address = $_GET['address'];

	    $table_name = $wpdb->prefix . 'userpostdata';

	   	$update_query = $wpdb->query("UPDATE $table_name SET userlatitude ='$Latitude' , userlongitude ='$Longitude' , address ='$address' WHERE id = '$id'");
	
   		if($update_query){echo '<div style="display:none" field empty</div>'; ?>
                     
				<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
					<script type="text/javascript">
						var pageurl = "<?php echo site_url('/developer'); ?>";
							swal({
								title: "Data Updated! ",
								text: "Successfully",
								icon: "success",
								button: "Submit "
								}).then(function() {
						window.location.href = pageurl;
						});
				</script>

	    <?php }else{ echo '<div style="display:none" field empty</div>'; ?>

			<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
				<script type="text/javascript">
					var pageurl = "<?php echo site_url('/developer'); ?>";
						swal({
						title: "OOPS ",
						text: "Data Not Updated!",
						icon: "error",
						button: "close"
						}).then(function() {
						window.location.href = pageurl;
					});
			</script>

	   <?php }


	}
