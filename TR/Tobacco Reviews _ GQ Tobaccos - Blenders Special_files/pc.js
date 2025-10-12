jQuery(function ($) {
	$(document).ready(function() {
    // Function to get query parameters from URL
    function getQueryParams(url) {
        var queryParams = {};
        var queryString = url.split('?')[1];
        if (queryString) {
            var pairs = queryString.split('&');
            pairs.forEach(function(pair) {
                var keyValue = pair.split('=');
                queryParams[keyValue[0]] = keyValue[1];
            });
        }
        return queryParams;
    }

    // Function to update or add query parameter
    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return uri + separator + key + "=" + value;
        }
    }

    // Get current URL
    var currentURL = window.location.href;
    
    if (currentURL.indexOf('tobaccoreviews.com/login') !== -1) {
        // Get query parameters
        var queryParams = getQueryParams(currentURL);

        // Check if 'pcdttm' parameter exists and matches
        var pcdttm = queryParams['pcdttm'];
        var currentDateTime = new Date().toISOString().slice(0, 13).replace(/[-:]/g, '');
        if (!pcdttm || pcdttm !== currentDateTime) {
            // Add or update 'pcdttm' parameter
            currentURL = updateQueryStringParameter(currentURL, 'pcdttm', currentDateTime);
            window.location.href = currentURL;
        }
    }
	});
});