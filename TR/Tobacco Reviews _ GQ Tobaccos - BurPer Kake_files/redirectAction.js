jQuery(function ($) {
	$(document).ready(function() {
		
		var blendDisplayCountDropdown = $('#switch-blend-display-count');
		blendDisplayCountDropdown.on('change', function() {
			$("#frmBlendSearch").submit();
		});

		var reviewSortByDropdown = $('#filter-review-blend'); 
		var currentOption = reviewSortByDropdown.val();

		reviewSortByDropdown.on('change', function() {
			var selectedOption = $(this).val();
			var currentUrl = window.location.href;
			if (selectedOption !== getQueryStringValue('orderBy', currentUrl)) {
				var newParams = {
					orderBy: selectedOption,
					pagenumber: 1 
				};
				
				var existingReviewParam = getQueryStringValue('review', currentUrl);		
				if (existingReviewParam !== null) {
					currentUrl = removeQueryString('review', currentUrl);
				}
			
				var newUrl = updateQueryString(newParams, currentUrl);
				window.location.href = newUrl;
			}
		});
		
		var userSortByDropdown = $('#filter-user'); 
		var currentOption = userSortByDropdown.val();

		userSortByDropdown.on('change', function() {
			var selectedOption = $(this).val();
			var currentUrl = window.location.href;
			if (selectedOption !== getQueryStringValue('orderBy', currentUrl)) {
				var newParams = {
					orderBy: selectedOption,
					pagenumber: 1 
				};
				
				var existingReviewParam = getQueryStringValue('user', currentUrl);		
				if (existingReviewParam !== null) {
					currentUrl = removeQueryString('user', currentUrl);
				}
			
				var newUrl = updateQueryString(newParams, currentUrl);
				window.location.href = newUrl;
			}
		});
		
		function getQueryStringValue(key, url) {
			var queryParams = new URLSearchParams(url.split('?')[1]);
			return queryParams.get(key);
		}

		function updateQueryString(newParams, url) {
			var queryParams = new URLSearchParams(url.split('?')[1]);

			for (var key in newParams) {
				if (newParams.hasOwnProperty(key)) {
					queryParams.set(key, newParams[key]);
				}
			}

			var newUrl = url.split('?')[0] + '/?' + queryParams.toString();
			return newUrl;
		}
		
		function removeQueryString(key, url) {
			var urlParts = url.split('?');
			if (urlParts.length >= 2) {
				var prefix = encodeURIComponent(key) + '=';
				var queryString = urlParts[1];
				var params = queryString.split('&');

				for (var i = params.length - 1; i >= 0; i--) {
					if (params[i].lastIndexOf(prefix, 0) !== -1) {
						params.splice(i, 1);
					}
				}

				url = urlParts[0] + (params.length > 0 ? '?' + params.join('&') : '');
			}
			return url;
		}
	});
});
