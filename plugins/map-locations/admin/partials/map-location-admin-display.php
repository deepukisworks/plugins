<?php function my_admin_page_contents() { 
wp_enqueue_script( 'jquery-1-9-1-jkh-js', 'https://code.jquery.com/jquery-1.9.1.js', array(), null, true );
wp_enqueue_script( 'jquery-ui-kj-js', 'https://code.jquery.com/ui/1.9.2/jquery-ui.js', array(), null, true );
wp_enqueue_script( 'jquery-dataTables-min-js', 'https://cdn.datatables.net/1.10.1/js/jquery.dataTables.min.js', array(), null, true );
wp_enqueue_script( 'dataTables-responsive-js', 'https://cdn.datatables.net/responsive/1.0.0/js/dataTables.responsive.js', array(), null, true );
?>
<script>
jQuery(document).ready(function($){
jQuery('#example').DataTable( {
        responsive: true
    });
});
	</script>
<div class="main_data_cls">
	<table id="example" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>User ID</th>
                <th>User Country</th>
                <th>User State</th>
                <th>User City</th>
                <th>User Latitude</th>
                <th>User Longitude</th>
            </tr>
        </thead>
 
	<?php
	    global $wpdb;
		$user_info_get_wp = $wpdb->prefix . 'user_information_get';
		$data = $wpdb->get_results("SELECT * FROM $user_info_get_wp ");	
		foreach ($data as $value) { ?>
			<tr>
				<td><?php echo $value->userid; ?></td>
				<td><?php echo $value->user_country; ?></td>
				<td><?php echo $value->user_state; ?></td>
				<td><?php echo $value->user_city; ?></td>
				<td><?php echo  $value->userlatitude; ?></td>
				<td><?php echo  $value->userlongitude; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
</div>
<?php }