(function ($) {

    $(function() {
        if ($('.love').length > 0) {
            $('.LoveCheck').change(function () {
                $this = $(this);
                $this.attr("disabled", "disabled");
				$('.loader').show();
                data = {
                    'action': 'love_me',
                    'nonce': love_me.nonce,
                    'post': $this.attr('id')
                };
				

                $.ajax({
                    type: "post",
                    data: data,
                    url: love_me.url,
                    dataType: "json",
                    success: function (results) {
                        $this.removeAttr("disabled");
                        $this.parent().toggleClass('liked');
                        $this.parent().find('.LoveCount').text(parseInt (results.likes));
                        $this.parent().find('.intitule').text(results.text);
						$('.loader').hide();
                    },
                    error: function () {
                    }
                });
            });
        }
    });
    
})(jQuery);
