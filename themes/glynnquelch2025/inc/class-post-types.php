<?php

/**
 * Class to handle all Post Type and register related functionality.
 *
 * @package GlynnQuelch 2025 Blog
 * @since 1.0.0
 */

namespace GlynnQuelch\Blog2025;

/**
 * Post_Types
 */
class Post_Types {

	/**
	 * The initialize function to setup hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		$instance = new self();
		$instance->setup_hooks();
	}

	/**
	 * Setup WordPress hooks.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_action( 'pre_get_posts', array( $this, 'include_projects_in_archives' ) );
		add_action( 'init', array( $this, 'register_project_post_type' ) );
		add_filter( 'get_the_archive_title', array( $this, 'remove_archive_title_prefix' ), 10, 3 );
	}

	/**
	 * Remove the prefix from archive titles for projects.
	 *
	 * @param string $title          The current archive title.
	 * @param string $original_title The original archive title.
	 * @param string $prefix         The prefix to remove.
	 *
	 * @return string The modified archive title.
	 */
	public function remove_archive_title_prefix( string $title, string $original_title, string $prefix ): string {
		if ( is_post_type_archive( 'project' ) ) {
			$title = str_replace( $prefix, '', $original_title );
		}
		return $title;
	}

	/**
	 * Register the Project custom post type.
	 *
	 * @return void
	 */
	public function register_project_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Projects', 'Post Type General Name', 'glynnquelch2025' ),
			'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'glynnquelch2025' ),
			'menu_name'             => __( 'Projects', 'glynnquelch2025' ),
			'name_admin_bar'        => __( 'Project', 'glynnquelch2025' ),
			'archives'              => __( 'Project Archives', 'glynnquelch2025' ),
			'attributes'            => __( 'Project Attributes', 'glynnquelch2025' ),
			'parent_item_colon'     => __( 'Parent Project:', 'glynnquelch2025' ),
			'all_items'             => __( 'All Projects', 'glynnquelch2025' ),
			'add_new_item'          => __( 'Add New Project', 'glynnquelch2025' ),
			'add_new'               => __( 'Add New', 'glynnquelch2025' ),
			'new_item'              => __( 'New Project', 'glynnquelch2025' ),
			'edit_item'             => __( 'Edit Project', 'glynnquelch2025' ),
			'update_item'           => __( 'Update Project', 'glynnquelch2025' ),
			'view_item'             => __( 'View Project', 'glynnquelch2025' ),
			'view_items'            => __( 'View Projects', 'glynnquelch2025' ),
			'search_items'          => __( 'Search Project', 'glynnquelch2025' ),
			'not_found'             => __( 'Not found', 'glynnquelch2025' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'glynnquelch2025' ),
			'featured_image'        => __( 'Featured Image', 'glynnquelch2025' ),
			'set_featured_image'    => __( 'Set featured image', 'glynnquelch2025' ),
			'remove_featured_image' => __( 'Remove featured image', 'glynnquelch2025' ),
			'use_featured_image'    => __( 'Use as featured image', 'glynnquelch2025' ),
			'insert_into_item'      => __( 'Insert into project', 'glynnquelch2025' ),
			'uploaded_to_this_item' => __( 'Uploaded to this project', 'glynnquelch2025' ),
			'items_list'            => __( 'Projects list', 'glynnquelch2025' ),
			'items_list_navigation' => __( 'Projects list navigation', 'glynnquelch2025' ),
			'filter_items_list'     => __( 'Filter projects list', 'glynnquelch2025' ),
		);
		// Add support for gutenberg features like excerpts and custom fields.
		$args = array(
			'label'                 => __( 'Project', 'glynnquelch2025' ),
			'description'           => '',
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-portfolio',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'projects',
			'rest_controller_class' => 'WP_REST_Posts_Controller',

		);
		register_post_type( 'project', $args );
	}

	/**
	 * Allows the Project post type to be included in the archive and search results.
	 *
	 * @param \WP_Query $query The current WP_Query instance.
	 *
	 * @return void
	 */
	public function include_projects_in_archives( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_archive() || $query->is_search() ) {
			$post_types = $query->get( 'post_type' );

			if ( empty( $post_types ) ) {
				$post_types = array( 'post', 'project' );
			} elseif ( is_string( $post_types ) ) {
				$post_types = array( $post_types, 'project' );
			} elseif ( is_array( $post_types ) && ! in_array( 'project', $post_types, true ) ) {
				$post_types[] = 'project';
			}

			$query->set( 'post_type', $post_types );
		}
	}
}
Post_Types::init();
