<?php
/**
 * WP-CLI command to import tobacco reviews from HTML files
 * 
 * Usage: wp tobacco-import import-reviews
 */

if ( ! defined( 'WP_CLI' ) ) {
    return;
}

class Tobacco_Import_Command extends WP_CLI_Command {

    /**
     * Import tobacco reviews from HTML files
     * 
     * ## OPTIONS
     * 
     * [--dry-run]
     * : Show what would be imported without actually importing
     * 
     * [--force]
     * : Force reimport even if posts already exist
     * 
     * ## EXAMPLES
     * 
     *     wp tobacco-import import-reviews
     *     wp tobacco-import import-reviews --dry-run
     *     wp tobacco-import import-reviews --force
     * 
     * @when after_wp_load
     */
    public function import_reviews( $args, $assoc_args ) {
        $dry_run = isset( $assoc_args['dry-run'] );
        $force = isset( $assoc_args['force'] );
        
        $import_dir = __DIR__ . '/import';
        $images_dir = $import_dir . '/images';
        
        if ( ! is_dir( $import_dir ) ) {
            WP_CLI::error( 'Import directory not found: ' . $import_dir );
        }
        
        if ( ! is_dir( $images_dir ) ) {
            WP_CLI::error( 'Images directory not found: ' . $images_dir );
        }
        
        $html_files = glob( $import_dir . '/*.html' );
        
        if ( empty( $html_files ) ) {
            WP_CLI::error( 'No HTML files found in import directory' );
        }
        
        WP_CLI::log( 'Found ' . count( $html_files ) . ' HTML files to process' );
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ( $html_files as $html_file ) {
            $filename = basename( $html_file, '.html' );
            
            try {
                $result = $this->import_single_review( $html_file, $images_dir, $dry_run, $force );
                
                if ( $result['status'] === 'imported' ) {
                    $imported++;
                    WP_CLI::success( "Imported: {$filename}" );
                } elseif ( $result['status'] === 'skipped' ) {
                    $skipped++;
                    WP_CLI::warning( "Skipped: {$filename} - {$result['reason']}" );
                } else {
                    $errors++;
                    WP_CLI::error( "Error: {$filename} - {$result['reason']}" );
                }
                
            } catch ( Exception $e ) {
                $errors++;
                WP_CLI::error( "Exception: {$filename} - " . $e->getMessage() );
            }
        }
        
        WP_CLI::log( '' );
        WP_CLI::success( "Import complete!" );
        WP_CLI::log( "Imported: {$imported}" );
        WP_CLI::log( "Skipped: {$skipped}" );
        WP_CLI::log( "Errors: {$errors}" );
    }
    
    /**
     * Import a single tobacco review
     */
    private function import_single_review( $html_file, $images_dir, $dry_run, $force ) {
        $content = file_get_contents( $html_file );
        
        if ( ! $content ) {
            return [ 'status' => 'error', 'reason' => 'Could not read file' ];
        }
        
        // Extract block attributes from the HTML comment
        if ( ! preg_match( '/<!-- wp:tobacco-archive\/tobacco-blend\s+({.*?})\s+-->/', $content, $matches ) ) {
            return [ 'status' => 'error', 'reason' => 'No block attributes found' ];
        }
        
        $attributes = json_decode( $matches[1], true );
        
        if ( ! $attributes ) {
            return [ 'status' => 'error', 'reason' => 'Invalid JSON in block attributes' ];
        }
        
        // Extract original URL
        $original_url = '';
        if ( preg_match( '/<!-- Original URL: (.*?) -->/', $content, $url_matches ) ) {
            $original_url = trim( $url_matches[1] );
        }
        
        // Create post title
        $title = trim( ( $attributes['brandName'] ?? '' ) . ' ' . ( $attributes['blendName'] ?? '' ) );
        
        if ( empty( $title ) ) {
            return [ 'status' => 'error', 'reason' => 'No title found' ];
        }
        
        // Check if post already exists
        $existing_posts = get_posts( [
            'post_type' => 'tobacco_review',
            'title' => $title,
            'post_status' => 'any',
            'numberposts' => 1
        ] );
        $existing_post = ! empty( $existing_posts ) ? $existing_posts[0] : null;
        
        if ( $existing_post && ! $force ) {
            return [ 'status' => 'skipped', 'reason' => 'Post already exists' ];
        }
        
        if ( $dry_run ) {
            return [ 'status' => 'imported', 'reason' => 'Dry run - would import' ];
        }
        
        // Prepare post data
        $post_data = [
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'tobacco_review',
            'post_author'  => 1, // Admin user
        ];
        
        // Update existing post or create new one
        if ( $existing_post ) {
            $post_data['ID'] = $existing_post->ID;
            $post_id = wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }
        
        if ( is_wp_error( $post_id ) ) {
            return [ 'status' => 'error', 'reason' => $post_id->get_error_message() ];
        }
        
        // Handle featured image
        if ( ! empty( $attributes['imageUrl'] ) ) {
            $this->set_featured_image( $post_id, $attributes['imageUrl'], $images_dir );
        }
        
        // Add original URL as meta
        if ( $original_url ) {
            update_post_meta( $post_id, '_original_url', $original_url );
        }
        
        return [ 'status' => 'imported', 'reason' => 'Success' ];
    }
    
    /**
     * Set featured image for the post
     */
    private function set_featured_image( $post_id, $image_url, $images_dir ) {
        // Extract filename from URL
        $filename = basename( parse_url( $image_url, PHP_URL_PATH ) );
        $local_image_path = $images_dir . '/' . $filename;
        
        if ( ! file_exists( $local_image_path ) ) {
            WP_CLI::warning( "Image file not found: {$filename}" );
            return;
        }
        
        // Check if attachment already exists
        $existing_attachment = get_posts( [
            'post_type' => 'attachment',
            'meta_query' => [
                [
                    'key' => '_wp_attached_file',
                    'value' => 'tobacco-reviews/' . $filename,
                    'compare' => 'LIKE'
                ]
            ]
        ] );
        
        if ( ! empty( $existing_attachment ) ) {
            $attachment_id = $existing_attachment[0]->ID;
        } else {
            // Upload the image
            $upload_dir = wp_upload_dir();
            $tobacco_dir = $upload_dir['basedir'] . '/tobacco-reviews';
            
            if ( ! is_dir( $tobacco_dir ) ) {
                wp_mkdir_p( $tobacco_dir );
            }
            
            $destination = $tobacco_dir . '/' . $filename;
            
            if ( ! copy( $local_image_path, $destination ) ) {
                WP_CLI::warning( "Could not copy image: {$filename}" );
                return;
            }
            
            // Create attachment
            $attachment = [
                'post_mime_type' => wp_check_filetype( $filename )['type'],
                'post_title'     => sanitize_file_name( $filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ];
            
            $attachment_id = wp_insert_attachment( $attachment, $destination, $post_id );
            
            if ( is_wp_error( $attachment_id ) ) {
                WP_CLI::warning( "Could not create attachment: {$filename}" );
                return;
            }
            
            // Generate attachment metadata
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $destination );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );
        }
        
        // Set as featured image
        set_post_thumbnail( $post_id, $attachment_id );
        
        // Update the imageUrl in the block attributes to use the new URL
        $post = get_post( $post_id );
        $new_image_url = wp_get_attachment_image_url( $attachment_id, 'full' );
        
        if ( $new_image_url ) {
            $updated_content = str_replace( $image_url, $new_image_url, $post->post_content );
            wp_update_post( [
                'ID' => $post_id,
                'post_content' => $updated_content
            ] );
        }
    }
}

WP_CLI::add_command( 'tobacco-import', 'Tobacco_Import_Command' );
