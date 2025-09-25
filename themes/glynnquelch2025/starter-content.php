<?php
/**
 * Theme starter content
 *
 * @package GlynnQuelch2025
 * @author  Glynn Quelch
 * @since   1.0.0
 */

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//
// Starter content
//
if ( ! function_exists( 'gq_2025_starter_content' ) ) {
	/**
	 * Change starter content
	 *
	 * @param array<string, mixed> $content The starter content configuration.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_starter_content( $content ) {
		return array(
			'widgets'   => $content['widgets'],
			'posts'     => array(
				'home' => array(
					'post_type'    => 'page',
					'post_title'   => __( 'Home', 'gq-2025' ),
					'post_content' => '',
				),
				'about',
				'contact',
				'blog',
			),
			'nav_menus' => array(
				'yuki_header_el_menu_1' => array(
					'name'  => __( 'Top Bar Menu', 'gq-2025' ),
					'items' => array(
						'link_home',
						'page_about',
						'page_contact',
						'page_blog',
						'post_news',
					),
				),
				'yuki_header_el_menu_2' => array(
					'name'  => __( 'Primary Menu', 'gq-2025' ),
					'items' => array(
						'link_home',
						'page_about',
						'page_contact',
						'page_blog',
					),
				),
				'yuki_footer_el_menu'   => array(
					'name'  => __( 'Footer Menu', 'gq-2025' ),
					'items' => array(
						'link_home',
						'page_about',
						'page_contact',
						'page_blog',
					),
				),
			),
			'options'   => array(
				'show_on_front'  => 'page',
				'page_on_front'  => '{{home}}',
				'page_for_posts' => '{{blog}}',
			),
		);
	}
}
add_filter( 'yuki_starter_content', 'gq_2025_starter_content' );

//
// Homepage design
//

if ( ! function_exists( 'gq_2025_homepage_builder_id' ) ) {
	/**
	 * Change default homepage builder id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function gq_2025_homepage_builder_id() {
		return 'gq_2025_homepage_builder';
	}
}
add_filter( 'yuki_homepage_builder_id', 'gq_2025_homepage_builder_id' );

if ( ! function_exists( 'gq_2025_homepage_heading' ) ) {
	/**
	 * Generate heading element
	 *
	 * @param string               $title     The heading title.
	 * @param string               $sub_title The heading subtitle.
	 * @param array<string, mixed> $settings  Additional settings for the heading.
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	function gq_2025_homepage_heading( $title, $sub_title = '', $settings = array() ) {
		return array(
			'id'       => 'heading',
			'settings' => wp_parse_args(
				$settings,
				array(
					'title'            => $title,
					'sub-title'        => $sub_title,
					'alignment'        => 'left',
					'title-typography' => array(
						'family'        => 'inherit',
						'fontSize'      => array(
							'desktop' => '1.5rem',
							'tablet'  => '1.25rem',
							'mobile'  => '1rem',
						),
						'variant'       => '600',
						'lineHeight'    => '1.5',
						'textTransform' => 'capitalize',
					),
					'spacing'          => array(
						'top'    => '0px',
						'bottom' => '0px',
						'left'   => '0px',
						'right'  => '0px',
						'linked' => false,
					),
				)
			),
		);
	}
}

if ( ! function_exists( 'gq_2025_homepage_design' ) ) {
	/**
	 * Override default homepage design
	 *
	 * @return array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	function gq_2025_homepage_design() {
		return array(
			// Posts Grid
			array(
				'settings' => array(
					'margin' => array(
						'linked' => false,
						'left'   => '0px',
						'right'  => '0px',
						'top'    => '0px',
						'bottom' => '24px',
					),
				),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'posts-grid',
								'settings' => array(
									'posts-count'     => 6,
									'grid-columns'    => array(
										'desktop' => 3,
										'tablet'  => 2,
										'mobile'  => 1,
									),
									'items-gap'       => '48px',
									'card_background' => gq_2025_base_100_background(),
									'card_border'     => gq_2025_card_border(),
									'card_shadow'     => gq_2025_card_shadow(),
									'yuki_el_tax_style_cats' => 'badge',
									'yuki_el_tax_badge_bg_color_cats' => array(
										'initial' => 'var(--yuki-primary-color)',
										'hover'   => 'var(--yuki-primary-active)',
									),
									'yuki_el_thumbnail_height' => '180px',
									'structure'       => array(
										array(
											'id'      => 'thumbnail',
											'visible' => true,
										),
										array(
											'id'      => 'categories',
											'visible' => true,
										),
										array(
											'id'      => 'title',
											'visible' => true,
										),
										array(
											'id'      => 'excerpt',
											'visible' => true,
										),
										array(
											'id'      => 'metas',
											'visible' => true,
										),
									),
								),
							),
						),
						'settings' => array(),
					),
				),
			),

			// Sub heading
			array(
				'settings' => array(
					'margin' => array(
						'linked' => false,
						'left'   => '0px',
						'right'  => '0px',
						'top'    => '48px',
						'bottom' => '48px',
					),
				),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'heading',
								'settings' => array(
									'title'            => __( 'Top pick for you', 'gq-2025' ),
									'sub-title'        => '',
									'title-tag'        => 'h3',
									'alignment'        => 'center',
									'title-typography' => array(
										'family'        => 'inherit',
										'fontSize'      => array(
											'desktop' => '1.75rem',
											'tablet'  => '1.25rem',
											'mobile'  => '1rem',
										),
										'variant'       => '600',
										'lineHeight'    => '1.75',
										'textTransform' => 'capitalize',
									),
									'spacing'          => array(
										'top'    => '0px',
										'bottom' => '0px',
										'left'   => '0px',
										'right'  => '0px',
										'linked' => false,
									),
								),
							),
						),
						'settings' => array(),
					),
				),
			),
			// Magazine Grid #2
			array(
				'settings' => array(
					'margin' => array(
						'linked' => false,
						'left'   => '0px',
						'right'  => '0px',
						'top'    => '0px',
						'bottom' => '48px',
					),
				),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'magazine-grid',
								'settings' => array(
									'grid-layout'      => 'style3',
									'container-height' => '360px',
								),
							),
						),
						'settings' => array(),
					),
				),
			),

			// Sub heading
			array(
				'settings' => array(
					'margin' => array(
						'linked' => false,
						'left'   => '0px',
						'right'  => '0px',
						'top'    => '48px',
						'bottom' => '48px',
					),
				),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'heading',
								'settings' => array(
									'title'            => __( 'You may also like', 'gq-2025' ),
									'sub-title'        => '',
									'title-tag'        => 'h3',
									'alignment'        => 'center',
									'title-typography' => array(
										'family'        => 'inherit',
										'fontSize'      => array(
											'desktop' => '1.75rem',
											'tablet'  => '1.25rem',
											'mobile'  => '1rem',
										),
										'variant'       => '600',
										'lineHeight'    => '1.75',
										'textTransform' => 'capitalize',
									),
									'spacing'          => array(
										'top'    => '0px',
										'bottom' => '0px',
										'left'   => '0px',
										'right'  => '0px',
										'linked' => false,
									),
								),
							),
						),
						'settings' => array(),
					),
				),
			),
			// Posts Grid + Sidebar
			array(
				'settings' => array(
					'columns-gap' => '12px',
				),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'posts-grid',
								'settings' => array(
									'posts-count'  => 6,
									'grid-columns' => array(
										'desktop' => 2,
										'tablet'  => 2,
										'mobile'  => 1,
									),
									'yuki_el_thumbnail_height' => '180px',
									'yuki_el_tax_style_cats' => 'badge',
									'yuki_el_tax_badge_bg_color_cats' => array(
										'initial' => 'var(--yuki-primary-color)',
										'hover'   => 'var(--yuki-primary-active)',
									),
									'structure'    => array(
										array(
											'id'      => 'thumbnail',
											'visible' => true,
										),
										array(
											'id'      => 'categories',
											'visible' => true,
										),
										array(
											'id'      => 'title',
											'visible' => true,
										),
										array(
											'id'      => 'excerpt',
											'visible' => true,
										),
										array(
											'id'      => 'metas',
											'visible' => true,
										),
									),
								),
							),
						),
						'settings' => array(
							'width' => array(
								'desktop' => '70%',
								'tablet'  => '100%',
								'mobile'  => '100%',
							),
						),
					),
					array(
						'elements' => array(
							array(
								'id'       => 'posts-slider',
								'settings' => array(
									'container-height' => '360px',
									'autoplay'         => 'yes',
									'navigation'       => 'no',
									'yuki_el_title_typography' => array(
										'family'     => 'inherit',
										'fontSize'   => array(
											'desktop' => '1.25rem',
											'tablet'  => '1.15rem',
											'mobile'  => '1rem',
										),
										'variant'    => '700',
										'lineHeight' => '1.5em',
									),
								),
							),
							array(
								'id'       => 'frontpage-widgets-1',
								'settings' => array(),
							),
						),
						'settings' => array(
							'width' => array(
								'desktop' => '30%',
								'tablet'  => '100%',
								'mobile'  => '100%',
							),
						),
					),
				),
			),
			// Stretch Sliders
			array(
				'settings' => array( 'stretch' => 'yes' ),
				'columns'  => array(
					array(
						'elements' => array(
							array(
								'id'       => 'posts-slider',
								'settings' => array(
									'container-height' => '360px',
									'autoplay'         => 'yes',
									'navigation'       => 'yes',
									'slides-to-show'   => array(
										'desktop' => 3,
										'tablet'  => 3,
										'mobile'  => 1,
									),
									'yuki_el_title_typography' => array(
										'family'     => 'inherit',
										'fontSize'   => array(
											'desktop' => '1.25rem',
											'tablet'  => '1.15rem',
											'mobile'  => '1rem',
										),
										'variant'    => '700',
										'lineHeight' => '1.5em',
									),
								),
							),
						),
						'settings' => array(),
					),
				),
			),
		);
	}
}
add_filter( 'gq_2025_homepage_builder_default_value', 'gq_2025_homepage_design' );
