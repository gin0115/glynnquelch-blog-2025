<?php
/**
 * Theme bootstrap
 *
 * @package GlynnQuelch2025
 * @author  Glynn Quelch
 * @since   1.0.0
 */

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GQ2025_VERSION' ) ) {
	/**
	 * Theme version number.
	 *
	 * @since 1.0.0
	 */
	define( 'GQ2025_VERSION', '1.0.0' );
}

if ( ! defined( 'GQ2025_PATH' ) ) {
	/**
	 * Theme directory path.
	 *
	 * @since 1.0.0
	 */
	define( 'GQ2025_PATH', trailingslashit( get_stylesheet_directory() ) );
}

if ( ! defined( 'GQ2025_URL' ) ) {
	/**
	 * Theme directory URL.
	 *
	 * @since 1.0.0
	 */
	define( 'GQ2025_URL', trailingslashit( get_stylesheet_directory_uri() ) );
}

if ( ! defined( 'GQ2025_ASSETS_URL' ) ) {
	/**
	 * Theme assets URL.
	 *
	 * @since 1.0.0
	 */
	define( 'GQ2025_ASSETS_URL', GQ2025_URL . 'assets/' );
}

/**
 * Prints HTML with categories information for the current post.
 *
 * Override to allow custom post types.
 *
 * @param string $before Text to display before the categories.
 * @param string $after  Text to display after the categories.
 * @param array  $style  CSS classes to apply to the category links.
 *
 * @return void
 */
function yuki_post_categories( $before = '', $after = '', $style = array() ) {
	// Hide category for pages.
	if ( 'page' === get_post_type() || empty( get_the_category() ) ) {
		return;
	}
	global $wp_rewrite;
	$style = esc_attr( \LottaFramework\Utils::clsx( $style ) );
	$rel   = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ? 'rel="category tag"' : 'rel="category"' );
	echo $before;
	foreach ( get_the_category() as $category ) {
		echo '<a class="' . $style . '" href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . esc_html( $category->name ) . '</a>';
	}
	echo $after;
}

if ( ! function_exists( 'glynnquelch2025_image_url' ) ) {
	/**
	 * Get image URL from theme assets.
	 *
	 * @param string $image The image filename.
	 *
	 * @return string The complete image URL.
	 * @since 1.0.0
	 */
	function glynnquelch2025_image_url( string $image ): string {
		return GQ2025_ASSETS_URL . 'images/' . $image;
	}
}

// Require customizer options.
require_once GQ2025_PATH . 'customizer.php';
// Require starter content options.
require_once GQ2025_PATH . 'starter-content.php';

// Require additional classes and functions.
require_once GQ2025_PATH . 'inc/class-acf.php';
require_once GQ2025_PATH . 'inc/class-post-types.php';
require_once GQ2025_PATH . 'inc/class-media.php';
