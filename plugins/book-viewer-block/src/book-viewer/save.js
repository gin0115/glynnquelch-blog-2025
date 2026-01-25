/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save component for Book Viewer block.
 * Outputs semantic HTML that serves as the no-JS fallback.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Saved element.
 */
export default function save( { attributes } ) {
	const { pages, frontCover, backCover, pageWidth, pageHeight, paddingColor } = attributes;

	// Check if we need an auto-blank page for even spread on desktop
	// Only needed when page count is odd (for 2-up layout)
	const needsAutoBlank = pages.length % 2 !== 0;

	const blockProps = useBlockProps.save( {
		className: 'book-viewer',
		'data-page-width': pageWidth,
		'data-page-height': pageHeight,
		'data-has-auto-blank': needsAutoBlank ? 'true' : 'false',
	} );

	// Calculate aspect ratio for responsive sizing
	const aspectRatio = pageHeight / pageWidth;

	// Build inline styles with optional padding color
	const bookStyles = {
		'--book-page-width': `${ pageWidth }px`,
		'--book-page-height': `${ pageHeight }px`,
		'--book-aspect-ratio': aspectRatio,
	};
	
	if ( paddingColor ) {
		bookStyles['--book-padding-color'] = paddingColor;
	}

	return (
		<div { ...blockProps }>
			{ /* Container for StPageFlip to take over */ }
			<div
				className="book-viewer__book"
				style={ bookStyles }
			>
				{ /* Front Cover - always full width in fallback */ }
				{ frontCover && (
					<div className="book-viewer__page book-viewer__page--cover book-viewer__page--front">
						<img
							src={ frontCover.url }
							alt={ frontCover.alt || 'Front cover' }
							loading="lazy"
							decoding="async"
							width={ frontCover.width }
							height={ frontCover.height }
						/>
					</div>
				) }

				{ /* Inner Pages - CSS grid handles 2-up desktop, 1-up mobile */ }
				<div className="book-viewer__pages">
					{ pages.map( ( page, index ) => (
						<div
							key={ page.id }
							className={ `book-viewer__page book-viewer__page--inner ${ page.isBlank ? 'book-viewer__page--blank' : '' }` }
							data-page-number={ index + 1 }
						>
							{ page.isBlank ? (
								<div className="book-viewer__blank"></div>
							) : (
								<img
									src={ page.url }
									alt={ page.alt || `Page ${ index + 1 }` }
									loading="lazy"
									decoding="async"
									width={ page.width }
									height={ page.height }
								/>
							) }
						</div>
					) ) }
					{ /* Auto-blank page for odd page count - hidden on mobile via CSS */ }
					{ needsAutoBlank && (
						<div
							className="book-viewer__page book-viewer__page--inner book-viewer__page--blank book-viewer__page--auto-blank"
							data-page-number={ pages.length + 1 }
							aria-hidden="true"
						>
							<div className="book-viewer__blank"></div>
						</div>
					) }
				</div>

				{ /* Back Cover - always full width in fallback */ }
				{ backCover && (
					<div className="book-viewer__page book-viewer__page--cover book-viewer__page--back">
						<img
							src={ backCover.url }
							alt={ backCover.alt || 'Back cover' }
							loading="lazy"
							decoding="async"
							width={ backCover.width }
							height={ backCover.height }
						/>
					</div>
				) }
			</div>

			{ /* Navigation for JS-enhanced version */ }
			<div className="book-viewer__nav" aria-hidden="true">
				<button className="book-viewer__nav-btn book-viewer__nav-btn--prev" type="button">
					<span className="screen-reader-text">Previous page</span>
					<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
						<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" fill="currentColor" />
					</svg>
				</button>
				<button className="book-viewer__nav-btn book-viewer__nav-btn--next" type="button">
					<span className="screen-reader-text">Next page</span>
					<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
						<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" fill="currentColor" />
					</svg>
				</button>
			</div>

			{ /* Page indicator for JS-enhanced version - shows actual content pages, not auto-blanks */ }
			<div className="book-viewer__indicator" aria-hidden="true">
				<span className="book-viewer__current-page">1</span>
				<span className="book-viewer__separator">/</span>
				<span className="book-viewer__total-pages">{ pages.length + ( frontCover ? 1 : 0 ) + ( backCover ? 1 : 0 ) }</span>
			</div>
		</div>
	);
}

