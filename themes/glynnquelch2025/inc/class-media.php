<?php

/**
 * Class to handle all media related functionality.
 *
 * @package GlynnQuelch 2025 Blog
 * @since 1.0.0
 */

namespace GlynnQuelch\Blog2025;

/**
 * Media
 */
class Media {
	/**
	 * Initialize the class and set up hooks.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {
		$instance = new self();
		$instance->setup_hooks();
	}

	/**
	 * Set up WordPress hooks.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_filter( 'after_setup_theme', array( $this, 'register_image_sizes' ) );
		add_filter( 'image_size_names_choose', array( $this, 'add_custom_image_size_names' ) );
	}

	/**
	 * Register custom image sizes.
	 *
	 * @return void
	 */
	public function register_image_sizes(): void {
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'card-small', 328, 240, true );
		add_image_size( 'card-medium', 400, 300, true );
		add_image_size( 'card-large', 600, 450, true );
		add_image_size( 'thumb-medium', 500, 500, true );
		add_image_size( 'thumb-large', 1000, 1000, true );
	}

	/**
	 * Add custom image size names to the media selector.
	 *
	 * @param array<string, string> $sizes Existing image sizes.
	 *
	 * @return array<string, string> Modified array with custom sizes added.
	 */
	public function add_custom_image_size_names( array $sizes ): array {
		$sizes['card-small']   = __( 'Card Small', 'glynnquelch2025' );
		$sizes['card-medium']  = __( 'Card Medium', 'glynnquelch2025' );
		$sizes['card-large']   = __( 'Card Large', 'glynnquelch2025' );
		$sizes['thumb-medium'] = __( 'Thumbnail Medium', 'glynnquelch2025' );
		$sizes['thumb-large']  = __( 'Thumbnail Large', 'glynnquelch2025' );
		return $sizes;
	}
}
Media::init();
