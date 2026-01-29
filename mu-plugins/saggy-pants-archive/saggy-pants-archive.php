<?php
/**
 * Plugin Name: Saggy Pants Archive
 * Description: Archive post type, term images, and jukebox auto-updater
 * Version: 1.0.0
 * Author: Glynn Quelch
 */

namespace SaggyPants;

defined( 'ABSPATH' ) || exit;

define( 'SAGGY_PANTS_PATH', __DIR__ );
define( 'SAGGY_PANTS_URL', plugin_dir_url( __FILE__ ) );
define( 'SAGGY_PANTS_ARCHIVE_DIR_URL', SAGGY_PANTS_URL ); // Alias for legacy class

// Load classes
require_once SAGGY_PANTS_PATH . '/src/Settings.php';
require_once SAGGY_PANTS_PATH . '/src/Scheduler.php';
require_once SAGGY_PANTS_PATH . '/src/JukeboxUpdater.php';
require_once SAGGY_PANTS_PATH . '/src/PlaylistGenerator.php';
require_once SAGGY_PANTS_PATH . '/src/class-archive-type-image.php';

// Initialize
add_action(
	'plugins_loaded',
	function () {
		// Jukebox updater
		Settings::init();
		Scheduler::init();
		JukeboxUpdater::init();

		// Archive type term images
		\Archive_Type_Image::init();
	}
);

/**
 * Register the sp-archive custom post type
 */
function saggy_pants_archive_register_post_type() {
	$labels = array(
		'name'                  => _x( 'Archive', 'Post type general name', 'saggy-pants-archive' ),
		'singular_name'         => _x( 'Archive', 'Post type singular name', 'saggy-pants-archive' ),
		'menu_name'             => _x( 'SP Archive', 'Admin Menu text', 'saggy-pants-archive' ),
		'name_admin_bar'        => _x( 'Archive', 'Add New on Toolbar', 'saggy-pants-archive' ),
		'add_new'               => __( 'Add New', 'saggy-pants-archive' ),
		'add_new_item'          => __( 'Add New Archive', 'saggy-pants-archive' ),
		'new_item'              => __( 'New Archive', 'saggy-pants-archive' ),
		'edit_item'             => __( 'Edit Archive', 'saggy-pants-archive' ),
		'view_item'             => __( 'View Archive', 'saggy-pants-archive' ),
		'all_items'             => __( 'All Archives', 'saggy-pants-archive' ),
		'search_items'          => __( 'Search Archives', 'saggy-pants-archive' ),
		'parent_item_colon'     => __( 'Parent Archives:', 'saggy-pants-archive' ),
		'not_found'             => __( 'No archives found.', 'saggy-pants-archive' ),
		'not_found_in_trash'    => __( 'No archives found in Trash.', 'saggy-pants-archive' ),
		'featured_image'        => _x( 'Archive Image', 'Overrides the "Featured Image" phrase', 'saggy-pants-archive' ),
		'set_featured_image'    => _x( 'Set archive image', 'Overrides the "Set featured image" phrase', 'saggy-pants-archive' ),
		'remove_featured_image' => _x( 'Remove archive image', 'Overrides the "Remove featured image" phrase', 'saggy-pants-archive' ),
		'use_featured_image'    => _x( 'Use as archive image', 'Overrides the "Use as featured image" phrase', 'saggy-pants-archive' ),
		'archives'              => _x( 'Archive archives', 'The post type archive label', 'saggy-pants-archive' ),
		'insert_into_item'      => _x( 'Insert into archive', 'Overrides the "Insert into post" phrase', 'saggy-pants-archive' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this archive', 'Overrides the "Uploaded to this post" phrase', 'saggy-pants-archive' ),
		'filter_items_list'     => _x( 'Filter archives list', 'Screen reader text for the filter links', 'saggy-pants-archive' ),
		'items_list_navigation' => _x( 'Archives list navigation', 'Screen reader text for the pagination', 'saggy-pants-archive' ),
		'items_list'            => _x( 'Archives list', 'Screen reader text for the items list', 'saggy-pants-archive' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'archive' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => null,
		'menu_icon'          => 'dashicons-archive',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes' ),
		'taxonomies'         => array( 'post_tag', 'category' ),
		'delete_with_user'   => false,
	);

	register_post_type( 'sp-archive', $args );
}
add_action( 'init', __NAMESPACE__ . '\saggy_pants_archive_register_post_type' );

/**
 * Register archive-type taxonomy
 */
function saggy_pants_archive_register_type_taxonomy() {
	$labels = array(
		'name'              => _x( 'Archive Types', 'taxonomy general name', 'saggy-pants-archive' ),
		'singular_name'     => _x( 'Archive Type', 'taxonomy singular name', 'saggy-pants-archive' ),
		'search_items'      => __( 'Search Archive Types', 'saggy-pants-archive' ),
		'all_items'         => __( 'All Archive Types', 'saggy-pants-archive' ),
		'parent_item'       => __( 'Parent Archive Type', 'saggy-pants-archive' ),
		'parent_item_colon' => __( 'Parent Archive Type:', 'saggy-pants-archive' ),
		'edit_item'         => __( 'Edit Archive Type', 'saggy-pants-archive' ),
		'update_item'       => __( 'Update Archive Type', 'saggy-pants-archive' ),
		'add_new_item'      => __( 'Add New Archive Type', 'saggy-pants-archive' ),
		'new_item_name'     => __( 'New Archive Type Name', 'saggy-pants-archive' ),
		'menu_name'         => __( 'Archive Types', 'saggy-pants-archive' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'archive-type' ),
	);

	register_taxonomy( 'archive-type', array( 'sp-archive' ), $args );
}
add_action( 'init', __NAMESPACE__ . '\saggy_pants_archive_register_type_taxonomy' );

/**
 * Register sp-band taxonomy
 */
function saggy_pants_archive_register_band_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Bands', 'taxonomy general name', 'saggy-pants-archive' ),
		'singular_name'              => _x( 'Band', 'taxonomy singular name', 'saggy-pants-archive' ),
		'search_items'               => __( 'Search Bands', 'saggy-pants-archive' ),
		'popular_items'              => __( 'Popular Bands', 'saggy-pants-archive' ),
		'all_items'                  => __( 'All Bands', 'saggy-pants-archive' ),
		'edit_item'                  => __( 'Edit Band', 'saggy-pants-archive' ),
		'update_item'                => __( 'Update Band', 'saggy-pants-archive' ),
		'add_new_item'               => __( 'Add New Band', 'saggy-pants-archive' ),
		'new_item_name'              => __( 'New Band Name', 'saggy-pants-archive' ),
		'separate_items_with_commas' => __( 'Separate bands with commas', 'saggy-pants-archive' ),
		'add_or_remove_items'        => __( 'Add or remove bands', 'saggy-pants-archive' ),
		'choose_from_most_used'      => __( 'Choose from the most used bands', 'saggy-pants-archive' ),
		'not_found'                  => __( 'No bands found.', 'saggy-pants-archive' ),
		'menu_name'                  => __( 'Bands', 'saggy-pants-archive' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'band' ),
	);

	register_taxonomy( 'sp-band', array( 'sp-archive' ), $args );
}
add_action( 'init', __NAMESPACE__ . '\saggy_pants_archive_register_band_taxonomy' );

/**
 * Set default sort order for sp-archive archives and ensure taxonomy queries work
 */
function saggy_pants_archive_set_archive_sort_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Set sort order for post type archives
	if ( is_post_type_archive( 'sp-archive' ) ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	// Ensure taxonomy archives query the correct post type
	if ( is_tax( 'sp-band' ) || is_tax( 'archive-type' ) ) {
		$query->set( 'post_type', 'sp-archive' );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	// Ensure tag archives only show sp-archive posts
	if ( is_tag() ) {
		$query->set( 'post_type', 'sp-archive' );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\saggy_pants_archive_set_archive_sort_order' );
