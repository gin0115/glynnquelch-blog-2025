jQuery(function ($) {
	$(document).ready(function($) {
		var totalCount = $('#totalCount').text();
		$('#blends-count').text(totalCount);
		$('#filter-rating a').on('click', function(e) {
			e.preventDefault();
			var filterRating = $(this).data('filter');
			$('.blend').hide();
			$('.' + filterRating).show();

			var filterCount = $('#' + filterRating).text();
			$('#blends-count').text(filterCount);
		});
		$('#filter-reset').on('click', function(e) {
			e.preventDefault();
			$('#blends-count').text(totalCount);
			$('.blend').show();
		});
		$('.divClickToShowHide').click(function() {
			var collapsibleDiv = $(this).next('.divCollapsible');
			var plusIcon = $(this).find('.fa-plus');
        	var minusIcon = $(this).find('.fa-minus');

			if (collapsibleDiv.hasClass('closed')) {
				collapsibleDiv.removeClass('closed').addClass('open');
				collapsibleDiv.slideDown(300); 
				plusIcon.hide();
            	minusIcon.show();
			} else {
				collapsibleDiv.removeClass('open').addClass('closed');
				collapsibleDiv.slideUp(300); 
				plusIcon.show();
            	minusIcon.hide();
			}
		});
		
	}); 
});
