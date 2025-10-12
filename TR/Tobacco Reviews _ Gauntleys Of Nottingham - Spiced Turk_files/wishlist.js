jQuery(function ($) {
	$(document).ready(function() {
		$('.wishlist-button').on('click', function() {
			var button = $(this);
			var blendId = button.data('tobacco-id');
		  	var isWishlisting = button.hasClass('wishlisted');
		  	$.ajax({
			  	url: customAjax.ajaxurl,
			  	method: 'POST',
				dataType: 'json',
			  	data: {
				  action: 'update_wishlist',
				  blendId: blendId,
				  isWishlisting: !isWishlisting 
			  	},
			  	success: function(response) {
					var actionTaken = response.result; // wishlisted || unwishlisted
					if (actionTaken === 'wishlisted') {
						button.addClass('wishlisted');
						button.html('On your Wishlist <i class="fa fa-star" aria-hidden="true"></i>');
					} else {
						button.removeClass('wishlisted');
						button.html('Add to Wishlist');
					}
			  	},
			  	error: function(xhr, status, error) {
			  		console.log('response error: '+error);
					//console.log('response error');
				}
		  });
		});
	});
});
