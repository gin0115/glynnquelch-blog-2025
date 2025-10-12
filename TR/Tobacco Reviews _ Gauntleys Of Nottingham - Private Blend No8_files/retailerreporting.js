jQuery(function ($) {
	$(document).ready(function() {
		
		// 	wp_localize_script('retailerreporting', 'customAjax', array('ajaxurl' => admin_url('admin-ajax.php'),'security' => wp_create_nonce('retailer_nonce')));

		$('#download-report').on('click', function(e) {
			e.preventDefault(); 
			var selectedMonth = $('#month').val();
			var selectedYear = $('#year').val();

			// AJAX request
			$.ajax({
				url: customAjax.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'retailer_reporting',
					month: selectedMonth,
					year: selectedYear,
					security: customAjax.security, 
				},
				success: function(response) {
					console.log(response);
					// Create a hidden anchor element
					const a = document.createElement('a');
					a.href = response.file_path;
					a.download = 'file_name.xlsx';
					a.style.display = 'none';

					// Append the anchor to the document body
					document.body.appendChild(a);

					// Trigger a click event on the anchor to initiate the download
					a.click();

					// Remove the anchor element from the document
					document.body.removeChild(a);
				
				},
				error: function(xhr, status, error) {
					console.log('response error: '+error);
				}
			});	
			
		});			
		
	});
});

