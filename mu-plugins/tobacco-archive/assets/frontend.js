console.log('Tobacco Archive frontend.js loaded');

document.addEventListener('DOMContentLoaded', () => {
	const queryBlocks = document.querySelectorAll('.tobacco-query-block');
	
	queryBlocks.forEach(block => {
		initTobaccoQueryBlock(block);
	});
});

function initTobaccoQueryBlock(block) {
	const filtersContainer = block.querySelector('.tobacco-query-filters');
	const gridContainer = block.querySelector('.tobacco-query-grid');
	
	if (!filtersContainer || !gridContainer) {
		// Just handle modal clicks if no filters
		setupModalClicks(block);
		return;
	}
	
	let allTobaccoData = [];
	let filteredData = [];
	let activeFilters = {};
	
	// Load tobacco data
	loadTobaccoData();
	
	// Setup filter event listeners
	setupFilters();
	
	// Setup clear all button
	setupClearAllButton();
	
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
		renderGrid();
		updateResultsCount();
	}
	
	function setupFilters() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		
		filterSelects.forEach(select => {
			select.addEventListener('change', handleFilterChange);
		});
	}
	
	function setupClearAllButton() {
		const clearAllBtn = filtersContainer.querySelector('.clear-all-btn');
		if (clearAllBtn) {
			clearAllBtn.addEventListener('click', clearAllFilters);
		}
	}
	
	function handleFilterChange() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		activeFilters = {};
		
		filterSelects.forEach(select => {
			if (select.value) {
				activeFilters[select.dataset.filter] = select.value.toLowerCase();
			}
		});
		
		// Filter data - single select filtering
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
		
		updateFilterChips();
		renderGrid();
		updateResultsCount();
	}
	
	function updateFilterChips() {
		const chipsContainer = filtersContainer.querySelector('.filter-chips');
		const chipsContainerWrapper = filtersContainer.querySelector('.active-filters');
		
		if (!chipsContainer) return;
		
		// Clear existing chips
		chipsContainer.innerHTML = '';
		
		// Check if we have any active filters
		const hasActiveFilters = Object.keys(activeFilters).length > 0;
		chipsContainerWrapper.style.display = hasActiveFilters ? 'flex' : 'none';
		
		// Create chips for each active filter
		Object.entries(activeFilters).forEach(([filterType, value]) => {
			const chip = createFilterChip(filterType, value);
			chipsContainer.appendChild(chip);
		});
	}
	
	function createFilterChip(filterType, value) {
		const chip = document.createElement('div');
		chip.className = 'filter-chip';
		chip.dataset.filterType = filterType;
		chip.dataset.value = value;
		
		const filterLabel = getFilterLabel(filterType);
		chip.innerHTML = `
			<span class="chip-label">${filterLabel}: ${value}</span>
			<button type="button" class="chip-remove" aria-label="Remove filter">×</button>
		`;
		
		// Add click handler to remove chip
		const removeBtn = chip.querySelector('.chip-remove');
		removeBtn.addEventListener('click', (e) => {
			e.preventDefault();
			removeFilterChip(filterType, value);
		});
		
		return chip;
	}
	
	function getFilterLabel(filterType) {
		const labels = {
			'brandName': 'Brand',
			'blendType': 'Type',
			'contents': 'Content',
			'manufacturedBy': 'Manufacturer',
			'series': 'Series',
			'flavoring': 'Flavoring'
		};
		return labels[filterType] || filterType;
	}
	
	function removeFilterChip(filterType, value) {
		const select = filtersContainer.querySelector(`[data-filter="${filterType}"]`);
		if (select) {
			select.value = '';
			handleFilterChange();
		}
	}
	
	function clearAllFilters() {
		const filterSelects = filtersContainer.querySelectorAll('.filter-select');
		
		filterSelects.forEach(select => {
			select.value = '';
		});
		
		handleFilterChange();
	}
	
	function renderGrid() {
		const tobaccoItems = gridContainer.querySelectorAll('.tobacco-query-item');
		
		tobaccoItems.forEach((item, index) => {
			const tobaccoData = allTobaccoData[index];
			const shouldShow = filteredData.includes(tobaccoData);
			
<<<<<<< HEAD
			item.style.display = shouldShow ? 'flex' : 'none';
=======
			item.style.display = shouldShow ? 'block' : 'none';
>>>>>>> main
		});
	}
	
	function updateResultsCount() {
		const resultsText = filtersContainer.querySelector('.results-text');
		if (resultsText) {
			resultsText.textContent = `Showing ${filteredData.length} of ${allTobaccoData.length} reviews`;
		}
	}
	
	// Setup modal clicks
	setupModalClicks(block);
}

function setupModalClicks(block) {
	const tobaccoItems = block.querySelectorAll('.tobacco-query-item');
	
	tobaccoItems.forEach((item, index) => {
		item.style.cursor = 'pointer';
		
		item.addEventListener('click', (e) => {
			try {
				const tobaccoData = JSON.parse(item.dataset.tobacco);
				openTobaccoModal(tobaccoData);
			} catch (error) {
				console.error('Error parsing tobacco data:', error);
			}
		});
	});
}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function openTobaccoModal(tobacco) {
		let modal = document.getElementById('tobacco-modal');
		if (!modal) {
			modal = createModal();
			document.body.appendChild(modal);
		}

		populateModal(modal, tobacco);
		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}

	function createModal() {
		const modal = document.createElement('div');
		modal.id = 'tobacco-modal';
		modal.className = 'tobacco-modal';
		modal.innerHTML = `
			<div class="tobacco-modal-overlay"></div>
			<div class="tobacco-modal-content">
				<button class="tobacco-modal-close" type="button">&times;</button>
				<div class="tobacco-modal-body"></div>
			</div>
		`;

		// Close button event
		const closeBtn = modal.querySelector('.tobacco-modal-close');
		closeBtn.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			console.log('Close button clicked');
			closeModal();
		});
		
		// Also add mousedown event to ensure it works
		closeBtn.addEventListener('mousedown', (e) => {
			e.preventDefault();
			e.stopPropagation();
		});

		// Overlay click event
		const overlay = modal.querySelector('.tobacco-modal-overlay');
		overlay.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			console.log('Overlay clicked');
			closeModal();
		});

		// Escape key event
		const escapeHandler = function(e) {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				console.log('Escape key pressed');
				closeModal();
				document.removeEventListener('keydown', escapeHandler);
			}
		};
		document.addEventListener('keydown', escapeHandler);

		return modal;
	}

	function populateModal(modal, tobacco) {
		const modalBody = modal.querySelector('.tobacco-modal-body');
		
		let html = `
			<div class="tobacco-modal-header">
				<h2 class="tobacco-modal-title">
					<span class="brand-name">${escapeHtml(tobacco.brandName || '')}</span>
					<span class="blend-name">${escapeHtml(tobacco.blendName || '')}</span>
				</h2>
		`;
		
		if (tobacco.overallRating > 0) {
			html += `
				<div class="tobacco-modal-rating">
					Rating: ${tobacco.overallRating}/4
					${tobacco.totalReviews > 0 ? `<span>from ${tobacco.totalReviews} ${tobacco.totalReviews === 1 ? 'review' : 'reviews'}</span>` : ''}
				</div>
			`;
		}
		
		html += '</div>';
		
		html += '<div class="tobacco-modal-main">';
		html += '<div class="tobacco-modal-left">';
		html += '<div class="tobacco-modal-details">';
		
		if (tobacco.series) {
			html += `<p><strong>Series:</strong> ${escapeHtml(tobacco.series)}</p>`;
		}
		if (tobacco.blendedBy) {
			html += `<p><strong>Blended By:</strong> ${escapeHtml(tobacco.blendedBy)}</p>`;
		}
		if (tobacco.manufacturedBy) {
			html += `<p><strong>Manufactured By:</strong> ${escapeHtml(tobacco.manufacturedBy)}</p>`;
		}
		if (tobacco.blendType) {
			html += `<p><strong>Blend Type:</strong> ${escapeHtml(tobacco.blendType)}</p>`;
		}
		if (tobacco.contents) {
			html += `<p><strong>Contents:</strong> ${escapeHtml(tobacco.contents)}</p>`;
		}
		if (tobacco.flavoring) {
			html += `<p><strong>Flavoring:</strong> ${escapeHtml(tobacco.flavoring)}</p>`;
		}
		if (tobacco.cut) {
			html += `<p><strong>Cut:</strong> ${escapeHtml(tobacco.cut)}</p>`;
		}
		if (tobacco.country) {
			html += `<p><strong>Country:</strong> ${escapeHtml(tobacco.country)}</p>`;
		}
		if (tobacco.productionStatus) {
			html += `<p><strong>Production:</strong> ${escapeHtml(tobacco.productionStatus)}</p>`;
		}
		
		// Add original URL if available
		if (tobacco.originalUrl) {
			html += `<p><strong>Original Review:</strong> <a href="${escapeHtml(tobacco.originalUrl)}" target="_blank" rel="noopener">View on TobaccoReviews.com</a></p>`;
		}
		
		html += '</div>';
		
		html += '</div>'; // Close left section
		
		// Add image section
		if (tobacco.imageUrl) {
			html += `
				<div class="tobacco-modal-right">
					<div class="tobacco-modal-image">
						<img src="${escapeHtml(tobacco.imageUrl)}" alt="${escapeHtml(tobacco.brandName || '')} ${escapeHtml(tobacco.blendName || '')}" />
					</div>
				</div>
			`;
		}
		
		html += '</div>'; // Close main section
		
		// Add rating breakdown (always show all 4 stars)
		if (tobacco.star4Count !== undefined || tobacco.star3Count !== undefined || tobacco.star2Count !== undefined || tobacco.star1Count !== undefined) {
			const total = (tobacco.star4Count || 0) + (tobacco.star3Count || 0) + (tobacco.star2Count || 0) + (tobacco.star1Count || 0);
			html += `
				<div class="tobacco-modal-rating-breakdown">
					<h3>Rating Breakdown</h3>
					<div class="rating-breakdown">
			`;
			
			// Always show all 4 stars, even with 0 ratings
			const star4Count = tobacco.star4Count || 0;
			const star3Count = tobacco.star3Count || 0;
			const star2Count = tobacco.star2Count || 0;
			const star1Count = tobacco.star1Count || 0;
			
			const star4Percentage = total > 0 ? (star4Count / total) * 100 : 0;
			const star3Percentage = total > 0 ? (star3Count / total) * 100 : 0;
			const star2Percentage = total > 0 ? (star2Count / total) * 100 : 0;
			const star1Percentage = total > 0 ? (star1Count / total) * 100 : 0;
			
			html += `
				<div class="rating-bar">
					<div class="rating-label">4 stars:</div>
					<div class="rating-visual">
						<div class="rating-bar-bg">
							<div class="rating-bar-fill" style="width:${star4Percentage}%"></div>
						</div>
						<span class="rating-count">${star4Count}</span>
					</div>
				</div>
				<div class="rating-bar">
					<div class="rating-label">3 stars:</div>
					<div class="rating-visual">
						<div class="rating-bar-bg">
							<div class="rating-bar-fill" style="width:${star3Percentage}%"></div>
						</div>
						<span class="rating-count">${star3Count}</span>
					</div>
				</div>
				<div class="rating-bar">
					<div class="rating-label">2 stars:</div>
					<div class="rating-visual">
						<div class="rating-bar-bg">
							<div class="rating-bar-fill" style="width:${star2Percentage}%"></div>
						</div>
						<span class="rating-count">${star2Count}</span>
					</div>
				</div>
				<div class="rating-bar">
					<div class="rating-label">1 star:</div>
					<div class="rating-visual">
						<div class="rating-bar-bg">
							<div class="rating-bar-fill" style="width:${star1Percentage}%"></div>
						</div>
						<span class="rating-count">${star1Count}</span>
					</div>
				</div>
			`;
			
			html += '</div></div>';
		}
		
		if (tobacco.strength || tobacco.flavoringRating || tobacco.roomNote || tobacco.taste) {
			html += `
				<div class="tobacco-modal-profile">
					<h3>Profile</h3>
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
		
		if (tobacco.description) {
			html += `
				<div class="tobacco-modal-description">
					<h3>Description</h3>
					<p>${escapeHtml(tobacco.description)}</p>
				</div>
			`;
		}
		
		if (tobacco.notes) {
			html += `
				<div class="tobacco-modal-notes">
					<h3>Notes</h3>
					<p>${escapeHtml(tobacco.notes)}</p>
				</div>
			`;
		}
		
		modalBody.innerHTML = html;
	}
	
	function closeModal() {
		console.log('closeModal called');
		const modal = document.getElementById('tobacco-modal');
		if (modal) {
			console.log('Modal found, closing...');
			modal.style.display = 'none';
			document.body.style.overflow = '';
		} else {
			console.log('Modal not found');
		}
	}
