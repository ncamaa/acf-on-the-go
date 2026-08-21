<?php
/**
 * Constants normally defined at runtime in the plugin's main file,
 * declared here so PHPStan can resolve them during static analysis.
 *
 * @package ACF_On_The_Go
 */

if ( ! defined( 'ACFG_FILE' ) ) {
	define( 'ACFG_FILE', __DIR__ . '/acf-on-the-go.php' );
}
if ( ! defined( 'ACFG_DIR' ) ) {
	define( 'ACFG_DIR', __DIR__ . '/' );
}
if ( ! defined( 'ACFG_URL' ) ) {
	define( 'ACFG_URL', '' );
}
if ( ! defined( 'ACFG_BASENAME' ) ) {
	define( 'ACFG_BASENAME', 'acf-on-the-go/acf-on-the-go.php' );
}
if ( ! defined( 'ACFG_VERSION' ) ) {
	define( 'ACFG_VERSION', '1.0.3' );
}
