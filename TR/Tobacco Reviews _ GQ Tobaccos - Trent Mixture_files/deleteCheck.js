jQuery(function ($) {
	$(document).ready(function($) {
		$(".anchDeleteLink").on("click", function(e) {
			e.preventDefault();
			var deleteMessage = 'Are you sure you want to delete?';
			if ($(this).hasClass('anchDeleteReview')) {
				var deleteMessage = 'Are you sure you want to delete your review?  \nThis action cannot be undone.';
			}
			if ($(this).hasClass('anchDeleteRetailer')) {
				var deleteMessage = 'Are you sure you want to delete this retailer?  \nThis action cannot be undone.';
			}
			if (confirm(deleteMessage)) {
				var deleteUrl = $(this).attr("href");
				window.location.href = deleteUrl;
			}
		});
	}); 
});
