jQuery(function ($) {
	$(document).ready(function() {
	  // Function to handle the show/hide behavior and animation
	  function toggleManualBrandInput() {
		var selectedValue = $('#brandInput').val();
		var divManualBrandWrapper = $('.divManualBrandWrapper');
		var brandInputText = $('#brandInputText');

		if (selectedValue === 'new') {
		  divManualBrandWrapper.slideDown();
		  brandInputText.prop('required', true);
		} else {
		  divManualBrandWrapper.slideUp();
		  brandInputText.prop('required', false);
		}
	  }

	  // Call the function when the page loads
	  toggleManualBrandInput();

	  // Call the function whenever the select changes
	  $('#brandInput').change(function () {
		toggleManualBrandInput();
	  });
		
	  // Function to populate the Blender textbox when a Blender link is clicked
	  function populateBlenderTextBox(blenderName) {
		var blenderInput = $('#blenderInput');
		var currentBlender = blenderInput.val();

		// Remove square brackets from the Blender name
		var blenderNameWithoutBrackets = blenderName.replace(/\[|\]/g, '').trim();

		if (!currentBlender) {
		  blenderInput.val(blenderNameWithoutBrackets);
		} else {
		  // Check if the Blender name is already in the textbox (with or without brackets)
		  var blenderRegex = new RegExp('(^|,\\s?)' + blenderNameWithoutBrackets + '(\\s?,|$)', 'i');
		  if (!blenderRegex.test(currentBlender)) {
			blenderInput.val(currentBlender + ', ' + blenderNameWithoutBrackets);
		  }
		}
	  }

	  // Function to handle showing/hiding Blender links based on the selected brand
	  function showHideBlenderLinks(selectedBrand) {
		var blenderLinks = $('#blenderLinks');
		blenderLinks.children().hide(); // Hide all Blender links

		if (selectedBrand === '') {
		  blenderLinks.hide(); // Hide the Blender links wrapper when no brand is selected
		} else {
		  blenderLinks.show(); // Show the Blender links wrapper if there are associated Blender links
		  $('.divBlenderByBrandWrapper').hide(); // Hide all Blender wrappers
		  $('#brandBlenderWrapper-' + selectedBrand).show(); // Show Blender links for the selected brand
		}
	  }
	  
	 // Function to swap between suggested values when moderating
	 $('.anchSwapModeratedValue').on('click', function(event) {
		 event.preventDefault();
		 var clickedOn = $(this);
		 var typeOfInput = '';
		 		 
		 var parentDivWrapper = clickedOn.closest('.divSuggestedValueWrapper');
		 var previousFormWrapper = parentDivWrapper.prev('div');
		 var previousTextInput = previousFormWrapper.find('.txtBlendEditField');
		 if (parentDivWrapper.hasClass('divSwappingTextInput')) {
			 typeOfInput = 'text';
		 }
		 
		 var originalValue = parentDivWrapper.find('.divSuggestedOptionsSwapWrapper').find('.divOriginalValue').html();
		 var suggestedValue = parentDivWrapper.find('.divSuggestedOptionsSwapWrapper').find('.divSuggestedValue').html();
		 if (typeOfInput == 'text') {
			 if (clickedOn.hasClass('anchSwapModeratedReplacement')) {
				previousTextInput.val(suggestedValue);
			 } else if (clickedOn.hasClass('anchSwapModeratedOriginal')) {
				previousTextInput.val(originalValue);	
			 } 	 
		 }		 
	 });

		// may get more complex when we get to checkboxes, dropdowns, etc..
		
	  // Call the function on page load to set up the initial Blender links based on the selected brand
	  var selectedBrand = $('#brandInput').val();
	  showHideBlenderLinks(selectedBrand);

	  // Call the function when the brand select changes
	  $('#brandInput').change(function() {
		var selectedBrand = $(this).val();
		showHideBlenderLinks(selectedBrand);
	  });

	  // Event delegation for the Blender links
	  $('#blenderLinks').on('click', '.anchFillBlenderName', function(event) {
		event.preventDefault(); // Prevent the link from navigating
		var blenderName = $(this).text().trim(); // Get the Blender name from the clicked link
		populateBlenderTextBox(blenderName); // Populate the Blender textbox
	  });
		
	function showImagePreview(input) {
		if (input.files && input.files[0]) {
		  const reader = new FileReader();
		  reader.onload = function (e) {
			$('#imagePreview').attr('src', e.target.result);
		  };
		  reader.readAsDataURL(input.files[0]);
		}
	  }
	  $('#imageInput').on('change', function () {
		showImagePreview(this);
	  });
		
		$('.btnDisapprove').on('click', function (event)  {		
            if (confirm('Are you sure you want to reject and delete this update?')) {
            	return true;
            } else {
				event.preventDefault();
				return false;
            }
         });
		
	  let formChanged = false; // Handle form changes
	  function handleFormChange() { 
		  formChanged = true; 
	  }
	  $('.frmAddEditBlend, .frmAddEditReview, .frmSettings, .frmTestForChanges').on('change', 'input, select, textarea', handleFormChange);
	  function showLeavePrompt() { 
		  return "You have unsaved changes. Are you sure you want to leave?"; 
	  }
	  function checkUnsavedChanges() {
      	if (formChanged) {
      	  return showLeavePrompt();
    	}
  	  }
		// Function to remove the beforeunload event listener temporarily
		function removeBeforeUnloadListener() {
			$(window).off('beforeunload');
		}
		// Function to reattach the beforeunload event listener
		function reattachBeforeUnloadListener() {
		  $(window).on('beforeunload', function () {
			const promptMessage = checkUnsavedChanges();
			if (promptMessage) {
			  return promptMessage;
			}
		  });
		}
		// Attach event listener to the beforeunload event to trigger the prompt
	  $(window).on('beforeunload', function () {
		const promptMessage = checkUnsavedChanges();
		if (promptMessage) {
		  return promptMessage;
		}
	  });
		
		$('.frmAddEditReview, .frmAddEditBlend, .frmSettings').on('submit', function () {
		  removeBeforeUnloadListener();
			$('.divHideOnSuccess').hide();
			$('.divShowOnSubmit').show();
		});
		
		
		
		$('#cancelLink').on('click', function (event) { //browser is handling this
  event.preventDefault();
  const promptMessage = checkUnsavedChanges();
  if (promptMessage) {
    const confirmLeave = confirm(promptMessage);
    if (confirmLeave) {
      // If the user confirms, go back or refresh the page
      if (document.referrer.includes(window.location.hostname)) {
        history.back(); // Go back to the previous page on your website
      } else {
        location.reload(); // Refresh the current page if not on your website
      }
    }
  } else {
    // If there are no unsaved changes, go back or refresh the page directly
    if (document.referrer.includes(window.location.hostname)) {
      history.back(); // Go back to the previous page on your website
    } else {
      location.reload(); // Refresh the current page if not on your website
    }
  }
});
	});
});
