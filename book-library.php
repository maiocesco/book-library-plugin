<?php
/**
 * Plugin Name: Book Library
 * Description: Manage and display the blogger's read books list, with Google Books lookup + Amazon affiliate links.
 * Version: 0.1.0
 * Author: Francesco Maietti
 * Author URI: https://francesco.im
 * Text Domain: book-library
 * Domain Path: /languages
 * License: GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'BL_PLUGIN_FILE', __FILE__ );
define( 'BL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BL_VERSION', '0.1.0' );
// Carica l’autoloader di Composer per AWS SDK e Guzzle
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    error_log( 'Book Library Plugin: Composer autoloader not found in ' . __DIR__ . '/vendor/autoload.php' );
}

// Include core files
require_once BL_PLUGIN_DIR . 'includes/plugin-setup.php';
require_once BL_PLUGIN_DIR . 'includes/admin/settings-page.php';
require_once BL_PLUGIN_DIR . 'includes/admin/metabox.php';
require_once BL_PLUGIN_DIR . 'includes/shortcode.php';

// Load textdomain for translations
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'book-library', false, dirname( plugin_basename( BL_PLUGIN_FILE ) ) . '/languages/' );
});

// Activation: register CPT and taxonomy, then flush rewrite rules
register_activation_hook( BL_PLUGIN_FILE, function() {
    bl_register_cpt_and_tax();
    flush_rewrite_rules();
});

// Deactivation: flush rewrite rules
register_deactivation_hook( BL_PLUGIN_FILE, function() {
    flush_rewrite_rules();
});
