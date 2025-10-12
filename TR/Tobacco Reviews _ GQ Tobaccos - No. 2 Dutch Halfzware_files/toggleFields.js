jQuery(function ($) {
	$(document).ready(function($) {
		var toggleButton = $('.divMoreSearchFieldsToggle');
		var fieldsContainer = $('.divMoreSearchFieldsInputs');
		var icon = toggleButton.find('.fa');

		toggleButton.on('click', function() {
			fieldsContainer.slideToggle();
			fieldsContainer.toggleClass('open closed');
			icon.toggleClass('fa-plus-circle fa-minus-circle');
		});

		// Initial state based on presence of 'open' class
		if (fieldsContainer.hasClass('open')) {
			fieldsContainer.show();
			icon.addClass('fa-minus-circle').removeClass('fa-plus-circle');
		}
		
		// Handle checkbox click event
		$('.chkCheckboxFilterSearch').on('change', function() {
			var excludeSpan = $(this).siblings('.spanExclude');
			var excludedInput = $(this).siblings('.excludedValueInput');
			var excludeLabel = $(this).siblings('.spanCheckboxLabel');

			// If the checkbox was checked, remove excluded
			if ($(this).prop('checked')) {
				excludeLabel.removeClass('excluded');
				excludedInput.val('0'); 
			}
		});
     
		// Handle exclude span click event
		$('.spanExclude').on('click', function() {
			var checkbox = $(this).siblings('.chkCheckbox');
			var excludedInput = $(this).siblings('.excludedValueInput');
			var excludeLabel = $(this).siblings('.spanCheckboxLabel');

			if (excludedInput.val() === '0') {
				excludedInput.val('1'); // Exclude
			} else {
				excludedInput.val('0'); // Include
			}

			excludeLabel.toggleClass('excluded');
		   	checkbox.prop('checked', false);
		});
		
		// Handle exclude all click event
		$('.anchExcludeAll').on('click', function(e) {
			e.preventDefault();
			var checkboxes = $(this).closest('.divCheckboxContainer').find('.chkCheckboxFilterSearch');
			checkboxes.each(function() {
				var checkbox = $(this);
				var excludedInput = checkbox.siblings('.excludedValueInput');
				var excludeLabel = checkbox.siblings('.spanCheckboxLabel');
				if (!checkbox.prop('checked') && excludedInput.val() === '0') {
					excludedInput.val('1'); // Exclude
					excludeLabel.addClass('excluded');
				}
			});			
		});
   
	}); 
});
