<?php
/**
 * Plugin Name: ACF On The Go
 * Plugin URI: https://www.linkedin.com/in/nadav-cohen-wd/
 * Description: Edit ACF text fields from the front end of your website
 * Version: 1.0
 * Author: Nadav Cohen (amaa)
 * Developer: miyanialkesh7
 * Author URI: https://www.linkedin.com/in/nadav-cohen-wd/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: acf-on-thego
 * Domain Path: /languages/
 */

 /*
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Define variables
 */
define('ACFG_FILE', __FILE__);
define('ACFG_DIR', plugin_dir_path(ACFG_FILE));
define('ACFG_URL', plugins_url('/', ACFG_FILE));
define('ACFG_BASENAME', plugin_basename(__FILE__));
define('ACFG_TEXTDOMAIN', 'acf-on-the-go');



/**
 * Main Plugin 'acfg' class.
 */
if (!class_exists('ACFG_Init')) {
    class ACFG_Init {

        /**
         * 'acf-on-the-go' constructor.
         *
         * The main plugin actions registered for WordPress
         */
        public function __construct() {
            add_action('init',array($this,'acfg_validate_depencency'));
            $this->hooks();
            $this->acfg_include_files();
        }

        /**
         * Initialize
         */
        public function hooks() {
            add_action('plugins_loaded', array($this, 'acfg_load_language_files'));
            add_action('admin_enqueue_scripts', array($this, 'acfg_admin_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'acfg_front_scripts'));
         }

            /**
         * check acf is exist or not
         */
        public function acfg_validate_depencency() {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $checkplugin = is_plugin_active('advanced-custom-fields/acf.php');
            $checkplugin_pro = is_plugin_active('advanced-custom-fields-pro/acf.php');

            // if($checkplugin == false){
            if(!class_exists('ACF')){
                add_action('admin_notices',array($this,'acfg_validate_depencency_msg'));
                return;
            }
        }


        /**
         * check acf is exist or not
         */
        public function acfg_validate_depencency_msg() {
            $screen = get_current_screen();
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $checkplugin = is_plugin_active('advanced-custom-fields/acf.php');
            $checkplugin_pro = is_plugin_active('advanced-custom-fields-pro/acf.php');

            if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
                return;
            }

            $plugin = 'advanced-custom-fields/acf.php';
            $plugin_pro = 'advanced-custom-fields-pro/acf.php';
            $file_path = 'advanced-custom-fields/acf.php';
            $installed_plugins = get_plugins();

            if (isset($installed_plugins[$file_path])) { // check if plugin is installed
                if (!current_user_can('activate_plugins')) {
                    return;
                }

                if (isset($installed_plugins[$plugin_pro])) {
                    $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_pro . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_pro);
                    $message = '<p><strong>' . __('ACF on the Go', ACFG_TEXTDOMAIN) . '</strong>' . __(' Plguin not working because you need to activate the <strong>Advanced Custom Fields</strong> plugin.', ACFG_TEXTDOMAIN) . '</p>';
                    $message .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, __('Activate ACF Pro Now', ACFG_TEXTDOMAIN)) . '</p>';
                } else {
                    $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);
                    $message = '<p><strong>' . __('ACF on the Go', ACFG_TEXTDOMAIN) . '</strong>' . __(' Plguin not working because you need to activate the <strong>Advanced Custom Fields</strong> plugin.', ACFG_TEXTDOMAIN) . '</p>';
                    $message .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, __('Activate ACF Now', ACFG_TEXTDOMAIN)) . '</p>';
                }

            } else {
                if (!current_user_can('install_plugins')) {
                    return;
                }

                $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=advanced-custom-fields'), 'install-plugin_advanced-custom-fields');

                $message = '<p><strong>' . __('ACF on the Go', ACFG_TEXTDOMAIN) . '</strong>' . __(' Plguin not working because you need to install the <strong>Advanced Custom Fields</strong> plugin', ACFG_TEXTDOMAIN) . '</p>';
                $message .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Install ACF Now', ACFG_TEXTDOMAIN)) . '</p>';
            }

            echo '<div class="error is-dismissible"><p>' . $message . '</p></div>';
        }

        /**
         * @return Loads plugin textdomain
         */
        public function acfg_load_language_files() {
            load_plugin_textdomain(ACFG_TEXTDOMAIN, false, dirname(ACFG_BASENAME) . '/languages');
        }

        /**
         *
         * @return Enqueue admin panel required css/js
         */
        public function acfg_admin_scripts() {

        }

        /**
         * Load files
         */
        public function acfg_include_files() {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $checkplugin = is_plugin_active('advanced-custom-fields/acf.php');
            $checkplugin_pro = is_plugin_active('advanced-custom-fields-pro/acf.php');

            if($checkplugin == true || $checkplugin_pro == true){
                include_once( ACFG_DIR . 'includes/acfg-front-loader.php' );
            }
        }

        /**
         *
         * @return Enqueue admin panel required css/js
         */
        public function acfg_front_scripts() {
           if(is_user_logged_in()){
                wp_enqueue_style( 'jquery-ui', ACFG_URL . 'assets/front/css/jquery-ui-dialog.min.css', array());
                wp_enqueue_style( 'acfg-editor', ACFG_URL . 'assets/front/css/medium-editor.min.css', array());
                wp_enqueue_style( 'acfg-css', ACFG_URL . 'assets/front/css/front-style.css', array());
                wp_enqueue_style( 'acfg-toaster', ACFG_URL . 'assets/front/css/jquery.toast.css', array());
                wp_enqueue_script('acfg-front-js', ACFG_URL . 'assets/front/js/front.js', array( 'jquery' ), false );
                wp_enqueue_script('acfg-toster-js', ACFG_URL . 'assets/front/js/jquery.toast.js', array( 'jquery' ));
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style( 'wp-jquery-ui-dialog' );
                wp_localize_script( 'acfg-front-js', 'js_object', array(
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                ));
            }
        }
    }

}

/*
* Starts our plugin class, easy!
*/
new ACFG_Init();
