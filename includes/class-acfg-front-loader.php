<?php
/**
 * Front-end loader for ACF On The Go.
 *
 * Registers the ACF value filters that render the front-end edit
 * controls and handles the AJAX request that persists edited values.
 *
 * @package ACF_On_The_Go
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ACFG_Front_Loader' ) ) {

	/**
	 * Handles front-end rendering and saving of editable ACF fields.
	 */
	class ACFG_Front_Loader {

		/**
		 * 'acf-on-the-go' constructor.
		 *
		 * The main plugin actions registered for WordPress.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Initialize.
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'register_filters' ) );
			add_action( 'wp_ajax_scrap_it', array( $this, 'acfg_update_fields' ) );
		}

		/**
		 * Renders text fields with additional html that allows to target these areas via javascript.
		 *
		 * @param string $value   Field value.
		 * @param int    $post_id Post ID.
		 * @param array  $field   ACF field array.
		 * @return string Returns edited value with additional html.
		 *
		 * @since 1.0.0
		 */
		public function acfg_selector( $value, $post_id, $field ) {
			// URLs, anchors, empty values and emails aren't meant to be inline-editable.
			if ( 0 === strpos( $value, 'http' ) || '#' === $value || '' === $value || filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
				return $value;
			}

			$wrapper_class = isset( $field['wrapper']['class'] ) ? $field['wrapper']['class'] : '';

			if ( false === strpos( $wrapper_class, 'acfgo' ) ) {
				return $value;
			}

			return $this->acfg_render_editable_field( $value, $post_id, $field );
		}

		/**
		 * Renders wysiwyg fields with additional html that allows to target these areas via javascript.
		 *
		 * @param string $value   Field value.
		 * @param int    $post_id Post ID.
		 * @param array  $field   ACF field array.
		 * @return string Returns edited value with additional html.
		 *
		 * @since 1.0.0
		 */
		public function acfg_textarea_selector( $value, $post_id, $field ) {
			$wrapper_class = isset( $field['wrapper']['class'] ) ? $field['wrapper']['class'] : '';

			if ( false === strpos( $wrapper_class, 'acfgo' ) ) {
				return $value;
			}

			return $this->acfg_render_editable_field( $value, $post_id, $field );
		}

		/**
		 * Builds the front-end editable markup for a given ACF field/value pair.
		 *
		 * @param string $value   Field value.
		 * @param int    $post_id Post ID.
		 * @param array  $field   ACF field array.
		 * @return string Rendered markup, escaped for output.
		 *
		 * @since 1.0.2
		 */
		protected function acfg_render_editable_field( $value, $post_id, $field ) {
			$field_id    = $field['ID'];
			$field_label = $field['label'];
			$field_type  = $field['type'];
			$field_key   = $field['key'];
			$field_name  = $field['name'];
			$pen_icon    = ACFG_URL . 'assets/img/pencil12.png';

			return sprintf(
				'<span class="acf-onthego-content-wrapper">
					<span class="acf-onthego" data-field-id="%1$s" data-field-type="%2$s" data-postid="%3$s" data-name="%4$s" data-key="%5$s">%6$s</span>
					<a data-id="#%1$s" data-field-label="%7$s" class="acfg-editor acfg-dialog" href="javascript:void(0);">
						<img height="12" width="12" src="%8$s" alt="">
					</a>
					<div id="%1$s" class="acfg-dialogbox" data-field-id="%1$s" data-field-type="%2$s" data-field-label="%7$s" data-postid="%3$s" data-name="%4$s" data-key="%5$s">
						<textarea class="acfg-inner-content" rows="4" cols="50">%6$s</textarea>
					</div>
				</span>',
				esc_attr( $field_id ),
				esc_attr( $field_type ),
				esc_attr( (string) $post_id ),
				esc_attr( $field_name ),
				esc_attr( $field_key ),
				esc_html( $value ),
				esc_attr( $field_label ),
				esc_url( $pen_icon )
			);
		}

		/**
		 * Registers filters required for ACF field rendering.
		 *
		 * @since 1.0.0
		 */
		public function register_filters() {
			if ( is_user_logged_in() && ! is_admin() && current_user_can( 'edit_posts' ) ) {
				add_filter( 'acf/load_value/type=text', array( $this, 'acfg_selector' ), 10, 3 );
				add_filter( 'acf/load_value/type=textarea', array( $this, 'acfg_textarea_selector' ), 10, 3 );
			}
		}

		/**
		 * Updates edited ACF fields in the database.
		 *
		 * Handles the `scrap_it` AJAX action. Requires a valid nonce and
		 * verifies the current user is allowed to edit the target post
		 * before writing the new field value.
		 *
		 * @since 1.0.0
		 */
		public function acfg_update_fields() {
			check_ajax_referer( 'acfg_update_fields', 'nonce' );

			if ( ! is_user_logged_in() ) {
				wp_send_json_error( array( 'message' => __( 'You must be logged in to do that.', 'acf-on-the-go' ) ) );
			}

			if ( ! isset( $_REQUEST['textArr'][0] ) || ! is_array( $_REQUEST['textArr'][0] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid request.', 'acf-on-the-go' ) ) );
			}

			$text_arr = isset( $_REQUEST['textArr'][0] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['textArr'][0] ) ) : array();

			$field_key      = isset( $text_arr[0] ) ? $text_arr[0] : '';
			$field_content  = isset( $text_arr[1] ) ? $text_arr[1] : '';
			$field_name     = isset( $text_arr[2] ) ? $text_arr[2] : '';
			$current_postid = isset( $text_arr[3] ) ? absint( $text_arr[3] ) : 0;

			if ( ! $current_postid || '' === $field_name || ! current_user_can( 'edit_post', $current_postid ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this field.', 'acf-on-the-go' ) ) );
			}

			$old_field_value = get_field( $field_name, $current_postid, true );
			update_field( $field_name, $field_content, $current_postid );

			if ( $old_field_value === $field_content ) {
				wp_send_json( array( 'status' => 'no-changes' ) );
			}

			wp_send_json(
				array(
					'status'        => 'success',
					'field_key'     => $field_key,
					'field_content' => $field_content,
				)
			);
		}
	}
}

new ACFG_Front_Loader();
