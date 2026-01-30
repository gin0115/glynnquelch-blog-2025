<?php
/**
 * Archive Type Term Image Handler
 *
 * Adds an image field to archive-type taxonomy terms and provides
 * a fallback for featured images on sp-archive posts.
 *
 * @package SaggyPantsArchive
 * @since   1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Archive_Type_Image
 *
 * Handles the image field for archive-type taxonomy terms.
 */
class Archive_Type_Image {

	/**
	 * Meta key for the term image.
	 *
	 * @var string
	 */
	const META_KEY = 'archive_type_image_id';

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public static function init() {
		// Add fields to add/edit term forms.
		add_action( 'archive-type_add_form_fields', array( __CLASS__, 'add_image_field' ) );
		add_action( 'archive-type_edit_form_fields', array( __CLASS__, 'edit_image_field' ), 10 );

		// Save the term meta.
		add_action( 'created_archive-type', array( __CLASS__, 'save_image_field' ) );
		add_action( 'edited_archive-type', array( __CLASS__, 'save_image_field' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		// Filter featured image for sp-archive posts.
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'fallback_thumbnail_html' ), 10, 5 );
		add_filter( 'get_post_metadata', array( __CLASS__, 'fallback_thumbnail_id' ), 10, 4 );

		// Add image column to term list.
		add_filter( 'manage_edit-archive-type_columns', array( __CLASS__, 'add_image_column' ) );
		add_filter( 'manage_archive-type_custom_column', array( __CLASS__, 'render_image_column' ), 10, 3 );
	}

	/**
	 * Enqueue admin scripts for the media uploader.
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public static function enqueue_admin_scripts( $hook ) {
		// Only load on taxonomy edit pages.
		$screen = get_current_screen();
		if ( ! $screen || 'archive-type' !== $screen->taxonomy ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'saggy-pants-term-image',
			SAGGY_PANTS_ARCHIVE_DIR_URL . 'assets/js/term-image.js',
			array( 'jquery' ),
			'1.1.0',
			true
		);

		wp_localize_script(
			'saggy-pants-term-image',
			'saggyPantsTermImage',
			array(
				'title'  => __( 'Select or Upload Term Image', 'saggy-pants-archive' ),
				'button' => __( 'Use this image', 'saggy-pants-archive' ),
			)
		);
	}

	/**
	 * Add image field to the "Add New Term" form.
	 *
	 * @return void
	 */
	public static function add_image_field() {
		?>
		<div class="form-field term-image-wrap">
			<label for="archive-type-image"><?php esc_html_e( 'Term Image', 'saggy-pants-archive' ); ?></label>
			<div id="archive-type-image-preview" style="margin-bottom: 10px;"></div>
			<input type="hidden" name="archive_type_image_id" id="archive-type-image-id" value="" />
			<button type="button" class="button" id="archive-type-image-upload">
				<?php esc_html_e( 'Select Image', 'saggy-pants-archive' ); ?>
			</button>
			<button type="button" class="button" id="archive-type-image-remove" style="display:none;">
				<?php esc_html_e( 'Remove Image', 'saggy-pants-archive' ); ?>
			</button>
			<p class="description"><?php esc_html_e( 'This image will be used as a fallback for archives without a featured image.', 'saggy-pants-archive' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add image field to the "Edit Term" form.
	 *
	 * @param WP_Term $term The term being edited.
	 * @return void
	 */
	public static function edit_image_field( $term ) {
		$image_id = get_term_meta( $term->term_id, self::META_KEY, true );
		$image    = $image_id ? wp_get_attachment_image( $image_id, 'thumbnail' ) : '';
		?>
		<tr class="form-field term-image-wrap">
			<th scope="row">
				<label for="archive-type-image"><?php esc_html_e( 'Term Image', 'saggy-pants-archive' ); ?></label>
			</th>
			<td>
				<div id="archive-type-image-preview" style="margin-bottom: 10px;">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<input type="hidden" name="archive_type_image_id" id="archive-type-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
				<button type="button" class="button" id="archive-type-image-upload">
					<?php esc_html_e( 'Select Image', 'saggy-pants-archive' ); ?>
				</button>
				<button type="button" class="button" id="archive-type-image-remove" style="<?php echo $image_id ? '' : 'display:none;'; ?>">
					<?php esc_html_e( 'Remove Image', 'saggy-pants-archive' ); ?>
				</button>
				<p class="description"><?php esc_html_e( 'This image will be used as a fallback for archives without a featured image.', 'saggy-pants-archive' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save the image field.
	 *
	 * @param int $term_id The term ID.
	 * @return void
	 */
	public static function save_image_field( $term_id ) {
		if ( ! isset( $_POST['archive_type_image_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$image_id = absint( $_POST['archive_type_image_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $image_id ) {
			update_term_meta( $term_id, self::META_KEY, $image_id );
		} else {
			delete_term_meta( $term_id, self::META_KEY );
		}
	}

	/**
	 * Add image column to taxonomy list.
	 *
	 * @param array $columns The columns.
	 * @return array
	 */
	public static function add_image_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( 'name' === $key ) {
				$new_columns['image'] = __( 'Image', 'saggy-pants-archive' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	/**
	 * Render the image column.
	 *
	 * @param string $content     The column content.
	 * @param string $column_name The column name.
	 * @param int    $term_id     The term ID.
	 * @return string
	 */
	public static function render_image_column( $content, $column_name, $term_id ) {
		if ( 'image' !== $column_name ) {
			return $content;
		}

		$image_id = get_term_meta( $term_id, self::META_KEY, true );
		if ( $image_id ) {
			return wp_get_attachment_image( $image_id, array( 40, 40 ) );
		}

		return '—';
	}

	/**
	 * Get the term image ID for a given post.
	 *
	 * Returns the image ID of the first archive-type term that has an image set.
	 *
	 * @param int $post_id The post ID.
	 * @return int|false The image ID or false if none found.
	 */
	public static function get_term_image_for_post( $post_id ) {
		$terms = get_the_terms( $post_id, 'archive-type' );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return false;
		}

		// Get the first term with an image.
		foreach ( $terms as $term ) {
			$image_id = get_term_meta( $term->term_id, self::META_KEY, true );
			if ( $image_id ) {
				return (int) $image_id;
			}
		}

		return false;
	}

	/**
	 * Filter the thumbnail ID to provide a fallback.
	 *
	 * @param mixed  $value     The meta value.
	 * @param int    $object_id The object ID.
	 * @param string $meta_key  The meta key.
	 * @param bool   $single    Whether to return a single value.
	 * @return mixed
	 */
	public static function fallback_thumbnail_id( $value, $object_id, $meta_key, $single ) {
		// Only filter _thumbnail_id.
		if ( '_thumbnail_id' !== $meta_key ) {
			return $value;
		}

		// Don't apply fallback in admin - we want to see the actual state.
		if ( is_admin() ) {
			return $value;
		}

		// Only for sp-archive post type.
		if ( 'sp-archive' !== get_post_type( $object_id ) ) {
			return $value;
		}

		// Prevent infinite loop by removing filter temporarily.
		remove_filter( 'get_post_metadata', array( __CLASS__, 'fallback_thumbnail_id' ), 10 );

		// Get the actual thumbnail ID.
		$thumbnail_id = get_post_meta( $object_id, '_thumbnail_id', true );

		// Re-add the filter.
		add_filter( 'get_post_metadata', array( __CLASS__, 'fallback_thumbnail_id' ), 10, 4 );

		// If there's already a thumbnail, return null to use default behavior.
		if ( $thumbnail_id ) {
			return $value;
		}

		// Get fallback from term.
		$term_image_id = self::get_term_image_for_post( $object_id );

		if ( $term_image_id ) {
			return $single ? $term_image_id : array( $term_image_id );
		}

		return $value;
	}

	/**
	 * Filter the thumbnail HTML to provide a fallback.
	 *
	 * This is a secondary filter in case the metadata filter doesn't work.
	 *
	 * @param string       $html              The post thumbnail HTML.
	 * @param int          $post_id           The post ID.
	 * @param int          $post_thumbnail_id The post thumbnail ID.
	 * @param string|int[] $size              The image size.
	 * @param string|array $attr              Image attributes.
	 * @return string
	 */
	public static function fallback_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		// If there's already HTML, return it.
		if ( $html ) {
			return $html;
		}

		// Don't apply fallback in admin - we want to see the actual state.
		if ( is_admin() ) {
			return $html;
		}

		// Only for sp-archive post type.
		if ( 'sp-archive' !== get_post_type( $post_id ) ) {
			return $html;
		}

		// Get fallback from term.
		$term_image_id = self::get_term_image_for_post( $post_id );

		if ( $term_image_id ) {
			return wp_get_attachment_image( $term_image_id, $size, false, $attr );
		}

		return $html;
	}
}


