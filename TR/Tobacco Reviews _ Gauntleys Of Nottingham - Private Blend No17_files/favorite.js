jQuery(function ($) {
	$(document).ready(function() {
	  	$('.favorite-button').on('click', function() {
			var button = $(this);
			var blendId = button.data('tobacco-id');
		  	var isFavoriting = button.hasClass('favorited');
		  	$.ajax({
			  	url: customAjax.ajaxurl,
			  	method: 'POST',
				dataType: 'json',
			  	data: {
				  action: 'update_favorite',
				  blendId: blendId,
				  isFavoriting: !isFavoriting 
			  	},
			  	success: function(response) {
					console.log();
					var actionTaken = response.result; // favorited || unfavorited
					if (actionTaken === 'favorited') {
						button.addClass('favorited');
						button.html('Favorited  <i class="fa fa-star" aria-hidden="true"></i>');
					} else {
						button.removeClass('favorited');
						button.html('Add to Favorites');
					}
			  	},
			  	error: function(xhr, status, error) {
			  		console.log('response error');
				}
		  });
		});		
	});
});
