jQuery(document).ready(function(){
	jQuery('body').on('click','.cp_btn',function(){
		
		jQuery('body').addClass("cpp_popup_body");
		jQuery('body').append('<div class="cp_loading"><img src="'+ pop_custom.template_url +'/includes/images/loader.gif" class="cp_loader"></div>');
		var loading = jQuery('.cp_loading');
		loading.show();


        var id = jQuery(this).data("id");
        var current = jQuery(this);
        jQuery.ajax({
	        url:ajax_url,
            type:'POST',
	        data:'action=post_popup&popup_id='+id,
	        success : function(response) {
	        	var loading = jQuery('.cp_loading');
				loading.remove(); 

	            jQuery("#cp_popup_id").css("display","block");
	            jQuery("#cp_popup_id .mailppss").html(response);

	        },
	        error: function() {

	            alert('Error occured');
	        }
	    });
       return false;
    });
    var modal = document.getElementById("cp_popup_id");
	var span = document.getElementsByClassName("cp_close")[0];

	jQuery(document).on('click','.cp_close',function(){
		jQuery("#cp_popup_id").css("display","none");
		jQuery('body').removeClass("cpp_popup_body");
	});
	
	window.onclick = function(event) {
	  if (event.target == modal) {
	    modal.style.display = "none";
	    jQuery('body').removeClass("cpp_popup_body");
	  }
	}

	
	jQuery(".mailppss").scroll(function(){
		var scrollTrigger = 70;
        var scrollTop = jQuery(".mailppss").scrollTop();
        if (scrollTop > scrollTrigger) {
            jQuery('#back-to-top').addClass('show');
        } else {
            jQuery('#back-to-top').removeClass('show');
        }
        
	});
     
   
    jQuery('body').on('click', '#back-to-top', function() {
   		var h2 = jQuery(".mailppss").position();
        jQuery('.mailppss').animate({
            scrollTop: 0
        }, 700);
    	return false;

	});


})
