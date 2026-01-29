/**
 * Equalise Grid Heights
 * 
 * Equalizes the height of grid items in each row for elements with .equalise class.
 * - Text (h2) stays at the bottom
 * - Image centers vertically in remaining space
 * - Both are horizontally centered
 * 
 * Supports two structures:
 * 1. Query Loop: .wp-block-query.equalise > ul > li.wp-block-post > .wp-block-group
 * 2. Group Grid: .wp-block-group.equalise.is-layout-grid > .wp-block-group (direct children)
 * 
 * @package GlynnQuelch2025
 */

(function() {
	'use strict';

	/**
	 * Get the number of columns from a grid element
	 */
	function getColumnsCount(grid) {
		const gridStyle = window.getComputedStyle(grid);
		const columns = gridStyle.getPropertyValue('grid-template-columns');
		if (columns && columns !== 'none') {
			return columns.split(' ').filter(c => c.trim() !== '').length;
		}
		// Fallback: check for columns-X class
		const match = grid.className.match(/columns-(\d+)/);
		return match ? parseInt(match[1], 10) : 3;
	}

	/**
	 * Reset heights before recalculating
	 * @param {NodeList} items - The items to reset
	 * @param {boolean} isDirectGroup - If true, reset the item itself, not a child .wp-block-group
	 */
	function resetHeights(items, isDirectGroup = false) {
		items.forEach(item => {
			const target = isDirectGroup ? item : item.querySelector('.wp-block-group');
			if (target) {
				target.style.height = '';
				target.style.minHeight = '';
			}
		});
	}

	/**
	 * Equalize heights for items in each row
	 * @param {NodeList} items - All grid items
	 * @param {number} startIndex - Start index of the row
	 * @param {number} count - Number of items in this row
	 * @param {boolean} isDirectGroup - If true, the item itself is the group to resize
	 */
	function equalizeRow(items, startIndex, count, isDirectGroup = false) {
		const rowItems = Array.from(items).slice(startIndex, startIndex + count);
		
		// Find the tallest item in the row
		let maxHeight = 0;
		rowItems.forEach(item => {
			const target = isDirectGroup ? item : item.querySelector('.wp-block-group');
			if (target) {
				// Temporarily reset height to get natural height
				target.style.height = '';
				target.style.minHeight = '';
				const height = target.offsetHeight;
				if (height > maxHeight) {
					maxHeight = height;
				}
			}
		});

		// Apply the max height to all items in the row
		rowItems.forEach(item => {
			const target = isDirectGroup ? item : item.querySelector('.wp-block-group');
			if (target) {
				target.style.minHeight = maxHeight + 'px';
			}
		});
	}

	/**
	 * Process Query Loop grids: .wp-block-query.equalise
	 */
	function processQueryLoopGrids() {
		const grids = document.querySelectorAll('.wp-block-query.equalise');

		grids.forEach(container => {
			const grid = container.querySelector('ul.wp-block-post-template');
			if (!grid) return;

			const items = grid.querySelectorAll(':scope > li.wp-block-post');
			if (items.length === 0) return;

			const columnsCount = getColumnsCount(grid);

			// Reset all heights first
			resetHeights(items, false);

			// Process each row
			for (let i = 0; i < items.length; i += columnsCount) {
				const itemsInRow = Math.min(columnsCount, items.length - i);
				equalizeRow(items, i, itemsInRow, false);
			}
		});
	}

	/**
	 * Process Group grids: .wp-block-group.equalise.is-layout-grid
	 */
	function processGroupGrids() {
		const grids = document.querySelectorAll('.wp-block-group.equalise.is-layout-grid');

		grids.forEach(grid => {
			// Get direct child .wp-block-group elements
			const items = grid.querySelectorAll(':scope > .wp-block-group');
			if (items.length === 0) return;

			const columnsCount = getColumnsCount(grid);

			// Reset all heights first
			resetHeights(items, true);

			// Process each row
			for (let i = 0; i < items.length; i += columnsCount) {
				const itemsInRow = Math.min(columnsCount, items.length - i);
				equalizeRow(items, i, itemsInRow, true);
			}
		});
	}

	/**
	 * Process all equalise grids on the page
	 */
	function processEqualiseGrids() {
		processQueryLoopGrids();
		processGroupGrids();
	}

	/**
	 * Debounce function for resize events
	 */
	function debounce(func, wait) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}

	/**
	 * Initialize
	 */
	function init() {
		// Run on page load
		processEqualiseGrids();

		// Run on window resize (debounced)
		window.addEventListener('resize', debounce(processEqualiseGrids, 250));

		// Run after images load
		window.addEventListener('load', processEqualiseGrids);
	}

	// Start when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

