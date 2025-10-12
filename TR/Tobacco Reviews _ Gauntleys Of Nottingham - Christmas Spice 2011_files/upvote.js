jQuery(function ($) {
	$(document).ready(function() {
	  	$('.upvote-button').on('click', function() {
			var button = $(this);
			var reviewId = button.data('review-id');
		  	var isUpvoted = button.hasClass('upvoted');
		  	$.ajax({
			  	url: customAjax.ajaxurl,
			  	method: 'POST',
				dataType: 'json',
			  	data: {
				  action: 'update_upvote',
				  reviewId: reviewId,
				  isUpvoted: !isUpvoted // if it has the class upvoted, then we're downvoting
			  	},
			  	success: function(response) {
					var updatedCount = response.upVotes;
					var label = (updatedCount == 1) ? 'person' : 'people';
					var container = button.closest('.upvote-container');
					
					container.find('.upvote-count').text(updatedCount);
					container.find('.update-count-label').text(label);
					if (isUpvoted) {
						button.removeClass('upvoted');
						var buttonWrapper = button.closest('.divUpvoteButtonWrapper-Upvoted');
						buttonWrapper.removeClass('divUpvoteButtonWrapper-Upvoted');
						buttonWrapper.removeClass('divUpvoteAlreadyUpvote');
						buttonWrapper.addClass('divUpvoteButtonWrapper');
						buttonWrapper.addClass('divUpvoteCanUpvote');
						button.html('Yes');					
					} else {
						button.addClass('upvoted');
						var buttonWrapper = button.closest('.divUpvoteButtonWrapper');
						buttonWrapper.removeClass('divUpvoteButtonWrapper');
						buttonWrapper.removeClass('divUpvoteCanUpvote');
						buttonWrapper.addClass('divUpvoteButtonWrapper-Upvoted');
						buttonWrapper.addClass('divUpvoteAlreadyUpvote');
						button.html('Yes <i class="fa-solid fa-thumbs-up"></i>');
						// if we want to pass these via globals we need to pass from php -> js, probably in a single step at the top of all js RRC
					}
					
			  	},
			  	error: function(xhr, status, error) {
			  		console.log('response error');
				}
		  });
		});
	});
});
