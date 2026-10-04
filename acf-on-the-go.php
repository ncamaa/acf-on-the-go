<?php
/**
 * Plugin Name: ACF On The Go
 * Plugin URI: https://github.com/ncamaa/acf-on-the-go/edit/master/README.md
 * Description: Edit ACF text fields from the front end of your website
 * Version: 2.0
 * Author: Nadav Cohen (amaa)
 * Developer: Alkesh Miyani
 * Author URI: https://www.linkedin.com/in/nadav-cohen-wd/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 4.8
 * Requires PHP: 5.6
 *
 * Text Domain: acf-on-the-go
 * Domain Path: /languages/
 *
 * @package ACF_On_The_Go
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Plugin constants: main file path, directory path, public URL,
 * basename, and version (used for asset cache-busting).
 */
define( 'ACFG_FILE', __FILE__ );
define( 'ACFG_DIR', plugin_dir_path( ACFG_FILE ) );
define( 'ACFG_URL', plugins_url( '/', ACFG_FILE ) );
define( 'ACFG_BASENAME', plugin_basename( __FILE__ ) );
define( 'ACFG_VERSION', '2.0' );

if ( ! class_exists( 'ACFG_Init' ) ) {

	/**
	 * Main Plugin 'acfg' class.
	 */
	class ACFG_Init {

		/**
		 * 'acf-on-the-go' constructor.
		 *
		 * The main plugin actions registered for WordPress. The front-end
		 * loader is included on `plugins_loaded` so that ACF / Secure Custom
		 * Fields has already been loaded by the time we check for it,
		 * regardless of plugin load order.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'acfg_validate_depencency' ) );
			add_action( 'plugins_loaded', array( $this, 'acfg_include_files' ) );
			$this->hooks();
		}

		/**
		 * Registers the admin and front-end asset hooks.
		 */
		public function hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'acfg_admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'acfg_front_scripts' ) );
		}

		/**
		 * Checks whether ACF (or Secure Custom Fields) is loaded and, if not,
		 * queues an admin notice explaining how to fix it.
		 */
		public function acfg_validate_depencency() {
			if ( ! class_exists( 'ACF' ) ) {
				add_action( 'admin_notices', array( $this, 'acfg_validate_depencency_msg' ) );
				return;
			}
		}

		/**
		 * Displays an admin notice when Advanced Custom Fields (or its Secure Custom Fields successor) is missing or inactive.
		 */
		public function acfg_validate_depencency_msg() {
			$screen = get_current_screen();

			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin            = 'advanced-custom-fields/acf.php';
			$plugin_pro        = 'advanced-custom-fields-pro/acf.php';
			$plugin_scf        = 'secure-custom-fields/secure-custom-fields.php';
			$installed_plugins = get_plugins();

			if ( isset( $installed_plugins[ $plugin ] ) || isset( $installed_plugins[ $plugin_pro ] ) || isset( $installed_plugins[ $plugin_scf ] ) ) { // Check if a supported plugin is installed.
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				if ( isset( $installed_plugins[ $plugin_pro ] ) ) {
					$activate_plugin = $plugin_pro;
					$activate_label  = __( 'Activate ACF Pro Now', 'acf-on-the-go' );
				} elseif ( isset( $installed_plugins[ $plugin ] ) ) {
					$activate_plugin = $plugin;
					$activate_label  = __( 'Activate ACF Now', 'acf-on-the-go' );
				} else {
					$activate_plugin = $plugin_scf;
					$activate_label  = __( 'Activate Secure Custom Fields Now', 'acf-on-the-go' );
				}

				$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $activate_plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $activate_plugin );
				$message        = '<p><strong>' . __( 'ACF on the Go', 'acf-on-the-go' ) . '</strong>' . __( ' Plugin not working because you need to activate the <strong>Advanced Custom Fields</strong> or <strong>Secure Custom Fields</strong> plugin.', 'acf-on-the-go' ) . '</p>';
				$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $activation_url ), $activate_label ) . '</p>';
			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=secure-custom-fields' ), 'install-plugin_secure-custom-fields' );

				$message  = '<p><strong>' . __( 'ACF on the Go', 'acf-on-the-go' ) . '</strong>' . __( ' Plugin not working because you need to install the <strong>Advanced Custom Fields</strong> or <strong>Secure Custom Fields</strong> plugin', 'acf-on-the-go' ) . '</p>';
				$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $install_url ), __( 'Install Secure Custom Fields Now', 'acf-on-the-go' ) ) . '</p>';
			}

			// $message already contains its own paragraphs, so it is not wrapped in another <p>.
			echo '<div class="notice notice-error is-dismissible">' . wp_kses_post( $message ) . '</div>';
		}

		/**
		 * Enqueue admin panel required css/js.
		 *
		 * Intentionally empty: the plugin has no admin UI. Kept so that any
		 * third-party code referencing this callback keeps working.
		 */
		public function acfg_admin_scripts() {
		}

		/**
		 * Loads the front-end loader when ACF is available.
		 *
		 * ACF, ACF Pro and Secure Custom Fields all define the `ACF` class, so
		 * checking for it also covers copies bundled in a theme or loaded as
		 * a must-use plugin, which `is_plugin_active()` would miss.
		 */
		public function acfg_include_files() {
			if ( class_exists( 'ACF' ) ) {
				include_once ACFG_DIR . 'includes/class-acfg-front-loader.php';
			}
		}

		/**
		 * Enqueue front-end required css/js.
		 *
		 * Assets (and the AJAX nonce) are only output for users who can
		 * edit content, and only when ACF is available -- the same
		 * conditions under which editable fields are rendered.
		 */
		public function acfg_front_scripts() {
			if ( is_user_logged_in() && current_user_can( 'edit_posts' ) && class_exists( 'ACF' ) ) {
				wp_enqueue_style( 'acfg-jquery-ui-dialog', ACFG_URL . 'assets/front/css/jquery-ui-dialog.min.css', array(), ACFG_VERSION );
				wp_enqueue_style( 'acfg-editor', ACFG_URL . 'assets/front/css/medium-editor.min.css', array(), ACFG_VERSION );
				wp_enqueue_style( 'acfg-css', ACFG_URL . 'assets/front/css/front-style.css', array(), ACFG_VERSION );
				wp_enqueue_style( 'acfg-toaster', ACFG_URL . 'assets/front/css/jquery.toast.css', array(), ACFG_VERSION );
				wp_enqueue_style( 'wp-jquery-ui-dialog' );

				// Declare real dependencies so WordPress loads jQuery UI Dialog and the toast library before our script.
				wp_enqueue_script( 'acfg-toster-js', ACFG_URL . 'assets/front/js/jquery.toast.js', array( 'jquery' ), ACFG_VERSION, true );
				wp_enqueue_script( 'acfg-front-js', ACFG_URL . 'assets/front/js/front.js', array( 'jquery', 'jquery-ui-dialog', 'acfg-toster-js' ), ACFG_VERSION, true );

				// Prefixed global name so it cannot clash with other plugins' localized data.
				wp_localize_script(
					'acfg-front-js',
					'acfg_object',
					array(
						'ajaxurl'      => admin_url( 'admin-ajax.php' ),
						'nonce'        => wp_create_nonce( 'acfg_update_fields' ),
						'success_txt'  => __( 'Success', 'acf-on-the-go' ),
						'nochange_txt' => __( 'No change', 'acf-on-the-go' ),
						'success_msg'  => __( 'Updated Successfully', 'acf-on-the-go' ),
						'nochange_msg' => __( 'Nothing to change', 'acf-on-the-go' ),
						'error_txt'    => __( 'Error', 'acf-on-the-go' ),
						'error_msg'    => __( 'The field could not be updated. Please reload the page and try again.', 'acf-on-the-go' ),
						'update_txt'   => __( 'Update', 'acf-on-the-go' ),
						'close_txt'    => __( 'Close', 'acf-on-the-go' ),
					)
				);
			}
		}
	}

}

/*
 * Boot the plugin.
 */
new ACFG_Init();
