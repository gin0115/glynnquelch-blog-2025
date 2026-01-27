/**
 * Term Image Media Uploader
 *
 * Handles the media uploader for archive-type taxonomy term images.
 *
 * @package SaggyPantsArchive
 * @since   1.1.0
 */

( function( $ ) {
	'use strict';

	var frame;

	/**
	 * Initialize the media uploader.
	 */
	function init() {
		$( document ).on( 'click', '#archive-type-image-upload', openMediaUploader );
		$( document ).on( 'click', '#archive-type-image-remove', removeImage );

		// Clear form on successful term creation (add new term form).
		$( document ).ajaxSuccess( function( event, xhr, settings ) {
			if ( settings.data && settings.data.indexOf( 'action=add-tag' ) !== -1 ) {
				clearForm();
			}
		} );
	}

	/**
	 * Open the media uploader.
	 *
	 * @param {Event} e The click event.
	 */
	function openMediaUploader( e ) {
		e.preventDefault();

		// If frame already exists, open it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create a new media frame.
		frame = wp.media( {
			title: saggyPantsTermImage.title,
			button: {
				text: saggyPantsTermImage.button
			},
			multiple: false,
			library: {
				type: 'image'
			}
		} );

		// Handle image selection.
		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			setImage( attachment );
		} );

		frame.open();
	}

	/**
	 * Set the selected image.
	 *
	 * @param {Object} attachment The attachment object.
	 */
	function setImage( attachment ) {
		var imgUrl = attachment.sizes && attachment.sizes.thumbnail
			? attachment.sizes.thumbnail.url
			: attachment.url;

		$( '#archive-type-image-id' ).val( attachment.id );
		$( '#archive-type-image-preview' ).html( '<img src="' + imgUrl + '" style="max-width: 150px; height: auto;" />' );
		$( '#archive-type-image-remove' ).show();
	}

	/**
	 * Remove the selected image.
	 *
	 * @param {Event} e The click event.
	 */
	function removeImage( e ) {
		e.preventDefault();

		$( '#archive-type-image-id' ).val( '' );
		$( '#archive-type-image-preview' ).html( '' );
		$( '#archive-type-image-remove' ).hide();
	}

	/**
	 * Clear the form after adding a new term.
	 */
	function clearForm() {
		$( '#archive-type-image-id' ).val( '' );
		$( '#archive-type-image-preview' ).html( '' );
		$( '#archive-type-image-remove' ).hide();
	}

	// Initialize on document ready.
	$( document ).ready( init );

} )( jQuery );


