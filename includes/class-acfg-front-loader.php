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
		 * CSS class that marks an ACF field as front-end editable.
		 *
		 * Added by site builders under the field's "Wrapper Attributes -> Class".
		 *
		 * @since 2.0
		 * @var string
		 */
		const EDITABLE_CLASS = 'acfgo';

		/**
		 * ACF field types this plugin knows how to edit.
		 *
		 * @since 2.0
		 * @var string[]
		 */
		const EDITABLE_TYPES = array( 'text', 'textarea' );

		/**
		 * 'acf-on-the-go' constructor.
		 *
		 * The main plugin actions registered for WordPress.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Registers the ACF filter bootstrap and the AJAX save handler.
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'register_filters' ) );
			add_action( 'wp_ajax_acfg_update_fields', array( $this, 'acfg_update_fields' ) );
		}

		/**
		 * Renders text fields with additional html that allows to target these areas via javascript.
		 *
		 * @param mixed      $value   Field value.
		 * @param int|string $post_id Post ID.
		 * @param array      $field   ACF field array.
		 * @return mixed Returns edited value with additional html.
		 *
		 * @since 1.0.0
		 */
		public function acfg_selector( $value, $post_id, $field ) {
			// Only plain strings can be made editable; leave null/arrays/etc. untouched for ACF.
			if ( ! is_string( $value ) ) {
				return $value;
			}

			// URLs, anchors, empty values and emails aren't meant to be inline-editable.
			if ( 0 === strpos( $value, 'http' ) || '#' === $value || '' === $value || filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
				return $value;
			}

			if ( ! $this->acfg_can_edit( $post_id, $field ) ) {
				return $value;
			}

			return $this->acfg_render_editable_field( $value, $post_id, $field );
		}

		/**
		 * Renders textarea fields with additional html that allows to target these areas via javascript.
		 *
		 * Unlike text fields, empty textareas stay editable so content can be added from the front end.
		 *
		 * @param mixed      $value   Field value.
		 * @param int|string $post_id Post ID.
		 * @param array      $field   ACF field array.
		 * @return mixed Returns edited value with additional html.
		 *
		 * @since 1.0.0
		 */
		public function acfg_textarea_selector( $value, $post_id, $field ) {
			// A never-saved textarea loads as null; treat it as empty so it can still be filled in.
			if ( null === $value ) {
				$value = '';
			}

			if ( ! is_string( $value ) || ! $this->acfg_can_edit( $post_id, $field ) ) {
				return $value;
			}

			return $this->acfg_render_editable_field( $value, $post_id, $field );
		}

		/**
		 * Checks whether an ACF field definition is opted in to front-end editing.
		 *
		 * @param mixed $field ACF field array (or anything ACF returned for a lookup).
		 * @return bool True when the field is a supported type carrying the editable wrapper class.
		 *
		 * @since 2.0
		 */
		protected function acfg_is_editable_field( $field ) {
			if ( ! is_array( $field ) || empty( $field['type'] ) || ! in_array( $field['type'], self::EDITABLE_TYPES, true ) ) {
				return false;
			}

			$wrapper_class = isset( $field['wrapper']['class'] ) ? (string) $field['wrapper']['class'] : '';

			return false !== strpos( $wrapper_class, self::EDITABLE_CLASS );
		}

		/**
		 * Checks whether the current user may edit the given field on the given post.
		 *
		 * Only numeric post IDs are supported: options pages, terms and users use
		 * string IDs (e.g. "option", "term_5") that the save handler can't write to,
		 * so no edit control is shown for them.
		 *
		 * @param int|string $post_id ACF post ID.
		 * @param array      $field   ACF field array.
		 * @return bool
		 *
		 * @since 2.0
		 */
		protected function acfg_can_edit( $post_id, $field ) {
			if ( ! $this->acfg_is_editable_field( $field ) || ! is_numeric( $post_id ) ) {
				return false;
			}

			return current_user_can( 'edit_post', (int) $post_id );
		}

		/**
		 * Checks that a submitted meta name really belongs to the given ACF field.
		 *
		 * Prevents a valid, editable field key from being paired with some other
		 * meta name in order to write to it.
		 *
		 * @param array  $field      ACF field array (already confirmed editable).
		 * @param string $field_name Meta name submitted by the browser.
		 * @param int    $post_id    Post ID being edited.
		 * @return bool
		 *
		 * @since 2.0
		 */
		protected function acfg_field_name_matches( $field, $field_name, $post_id ) {
			// Top-level field: the meta name is simply the field name.
			if ( $field['name'] === $field_name ) {
				return true;
			}

			// Sub field of a Group field: ACF stores it as "{group name}_{sub field name}".
			if ( ! empty( $field['parent'] ) ) {
				$parent = acf_get_field( $field['parent'] );

				if ( is_array( $parent ) && 'group' === $parent['type'] && $parent['name'] . '_' . $field['name'] === $field_name ) {
					return true;
				}
			}

			// Anything else (e.g. deeper nesting) is accepted only if ACF has already linked this name to the field key.
			return function_exists( 'acf_get_reference' ) && acf_get_reference( $field_name, $post_id ) === $field['key'];
		}

		/**
		 * Builds the front-end editable markup for a given ACF field/value pair.
		 *
		 * @param string     $value   Field value.
		 * @param int|string $post_id Post ID.
		 * @param array      $field   ACF field array.
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
		 * Per-post permissions are re-checked for every field in acfg_can_edit();
		 * this is only a cheap early bail-out for visitors who can't edit anything.
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
		 * Handles the `acfg_update_fields` AJAX action. Requires a valid nonce,
		 * verifies the current user is allowed to edit the target post, and
		 * verifies the requested field is a real, front-end-editable ACF field
		 * (so the endpoint can't be used to write arbitrary post meta).
		 *
		 * Expected payload: textArr[0] = [ field key, content, field name, post ID ].
		 *
		 * @since 1.0.0
		 */
		public function acfg_update_fields() {
			check_ajax_referer( 'acfg_update_fields', 'nonce' );

			if ( ! is_user_logged_in() ) {
				wp_send_json_error( array( 'message' => __( 'You must be logged in to do that.', 'acf-on-the-go' ) ) );
			}

			if ( ! isset( $_POST['textArr'][0] ) || ! is_array( $_POST['textArr'][0] ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid request.', 'acf-on-the-go' ) ) );
			}

			// Each element is sanitized individually below, according to its role.
			$text_arr = wp_unslash( $_POST['textArr'][0] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$field_key      = isset( $text_arr[0] ) && is_scalar( $text_arr[0] ) ? sanitize_text_field( (string) $text_arr[0] ) : '';
			$raw_content    = isset( $text_arr[1] ) && is_scalar( $text_arr[1] ) ? (string) $text_arr[1] : '';
			$field_name     = isset( $text_arr[2] ) && is_scalar( $text_arr[2] ) ? sanitize_text_field( (string) $text_arr[2] ) : '';
			$current_postid = isset( $text_arr[3] ) && is_scalar( $text_arr[3] ) ? absint( $text_arr[3] ) : 0;

			if ( ! $current_postid || '' === $field_key || '' === $field_name || ! current_user_can( 'edit_post', $current_postid ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this field.', 'acf-on-the-go' ) ) );
			}

			// The field key must resolve to a real ACF field that has been opted in to front-end editing.
			$field = function_exists( 'acf_get_field' ) ? acf_get_field( $field_key ) : false;

			if ( ! $this->acfg_is_editable_field( $field ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this field.', 'acf-on-the-go' ) ) );
			}

			if ( ! $this->acfg_field_name_matches( $field, $field_name, $current_postid ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this field.', 'acf-on-the-go' ) ) );
			}

			// Textareas keep their line breaks; single-line text fields don't need them.
			$field_content = ( 'textarea' === $field['type'] ) ? sanitize_textarea_field( $raw_content ) : sanitize_text_field( $raw_content );

			$old_field_value = get_field( $field_name, $current_postid, false );

			if ( (string) $old_field_value === $field_content ) {
				wp_send_json( array( 'status' => 'no-changes' ) );
			}

			update_field( $field_name, $field_content, $current_postid );

			wp_send_json(
				array(
					'status'        => 'success',
					'field_key'     => $field['key'],
					'field_content' => $field_content,
				)
			);
		}
	}
}

new ACFG_Front_Loader();
