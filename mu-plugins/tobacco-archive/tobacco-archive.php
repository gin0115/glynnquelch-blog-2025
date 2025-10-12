<?php
/**
 * Tobacco Archive Plugin
 *
 * @since       1.0.0
 * @version     1.0.0
 * @package     TobaccoArchive
 * @author      Glynn Quelch
 * @license     GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:             Tobacco Archive
 * Plugin URI:              https://glynnquelch.com
 * Description:             Custom Gutenberg blocks for tobacco blend archives.
 * Version:                 1.0.0
 * Requires at least:       6.0
 * Tested up to:            6.7
 * Requires PHP:            8.0
 * Author:                  Glynn Quelch
 * Author URI:              https://glynnquelch.com
 * License:                 GPL v3 or later
 * License URI:             https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:             tobacco-archive
 * Domain Path:             /languages
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'TOBACCO_ARCHIVE_BASENAME', plugin_basename( __FILE__ ) );
define( 'TOBACCO_ARCHIVE_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TOBACCO_ARCHIVE_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register the tobacco blocks
 */
function tobacco_archive_register_blocks() {
	// Register the tobacco blend block
	$blend_block_path = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-blend/';
	if ( file_exists( $blend_block_path . 'index.js' ) ) {
		register_block_type( $blend_block_path );
	}

	// Register the tobacco query block
	$query_block_path = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-query/';
	if ( file_exists( $query_block_path . 'index.js' ) ) {
		register_block_type( $query_block_path, array(
			'render_callback' => 'tobacco_archive_render_query_block',
		) );
	}
}
add_action( 'init', 'tobacco_archive_register_blocks' );

/**
 * Register the tobacco review custom post type
 */
function tobacco_archive_register_post_type() {
	$labels = array(
		'name'                  => _x( 'Tobacco Reviews', 'Post type general name', 'tobacco-archive' ),
		'singular_name'         => _x( 'Tobacco Review', 'Post type singular name', 'tobacco-archive' ),
		'menu_name'             => _x( 'Tobacco Reviews', 'Admin Menu text', 'tobacco-archive' ),
		'name_admin_bar'        => _x( 'Tobacco Review', 'Add New on Toolbar', 'tobacco-archive' ),
		'add_new'               => __( 'Add New', 'tobacco-archive' ),
		'add_new_item'          => __( 'Add New Tobacco Review', 'tobacco-archive' ),
		'new_item'              => __( 'New Tobacco Review', 'tobacco-archive' ),
		'edit_item'             => __( 'Edit Tobacco Review', 'tobacco-archive' ),
		'view_item'             => __( 'View Tobacco Review', 'tobacco-archive' ),
		'all_items'             => __( 'All Tobacco Reviews', 'tobacco-archive' ),
		'search_items'          => __( 'Search Tobacco Reviews', 'tobacco-archive' ),
		'parent_item_colon'     => __( 'Parent Tobacco Reviews:', 'tobacco-archive' ),
		'not_found'             => __( 'No tobacco reviews found.', 'tobacco-archive' ),
		'not_found_in_trash'    => __( 'No tobacco reviews found in Trash.', 'tobacco-archive' ),
		'featured_image'        => _x( 'Tobacco Blend Image', 'Overrides the "Featured Image" phrase', 'tobacco-archive' ),
		'set_featured_image'    => _x( 'Set tobacco blend image', 'Overrides the "Set featured image" phrase', 'tobacco-archive' ),
		'remove_featured_image' => _x( 'Remove tobacco blend image', 'Overrides the "Remove featured image" phrase', 'tobacco-archive' ),
		'use_featured_image'    => _x( 'Use as tobacco blend image', 'Overrides the "Use as featured image" phrase', 'tobacco-archive' ),
		'archives'              => _x( 'Tobacco review archives', 'The post type archive label', 'tobacco-archive' ),
		'insert_into_item'      => _x( 'Insert into tobacco review', 'Overrides the "Insert into post" phrase', 'tobacco-archive' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this tobacco review', 'Overrides the "Uploaded to this post" phrase', 'tobacco-archive' ),
		'filter_items_list'     => _x( 'Filter tobacco reviews list', 'Screen reader text for the filter links', 'tobacco-archive' ),
		'items_list_navigation' => _x( 'Tobacco reviews list navigation', 'Screen reader text for the pagination', 'tobacco-archive' ),
		'items_list'            => _x( 'Tobacco reviews list', 'Screen reader text for the items list', 'tobacco-archive' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false, // Not public - no frontend access
		'publicly_queryable' => false, // Not queryable on frontend
		'show_ui'            => true, // Show in admin
		'show_in_menu'       => true, // Show in admin menu
		'show_in_nav_menus'  => false, // Not in nav menus
		'show_in_admin_bar'  => true, // Show in admin bar
		'show_in_rest'       => true, // Enable REST API
		'query_var'          => false, // No query var
		'rewrite'            => false, // No rewrite rules
		'capability_type'    => 'post',
		'has_archive'        => false, // No archive page
		'hierarchical'       => false,
		'menu_position'      => null,
		'menu_icon'          => 'dashicons-star-filled',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'taxonomies'         => array(),
		'delete_with_user'   => false,
	);

	register_post_type( 'tobacco_review', $args );
}
add_action( 'init', 'tobacco_archive_register_post_type' );

/**
 * Add REST API endpoint for tobacco reviews
 */
function tobacco_archive_register_rest_routes() {
	register_rest_route(
		'tobacco-archive/v1',
		'/reviews',
		array(
			'methods'             => 'GET',
			'callback'            => 'tobacco_archive_get_reviews',
			'permission_callback' => '__return_true',
			'args'                => array(
				'per_page' => array(
					'default'           => 100,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
// REST API removed - using inline filtering instead
// add_action( 'rest_api_init', 'tobacco_archive_register_rest_routes' );

/**
 * Get tobacco reviews for REST API
 */
function tobacco_archive_get_reviews( $request ) {
	$per_page = $request->get_param( 'per_page' );

	$args = array(
		'post_type'      => 'tobacco_review',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$posts   = get_posts( $args );
	$reviews = array();

	foreach ( $posts as $post ) {
		// Get all the tobacco blend attributes from post meta
		$meta        = get_post_meta( $post->ID );
		$review_data = array(
			'id'      => $post->ID,
			'title'   => $post->post_title,
			'content' => $post->post_content,
		);

		// Map meta fields to review data
		$meta_mapping = array(
			'brandName'        => 'brandName',
			'blendName'        => 'blendName',
			'overallRating'    => 'overallRating',
			'totalReviews'     => 'totalReviews',
			'star4Count'       => 'star4Count',
			'star3Count'       => 'star3Count',
			'star2Count'       => 'star2Count',
			'star1Count'       => 'star1Count',
			'series'           => 'series',
			'blendedBy'        => 'blendedBy',
			'manufacturedBy'   => 'manufacturedBy',
			'blendType'        => 'blendType',
			'contents'         => 'contents',
			'flavoring'        => 'flavoring',
			'cut'              => 'cut',
			'country'          => 'country',
			'productionStatus' => 'productionStatus',
			'strength'         => 'strength',
			'flavoringRating'  => 'flavoringRating',
			'roomNote'         => 'roomNote',
			'taste'            => 'taste',
			'description'      => 'description',
			'notes'            => 'notes',
			'imageUrl'         => 'imageUrl',
		);

		foreach ( $meta_mapping as $meta_key => $data_key ) {
			if ( isset( $meta[ $meta_key ] ) && ! empty( $meta[ $meta_key ][0] ) ) {
				$value = $meta[ $meta_key ][0];
				// Convert numeric values
				if ( in_array( $meta_key, array( 'overallRating', 'totalReviews', 'star4Count', 'star3Count', 'star2Count', 'star1Count' ) ) ) {
					$value = floatval( $value );
				}
				$review_data[ $data_key ] = $value;
			} else {
				$review_data[ $data_key ] = '';
			}
		}

		// Get featured image if available
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
		if ( $thumbnail_id ) {
			$review_data['imageUrl'] = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
		}

		$reviews[] = $review_data;
	}

	return $reviews;
}

/**
 * Server-side render callback for tobacco query block
 */
function tobacco_archive_render_query_block( $attributes, $content ) {
	$show_filters = $attributes['showFilters'] ?? true;
	$items_per_page = $attributes['itemsPerPage'] ?? 12;
	$show_pagination = $attributes['showPagination'] ?? true;
	$grid_columns = $attributes['gridColumns'] ?? 3;
	$show_ratings = $attributes['showRatings'] ?? true;
	$show_profile = $attributes['showProfile'] ?? true;
	$show_description = $attributes['showDescription'] ?? false;

	// Get all tobacco reviews
	$args = array(
		'post_type' => 'tobacco_review',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	);

	$posts = get_posts( $args );
	$tobacco_data = array();
	$filter_options = array(
		'contents' => array(),
		'flavoring' => array(),
		'brandName' => array(),
		'blendType' => array(),
		'manufacturedBy' => array(),
		'series' => array(),
	);

	foreach ( $posts as $post ) {
		$tobacco = array(
			'id' => $post->ID,
			'title' => $post->post_title,
		);

		// Extract data from the tobacco-blend block in post content
		if ( preg_match( '/<!-- wp:tobacco-archive\/tobacco-blend\s+({.*?})\s+-->/', $post->post_content, $matches ) ) {
			$block_attributes = json_decode( $matches[1], true );
			if ( $block_attributes ) {
				$tobacco = array_merge( $tobacco, $block_attributes );
				
				// Process comma-separated values into arrays for filtering
				if ( ! empty( $tobacco['contents'] ) ) {
					$tobacco['contentsArray'] = array_map( 'trim', explode( ',', $tobacco['contents'] ) );
				}
				if ( ! empty( $tobacco['flavoring'] ) ) {
					$tobacco['flavoringArray'] = array_map( 'trim', explode( ',', $tobacco['flavoring'] ) );
				}
			}
		}
		
		// Extract original URL from post content
		if ( preg_match( '/<!-- Original URL: (.*?) -->/', $post->post_content, $url_matches ) ) {
			$tobacco['originalUrl'] = trim( $url_matches[1] );
		}
		
		// Skip if no tobacco data found
		if ( empty( $tobacco['brandName'] ) && empty( $tobacco['blendName'] ) ) {
			continue;
		}

		// Get featured image if no imageUrl from block
		if ( empty( $tobacco['imageUrl'] ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if ( $thumbnail_id ) {
				$tobacco['imageUrl'] = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
			}
		}

		$tobacco_data[] = $tobacco;
		
		// Collect filter options
		foreach ( $filter_options as $key => $values ) {
			if ( $key === 'contents' && ! empty( $tobacco['contentsArray'] ) ) {
				$filter_options[$key] = array_merge( $filter_options[$key], $tobacco['contentsArray'] );
			} elseif ( $key === 'flavoring' && ! empty( $tobacco['flavoringArray'] ) ) {
				$filter_options[$key] = array_merge( $filter_options[$key], $tobacco['flavoringArray'] );
			} elseif ( ! empty( $tobacco[$key] ) ) {
				$filter_options[$key][] = $tobacco[$key];
			}
		}
	}
	
	// Remove duplicates and sort filter options
	foreach ( $filter_options as $key => $values ) {
		$filter_options[$key] = array_unique( array_filter( $values ) );
		sort( $filter_options[$key] );
	}

	// Filter options are now ready

	// Start output
	ob_start();
	?>
	<div class="tobacco-query-block" 
		 data-show-filters="<?php echo esc_attr( $show_filters ? 'true' : 'false' ); ?>"
		 data-items-per-page="<?php echo esc_attr( $items_per_page ); ?>"
		 data-show-pagination="<?php echo esc_attr( $show_pagination ? 'true' : 'false' ); ?>"
		 data-grid-columns="<?php echo esc_attr( $grid_columns ); ?>"
		 data-show-ratings="<?php echo esc_attr( $show_ratings ? 'true' : 'false' ); ?>"
		 data-show-profile="<?php echo esc_attr( $show_profile ? 'true' : 'false' ); ?>"
		 data-show-description="<?php echo esc_attr( $show_description ? 'true' : 'false' ); ?>">

		<?php if ( $show_filters ) : ?>
		<div class="tobacco-query-filters">
			<h3><?php _e( 'Filter Reviews', 'tobacco-archive' ); ?></h3>
			
			<!-- Active Filters Display -->
			<div class="active-filters" style="display: none;">
				<div class="active-filters-label"><?php _e( 'Active Filters:', 'tobacco-archive' ); ?></div>
				<div class="filter-chips"></div>
				<button type="button" class="clear-all-btn"><?php _e( 'Clear All', 'tobacco-archive' ); ?></button>
			</div>
			
			<!-- Filter Options -->
			<div class="filter-options">
				<div class="filter-row">
					<div class="filter-item">
						<label><?php _e( 'Brand', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="brandName">
							<option value=""><?php _e( 'All Brands', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['brandName'] as $brand ) : ?>
								<option value="<?php echo esc_attr( $brand ); ?>"><?php echo esc_html( $brand ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<div class="filter-item">
						<label><?php _e( 'Type', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="blendType">
							<option value=""><?php _e( 'All Types', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['blendType'] as $type ) : ?>
								<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<div class="filter-item">
						<label><?php _e( 'Contents', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="contents">
							<option value=""><?php _e( 'All Contents', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['contents'] as $content ) : ?>
								<option value="<?php echo esc_attr( $content ); ?>"><?php echo esc_html( $content ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				
				<div class="filter-row">
					<div class="filter-item">
						<label><?php _e( 'Manufacturer', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="manufacturedBy">
							<option value=""><?php _e( 'All Manufacturers', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['manufacturedBy'] as $manufacturer ) : ?>
								<option value="<?php echo esc_attr( $manufacturer ); ?>"><?php echo esc_html( $manufacturer ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<div class="filter-item">
						<label><?php _e( 'Series', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="series">
							<option value=""><?php _e( 'All Series', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['series'] as $series ) : ?>
								<option value="<?php echo esc_attr( $series ); ?>"><?php echo esc_html( $series ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<div class="filter-item">
						<label><?php _e( 'Flavoring', 'tobacco-archive' ); ?></label>
						<select class="filter-select" data-filter="flavoring">
							<option value=""><?php _e( 'All Flavoring', 'tobacco-archive' ); ?></option>
							<?php foreach ( $filter_options['flavoring'] as $flavoring ) : ?>
								<option value="<?php echo esc_attr( $flavoring ); ?>"><?php echo esc_html( $flavoring ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			
			<div class="filter-results">
				<span class="results-text">
					<?php printf( __( 'Showing %d of %d reviews', 'tobacco-archive' ), count( $tobacco_data ), count( $tobacco_data ) ); ?>
				</span>
			</div>
		</div>
		<?php endif; ?>

		<div class="tobacco-query-grid" style="--grid-columns: <?php echo esc_attr( $grid_columns ); ?>">
			<?php foreach ( $tobacco_data as $tobacco ) : ?>
				<div class="tobacco-query-item" data-tobacco='<?php echo esc_attr( json_encode( $tobacco ) ); ?>'>
					<div class="tobacco-item-image-container">
						<?php if ( ! empty( $tobacco['imageUrl'] ) ) : ?>
							<img src="<?php echo esc_url( $tobacco['imageUrl'] ); ?>" alt="<?php echo esc_attr( $tobacco['brandName'] ?? '' ); ?> <?php echo esc_attr( $tobacco['blendName'] ?? '' ); ?>" />
						<?php endif; ?>
						
						<div class="tobacco-item-overlay">
							<h4 class="tobacco-item-title">
								<span class="brand-name"><?php echo esc_html( $tobacco['brandName'] ?? '' ); ?></span>
								<span class="blend-name"><?php echo esc_html( $tobacco['blendName'] ?? '' ); ?></span>
							</h4>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Enqueue block assets
 */
function tobacco_archive_enqueue_assets() {
	// Enqueue tobacco blend block assets
	$blend_js_path         = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-blend/index.js';
	$blend_editor_css_path = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-blend/index.css';
	$blend_style_css_path  = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-blend/style-index.css';

	if ( file_exists( $blend_js_path ) ) {
		wp_enqueue_script(
			'tobacco-archive-blend-editor',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-blend/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			filemtime( $blend_js_path ),
			true
		);
	}

	if ( file_exists( $blend_editor_css_path ) ) {
		wp_enqueue_style(
			'tobacco-archive-blend-editor',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-blend/index.css',
			array(),
			filemtime( $blend_editor_css_path )
		);
	}

	if ( file_exists( $blend_style_css_path ) ) {
		wp_enqueue_style(
			'tobacco-archive-blend-style',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-blend/style-index.css',
			array(),
			filemtime( $blend_style_css_path )
		);
	}

	// Enqueue tobacco query block assets
	$query_js_path         = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-query/index.js';
	$query_editor_css_path = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-query/index.css';
	$query_style_css_path  = TOBACCO_ARCHIVE_DIR_PATH . 'blocks/build/tobacco-query/style-index.css';

	if ( file_exists( $query_js_path ) ) {
		wp_enqueue_script(
			'tobacco-archive-query-editor',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-query/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			filemtime( $query_js_path ),
			true
		);
	}

	if ( file_exists( $query_editor_css_path ) ) {
		wp_enqueue_style(
			'tobacco-archive-query-editor',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-query/index.css',
			array(),
			filemtime( $query_editor_css_path )
		);
	}

	if ( file_exists( $query_style_css_path ) ) {
		wp_enqueue_style(
			'tobacco-archive-query-style',
			TOBACCO_ARCHIVE_DIR_URL . 'blocks/build/tobacco-query/style-index.css',
			array(),
			filemtime( $query_style_css_path )
		);
	}

	// Enqueue frontend JavaScript for tobacco query block
	$query_frontend_js_path = TOBACCO_ARCHIVE_DIR_PATH . 'assets/frontend.js';
	if ( file_exists( $query_frontend_js_path ) ) {
		wp_enqueue_script(
			'tobacco-archive-query-frontend',
			TOBACCO_ARCHIVE_DIR_URL . 'assets/frontend.js',
			array(),
			filemtime( $query_frontend_js_path ),
			true
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'tobacco_archive_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'tobacco_archive_enqueue_assets' );

// Include WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once TOBACCO_ARCHIVE_DIR_PATH . 'import-tobacco-reviews.php';
}
