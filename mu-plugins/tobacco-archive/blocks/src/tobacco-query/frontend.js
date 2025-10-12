// Frontend JavaScript for Tobacco Query Block
document.addEventListener('DOMContentLoaded', function() {
	const queryBlocks = document.querySelectorAll('.tobacco-query-block');
	
	queryBlocks.forEach(block => {
		initTobaccoQueryBlock(block);
	});
});

function initTobaccoQueryBlock(block) {
	const filtersContainer = block.querySelector('.tobacco-query-filters');
	const gridContainer = block.querySelector('.tobacco-query-grid');
	const paginationContainer = block.querySelector('.tobacco-query-pagination');
	
	if (!filtersContainer || !gridContainer) return;
	
	let allTobaccoData = [];
	let filteredData = [];
	let currentPage = 1;
	
	const itemsPerPage = parseInt(block.dataset.itemsPerPage) || 12;
	const showPagination = block.dataset.showPagination === 'true';
	const showRatings = block.dataset.showRatings === 'true';
	const showProfile = block.dataset.showProfile === 'true';
	const showDescription = block.dataset.showDescription === 'true';
	
	// Load tobacco data
	loadTobaccoData();
	
	// Setup filter event listeners
	setupFilters();
	
	// Setup pagination
	if (showPagination) {
		setupPagination();
	}
	
	function loadTobaccoData() {
		// Get all tobacco items and parse their data
		const tobaccoItems = gridContainer.querySelectorAll('.tobacco-query-item');
		allTobaccoData = Array.from(tobaccoItems).map(item => {
			try {
				const data = JSON.parse(item.dataset.tobacco);
				// Convert comma-separated strings to arrays for easy filtering
				if (data.contents) {
					data.contentsArray = data.contents.split(',').map(s => s.trim().toLowerCase());
				}
				if (data.flavoring) {
					data.flavoringArray = data.flavoring.split(',').map(s => s.trim().toLowerCase());
				}
				return data;
			} catch (error) {
				console.error('Error parsing tobacco data:', error);
				return null;
			}
		}).filter(Boolean);
		
		filteredData = [...allTobaccoData];
		populateFilterOptions();
		renderGrid();
		updateResultsCount();
	}
	
	function populateFilterOptions() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		
		filterSelects.forEach(select => {
			const filterType = select.dataset.filter;
			const currentValue = select.value;
			
			// Clear existing options except the first one
			select.innerHTML = select.querySelector('option').outerHTML;
			
			// Get unique values for this filter
			let uniqueValues = [];
			if (filterType === 'contents') {
				uniqueValues = [...new Set(allTobaccoData.flatMap(item => item.contentsArray || []))].sort();
			} else if (filterType === 'flavoring') {
				uniqueValues = [...new Set(allTobaccoData.flatMap(item => item.flavoringArray || []))].sort();
			} else {
				uniqueValues = [...new Set(allTobaccoData.map(item => item[filterType]).filter(Boolean))].sort();
			}
			
			// Add options
			uniqueValues.forEach(value => {
				const option = document.createElement('option');
				option.value = value;
				option.textContent = value;
				select.appendChild(option);
			});
			
			// Restore previous selection
			select.value = currentValue;
		});
	}
	
	function setupFilters() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		
		filterSelects.forEach(select => {
			select.addEventListener('change', handleFilterChange);
		});
	}
	
	function handleFilterChange() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		const activeFilters = {};
		
		filterSelects.forEach(select => {
			if (select.value) {
				activeFilters[select.dataset.filter] = select.value.toLowerCase();
			}
		});
		
		// Filter data - simple show/hide based on filter values
		filteredData = allTobaccoData.filter(item => {
			return Object.entries(activeFilters).every(([key, value]) => {
				if (!value) return true; // No filter applied
				
				// Handle contents and flavoring arrays
				if (key === 'contents' && item.contentsArray) {
					return item.contentsArray.some(content => content.includes(value));
				}
				if (key === 'flavoring' && item.flavoringArray) {
					return item.flavoringArray.some(flavor => flavor.includes(value));
				}
				
				// Simple string matching for other fields
				const itemValue = item[key];
				if (!itemValue) return false;
				return itemValue.toLowerCase().includes(value);
			});
		});
		
		currentPage = 1;
		renderGrid();
		updateResultsCount();
		updatePagination();
	}
	
	function renderGrid() {
		const startIndex = (currentPage - 1) * itemsPerPage;
		const endIndex = startIndex + itemsPerPage;
		const pageData = filteredData.slice(startIndex, endIndex);
		
		gridContainer.innerHTML = '';
		
		if (pageData.length === 0) {
			gridContainer.innerHTML = '<div class="no-results">No tobacco reviews found matching your criteria.</div>';
			return;
		}
		
		pageData.forEach(tobacco => {
			const item = createTobaccoItem(tobacco);
			gridContainer.appendChild(item);
		});
	}
	
	function createTobaccoItem(tobacco) {
		const item = document.createElement('div');
		item.className = 'tobacco-query-item';
		
		let html = `
			<div class="tobacco-item-header">
				<h4 class="tobacco-item-title">
					<span class="brand-name">${escapeHtml(tobacco.brandName || '')}</span>
					<span class="blend-name">${escapeHtml(tobacco.blendName || '')}</span>
				</h4>
		`;
		
		if (showRatings && tobacco.overallRating > 0) {
			html += `
				<div class="tobacco-item-rating">
					${tobacco.overallRating}/4
					${tobacco.totalReviews > 0 ? `<span class="review-count">(${tobacco.totalReviews} ${tobacco.totalReviews === 1 ? 'review' : 'reviews'})</span>` : ''}
				</div>
			`;
		}
		
		html += '</div>';
		
		if (tobacco.imageUrl) {
			html += `
				<div class="tobacco-item-image">
					<img src="${escapeHtml(tobacco.imageUrl)}" alt="${escapeHtml(tobacco.brandName || '')} ${escapeHtml(tobacco.blendName || '')}" />
				</div>
			`;
		}
		
		html += '<div class="tobacco-item-details">';
		
		if (tobacco.series) {
			html += `<p><strong>Series:</strong> ${escapeHtml(tobacco.series)}</p>`;
		}
		if (tobacco.blendType) {
			html += `<p><strong>Type:</strong> ${escapeHtml(tobacco.blendType)}</p>`;
		}
		if (tobacco.contents) {
			html += `<p><strong>Contents:</strong> ${escapeHtml(tobacco.contents)}</p>`;
		}
		
		html += '</div>';
		
		if (showProfile && (tobacco.strength || tobacco.flavoringRating || tobacco.roomNote || tobacco.taste)) {
			html += `
				<div class="tobacco-item-profile">
					<h5>Profile</h5>
					<div class="profile-ratings">
			`;
			
			if (tobacco.strength) {
				html += `<div class="profile-rating"><strong>Strength:</strong> ${escapeHtml(tobacco.strength)}</div>`;
			}
			if (tobacco.flavoringRating) {
				html += `<div class="profile-rating"><strong>Flavoring:</strong> ${escapeHtml(tobacco.flavoringRating)}</div>`;
			}
			if (tobacco.roomNote) {
				html += `<div class="profile-rating"><strong>Room Note:</strong> ${escapeHtml(tobacco.roomNote)}</div>`;
			}
			if (tobacco.taste) {
				html += `<div class="profile-rating"><strong>Taste:</strong> ${escapeHtml(tobacco.taste)}</div>`;
			}
			
			html += '</div></div>';
		}
		
		if (showDescription && tobacco.description) {
			const shortDescription = tobacco.description.length > 150 ? 
				tobacco.description.substring(0, 150) + '...' : 
				tobacco.description;
			html += `<div class="tobacco-item-description"><p>${escapeHtml(shortDescription)}</p></div>`;
		}
		
		item.innerHTML = html;
		return item;
	}
	
	function setupPagination() {
		if (!paginationContainer) return;
		
		const prevBtn = paginationContainer.querySelector('.prev-btn');
		const nextBtn = paginationContainer.querySelector('.next-btn');
		
		if (prevBtn) {
			prevBtn.addEventListener('click', () => {
				if (currentPage > 1) {
					currentPage--;
					renderGrid();
					updatePagination();
				}
			});
		}
		
		if (nextBtn) {
			nextBtn.addEventListener('click', () => {
				const totalPages = Math.ceil(filteredData.length / itemsPerPage);
				if (currentPage < totalPages) {
					currentPage++;
					renderGrid();
					updatePagination();
				}
			});
		}
	}
	
	function updatePagination() {
		if (!paginationContainer) return;
		
		const totalPages = Math.ceil(filteredData.length / itemsPerPage);
		const prevBtn = paginationContainer.querySelector('.prev-btn');
		const nextBtn = paginationContainer.querySelector('.next-btn');
		const currentPageSpan = paginationContainer.querySelector('.current-page');
		const totalPagesSpan = paginationContainer.querySelector('.total-pages');
		
		if (prevBtn) {
			prevBtn.disabled = currentPage <= 1;
		}
		
		if (nextBtn) {
			nextBtn.disabled = currentPage >= totalPages;
		}
		
		if (currentPageSpan) {
			currentPageSpan.textContent = currentPage;
		}
		
		if (totalPagesSpan) {
			totalPagesSpan.textContent = totalPages;
		}
	}
	
	function updateResultsCount() {
		const resultsText = filtersContainer.querySelector('.results-text');
		if (resultsText) {
			resultsText.textContent = `Showing ${filteredData.length} of ${allTobaccoData.length} reviews`;
		}
	}
	
	function showError(message) {
		gridContainer.innerHTML = `<div class="error-message">${escapeHtml(message)}</div>`;
	}
	
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
}
