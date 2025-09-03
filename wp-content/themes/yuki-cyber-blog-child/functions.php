<?php
/**
 * Yuki Cyber Blog Child Theme Functions
 *
 * @package Yuki_Cyber_Blog_Child
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue parent and child theme styles
 *
 * @return void
 */
function yuki_cyber_blog_child_enqueue_styles() {
	// Enqueue parent theme stylesheet
	wp_enqueue_style(
		'yuki-cyber-blog-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->parent()->get( 'Version' )
	);

	// Enqueue child theme stylesheet
	wp_enqueue_style(
		'yuki-cyber-blog-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'yuki-cyber-blog-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'yuki_cyber_blog_child_enqueue_styles' );

/**
 * Add your custom functions below this line
 */
