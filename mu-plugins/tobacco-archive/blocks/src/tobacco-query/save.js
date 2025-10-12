import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const {
		showFilters,
		itemsPerPage,
		showPagination,
		gridColumns,
		showRatings,
		showProfile,
		showDescription
	} = attributes;

	return (
		<div {...useBlockProps.save()}>
			<div 
				className="tobacco-query-block"
				data-show-filters={showFilters}
				data-items-per-page={itemsPerPage}
				data-show-pagination={showPagination}
				data-grid-columns={gridColumns}
				data-show-ratings={showRatings}
				data-show-profile={showProfile}
				data-show-description={showDescription}
			>
				{/* This will be rendered server-side by PHP */}
			</div>
		</div>
	);
}
