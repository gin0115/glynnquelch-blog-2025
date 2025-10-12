jQuery(function ($) {
	$(document).ready(function() {
		
		const SITE_URL = 'https://www.tobaccoreviews.com/';
		// Variables to keep track of pagination
        const hitsPerPage = 20; 
		
		function buildBrandBlendURL(path, partToFormat) {
		  let formattedName = partToFormat.trim().toLowerCase().replace(/\s+/g, '-');
		  formattedName = formattedName.replace(/[^a-z0-9_\-]+/g, '');
		  formattedName = formattedName.replace(/-{2,}/g, '-');
		  let link = SITE_URL+path+'/'+formattedName;
		//console.log(link);
		  return link;
		}
		function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

    	const searchClient = algoliasearch('4HOY9H034V', 'c36743e2f29dcb2e06b752cc4d4fcfbb');
	
		const tableWrapper = $('.divTableWrapper');
		const searchForm = $('#frmBlendAlgoliaSearch');
		const searchInput = $('#blendSearchInput');
		const resultsContainer = $('.algoliaResultsContainer');
		const paginationContainer = $('.algoliaPaginationContainer');

		function performSearch(query) {
			resultsContainer.empty();
			paginationContainer.empty();
            var currentPage = 1;
			var pageNumberFromURL = getUrlParameter('pagenumber');
			if (pageNumberFromURL) {
				currentPage = pageNumberFromURL;			
			}			
			var currentPageIndex = parseInt(currentPage - 1);
			searchClient.search([
			  {
				indexName: 'prod_202312011124',
				query: query,
				page: currentPageIndex, 
                hitsPerPage: hitsPerPage, 
			  }
			]).then(function (response) {
				console.log(response);
				const searchResults = response.results[0].hits;
				//console.log(searchResults);
				// Calculate the total number of pages based on the total hits and hitsPerPage
				const nbHits = parseInt(response.results[0].nbHits);
				//alert('found results = '+nbHits);
				var totalPages = 0;
				if (nbHits > 0) {
					totalPages = Math.ceil(response.results[0].nbHits / hitsPerPage);
				} 
				if (totalPages > 0) {
					searchResults.forEach(function(result) {
					const Id = result.Id;
					const brand = result.Brand;
					const blend = result.Blend;
					const reviewCount = result.ReviewCount;
					const meanRating = parseFloat(result.MeanRating);
					const numberOfSigFigs = 2; 
					const meanRatingFormatted = meanRating.toFixed(numberOfSigFigs);
					if (isNaN(meanRatingFormatted)) {
						var meanRatingLabel = '-';
					} else {
						var meanRatingLabel = meanRatingFormatted;
					}
					var starsHTML = '';
					var meanRatingForStars = meanRating.toFixed(1);
					if (meanRatingForStars > 0) {
						starsHTML = generateStars(meanRatingForStars, 4, 'mean');
					} else {
						starsHTML = '--';
					}
						
	
					let blendType = result.BlendType;

					if (blendType === null) {
						blendType = 'Unknown';
					}
					
					var blendLink = buildBrandBlendURL('blend/'+Id, brand+'-'+blend);
					blendLink += '/?retSearchTerm='+query;
					const nameColumnValue = '<a href="'+blendLink+'" target="" class="aBlendDetail aFullTDLink"><span>'+brand+' '+blend+'</span></a>';
					const reviewColumnValue = '<a href="'+blendLink+'" target="" class="aBlendDetail aFullTDLink"><span>'+reviewCount+'</span></a>';
					var ratingColumnValue = '<a href="'+blendLink+'" target="" class="aBlendDetail aFullTDLink">';
					ratingColumnValue += '<span class="spanDoubleDataColumn spanDoubleDataColumnLeft"></span>';
					ratingColumnValue += '<span class="spanDoubleDataColumn spanDoubleDataColumnRight">'+meanRatingLabel+'</span>';
					ratingColumnValue += '</a>';
					const blendTypeColumnValue = '<a href="'+blendLink+'" target="" class="aBlendDetail aFullTDLink"><span>'+blendType+'</span></a>';
					var trHTML = '<tr scope="row" class="blend trTableRow blendSearchIndexRow"><td>'+nameColumnValue+'</td><td>'+reviewColumnValue+'</td><td>'+ratingColumnValue+'</td><td>'+blendTypeColumnValue+'</td></tr>';
					resultsContainer.append(trHTML);
					});
					if (totalPages > 1) {
						var paginationHTML = '<div class="divPagionationWrapper"><ul class="pagination">';
						var baseLink = SITE_URL+'search';
						// Generate << 
						if (currentPage > 1) {
							paginationHTML += '<li><a href="'+baseLink+'/?pagenumber=1&searchTerm='+query+'"><i class="fa-solid fa-angles-left"></i></a></li>';
						}
						
						// Generate page numbers
						var paginationLinks = '';

						for (let i = 1; i <= totalPages; i++) {
							if (i === totalPages || Math.abs(i - currentPage) <= 3) {
								const isActive = i === currentPage ? 'current' : '';
								paginationLinks += '<li><a href="'+baseLink+'/?pagenumber='+i+'&searchTerm='+query+'" class="'+isActive+'">'+i+'</a></li>';
							} else if (Math.abs(i - currentPage) === 4) {
								paginationLinks += '<li><span class="ellipsis">...</span></li>';
							}
						}
						
						paginationHTML += paginationLinks;					
						
						// Generate ">>"
						if (currentPage < totalPages) {
							paginationHTML += '<li><a href="'+baseLink+'/?pagenumber='+totalPages+'&searchTerm='+query+'"><i class="fa-solid fa-angles-right"></i></a></li>';
						}
							
						paginationHTML += '</ul></div>';
						paginationContainer.html(paginationHTML);
					}
					
					tableWrapper.removeClass('divBlendTableWrapperNoResults');
				} else {
					resultsContainer.html('<tr colspan="4"><td><span class="spanUnableToFind">We were unable to find any blends matching your search term. </span></td></tr>');
					tableWrapper.addClass('divBlendTableWrapperNoResults');
				}
	

			}).catch(function (error) {
				console.error('Algolia search error:', error);
			});
		}
		
		
		
        // searching with search term in qsa
		const searchTermFromURL = getUrlParameter('searchTerm');
		if (searchTermFromURL) {
			searchInput.val(searchTermFromURL); //RRC sanitize
			const query = searchInput.val();
			performSearch(query);	
		}
		
		
		// searching on search form submit
		searchForm.on('submit', function (e) {
			e.preventDefault();
			const query = searchInput.val();
			performSearch(query);	
		});
		
		function generateStars(rating, maxRating, method = "standard") {
			let html = '';

			if (!rating) {
				return '';
			}

			if (method === 'standard') {

			} else if (method === 'mean') {
				const rounded = Math.round(rating * 2) / 2;

				if (rounded % 1 === 0) {
					html = generateStars(rounded, maxRating);
				} else {
					const emptyStars = Math.floor(maxRating - rounded);
					const wholeNumbers = rounded - 0.5;

					for (let i = 0; i < wholeNumbers; i++) {
						html += buildStarSVG();
					}

					html += buildHalfStarSVG();

					if (emptyStars) {
						for (let i = 0; i < emptyStars; i++) {
							html += buildEmptyStarSVG();
						}
					}
				}
			}

			return html;
		}

		const FILLED_STAR_COLOR = 'rgba(168,140,92,1)';
		const EMPTY_STAR_STROKE_COLOR = 'rgba(168,140,92,1)';
		const EMPTY_STAR_COLOR = '#FFF';

		function buildStarSVG() {
			const html = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="1em" width="1em"><polygon id="star" class="polyStar" points="50,10 61.18,35.36 90.14,35.36 66.36,54.64 77.54,80 50,65.72 22.46,80 33.64,54.64 9.86,35.36 38.82,35.36"></polygon></svg>';
			return html;
		}

		function buildEmptyStarSVG() {
			const html = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="1em" width="1em"><polygon id="star-empty" class="polyStar" points="50,10 61.18,3536 90.14,35.36 66.36,54.64 77.54,80 50,65.72 22.46,80 33.64,54.64 9.86,35.36 38.82,35.36"></polygon></svg>';
			return html;
		}

		function buildHalfStarSVG() {
			const html = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="1em" width="1em"><polygon id="star-left" class="polyStar" points="50,10 50,65.72 22.46,80 33.64,54.64 9.86,35.36 38.82,35.36" fill="'+FILLED_STAR_COLOR+'" stroke="'+EMPTY_STAR_STROKE_COLOR+'"></polygon><polygon id="star-right" class="polyStar" points="50,10 61.18,35.36 90.14,35.36 66.36,54.64 77.54,80 50,65.72" fill="none" stroke="'+EMPTY_STAR_STROKE_COLOR+'"></polygon></svg>';
			return html;
		}

	});
	
	

});

/*searchClient.search({ indexName: 'tobacc', query })
				.then(function (results) {
				console.log('results:'+results);
			})
				.catch(function (err) {
				console.error('error:'+err);
			});
			console.log('e:', e);*/

/*
 * {
    "hits": [
        {
            "Id": "1291",
            "Brand": "The Smoker",
            "Blend": "New York, New York",
            "Series": "The Smoker Ltd.",
            "Blender": "Mel Feldman",
            "Manufacturer": "Sutliff Tobacco Company",
            "TinDescription": "A very unique Virginia/perique blend consisting of four different types of Virginia (bright, stoved black and two types of red), plus a hint of sweet and tangy perique and made even more interesting by the addition of excellent Turkish tobaccos. Expect toasty sweetness with nice complexity without being heavy.",
            "Country": "US",
            "BlendType": "Virginia/Perique",
            "Contents": "Oriental/Turkish, Perique, Virginia",
            "Flavors": "",
            "Cures": "Air Cured",
            "Cut": "Ribbon",
            "Packaging": "2 ounce tin, 4 ounce tin",
            "Notes": "",
            "ImageFile": "a1318ebe97569cd97b88.png",
            "LastReviewOn": "2014-09-28 13:26:00.000",
            "ReviewCount": "5",
            "Rating1Count": "0",
            "Rating2Count": "2",
            "Rating3Count": "1",
            "Rating4Count": "2",
            "AvgRating": "4",
            "AvgStrength": "5",
            "AvgFlavorStrength": "0",
            "AvgRoomNote": "4",
            "AvgTaste": "5",
            "ProductionStatus": "No longer in production",
            "IsApproved": "1",
            "CreatedBy": "0",
            "CreatedOn": "2001-02-24 00:00:00.000",
            "UpdatedOn": "2019-12-06 20:11:36.713",
            "RetailerCount": "0",
            "FavoriteCount": "0",
            "ViewCount": "0",
            "TrialByFireUrl": "",
            "MeanRating": "3",
            "objectID": "1f9c79eb65600a_dashboard_generated_id",
            "_highlightResult": {
                "Blend": {
                    "value": "<em>New</em> <em>York</em>, <em>New</em> <em>York</em>",
                    "matchLevel": "full",
                    "fullyHighlighted": true,
                    "matchedWords": [
                        "new",
                        "york"
                    ]
                }
            }
        },
      
    ],
    "nbHits": 7,
    "page": 0,
    "nbPages": 1,
    "hitsPerPage": 20,
    "exhaustiveNbHits": true,
    "exhaustiveTypo": true,
    "exhaustive": {
        "nbHits": true,
        "typo": true
    },
    "query": "new york",
    "params": "query=new+york&",
    "index": "tobacc",
    "renderingContent": {},
    "processingTimeMS": 1,
    "processingTimingsMS": {
        "_request": {
            "roundTrip": 27
        }
    },
    "serverTimeMS": 1
}
 * 
 * */
