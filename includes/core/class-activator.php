<?php
/**
 * Fired during plugin activation.
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Activator {

    /**
     * Actions to perform on plugin activation.
     *
     * Creates database tables and sets default options.
     *
     * @since 1.0.0
     */
    public static function activate() {
        
        // Create database tables
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/database/class-db-setup.php';
        WP_Swiss_Business_Suite_DB_Setup::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation timestamp
        update_option( 'wp_swiss_business_suite_activated', current_time( 'timestamp' ) );
        update_option( 'wp_swiss_business_suite_version', WP_SWISS_BUSINESS_SUITE_VERSION );
    }

    /**
     * Set default plugin options.
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        
        $default_options = array(
            'default_language'    => 'de',
            'enabled_languages'   => array( 'de', 'fr', 'it', 'en' ),
            'booking_enabled'     => true,
            'booking_time_slots'  => 30, // minutes
            'booking_buffer_time' => 15, // minutes
            'email_notifications' => true,
            'admin_email'         => get_option( 'admin_email' ),
        );
        
        add_option( 'wp_swiss_business_suite_options', $default_options );
    }
}
