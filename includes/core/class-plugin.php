<?php
/**
 * The core plugin class.
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @var WP_Swiss_Business_Suite_Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version     = WP_SWISS_BUSINESS_SUITE_VERSION;
        $this->plugin_name = 'wp-swiss-business-suite';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        
        // Core classes
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/core/class-loader.php';
        
        // Database
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/database/class-db-setup.php';
        
        // Multilang classes
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-language-manager.php';
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-language-switcher.php';
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-translator.php';
        
        // Booking classes
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-booking-manager.php';
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-calendar.php';
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-email-handler.php';
        
        // Admin classes
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'admin/class-admin.php';
        
        // Public classes
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'public/class-public.php';

        $this->loader = new WP_Swiss_Business_Suite_Loader();
    }

    /**
     * Register all of the hooks related to the admin area.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Swiss_Business_Suite_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new WP_Swiss_Business_Suite_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        
        // Enqueue translation assets
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_translation_assets' );
        
        // Add language switcher to navigation menus
        $this->loader->add_filter( 'wp_nav_menu_items', $plugin_public, 'add_language_switcher_to_menu', 10, 2 );
        
        // Register language switcher shortcode
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
        
        // Initialize language switcher widget
        $language_switcher = new WP_Swiss_Business_Suite_Language_Switcher();
        $this->loader->add_action( 'widgets_init', $language_switcher, 'register_widget' );
        
        // Initialize translator
        $translator = new WP_Swiss_Business_Suite_Translator();
        
        // Initialize booking shortcodes
        $booking_manager = new WP_Swiss_Business_Suite_Booking_Manager();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     *
     * @return WP_Swiss_Business_Suite_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
