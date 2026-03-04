<?php
/**
 * Database setup and management.
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_DB_Setup {

    /**
     * Create all plugin database tables.
     *
     * @since 1.0.0
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Languages table
        $table_languages = $wpdb->prefix . 'sbs_languages';
        $sql_languages = "CREATE TABLE $table_languages (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(10) NOT NULL,
            name varchar(100) NOT NULL,
            native_name varchar(100) NOT NULL,
            flag_icon varchar(10) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            display_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) $charset_collate;";
        
        dbDelta( $sql_languages );
        
        // Insert default languages
        self::insert_default_languages();

        // Translations table
        $table_translations = $wpdb->prefix . 'sbs_translations';
        $sql_translations = "CREATE TABLE $table_translations (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            language_code varchar(10) NOT NULL,
            translated_title text,
            translated_content longtext,
            translated_excerpt text,
            meta_description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY language_code (language_code),
            UNIQUE KEY post_lang (post_id, language_code)
        ) $charset_collate;";
        
        dbDelta( $sql_translations );

        // Bookings table
        $table_bookings = $wpdb->prefix . 'sbs_bookings';
        $sql_bookings = "CREATE TABLE $table_bookings (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_number varchar(50) NOT NULL,
            customer_name varchar(200) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(50),
            service_type varchar(100),
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            duration int(11) DEFAULT 60,
            language varchar(10) DEFAULT 'de',
            status varchar(20) DEFAULT 'pending',
            notes text,
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY booking_number (booking_number),
            KEY customer_email (customer_email),
            KEY booking_date (booking_date),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta( $sql_bookings );

        // Booking meta table (for extensibility)
        $table_booking_meta = $wpdb->prefix . 'sbs_booking_meta';
        $sql_booking_meta = "CREATE TABLE $table_booking_meta (
            meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            PRIMARY KEY  (meta_id),
            KEY booking_id (booking_id),
            KEY meta_key (meta_key(191))
        ) $charset_collate;";
        
        dbDelta( $sql_booking_meta );

        // Services table
        $table_services = $wpdb->prefix . 'sbs_services';
        $sql_services = "CREATE TABLE $table_services (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            service_name varchar(200) NOT NULL,
            service_description text,
            duration int(11) DEFAULT 60,
            price decimal(10,2) DEFAULT 0.00,
            currency varchar(10) DEFAULT 'CHF',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta( $sql_services );
        
        // Insert default services
        self::insert_default_services();
        
        // Invoices table (Phase 2)
        $table_invoices = $wpdb->prefix . 'sbs_invoices';
        $sql_invoices = "CREATE TABLE $table_invoices (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            invoice_number varchar(50) NOT NULL,
            booking_id bigint(20) unsigned,
            customer_name varchar(200) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_address text,
            amount decimal(10,2) NOT NULL,
            currency varchar(10) DEFAULT 'CHF',
            vat_rate decimal(5,2) DEFAULT 7.70,
            status varchar(20) DEFAULT 'pending',
            issue_date datetime NOT NULL,
            due_date datetime NOT NULL,
            paid_date datetime,
            qr_reference varchar(27),
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY booking_id (booking_id),
            KEY status (status),
            KEY due_date (due_date)
        ) $charset_collate;";
        
        dbDelta( $sql_invoices );
    }

    /**
     * Insert default Swiss languages.
     *
     * @since 1.0.0
     */
    private static function insert_default_languages() {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_languages';
        
        $languages = array(
            array(
                'code'        => 'de',
                'name'        => 'German',
                'native_name' => 'Deutsch',
                'flag_icon'   => '🇩🇪',
                'is_active'   => 1,
                'display_order' => 1,
            ),
            array(
                'code'        => 'fr',
                'name'        => 'French',
                'native_name' => 'Français',
                'flag_icon'   => '🇫🇷',
                'is_active'   => 1,
                'display_order' => 2,
            ),
            array(
                'code'        => 'it',
                'name'        => 'Italian',
                'native_name' => 'Italiano',
                'flag_icon'   => '🇮🇹',
                'is_active'   => 1,
                'display_order' => 3,
            ),
            array(
                'code'        => 'en',
                'name'        => 'English',
                'native_name' => 'English',
                'flag_icon'   => '🇬🇧',
                'is_active'   => 1,
                'display_order' => 4,
            ),
        );
        
        foreach ( $languages as $lang ) {
            $wpdb->replace( $table, $lang );
        }
    }

    /**
     * Insert default services.
     *
     * @since 1.0.0
     */
    private static function insert_default_services() {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_services';
        
        $services = array(
            array(
                'service_name'        => 'Consultation',
                'service_description' => 'Standard consultation service',
                'duration'            => 60,
                'price'               => 150.00,
                'currency'            => 'CHF',
                'is_active'           => 1,
            ),
            array(
                'service_name'        => 'Quick Meeting',
                'service_description' => 'Short 30-minute meeting',
                'duration'            => 30,
                'price'               => 80.00,
                'currency'            => 'CHF',
                'is_active'           => 1,
            ),
        );
        
        foreach ( $services as $service ) {
            $existing = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table WHERE service_name = %s",
                $service['service_name']
            ) );
            
            if ( ! $existing ) {
                $wpdb->insert( $table, $service );
            }
        }
    }

    /**
     * Drop all plugin tables.
     * Called on plugin uninstall.
     *
     * @since 1.0.0
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'sbs_languages',
            $wpdb->prefix . 'sbs_translations',
            $wpdb->prefix . 'sbs_bookings',
            $wpdb->prefix . 'sbs_booking_meta',
            $wpdb->prefix . 'sbs_services',
        );
        
        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS $table" );
        }
    }
}
