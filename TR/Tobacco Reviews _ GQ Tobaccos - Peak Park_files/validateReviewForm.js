jQuery(function ($) {
	$(document).ready(function() {
	  $('#frmCreateReview').submit(function(event) {
		event.preventDefault(); // Prevent the form from submitting

		var isValid = true; // Flag to track form validity
		var errorMessages = []; // Array to store error messages

		// Validation for dropdowns
		$('select').each(function() {
		  var selectedValue = $(this).val();
		  if (selectedValue === 'false') {
			isValid = false;
			var dropdownName = $(this).attr('id');
			var errorMessage = 'Please select an option for ' + dropdownName;
			errorMessages.push(errorMessage);
		  }
		});

		// Validation for textarea
		
		//var notesContent = $('#notes').val();
		//if (notesContent.trim().length < 25) {
		//  isValid = false;
		//  errorMessages.push('Notes must contain at least 25 words');
		//}

		// Display error messages if any
		if (!isValid) {
		  var errorHtml = '<div class="alert alert-danger">';
		  errorHtml += '<ul>';
		  errorMessages.forEach(function(message) {
			errorHtml += '<li>' + message + '</li>';
		  });
		  errorHtml += '</ul>';
		  errorHtml += '</div>';
		  $('#errorContainer').html(errorHtml);
		} else {
		  // Form is valid, submit it
		  $('#frmCreateReview')[0].submit();
		}
	  });
});
	});