jQuery(function ($) {
	$(document).ready(function() {
	  if ($('.divScrollTo').length > 0) {
		var targetOffset = $('.divScrollTo').offset().top - 40;

		$('html, body').animate({
		  scrollTop: targetOffset
		}, 400, function() {
		  $('.divScrollTo').addClass('highlighted');

		  setTimeout(function() {
			$('.divScrollTo').removeClass('highlighted');
			setTimeout(function() {
			  $('.divScrollTo').removeAttr('style');
			}, 500);
		  }, 1500);
		});
	  }
		
// 		var $retailDiv = $('.divRetailCurrentStatsWrapper');
// 		var divOffsetTop = $retailDiv.offset().top;
//     	var scrollPastDistance = 60; 

// 		$(window).scroll(function() {
// 			var scrollTop = $(this).scrollTop();

// 			if (scrollTop > divOffsetTop - scrollPastDistance) {
// 				$retailDiv.addClass('fixed');
// 			} else {
// 				$retailDiv.removeClass('fixed');
// 			}
// 		});

	});


});