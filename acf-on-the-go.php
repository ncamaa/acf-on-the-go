<?php
/**
 * Plugin Name: ACF On The Go
 * Plugin URI: https://github.com/ncamaa/acf-on-the-go/edit/master/README.md
 * Description: Edit ACF text fields from the front end of your website
 * Version: 1.0.2
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
 * Define variables.
 */
define( 'ACFG_FILE', __FILE__ );
define( 'ACFG_DIR', plugin_dir_path( ACFG_FILE ) );
define( 'ACFG_URL', plugins_url( '/', ACFG_FILE ) );
define( 'ACFG_BASENAME', plugin_basename( __FILE__ ) );

if ( ! class_exists( 'ACFG_Init' ) ) {

	/**
	 * Main Plugin 'acfg' class.
	 */
	class ACFG_Init {

		/**
		 * 'acf-on-the-go' constructor.
		 *
		 * The main plugin actions registered for WordPress.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'acfg_validate_depencency' ) );
			$this->hooks();
			$this->acfg_include_files();
		}

		/**
		 * Initialize.
		 */
		public function hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'acfg_admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'acfg_front_scripts' ) );
		}

		/**
		 * Check ACF is active or not.
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

			echo '<div class="error is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';
		}

		/**
		 * Enqueue admin panel required css/js.
		 */
		public function acfg_admin_scripts() {
		}

		/**
		 * Load files.
		 */
		public function acfg_include_files() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$checkplugin     = is_plugin_active( 'advanced-custom-fields/acf.php' );
			$checkplugin_pro = is_plugin_active( 'advanced-custom-fields-pro/acf.php' );
			$checkplugin_scf = is_plugin_active( 'secure-custom-fields/secure-custom-fields.php' );

			if ( true === $checkplugin || true === $checkplugin_pro || true === $checkplugin_scf ) {
				include_once ACFG_DIR . 'includes/acfg-front-loader.php';
			}
		}

		/**
		 * Enqueue front-end required css/js.
		 */
		public function acfg_front_scripts() {
			if ( is_user_logged_in() ) {
				wp_enqueue_style( 'jquery-ui', ACFG_URL . 'assets/front/css/jquery-ui-dialog.min.css', array(), '1.0.1' );
				wp_enqueue_style( 'acfg-editor', ACFG_URL . 'assets/front/css/medium-editor.min.css', array(), '1.0.1' );
				wp_enqueue_style( 'acfg-css', ACFG_URL . 'assets/front/css/front-style.css', array(), '1.0.1' );
				wp_enqueue_style( 'acfg-toaster', ACFG_URL . 'assets/front/css/jquery.toast.css', array(), '1.0.1' );
				wp_enqueue_script( 'acfg-front-js', ACFG_URL . 'assets/front/js/front.js', array( 'jquery' ), '1.0.1', false );
				wp_enqueue_script( 'acfg-toster-js', ACFG_URL . 'assets/front/js/jquery.toast.js', array( 'jquery' ), '1.0.1', false );
				wp_enqueue_script( 'jquery-ui-core', '', array(), false, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- core-bundled script, version is managed by WordPress itself.
				wp_enqueue_script( 'jquery-ui-dialog', '', array(), false, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- core-bundled script, version is managed by WordPress itself.
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
				wp_localize_script(
					'acfg-front-js',
					'js_object',
					array(
						'ajaxurl'      => admin_url( 'admin-ajax.php' ),
						'nonce'        => wp_create_nonce( 'acfg_update_fields' ),
						'success_txt'  => __( 'Success', 'acf-on-the-go' ),
						'nochange_txt' => __( 'No change', 'acf-on-the-go' ),
						'success_msg'  => __( 'Updated Successfully', 'acf-on-the-go' ),
						'nochange_msg' => __( 'Nothing to change', 'acf-on-the-go' ),
						'update_txt'   => __( 'Update', 'acf-on-the-go' ),
						'close_txt'    => __( 'Close', 'acf-on-the-go' ),
					)
				);
			}
		}
	}

}

/*
 * Starts our plugin class, easy!
 */
new ACFG_Init();
