<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package QR_Code_Generator
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Clean up plugin data on uninstall.
 */
function qr_code_generator_uninstall() {
    // Delete plugin options if any are stored
    delete_option( 'qr_code_generator_version' );
    delete_option( 'qr_code_generator_settings' );
    
    // Clear any cached data
    wp_cache_flush();
    
    // Remove any custom database tables if created
    // (Currently not used, but prepared for future enhancements)
    global $wpdb;
    
    // Example: $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}qr_code_history" );
}

// Run uninstall cleanup
qr_code_generator_uninstall();
