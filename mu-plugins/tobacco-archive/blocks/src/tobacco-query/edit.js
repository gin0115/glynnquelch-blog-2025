import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

// Import editor styles
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const {
		showFilters,
		itemsPerPage,
		showPagination,
		gridColumns,
		showRatings,
		showProfile,
		showDescription
	} = attributes;

	const [tobaccoData, setTobaccoData] = useState([]);
	const [loading, setLoading] = useState(true);
	const [filters, setFilters] = useState({
		brand: '',
		blendType: '',
		contents: '',
		rating: '',
		flavoring: '',
		series: '',
		manufacturedBy: ''
	});

	// Fetch tobacco data on component mount
	useEffect(() => {
		fetchTobaccoData();
	}, []);

	const fetchTobaccoData = async () => {
		try {
			const response = await fetch('/wp-json/tobacco-archive/v1/reviews');
			if (response.ok) {
				const data = await response.json();
				setTobaccoData(data);
			}
		} catch (error) {
			console.error('Error fetching tobacco data:', error);
		} finally {
			setLoading(false);
		}
	};

	// Get unique values for filter options
	const getUniqueValues = (field) => {
		const values = tobaccoData.map(item => item[field]).filter(Boolean);
		return [...new Set(values)].sort();
	};

	// Filter tobacco data based on current filters
	const filteredData = tobaccoData.filter(item => {
		return Object.entries(filters).every(([key, value]) => {
			if (!value) return true;
			return item[key] && item[key].toLowerCase().includes(value.toLowerCase());
		});
	});

	const handleFilterChange = (filterKey, value) => {
		setFilters(prev => ({
			...prev,
			[filterKey]: value
		}));
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Display Settings', 'tobacco-archive')}>
					<ToggleControl
						label={__('Show Filters', 'tobacco-archive')}
						checked={showFilters}
						onChange={(value) => setAttributes({ showFilters: value })}
					/>
					<ToggleControl
						label={__('Show Ratings', 'tobacco-archive')}
						checked={showRatings}
						onChange={(value) => setAttributes({ showRatings: value })}
					/>
					<ToggleControl
						label={__('Show Profile', 'tobacco-archive')}
						checked={showProfile}
						onChange={(value) => setAttributes({ showProfile: value })}
					/>
					<ToggleControl
						label={__('Show Description', 'tobacco-archive')}
						checked={showDescription}
						onChange={(value) => setAttributes({ showDescription: value })}
					/>
					<ToggleControl
						label={__('Show Pagination', 'tobacco-archive')}
						checked={showPagination}
						onChange={(value) => setAttributes({ showPagination: value })}
					/>
					<RangeControl
						label={__('Items Per Page', 'tobacco-archive')}
						value={itemsPerPage}
						onChange={(value) => setAttributes({ itemsPerPage: value })}
						min={6}
						max={24}
						step={6}
					/>
					<RangeControl
						label={__('Grid Columns', 'tobacco-archive')}
						value={gridColumns}
						onChange={(value) => setAttributes({ gridColumns: value })}
						min={1}
						max={4}
					/>
				</PanelBody>
			</InspectorControls>

			<div className="tobacco-query-block">
				{loading ? (
					<div className="tobacco-query-loading">
						{__('Loading tobacco reviews...', 'tobacco-archive')}
					</div>
				) : (
					<>
						{showFilters && (
							<div className="tobacco-query-filters">
								<h3>{__('Filter Reviews', 'tobacco-archive')}</h3>
								<div className="filter-grid">
									<SelectControl
										label={__('Brand', 'tobacco-archive')}
										value={filters.brand}
										options={[
											{ label: __('All Brands', 'tobacco-archive'), value: '' },
											...getUniqueValues('brandName').map(brand => ({
												label: brand,
												value: brand
											}))
										]}
										onChange={(value) => handleFilterChange('brand', value)}
									/>
									<SelectControl
										label={__('Blend Type', 'tobacco-archive')}
										value={filters.blendType}
										options={[
											{ label: __('All Types', 'tobacco-archive'), value: '' },
											...getUniqueValues('blendType').map(type => ({
												label: type,
												value: type
											}))
										]}
										onChange={(value) => handleFilterChange('blendType', value)}
									/>
									<SelectControl
										label={__('Contents', 'tobacco-archive')}
										value={filters.contents}
										options={[
											{ label: __('All Contents', 'tobacco-archive'), value: '' },
											...getUniqueValues('contents').map(content => ({
												label: content,
												value: content
											}))
										]}
										onChange={(value) => handleFilterChange('contents', value)}
									/>
									<SelectControl
										label={__('Manufactured By', 'tobacco-archive')}
										value={filters.manufacturedBy}
										options={[
											{ label: __('All Manufacturers', 'tobacco-archive'), value: '' },
											...getUniqueValues('manufacturedBy').map(manufacturer => ({
												label: manufacturer,
												value: manufacturer
											}))
										]}
										onChange={(value) => handleFilterChange('manufacturedBy', value)}
									/>
									<SelectControl
										label={__('Series', 'tobacco-archive')}
										value={filters.series}
										options={[
											{ label: __('All Series', 'tobacco-archive'), value: '' },
											...getUniqueValues('series').map(series => ({
												label: series,
												value: series
											}))
										]}
										onChange={(value) => handleFilterChange('series', value)}
									/>
									<SelectControl
										label={__('Flavoring', 'tobacco-archive')}
										value={filters.flavoring}
										options={[
											{ label: __('All Flavoring', 'tobacco-archive'), value: '' },
											...getUniqueValues('flavoring').map(flavoring => ({
												label: flavoring,
												value: flavoring
											}))
										]}
										onChange={(value) => handleFilterChange('flavoring', value)}
									/>
								</div>
								<div className="filter-results">
									{__('Showing', 'tobacco-archive')} {filteredData.length} {__('of', 'tobacco-archive')} {tobaccoData.length} {__('reviews', 'tobacco-archive')}
								</div>
							</div>
						)}

						<div 
							className="tobacco-query-grid"
							style={{ '--grid-columns': gridColumns }}
						>
							{filteredData.slice(0, itemsPerPage).map((tobacco, index) => (
								<div key={index} className="tobacco-query-item">
									<div className="tobacco-item-header">
										<h4 className="tobacco-item-title">
											<span className="brand-name">{tobacco.brandName}</span>
											<span className="blend-name">{tobacco.blendName}</span>
										</h4>
										{showRatings && tobacco.overallRating > 0 && (
											<div className="tobacco-item-rating">
												{tobacco.overallRating}/4
												{tobacco.totalReviews > 0 && (
													<span className="review-count">
														({tobacco.totalReviews} {tobacco.totalReviews === 1 ? __('review', 'tobacco-archive') : __('reviews', 'tobacco-archive')})
													</span>
												)}
											</div>
										)}
									</div>
									
									{tobacco.imageUrl && (
										<div className="tobacco-item-image">
											<img src={tobacco.imageUrl} alt={`${tobacco.brandName} ${tobacco.blendName}`} />
										</div>
									)}

									<div className="tobacco-item-details">
										{tobacco.series && (
											<p><strong>{__('Series:', 'tobacco-archive')}</strong> {tobacco.series}</p>
										)}
										{tobacco.blendType && (
											<p><strong>{__('Type:', 'tobacco-archive')}</strong> {tobacco.blendType}</p>
										)}
										{tobacco.contents && (
											<p><strong>{__('Contents:', 'tobacco-archive')}</strong> {tobacco.contents}</p>
										)}
									</div>

									{showProfile && (tobacco.strength || tobacco.flavoringRating || tobacco.roomNote || tobacco.taste) && (
										<div className="tobacco-item-profile">
											<h5>{__('Profile', 'tobacco-archive')}</h5>
											<div className="profile-ratings">
												{tobacco.strength && (
													<div className="profile-rating">
														<strong>{__('Strength:', 'tobacco-archive')}</strong> {tobacco.strength}
													</div>
												)}
												{tobacco.flavoringRating && (
													<div className="profile-rating">
														<strong>{__('Flavoring:', 'tobacco-archive')}</strong> {tobacco.flavoringRating}
													</div>
												)}
												{tobacco.roomNote && (
													<div className="profile-rating">
														<strong>{__('Room Note:', 'tobacco-archive')}</strong> {tobacco.roomNote}
													</div>
												)}
												{tobacco.taste && (
													<div className="profile-rating">
														<strong>{__('Taste:', 'tobacco-archive')}</strong> {tobacco.taste}
													</div>
												)}
											</div>
										</div>
									)}

									{showDescription && tobacco.description && (
										<div className="tobacco-item-description">
											<p>{tobacco.description.substring(0, 150)}...</p>
										</div>
									)}
								</div>
							))}
						</div>

						{showPagination && filteredData.length > itemsPerPage && (
							<div className="tobacco-query-pagination">
								<p>{__('Pagination will be shown on frontend', 'tobacco-archive')}</p>
							</div>
						)}
					</>
				)}
			</div>
		</div>
	);
}
