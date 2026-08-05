<?php
/**
 * Fires on plugin uninstall.
 *
 * ACF On The Go stores no options, transients, or custom database
 * tables of its own -- it only writes ACF field values via ACF's
 * update_field(), and that data belongs to ACF, not this plugin, so
 * nothing is deleted here.
 *
 * @package ACF_On_The_Go
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
