(function ($) {
    $(function() {

		$('#love-me-update-wrapper').hide();

		$('#update-likes-yes').change(function() {
			if($(this).is(":checked")) {
				$('#love-me-update-wrapper').slideDown();
			} else {
				$('#love-me-update-wrapper').slideUp();
			}
		});

    });
})(jQuery);