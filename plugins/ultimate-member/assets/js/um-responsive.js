jQuery( window ).on( 'load',function() {
	um_responsive();
	um_modal_responsive();
});

jQuery(window).on( 'resize', function() {
	responsive_Modal();

	//jQuery('img.cropper-hidden').cropper('destroy');

	um_responsive();
	um_modal_responsive();
});