<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Admin {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            WP_SWISS_BUSINESS_SUITE_URL . 'admin/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            WP_SWISS_BUSINESS_SUITE_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        
        // Localize script with data
        wp_localize_script(
            $this->plugin_name,
            'wpSwissBizSuiteAdmin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wp-swiss-business-suite-admin' ),
            )
        );
    }

    /**
     * Add plugin admin menu.
     */
    public function add_admin_menu() {
        
        // Main menu
        add_menu_page(
            __( 'Swiss Business Suite', 'wp-swiss-business-suite' ),
            __( 'Swiss Business', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite',
            array( $this, 'display_dashboard' ),
            'dashicons-admin-multisite',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Dashboard', 'wp-swiss-business-suite' ),
            __( 'Dashboard', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite',
            array( $this, 'display_dashboard' )
        );
        
        // Bookings submenu
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Bookings', 'wp-swiss-business-suite' ),
            __( 'Bookings', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite-bookings',
            array( $this, 'display_bookings' )
        );
        
        // Languages submenu
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Languages', 'wp-swiss-business-suite' ),
            __( 'Languages', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite-languages',
            array( $this, 'display_languages' )
        );
        
        // Services submenu
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Services', 'wp-swiss-business-suite' ),
            __( 'Services', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite-services',
            array( $this, 'display_services' )
        );
        
        // Invoices submenu (Phase 2)
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Invoices', 'wp-swiss-business-suite' ),
            __( 'Invoices', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite-invoices',
            array( $this, 'display_invoices' )
        );
        
        // Settings submenu
        add_submenu_page(
            'wp-swiss-business-suite',
            __( 'Settings', 'wp-swiss-business-suite' ),
            __( 'Settings', 'wp-swiss-business-suite' ),
            'manage_options',
            'wp-swiss-business-suite-settings',
            array( $this, 'display_settings' )
        );
    }

    /**
     * Display dashboard page.
     */
    public function display_dashboard() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/dashboard.php';
    }

    /**
     * Display bookings page.
     */
    public function display_bookings() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/bookings.php';
    }

    /**
     * Display languages page.
     */
    public function display_languages() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/languages.php';
    }

    /**
     * Display services page.
     */
    public function display_services() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/services.php';
    }

    /**
     * Display invoices page.
     */
    public function display_invoices() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/invoices.php';
    }

    /**
     * Display settings page.
     */
    public function display_settings() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/views/settings.php';
    }
}
