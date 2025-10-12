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
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

/**
 * WordPress dependencies
 */
import { 
	PanelBody, 
	TextControl, 
	TextareaControl, 
	SelectControl, 
	RangeControl,
	Button,
	Placeholder,
	Spinner
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object} props - The block props.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
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
		imageUrl,
		imageId
	} = attributes;

	const blockProps = useBlockProps();

	// Rating options for profile ratings
	const ratingOptions = [
		{ label: __( 'None Detected', 'tobacco-archive' ), value: 'None Detected' },
		{ label: __( 'Extremely Mild', 'tobacco-archive' ), value: 'Extremely Mild' },
		{ label: __( 'Very Mild', 'tobacco-archive' ), value: 'Very Mild' },
		{ label: __( 'Mild', 'tobacco-archive' ), value: 'Mild' },
		{ label: __( 'Mild to Medium', 'tobacco-archive' ), value: 'Mild to Medium' },
		{ label: __( 'Medium', 'tobacco-archive' ), value: 'Medium' },
		{ label: __( 'Medium to Strong', 'tobacco-archive' ), value: 'Medium to Strong' },
		{ label: __( 'Strong', 'tobacco-archive' ), value: 'Strong' },
		{ label: __( 'Very Strong', 'tobacco-archive' ), value: 'Very Strong' },
		{ label: __( 'Overwhelming', 'tobacco-archive' ), value: 'Overwhelming' }
	];

	const roomNoteOptions = [
		{ label: __( 'Unnoticeable', 'tobacco-archive' ), value: 'Unnoticeable' },
		{ label: __( 'Very Pleasant', 'tobacco-archive' ), value: 'Very Pleasant' },
		{ label: __( 'Pleasant', 'tobacco-archive' ), value: 'Pleasant' },
		{ label: __( 'Pleasant to Tolerable', 'tobacco-archive' ), value: 'Pleasant to Tolerable' },
		{ label: __( 'Tolerable', 'tobacco-archive' ), value: 'Tolerable' },
		{ label: __( 'Tolerable to Strong', 'tobacco-archive' ), value: 'Tolerable to Strong' },
		{ label: __( 'Strong', 'tobacco-archive' ), value: 'Strong' }
	];

	const tasteOptions = [
		{ label: __( 'Extremely Mild (Flat)', 'tobacco-archive' ), value: 'Extremely Mild (Flat)' },
		{ label: __( 'Very Mild', 'tobacco-archive' ), value: 'Very Mild' },
		{ label: __( 'Mild', 'tobacco-archive' ), value: 'Mild' },
		{ label: __( 'Mild to Medium', 'tobacco-archive' ), value: 'Mild to Medium' },
		{ label: __( 'Medium', 'tobacco-archive' ), value: 'Medium' },
		{ label: __( 'Medium to Full', 'tobacco-archive' ), value: 'Medium to Full' },
		{ label: __( 'Full', 'tobacco-archive' ), value: 'Full' },
		{ label: __( 'Overwhelming', 'tobacco-archive' ), value: 'Overwhelming' }
	];

	const productionStatusOptions = [
		{ label: __( 'In Production', 'tobacco-archive' ), value: 'In Production' },
		{ label: __( 'No longer in production', 'tobacco-archive' ), value: 'No longer in production' },
		{ label: __( 'Limited Edition', 'tobacco-archive' ), value: 'Limited Edition' }
	];

	const onSelectImage = ( media ) => {
		setAttributes( {
			imageUrl: media.url,
			imageId: media.id
		} );
	};

	const onRemoveImage = () => {
		setAttributes( {
			imageUrl: '',
			imageId: 0
		} );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Basic Information', 'tobacco-archive' ) }>
					<TextControl
						label={ __( 'Brand Name', 'tobacco-archive' ) }
						value={ brandName }
						onChange={ ( value ) => setAttributes( { brandName: value } ) }
					/>
					<TextControl
						label={ __( 'Blend Name', 'tobacco-archive' ) }
						value={ blendName }
						onChange={ ( value ) => setAttributes( { blendName: value } ) }
					/>
					<RangeControl
						label={ __( 'Overall Rating (out of 4)', 'tobacco-archive' ) }
						value={ overallRating }
						onChange={ ( value ) => setAttributes( { overallRating: value } ) }
						min={ 0 }
						max={ 4 }
						step={ 0.1 }
					/>
					<TextControl
						label={ __( 'Total Reviews', 'tobacco-archive' ) }
						value={ totalReviews }
						onChange={ ( value ) => setAttributes( { totalReviews: parseInt( value ) || 0 } ) }
						type="number"
					/>
					<PanelBody title={ __( 'Star Rating Breakdown', 'tobacco-archive' ) }>
						<TextControl
							label={ __( '4 Star Reviews', 'tobacco-archive' ) }
							value={ star4Count }
							onChange={ ( value ) => setAttributes( { star4Count: parseInt( value ) || 0 } ) }
							type="number"
						/>
						<TextControl
							label={ __( '3 Star Reviews', 'tobacco-archive' ) }
							value={ star3Count }
							onChange={ ( value ) => setAttributes( { star3Count: parseInt( value ) || 0 } ) }
							type="number"
						/>
						<TextControl
							label={ __( '2 Star Reviews', 'tobacco-archive' ) }
							value={ star2Count }
							onChange={ ( value ) => setAttributes( { star2Count: parseInt( value ) || 0 } ) }
							type="number"
						/>
						<TextControl
							label={ __( '1 Star Reviews', 'tobacco-archive' ) }
							value={ star1Count }
							onChange={ ( value ) => setAttributes( { star1Count: parseInt( value ) || 0 } ) }
							type="number"
						/>
					</PanelBody>
					<TextControl
						label={ __( 'Series', 'tobacco-archive' ) }
						value={ series }
						onChange={ ( value ) => setAttributes( { series: value } ) }
					/>
					<TextControl
						label={ __( 'Blended By', 'tobacco-archive' ) }
						value={ blendedBy }
						onChange={ ( value ) => setAttributes( { blendedBy: value } ) }
					/>
					<SelectControl
						label={ __( 'Manufactured By', 'tobacco-archive' ) }
						value={ manufacturedBy }
						options={ [
							{ label: __( 'GQ Tobaccos', 'tobacco-archive' ), value: 'GQ Tobaccos' },
							{ label: __( 'Gauntleys of Nottingham', 'tobacco-archive' ), value: 'Gauntleys of Nottingham' }
						] }
						onChange={ ( value ) => setAttributes( { manufacturedBy: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Blend Details', 'tobacco-archive' ) }>
					<TextControl
						label={ __( 'Blend Type', 'tobacco-archive' ) }
						value={ blendType }
						onChange={ ( value ) => setAttributes( { blendType: value } ) }
					/>
					<TextControl
						label={ __( 'Contents', 'tobacco-archive' ) }
						value={ contents }
						onChange={ ( value ) => setAttributes( { contents: value } ) }
					/>
					<TextControl
						label={ __( 'Flavoring', 'tobacco-archive' ) }
						value={ flavoring }
						onChange={ ( value ) => setAttributes( { flavoring: value } ) }
					/>
					<TextControl
						label={ __( 'Cut', 'tobacco-archive' ) }
						value={ cut }
						onChange={ ( value ) => setAttributes( { cut: value } ) }
					/>
					<TextControl
						label={ __( 'Country', 'tobacco-archive' ) }
						value={ country }
						onChange={ ( value ) => setAttributes( { country: value } ) }
					/>
					<SelectControl
						label={ __( 'Production Status', 'tobacco-archive' ) }
						value={ productionStatus }
						options={ productionStatusOptions }
						onChange={ ( value ) => setAttributes( { productionStatus: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Profile Ratings', 'tobacco-archive' ) }>
					<SelectControl
						label={ __( 'Strength', 'tobacco-archive' ) }
						value={ strength }
						options={ ratingOptions }
						onChange={ ( value ) => setAttributes( { strength: value } ) }
					/>
					<SelectControl
						label={ __( 'Flavoring Rating', 'tobacco-archive' ) }
						value={ flavoringRating }
						options={ ratingOptions }
						onChange={ ( value ) => setAttributes( { flavoringRating: value } ) }
					/>
					<SelectControl
						label={ __( 'Room Note', 'tobacco-archive' ) }
						value={ roomNote }
						options={ roomNoteOptions }
						onChange={ ( value ) => setAttributes( { roomNote: value } ) }
					/>
					<SelectControl
						label={ __( 'Taste', 'tobacco-archive' ) }
						value={ taste }
						options={ tasteOptions }
						onChange={ ( value ) => setAttributes( { taste: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Content', 'tobacco-archive' ) }>
					<TextareaControl
						label={ __( 'Description', 'tobacco-archive' ) }
						value={ description }
						onChange={ ( value ) => setAttributes( { description: value } ) }
						rows={ 6 }
					/>
					<TextareaControl
						label={ __( 'Notes', 'tobacco-archive' ) }
						value={ notes }
						onChange={ ( value ) => setAttributes( { notes: value } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Image', 'tobacco-archive' ) }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectImage }
							allowedTypes={ [ 'image' ] }
							value={ imageId }
							render={ ( { open } ) => (
								<div>
									{ imageUrl && (
										<div className="tobacco-blend-image-preview">
											<img src={ imageUrl } alt={ __( 'Tobacco blend image', 'tobacco-archive' ) } />
											<Button onClick={ onRemoveImage } isDestructive>
												{ __( 'Remove Image', 'tobacco-archive' ) }
											</Button>
										</div>
									) }
									{ ! imageUrl && (
										<Button onClick={ open }>
											{ __( 'Select Image', 'tobacco-archive' ) }
										</Button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>

			<div className="tobacco-blend-block">
				{ ! brandName && ! blendName ? (
					<Placeholder
						icon="smiley"
						label={ __( 'Tobacco Blend', 'tobacco-archive' ) }
						instructions={ __( 'Configure your tobacco blend using the settings panel on the right.', 'tobacco-archive' ) }
					>
						<Button isPrimary onClick={ () => setAttributes( { brandName: 'Example Brand', blendName: 'Example Blend' } ) }>
							{ __( 'Add Example Content', 'tobacco-archive' ) }
						</Button>
					</Placeholder>
				) : (
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
				) }
			</div>
		</div>
	);
}
