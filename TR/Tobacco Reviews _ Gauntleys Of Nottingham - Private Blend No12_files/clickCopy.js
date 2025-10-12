jQuery(function ($) {
	$(document).ready(function($) {
		$('.share-link').click(function(event) {
			event.preventDefault();

			var itemLink = $(this).data('link');
			copyToClipboard(itemLink);

			alert('Link copied to clipboard: ' + itemLink);
		});

		function copyToClipboard(text) {
			var tempInput = $('<input>');
			$('body').append(tempInput);
			tempInput.val(text).select();
			document.execCommand('copy');
			tempInput.remove();
		}
		
		
	}); 
});
