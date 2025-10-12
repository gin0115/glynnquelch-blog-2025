jQuery(function ($) {
	$(document).ready(function() {
		
		$(".datepicker").datepicker({
            changeYear: true, 
            yearRange: "2010:2030",
        });
		
		// post form like reviews on sort by ddl
		
		$("#chkShowRetailerVisible").on("change", function() {
			var val = $(this).val();
			if ($(this).is(":checked")) {
		  		$("#chkShowRetailer").val(val);
			} else {
		  		$("#chkShowRetailer").val(0);
			}
	  	});
		
		$(".btnApplyRetailerLink").on("click", function(e) {
			e.preventDefault(); 
			$("#frmBlendSearch").submit();
		});
		
		$(".anchModifyRetailerLink").on("click", function (e) {
			e.preventDefault();
			
			var parentWrapper = $(this).closest(".divRetailerControlSingleBlendWrapper");
			var linkInputField = parentWrapper.find(".txtRetailerLink");
			var operationLinkWrapper = parentWrapper.find(".divOperationLinkId");
			var operationLinkId = operationLinkWrapper.text();
      		var operationBlendId = parentWrapper.find(".divOperationBlendId").text();
      		var operationRetailerId = parentWrapper.find(".divOperationRetailerId").text();
			
			var inputValue = linkInputField.val();
			
      		var retailer_action = ''; 
			
			if ($(this).hasClass('anchAddSaveRetailerLink')) {
				retailer_action = 'update_link';
			} else if ($(this).hasClass('anchRemoveRetailerLink') && inputValue) {
				retailer_action = 'remove_link';
			}
			if (retailer_action) {
				$.ajax({
					url: customAjax.ajaxurl,
					method: 'POST',
					dataType: 'json',
					data: {
						action: 'update_retailer',
						retailer_action: retailer_action,
						linkId: operationLinkId,
						blendId: operationBlendId,
						retailerId: operationRetailerId,
						url: inputValue,
					},
					success: function(response) {
						console.log(response);
						var actionTaken = response.result; 
						if (actionTaken === 'set inactive') {
							linkInputField.val('');
							var activeLinks = parseInt($(".spanRetailerActiveLinksLabel").text());
          					var remainingLinks = parseInt($(".spanRemainingLinksLabel").text());
							if (!isNaN(activeLinks) && !isNaN(remainingLinks)) {
								$(".spanRetailerActiveLinksLabel").text(activeLinks - 1);
								$(".spanRemainingLinksLabel").text(remainingLinks + 1);
							 }
						} else if (actionTaken === 'inserted') {
							var linkId = response.linkId; 
							operationLinkWrapper.html(linkId);
							var activeLinks = parseInt($(".spanRetailerActiveLinksLabel").text());
          					var remainingLinks = parseInt($(".spanRemainingLinksLabel").text());
							if (!isNaN(activeLinks) && !isNaN(remainingLinks)) {
								$(".spanRetailerActiveLinksLabel").text(activeLinks + 1);
								$(".spanRemainingLinksLabel").text(remainingLinks - 1);
							 }
						} else if (actionTaken === 'updated') {
							var activeLinks = parseInt($(".spanRetailerActiveLinksLabel").text());
          					var remainingLinks = parseInt($(".spanRemainingLinksLabel").text());
							if (!isNaN(activeLinks) && !isNaN(remainingLinks)) {
								$(".spanRetailerActiveLinksLabel").text(activeLinks + 1);
								$(".spanRemainingLinksLabel").text(remainingLinks - 1);
							 }
						}
					},
					error: function(xhr, status, error) {
						console.log('response error: '+error);
					}
				});	
			}	
		});	
	});
});

