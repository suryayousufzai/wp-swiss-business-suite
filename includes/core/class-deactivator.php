<?php
/**
 * Fired during plugin deactivation.
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Deactivator {

    /**
     * Actions to perform on plugin deactivation.
     *
     * Cleans up temporary data but preserves user data.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        
        // Clear scheduled events
        wp_clear_scheduled_hook( 'wp_swiss_business_suite_daily_cleanup' );
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any transients
        delete_transient( 'wp_swiss_business_suite_bookings_cache' );
        delete_transient( 'wp_swiss_business_suite_languages_cache' );
        
        // Note: We don't delete user data (bookings, translations) on deactivation
        // Only on uninstall (handled by uninstall.php)
    }
}
