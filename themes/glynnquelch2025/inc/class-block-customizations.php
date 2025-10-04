<?php
/**
 * Block Customizations Service
 *
 * Handles block variations, customizations, and enhancements.
 *
 * @package GlynnQuelch 2025 Blog
 * @since 1.0.0
 */

namespace GlynnQuelch\Blog2025;

/**
 * Block_Customizations
 */
class Block_Customizations {

	/**
	 * Initialize the service.
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
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block', array( $this, 'render_button_with_icon' ), 10, 2 );
	}

	/**
	 * Enqueue editor assets for block customizations.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		// Only enqueue in post editor, not in widgets editor
		if ( get_current_screen()->id === 'widgets' ) {
			return;
		}
		
		wp_enqueue_script(
			'block-customizations',
			GQ2025_URL . 'assets/js/block-customizations.js',
			array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-block-editor' ),
			GQ2025_VERSION,
			true
		);
	}

	/**
	 * Render button with icon.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block data.
	 *
	 * @return string The modified block content.
	 */
	public function render_button_with_icon( string $block_content, array $block ): string {
		// Only process core/button blocks.
		if ( 'core/button' !== $block['blockName'] ) {
			return $block_content;
		}

		// Only process on frontend, not in editor
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $block_content;
		}

		// Get icon attributes.
		$icon_svg = $block['attrs']['iconSvg'] ?? '';
		$icon_position = $block['attrs']['iconPosition'] ?? 'left';
		$icon_only = $block['attrs']['iconOnly'] ?? false;
		// Handle string values from JavaScript
		if ( is_string( $icon_only ) ) {
			$icon_only = ( $icon_only === 'true' || $icon_only === '1' );
		}
		$icon_size = $block['attrs']['iconSize'] ?? '32px';
		$icon_color = $block['attrs']['iconColor'] ?? 'currentColor';
		
		
		if ( empty( $icon_svg ) ) {
			return $block_content;
		}

		// Get button text for tooltip/alt
		$button_text = '';
		if ( preg_match( '/<a[^>]*>([^<]+)<\/a>/', $block_content, $matches ) ) {
			$button_text = $matches[1];
		}

		// Create icon element with positioning styles
		$icon_styles = array(
			'display: inline-block',
			'vertical-align: middle',
			'width: ' . $icon_size,
			'height: ' . $icon_size
		);

		// Add positioning margins (matching JavaScript logic)
		if ( $icon_only ) {
			// No margins for icon-only mode
			$icon_styles[] = 'margin: 0';
		} else {
			switch ( $icon_position ) {
				case 'right':
					$icon_styles[] = 'margin-left: 0.5em';
					$icon_styles[] = 'margin-right: 0';
					break;
				case 'top':
					$icon_styles[] = 'margin-bottom: 0.25em';
					$icon_styles[] = 'margin-top: 0';
					$icon_styles[] = 'margin-left: 0';
					$icon_styles[] = 'margin-right: 0';
					break;
				case 'bottom':
					$icon_styles[] = 'margin-top: 0.25em';
					$icon_styles[] = 'margin-bottom: 0';
					$icon_styles[] = 'margin-left: 0';
					$icon_styles[] = 'margin-right: 0';
					break;
				default: // left
					$icon_styles[] = 'margin-right: 0.5em';
					$icon_styles[] = 'margin-left: 0';
			}
		}

		// Apply color if specified
		if ( $icon_color && $icon_color !== 'clear' ) {
			$icon_styles[] = 'color: ' . $icon_color;
		}

		$icon_html = '<span class="button-icon" style="' . implode( '; ', $icon_styles ) . '"' . 
			( $icon_only ? ' title="' . esc_attr( $button_text ) . '"' : '' ) . '>' . 
			$icon_svg . '</span>';

		// Handle icon-only mode
		if ( $icon_only ) {
			// For icon-only, just replace the button content with the icon
			$modified_content = str_replace(
				'>' . $button_text . '</a>',
				'>' . $icon_html . '</a>',
				$block_content
			);
		} else {
			// Handle different positions
			switch ( $icon_position ) {
				case 'right':
					$modified_content = str_replace(
						'</a>',
						$icon_html . '</a>',
						$block_content
					);
					break;
				case 'top':
					$modified_content = str_replace(
						'<a class="wp-block-button__link',
						'<a class="wp-block-button__link" style="display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important;"',
						$block_content
					);
					$modified_content = str_replace(
						'>' . $button_text,
						'>' . $icon_html . $button_text,
						$modified_content
					);
					break;
				case 'bottom':
					$modified_content = str_replace(
						'<a class="wp-block-button__link',
						'<a class="wp-block-button__link" style="display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important;"',
						$block_content
					);
					$modified_content = str_replace(
						'>' . $button_text,
						'>' . $button_text . $icon_html,
						$modified_content
					);
					break;
				default: // left
					$modified_content = str_replace(
						'>' . $button_text,
						'>' . $icon_html . $button_text,
						$block_content
					);
			}
		}

		return $modified_content;
	}
}

// Initialize the service.
Block_Customizations::init();
