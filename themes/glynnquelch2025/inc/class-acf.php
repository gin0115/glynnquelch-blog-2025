<?php 

/**
 * Class to handle ACF custom fields and settings.
 * 
 * @package GlynnQuelch 2025 Blog
 * @since 1.0.0
 */

namespace GlynnQuelch\Blog2025;

/**
 * ACF
 */
class ACF {

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
     * @since 1.0.0
     */
    private function setup_hooks(): void {
        add_filter( 'acf/settings/load_json', array( $this, 'load_acf_json' ) );
        add_filter( 'acf/settings/save_json', array( $this, 'save_acf_json' ) );
    }

    /**
     * Add custom path for loading ACF JSON files from theme.
     * 
     * @param array<string> $paths Array of existing JSON load paths.
     * @return array<string> Modified array of paths.
     * @since 1.0.0
     */
    public function load_acf_json( array $paths ): array {
        $theme_path = get_stylesheet_directory() . '/acf-json';
        
        // Only add the path if the directory exists
        if ( is_dir( $theme_path ) ) {
            $paths[] = $theme_path;
        }
        
        return $paths;
    }

    /**
     * Set custom path for saving ACF JSON files to theme.
     * 
     * @param string $path The original save path.
     * @return string The modified save path.
     * @since 1.0.0
     */
    public function save_acf_json( string $path ): string {
        $theme_path = get_stylesheet_directory() . '/acf-json';
        
        // Create directory if it doesn't exist
        if ( ! is_dir( $theme_path ) ) {
            wp_mkdir_p( $theme_path );
        }
        
        return $theme_path;
    }
}
ACF::init();