<?php
/**
 * Theme customizer options
 *
 * @package GlynnQuelch2025
 * @author  Glynn Quelch
 * @since   1.0.0
 */

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gq_2025_return_yes' ) ) {
	/**
	 * Return 'yes' string value.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_return_yes() {
		return 'yes';
	}
}

if ( ! function_exists( 'gq_2025_return_no' ) ) {
	/**
	 * Return 'no' string value.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_return_no() {
		return 'no';
	}
}

if ( ! function_exists( 'gq_2025_base_100_background' ) ) {
	/**
	 * Return base-100 background configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_base_100_background() {
		return array(
			'type'  => 'color',
			'color' => 'var(--yuki-base-100)',
		);
	}
}

if ( ! function_exists( 'gq_2025_base_color_background' ) ) {
	/**
	 * Return base-color background configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_base_color_background() {
		return array(
			'type'  => 'color',
			'color' => 'var(--yuki-base-color)',
		);
	}
}

if ( ! function_exists( 'gq_2025_border_none' ) ) {
	/**
	 * Return no border configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_border_none() {
		return array(
			'style'   => 'none',
			'width'   => 1,
			'color'   => 'var(--yuki-base-300)',
			'hover'   => '',
			'inherit' => false,
		);
	}
}

//
// Disable default customizer cache
//
add_filter( 'yuki_enable_customizer_cache_default_value', 'gq_2025_return_no' );

//
// Header & footer background
//
add_filter( 'yuki_header_top_bar_row_background_default_value', 'gq_2025_base_100_background' );
add_filter( 'yuki_header_primary_navbar_row_background_default_value', 'gq_2025_base_100_background' );
add_filter( 'yuki_header_bottom_row_row_background_default_value', 'gq_2025_base_100_background' );

add_filter( 'yuki_footer_top_row_background_default_value', 'gq_2025_base_color_background' );
add_filter( 'yuki_footer_middle_row_background_default_value', 'gq_2025_base_color_background' );
add_filter( 'yuki_footer_bottom_row_background_default_value', 'gq_2025_base_color_background' );

//
// Header top row
//
if ( ! function_exists( 'gq_2025_header_top_row_elements' ) ) {
	/**
	 * Return header top row elements configuration.
	 *
	 * @return array<string, array<mixed>>
	 * @since 1.0.0
	 */
	function gq_2025_header_top_row_elements() {
		return array(
			'desktop' => array(
				array(
					'elements' => array(),
					'settings' => array( 'width' => '33.33%' ),
				),
				array(
					'elements' => array(),
					'settings' => array( 'width' => '33.33%' ),
				),
				array(
					'elements' => array(),
					'settings' => array( 'width' => '33.33%' ),
				),
			),
			'mobile'  => array(
				array(
					'elements' => array(),
					'settings' => array(
						'width'           => '100%',
						'justify-content' => 'center',
					),
				),
			),
		);
	}
}
add_filter( 'yuki_header_top_row_default_value', 'gq_2025_header_top_row_elements' );

if ( ! function_exists( 'gq_2025_header_top_row_height' ) ) {
	/**
	 * Return header top row height.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_header_top_row_height() {
		return '40px';
	}
}
add_filter( 'yuki_header_top_bar_row_min_height_default_value', 'gq_2025_header_top_row_height' );


//
// Header middle row
//

if ( ! function_exists( 'gq_2025_header_primary_row_elements' ) ) {
	/**
	 * Return header primary row elements configuration.
	 *
	 * @return array<string, array<mixed>>
	 * @since 1.0.0
	 */
	function gq_2025_header_primary_row_elements() {
		return array(
			'desktop' => array(
				array(
					'elements' => array( 'logo' ),
					'settings' => array(
						'width'           => '100%',
						'justify-content' => 'center',
					),
				),
			),
			'mobile'  => array(
				array(
					'elements' => array( 'logo' ),
					'settings' => array(
						'width'           => '100%',
						'justify-content' => 'center',
					),
				),
			),
		);
	}
}
add_filter( 'yuki_header_primary_row_default_value', 'gq_2025_header_primary_row_elements' );

if ( ! function_exists( 'gq_2025_primary_navbar_row_height' ) ) {
	/**
	 * Return primary navbar row height.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_primary_navbar_row_height() {
		return '160px';
	}
}
add_filter( 'yuki_header_primary_navbar_row_min_height_default_value', 'gq_2025_primary_navbar_row_height' );

if ( ! function_exists( 'gq_2025_header_row_background' ) ) {
	/**
	 * Return header row background configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_header_row_background() {
		return array(
			'type'  => 'color',
			'color' => 'var(--yuki-base-color)',
		);
	}
}
add_filter( 'yuki_header_primary_navbar_row_background_default_value', 'gq_2025_header_row_background' );
add_filter( 'yuki_header_bottom_row_background_default_value', 'gq_2025_header_row_background' );

if ( ! function_exists( 'gq_2025_primary_navbar_row_border_bottom' ) ) {
	/**
	 * Return primary navbar row border bottom configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_primary_navbar_row_border_bottom() {
		return array(
			'style'   => 'none',
			'width'   => 1,
			'color'   => 'var(--yuki-base-200)',
			'hover'   => '',
			'inherit' => false,
		);
	}
}
add_filter( 'yuki_header_primary_navbar_row_border_bottom_default_value', 'gq_2025_primary_navbar_row_border_bottom' );


//
// Header bottom row
//
if ( ! function_exists( 'gq_2025_header_bottom_row_elements' ) ) {
	/**
	 * Return header bottom row elements configuration.
	 *
	 * @return array<string, array<mixed>>
	 * @since 1.0.0
	 */
	function gq_2025_header_bottom_row_elements() {
		return array(
			'desktop' => array(
				array(
					'elements' => array( 'trigger' ),
					'settings' => array( 'width' => '20%' ),
				),
				array(
					'elements' => array( 'menu-2' ),
					'settings' => array(
						'width'           => '60%',
						'justify-content' => 'center',
					),
				),
				array(
					'elements' => array( 'socials', 'theme-switch', 'search' ),
					'settings' => array(
						'width'           => '20%',
						'justify-content' => 'flex-end',
					),
				),
			),
			'mobile'  => array(
				array(
					'elements' => array( 'trigger' ),
					'settings' => array( 'width' => '20%' ),
				),
				array(
					'elements' => array( 'socials' ),
					'settings' => array(
						'width'           => '60%',
						'justify-content' => 'center',
					),
				),
				array(
					'elements' => array( 'theme-switch', 'search' ),
					'settings' => array(
						'width'           => '20%',
						'justify-content' => 'flex-end',
					),
				),
			),
		);
	}
}
add_filter( 'yuki_header_bottom_row_default_value', 'gq_2025_header_bottom_row_elements' );

if ( ! function_exists( 'gq_2025_bottom_row_height' ) ) {
	/**
	 * Return bottom row height.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_bottom_row_height() {
		return '60px';
	}
}
add_filter( 'yuki_header_bottom_row_row_min_height_default_value', 'gq_2025_bottom_row_height' );

if ( ! function_exists( 'gq_2025_bottom_row_border_top' ) ) {
	/**
	 * Return bottom row border top configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_bottom_row_border_top() {
		return array(
			'style'   => 'solid',
			'width'   => 1,
			'color'   => 'var(--yuki-base-300)',
			'hover'   => '',
			'inherit' => false,
		);
	}
}
add_filter( 'yuki_header_bottom_row_row_border_top_default_value', 'gq_2025_bottom_row_border_top' );

if ( ! function_exists( 'gq_2025_bottom_row_border_bottom' ) ) {
	/**
	 * Return bottom row border bottom configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_bottom_row_border_bottom() {
		return array(
			'style'   => 'solid',
			'width'   => 1,
			'color'   => 'var(--yuki-base-300)',
			'hover'   => '',
			'inherit' => false,
		);
	}
}
add_filter( 'yuki_header_bottom_row_row_border_bottom_default_value', 'gq_2025_bottom_row_border_bottom' );

//
// Canvas area
//
if ( ! function_exists( 'gq_2025_canvas_drawer_placement' ) ) {
	/**
	 * Return canvas drawer placement.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_canvas_drawer_placement() {
		return 'left';
	}
}
add_filter( 'yuki_canvas_drawer_placement_default_value', 'gq_2025_canvas_drawer_placement' );

//
// Header menu element
//
if ( ! function_exists( 'gq_2025_header_menu_typography' ) ) {
	/**
	 * Return header menu typography configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_header_menu_typography() {
		return array(
			'family'        => 'inherit',
			'fontSize'      => '0.8rem',
			'variant'       => '600',
			'lineHeight'    => '1',
			'textTransform' => 'uppercase',
		);
	}
}
add_filter( 'yuki_header_el_menu_2_top_level_typography_default_value', 'gq_2025_header_menu_typography' );

if ( ! function_exists( 'gq_2025_header_menu_depth' ) ) {
	/**
	 * Return header menu depth.
	 *
	 * @return integer
	 * @since 1.0.0
	 */
	function gq_2025_header_menu_depth() {
		return 0;
	}
}
add_filter( 'yuki_header_el_menu_1_depth_default_value', 'gq_2025_header_menu_depth' );

if ( ! function_exists( 'gq_2025_header_menu_dropdown_typography' ) ) {
	/**
	 * Return header menu dropdown typography configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_header_menu_dropdown_typography() {
		return array(
			'family'     => 'inherit',
			'fontSize'   => '1rem',
			'variant'    => '500',
			'lineHeight' => '1',
		);
	}
}
add_filter( 'yuki_header_el_menu_1_dropdown_typography_default_value', 'gq_2025_header_menu_dropdown_typography' );

//
// Header socials element
//
if ( ! function_exists( 'gq_2025_socials_icons_color_type' ) ) {
	/**
	 * Return socials icons color type.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_socials_icons_color_type() {
		return 'custom';
	}
}
add_filter( 'yuki_header_el_socials_icons_color_type_default_value', 'gq_2025_socials_icons_color_type' );

//
// Header logo element
//

add_filter( 'yuki_header_el_logo_enable_site_tagline_default_value', 'gq_2025_return_yes' );

if ( ! function_exists( 'gq_2025_header_el_logo_content_alignment' ) ) {
	/**
	 * Return header logo content alignment.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_header_el_logo_content_alignment() {
		return 'center';
	}
}
add_filter( 'yuki_header_el_logo_content_alignment_default_value', 'gq_2025_header_el_logo_content_alignment' );

if ( ! function_exists( 'gq_2025_header_el_logo_position' ) ) {
	/**
	 * Return header logo position.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_header_el_logo_position() {
		return 'top';
	}
}
add_filter( 'yuki_header_el_logo_position_default_value', 'gq_2025_header_el_logo_position' );

if ( ! function_exists( 'gq_2025_header_el_logo_site_title_typography' ) ) {
	/**
	 * Return header logo site title typography configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_header_el_logo_site_title_typography() {
		return array(
			'family'        => 'inherit',
			'fontSize'      => '32px',
			'variant'       => '600',
			'lineHeight'    => '1.7',
			'textTransform' => 'uppercase',
		);
	}
}
add_filter( 'yuki_header_el_logo_site_title_typography_default_value', 'gq_2025_header_el_logo_site_title_typography' );

if ( ! function_exists( 'gq_2025_header_el_logo_site_tagline_typography' ) ) {
	/**
	 * Return header logo site tagline typography configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_header_el_logo_site_tagline_typography() {
		return array(
			'family'     => 'inherit',
			'fontSize'   => '15px',
			'variant'    => '500',
			'lineHeight' => '1.5',
		);
	}
}
add_filter( 'yuki_header_el_logo_site_tagline_typography_default_value', 'gq_2025_header_el_logo_site_tagline_typography' );

//
// Archive Layout
//
if ( ! function_exists( 'gq_2025_archive_layout' ) ) {
	/**
	 * Return archive layout.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_archive_layout() {
		return 'archive-grid';
	}
}
add_filter( 'yuki_archive_layout_default_value', 'gq_2025_archive_layout' );

if ( ! function_exists( 'gq_2025_archive_card_gap' ) ) {
	/**
	 * Return archive card gap.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_archive_card_gap() {
		return '48px';
	}
}
add_filter( 'yuki_card_gap_default_value', 'gq_2025_archive_card_gap' );

if ( ! function_exists( 'gq_2025_archive_entry_tax_style' ) ) {
	/**
	 * Return archive entry taxonomy style.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_archive_entry_tax_style() {
		return 'badge';
	}
}
add_filter( 'yuki_entry_tax_style_cats_default_value', 'gq_2025_archive_entry_tax_style' );

if ( ! function_exists( 'gq_2025_archive_entry_tax_badge_bg_color' ) ) {
	/**
	 * Return archive entry taxonomy badge background color.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_archive_entry_tax_badge_bg_color() {
		return array(
			'initial' => 'var(--yuki-primary-color)',
			'hover'   => 'var(--yuki-primary-active)',
		);
	}
}
add_filter( 'yuki_entry_tax_badge_bg_color_cats_default_value', 'gq_2025_archive_entry_tax_badge_bg_color' );

if ( ! function_exists( 'gq_2025_archive_header_alignment' ) ) {
	/**
	 * Return archive header alignment.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_archive_header_alignment() {
		return 'center';
	}
}
add_filter( 'yuki_archive_header_alignment_default_value', 'gq_2025_archive_header_alignment' );

// Disable sidebar
add_filter( 'yuki_archive_sidebar_section_default_value', 'gq_2025_return_no' );
if ( ! function_exists( 'gq_2025_pagination_alignment' ) ) {
	/**
	 * Return pagination alignment.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_pagination_alignment() {
		return 'center';
	}
}
add_filter( 'yuki_pagination_alignment_default_value', 'gq_2025_pagination_alignment' );

if ( ! function_exists( 'gq_2025_excerpt_length' ) ) {
	/**
	 * Return excerpt length.
	 *
	 * @return integer
	 * @since 1.0.0
	 */
	function gq_2025_excerpt_length() {
		return 6;
	}
}
add_filter( 'yuki_entry_excerpt_length_default_value', 'gq_2025_excerpt_length' );

if ( ! function_exists( 'gq_2025_archive_image_width' ) ) {
	/**
	 * Return archive image width configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_archive_image_width() {
		return array(
			'desktop' => '35%',
			'tablet'  => '35%',
			'mobile'  => '100%',
		);
	}
}
add_filter( 'yuki_archive_image_width_default_value', 'gq_2025_archive_image_width' );

//
// Colors
//

if ( ! function_exists( 'gq_2025_light_color_scheme' ) ) {
	/**
	 * Add light theme color scheme
	 *
	 * @param array<mixed> $palettes The existing color palettes (unused).
	 *
	 * @return array<int, array<string, string>>
	 * @since 1.0.0
	 */
	function gq_2025_light_color_scheme( $palettes ) {
		return array(
			array(
				'yuki-light-primary-color'  => '#54bd13',
				'yuki-light-primary-active' => '#30d124',
				'yuki-light-accent-color'   => '#000000',
				'yuki-light-accent-active'  => '#000000',
				'yuki-light-base-300'       => '#000000',
				'yuki-light-base-200'       => '#000000',
				'yuki-light-base-100'       => '#ffffff',
				'yuki-light-base-color'     => '#ffffff',
			),
			array(
				'yuki-light-primary-color'  => '#18c7ca',
				'yuki-light-primary-active' => '#16aac1',
				'yuki-light-accent-color'   => '#000000',
				'yuki-light-accent-active'  => '#000000',
				'yuki-light-base-300'       => '#000000',
				'yuki-light-base-200'       => '#000000',
				'yuki-light-base-100'       => '#ffffff',
				'yuki-light-base-color'     => '#ffffff',
			),
			array(
				'yuki-light-primary-color'  => '#7e52ff',
				'yuki-light-primary-active' => '#5d41eb',
				'yuki-light-accent-color'   => '#000000',
				'yuki-light-accent-active'  => '#000000',
				'yuki-light-base-300'       => '#000000',
				'yuki-light-base-200'       => '#000000',
				'yuki-light-base-100'       => '#ffffff',
				'yuki-light-base-color'     => '#ffffff',
			),
			array(
				'yuki-light-primary-color'  => '#f776b4',
				'yuki-light-primary-active' => '#f75c9a',
				'yuki-light-accent-color'   => '#000000',
				'yuki-light-accent-active'  => '#000000',
				'yuki-light-base-300'       => '#000000',
				'yuki-light-base-200'       => '#000000',
				'yuki-light-base-100'       => '#ffffff',
				'yuki-light-base-color'     => '#ffffff',
			),
			array(
				'yuki-light-primary-color'  => '#e78247',
				'yuki-light-primary-active' => '#f0a462',
				'yuki-light-accent-color'   => '#000000',
				'yuki-light-accent-active'  => '#000000',
				'yuki-light-base-300'       => '#000000',
				'yuki-light-base-200'       => '#000000',
				'yuki-light-base-100'       => '#ffffff',
				'yuki-light-base-color'     => '#ffffff',
			),
		);
	}
}
add_filter( 'yuki_light_color_palettes', 'gq_2025_light_color_scheme' );

if ( ! function_exists( 'gq_2025_dark_color_scheme' ) ) {
	/**
	 * Add dark theme color scheme
	 *
	 * @param array<mixed> $palettes The existing color palettes (unused).
	 *
	 * @return array<int, array<string, string>>
	 * @since 1.0.0
	 */
	function gq_2025_dark_color_scheme( $palettes ) {
		return array(
			array(
				'yuki-dark-primary-color'  => '#aef72f',
				'yuki-dark-primary-active' => '#88ff1f',
				'yuki-dark-accent-color'   => '#ffffff',
				'yuki-dark-accent-active'  => '#ffffff',
				'yuki-dark-base-300'       => '#ffffff',
				'yuki-dark-base-200'       => '#ffffff',
				'yuki-dark-base-100'       => '#000000',
				'yuki-dark-base-color'     => '#000000',
			),
			array(
				'yuki-dark-primary-color'  => '#2ff3f7',
				'yuki-dark-primary-active' => '#1fe2ff',
				'yuki-dark-accent-color'   => '#ffffff',
				'yuki-dark-accent-active'  => '#ffffff',
				'yuki-dark-base-300'       => '#ffffff',
				'yuki-dark-base-200'       => '#ffffff',
				'yuki-dark-base-100'       => '#000000',
				'yuki-dark-base-color'     => '#000000',
			),
			array(
				'yuki-dark-primary-color'  => '#af81ff',
				'yuki-dark-primary-active' => '#826af9',
				'yuki-dark-accent-color'   => '#ffffff',
				'yuki-dark-accent-active'  => '#ffffff',
				'yuki-dark-base-300'       => '#ffffff',
				'yuki-dark-base-200'       => '#ffffff',
				'yuki-dark-base-100'       => '#000000',
				'yuki-dark-base-color'     => '#000000',
			),
			array(
				'yuki-dark-primary-color'  => '#f776b4',
				'yuki-dark-primary-active' => '#f75c9a',
				'yuki-dark-accent-color'   => '#ffffff',
				'yuki-dark-accent-active'  => '#ffffff',
				'yuki-dark-base-300'       => '#ffffff',
				'yuki-dark-base-200'       => '#ffffff',
				'yuki-dark-base-100'       => '#000000',
				'yuki-dark-base-color'     => '#000000',
			),
			array(
				'yuki-dark-primary-color'  => '#f7a577',
				'yuki-dark-primary-active' => '#f7c65c',
				'yuki-dark-accent-color'   => '#ffffff',
				'yuki-dark-accent-active'  => '#ffffff',
				'yuki-dark-base-300'       => '#ffffff',
				'yuki-dark-base-200'       => '#ffffff',
				'yuki-dark-base-100'       => '#000000',
				'yuki-dark-base-color'     => '#000000',
			),
		);
	}
}
add_filter( 'yuki_dark_color_palettes', 'gq_2025_dark_color_scheme' );

//
// Card style
//
add_filter( 'yuki_card_background_default_value', 'gq_2025_base_100_background' );
add_filter( 'yuki_global_sidebar_widgets-background_default_value', 'gq_2025_base_100_background' );

if ( ! function_exists( 'gq_2025_card_border' ) ) {
	/**
	 * Return card border configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_card_border() {
		return array(
			'style'   => 'solid',
			'width'   => 2,
			'color'   => 'var(--yuki-base-300)',
			'hover'   => '',
			'inherit' => false,
		);
	}
}
add_filter( 'yuki_card_border_default_value', 'gq_2025_card_border' );
add_filter( 'yuki_global_sidebar_widgets-border_default_value', 'gq_2025_card_border' );

if ( ! function_exists( 'gq_2025_card_shadow' ) ) {
	/**
	 * Return card shadow configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_card_shadow() {
		return array(
			'enable'     => 'yes',
			'horizontal' => '10px',
			'vertical'   => '10px',
			'blur'       => '0px',
			'spread'     => '0px',
			'color'      => 'var(--yuki-base-200)',
		);
	}
}
add_filter( 'yuki_card_shadow_default_value', 'gq_2025_card_shadow' );
add_filter( 'yuki_global_sidebar_widgets-shadow_default_value', 'gq_2025_card_shadow' );

//
// Preloader
//
if ( ! function_exists( 'gq_2025_preloader_colors' ) ) {
	/**
	 * Return preloader colors configuration.
	 *
	 * @return array<string, string>
	 * @since 1.0.0
	 */
	function gq_2025_preloader_colors() {
		return array(
			'background' => 'var(--yuki-base-color)',
			'accent'     => 'var(--yuki-accent-color)',
			'primary'    => 'var(--yuki-primary-color)',
		);
	}
}
add_filter( 'yuki_preloader_colors_default_value', 'gq_2025_preloader_colors' );

//
// Preloader preset
//
if ( ! function_exists( 'gq_2025_preloader_preset' ) ) {
	/**
	 * Return preloader preset.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_preloader_preset() {
		return 'preset-4';
	}
}
add_filter( 'yuki_preloader_preset_default_value', 'gq_2025_preloader_preset' );

//
// Featured image
//
if ( ! function_exists( 'gq_2025_featured_image_position' ) ) {
	/**
	 * Return featured image position.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_featured_image_position() {
		return 'below';
	}
}
add_filter( 'yuki_post_featured_image_position_default_value', 'gq_2025_featured_image_position' );
add_filter( 'yuki_page_featured_image_position_default_value', 'gq_2025_featured_image_position' );

if ( ! function_exists( 'gq_2025_content_spacing' ) ) {
	/**
	 * Change default content spacing for homepage and archive
	 *
	 * @return string
	 */
	function gq_2025_content_spacing() {
		return '48px';
	}
}
add_filter( 'yuki_homepage_content_spacing_default_value', 'gq_2025_content_spacing' );
add_filter( 'yuki_archive_content_spacing_default_value', 'gq_2025_content_spacing' );

//
// Sticky header
//
add_filter( 'yuki_sticky_header_default_value', 'gq_2025_return_yes' );

if ( ! function_exists( 'gq_2025_sticky_rows' ) ) {
	/**
	 * Return sticky header rows configuration.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_sticky_rows() {
		return 'bottom-row';
	}
}
add_filter( 'yuki_sticky_header_rows_default_value', 'gq_2025_sticky_rows' );

//
// Default color mode
//
if ( ! function_exists( 'gq_2025_default_color_schema' ) ) {
	/**
	 * Return default color schema.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_default_color_schema() {
		return 'dark';
	}
}
add_filter( 'yuki_default_color_scheme_default_value', 'gq_2025_default_color_schema' );
