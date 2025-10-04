<?php
/**
 * Button with Icon Block
 *
 * @package GlynnQuelch 2025 Blog
 * @since 1.0.0
 */

namespace GlynnQuelch\Blog2025;

/**
 * Button_With_Icon_Block
 */
class Button_With_Icon_Block {

	/**
	 * Initialize the block.
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
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the Button with Icon block.
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type(
			'glynnquelch2025/button-with-icon',
			array(
				'api_version'     => 2,
				'attributes'      => array(
					'url'           => array(
						'type'    => 'string',
						'default' => '',
					),
					'text'          => array(
						'type'    => 'string',
						'default' => 'Button',
					),
					'icon'          => array(
						'type'    => 'string',
						'default' => 'github',
					),
					'className'     => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'render_callback' => array( $this, 'render_block' ),
				'supports'        => array(
					'html' => false,
				),
			)
		);
	}

	/**
	 * Render the Button with Icon block.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content   Block content.
	 *
	 * @return string The rendered block HTML.
	 */
	public function render_block( array $attributes, string $content ): string {
		$url        = esc_url( $attributes['url'] ?? '' );
		$text       = esc_html( $attributes['text'] ?? 'Button' );
		$icon       = esc_attr( $attributes['icon'] ?? 'github' );
		$class_name = esc_attr( $attributes['className'] ?? '' );

		// Don't render if no URL.
		if ( empty( $url ) ) {
			return '';
		}

		// Get the SVG icon.
		$svg_icon = $this->get_icon_svg( $icon );

		// Build the button HTML.
		$button_class = 'wp-block-button__link button-with-icon';
		if ( ! empty( $class_name ) ) {
			$button_class .= ' ' . $class_name;
		}

		$output = sprintf(
			'<div class="wp-block-button button-with-icon-wrapper">
				<a class="%s" href="%s">
					%s
					%s
				</a>
			</div>',
			$button_class,
			$url,
			$svg_icon,
			$text
		);

		return $output;
	}

	/**
	 * Get SVG icon for the specified icon type.
	 *
	 * @param string $icon_type The icon type.
	 *
	 * @return string The SVG icon HTML.
	 */
	private function get_icon_svg( string $icon_type ): string {
		$icons = array(
			'github'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="software-link-icon"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
			'packagist'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="software-link-icon"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.6 0 12 0zm4.5 17.5L12 15l-4.5 2.5V6.5h9v11z"/></svg>',
			'wordpress'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="software-link-icon"><path d="M12.158 12.786L9.46 20.625c.806.237 1.657.366 2.54.366 1.047 0 2.051-.18 2.986-.512-.024-.038-.046-.079-.065-.123l-2.762-7.57zM3.009 12c0 3.559 2.068 6.634 5.067 8.092L3.788 8.341C3.289 9.459 3.009 10.696 3.009 12zm15.54.096c0-1.112-.399-1.881-.742-2.48-.456-.742-.884-1.368-.884-2.109 0-.826.627-1.596 1.51-1.596.04 0 .078.005.116.007-1.598-1.464-3.73-2.359-6.07-2.359-3.141 0-5.904 1.612-7.512 4.053.211.007.41.011.579.011.94 0 2.395-.114 2.395-.114.484-.028.542.684.057.741 0 0-.487.057-.999.086l3.181 9.461 1.911-5.734-1.36-3.727c-.485-.029-.944-.086-.944-.086-.486-.029-.429-.77.057-.742 0 0 1.484.114 2.368.114.94 0 2.395-.114 2.395-.114.485-.028.543.684.057.741 0 0-.488.057-1 .086l3.157 9.393.871-2.909c.376-1.206.663-2.072.663-2.818zm1.783-6.985c.04.288.062.597.062.929 0 .915-.171 1.943-.684 3.226l-2.746 7.94c2.672-1.558 4.47-4.454 4.47-7.771 0-1.565-.4-3.038-1.102-4.324zM12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/></svg>',
			'docs'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="software-link-icon"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
			'download'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="software-link-icon"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>',
		);

		return $icons[ $icon_type ] ?? $icons['github'];
	}
}
