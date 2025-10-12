jQuery(function ($) {
	$(document).ready(function() {	
		
    	$('.wpf-field-name-user_login input').removeAttr('maxlength');	
		
	  	var timer;

        $(".divBannerDropdownWrapper").hover(function() {
            clearTimeout(timer);
            $(".divUserProfileLinkDropDown").stop(true, true).slideDown();
        }, function() {
            timer = setTimeout(function() {
                $(".divUserProfileLinkDropDown").slideUp();
            }, 500);
        });

        $(".divUserProfileLinkDropDown").hover(function() {
            clearTimeout(timer);
        }, function() {
            timer = setTimeout(function() {
                $(".divUserProfileLinkDropDown").slideUp();
            }, 500);
        });
	});


});
