/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {Object} props - The block props.
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	const {
		brandName,
		blendName,
		overallRating,
		totalReviews,
		star4Count,
		star3Count,
		star2Count,
		star1Count,
		series,
		blendedBy,
		manufacturedBy,
		blendType,
		contents,
		flavoring,
		cut,
		country,
		productionStatus,
		strength,
		flavoringRating,
		roomNote,
		taste,
		description,
		notes,
		imageUrl
	} = attributes;

	const blockProps = useBlockProps.save();

	// Don't render anything if no content is provided
	if ( ! brandName && ! blendName ) {
		return null;
	}

	return (
		<div { ...blockProps }>
			<div className="tobacco-blend-block">
				<div className="tobacco-blend-content">
					<div className="tobacco-blend-header">
						<h1 className="tobacco-blend-title">
							<span className="brand-name">{ brandName }</span>
							<span className="blend-name">{ blendName }</span>
						</h1>
						{ overallRating > 0 && (
							<div className="tobacco-blend-rating">
								{ __( 'Rating:', 'tobacco-archive' ) } { overallRating }/4
								{ totalReviews > 0 && (
									<span> { __( 'from', 'tobacco-archive' ) } { totalReviews } { totalReviews === 1 ? __( 'review', 'tobacco-archive' ) : __( 'reviews', 'tobacco-archive' ) }</span>
								) }
							</div>
						) }
					</div>

					<div className="tobacco-blend-main">
						<div className="tobacco-blend-info">
							<div className="tobacco-blend-details">
								{ series && <p><strong>{ __( 'Series:', 'tobacco-archive' ) }</strong> { series }</p> }
								{ blendedBy && <p><strong>{ __( 'Blended By:', 'tobacco-archive' ) }</strong> { blendedBy }</p> }
								{ manufacturedBy && <p><strong>{ __( 'Manufactured By:', 'tobacco-archive' ) }</strong> { manufacturedBy }</p> }
								{ blendType && <p><strong>{ __( 'Blend Type:', 'tobacco-archive' ) }</strong> { blendType }</p> }
								{ contents && <p><strong>{ __( 'Contents:', 'tobacco-archive' ) }</strong> { contents }</p> }
								{ flavoring && <p><strong>{ __( 'Flavoring:', 'tobacco-archive' ) }</strong> { flavoring }</p> }
								{ cut && <p><strong>{ __( 'Cut:', 'tobacco-archive' ) }</strong> { cut }</p> }
								{ country && <p><strong>{ __( 'Country:', 'tobacco-archive' ) }</strong> { country }</p> }
								{ productionStatus && <p><strong>{ __( 'Production:', 'tobacco-archive' ) }</strong> { productionStatus }</p> }
							</div>
							
							{ imageUrl && (
								<div className="tobacco-blend-image">
									<img src={ imageUrl } alt={ `${ brandName } ${ blendName }` } />
								</div>
							) }
						</div>

						{ ( strength || flavoringRating || roomNote || taste ) && (
							<div className="tobacco-blend-profile">
								<h3>{ __( 'Profile', 'tobacco-archive' ) }</h3>
								<div className="profile-ratings">
									{ strength && (
										<div className="profile-rating">
											<strong>{ __( 'Strength:', 'tobacco-archive' ) }</strong> { strength }
										</div>
									) }
									{ flavoringRating && (
										<div className="profile-rating">
											<strong>{ __( 'Flavoring:', 'tobacco-archive' ) }</strong> { flavoringRating }
										</div>
									) }
									{ roomNote && (
										<div className="profile-rating">
											<strong>{ __( 'Room Note:', 'tobacco-archive' ) }</strong> { roomNote }
										</div>
									) }
									{ taste && (
										<div className="profile-rating">
											<strong>{ __( 'Taste:', 'tobacco-archive' ) }</strong> { taste }
										</div>
									) }
								</div>
							</div>
						) }


					{ (star4Count > 0 || star3Count > 0 || star2Count > 0 || star1Count > 0) && (
						<div className="tobacco-blend-rating-breakdown">
							<h3>{ __( 'Rating Breakdown', 'tobacco-archive' ) }</h3>
							<div className="rating-breakdown">
								{ [4, 3, 2, 1].map( ( stars ) => {
									const count = stars === 4 ? star4Count : stars === 3 ? star3Count : stars === 2 ? star2Count : star1Count;
									const percentage = totalReviews > 0 ? ( count / totalReviews ) * 100 : 0;
									return (
										<div key={ stars } className="rating-bar">
											<div className="rating-label">
												{ stars } { stars === 1 ? __( 'star', 'tobacco-archive' ) : __( 'stars', 'tobacco-archive' ) }:
											</div>
											<div className="rating-visual">
												<div className="rating-bar-bg">
													<div 
														className="rating-bar-fill" 
														style={ { width: `${ percentage }%` } }
													></div>
												</div>
												<span className="rating-count">{ count }</span>
											</div>
										</div>
									);
								} ) }
							</div>
						</div>
					) }

					{ description && (
						<div className="tobacco-blend-description">
							<h3>{ __( 'Description', 'tobacco-archive' ) }</h3>
							<p>{ description }</p>
						</div>
					) }

						{ notes && (
							<div className="tobacco-blend-notes">
								<h3>{ __( 'Notes', 'tobacco-archive' ) }</h3>
								<p><strong>{ __( 'Notes:', 'tobacco-archive' ) }</strong> { notes }</p>
							</div>
						) }
					</div>
				</div>
			</div>
		</div>
	);
}
