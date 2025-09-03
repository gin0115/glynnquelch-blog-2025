<?php
/**
 * Theme bootstrap
 *
 * @package Yuki Cyber Blog
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'YUKI_CYBER_BLOG_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( 'YUKI_CYBER_BLOG_VERSION', '1.0.0' );
}

if ( ! defined( 'YUKI_CYBER_BLOG_PATH' ) ) {
	define( 'YUKI_CYBER_BLOG_PATH', trailingslashit( get_stylesheet_directory() ) );
}

if ( ! defined( 'YUKI_CYBER_BLOG_URL' ) ) {
	define( 'YUKI_CYBER_BLOG_URL', trailingslashit( get_stylesheet_directory_uri() ) );
}

if ( ! defined( 'YUKI_CYBER_BLOG_ASSETS_URL' ) ) {
	define( 'YUKI_CYBER_BLOG_ASSETS_URL', YUKI_CYBER_BLOG_URL . 'assets/' );
}

if ( ! function_exists( 'yuki_cyber_blog_image_url' ) ) {
	/**
	 * Get image url
	 *
	 * @param $image
	 *
	 * @return string
	 */
	function yuki_cyber_blog_image_url( $image ) {
		return YUKI_CYBER_BLOG_ASSETS_URL . 'images/' . $image;
	}
}

// require customizer options
require_once YUKI_CYBER_BLOG_PATH . 'customizer.php';
