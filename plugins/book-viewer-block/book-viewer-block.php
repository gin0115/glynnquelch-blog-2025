<?php
/**
 * Plugin Name: Book Viewer Block
 * Plugin URI: https://github.com/glynnquelch/book-viewer-block
 * Description: A Gutenberg block for displaying images as a flipable book with page-turn effects.
 * Author: Glynn Quelch
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: book-viewer-block
 *
 * @package book-viewer-block
 */

namespace BookViewerBlock;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'BOOK_VIEWER_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'BOOK_VIEWER_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * Plugin URL.
 */
define( 'BOOK_VIEWER_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Register StPageFlip library early.
 */
function register_stpageflip() {
	wp_register_script(
		'stpageflip',
		'https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js',
		array(),
		'2.0.7',
		true
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_stpageflip', 1 );

/**
 * Register the block with StPageFlip as view script dependency.
 */
function register_block() {
	// Ensure stpageflip is registered first
	if ( ! wp_script_is( 'stpageflip', 'registered' ) ) {
		register_stpageflip();
	}

	register_block_type(
		BOOK_VIEWER_PLUGIN_DIR . '/build/book-viewer',
		array(
			'view_script_handles' => array( 'stpageflip' ),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_block' );

/**
 * Fallback: ensure StPageFlip loads when block is on page.
 */
function ensure_stpageflip_loaded() {
	if ( is_admin() ) {
		return;
	}

	if ( has_block( 'book-viewer/book-viewer' ) ) {
		wp_enqueue_script( 'stpageflip' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\ensure_stpageflip_loaded', 20 );
