<?php
/**
 * Plugin Name:       WP Swiss Business Suite
 * Plugin URI:        https://github.com/suryayousufzai/wp-swiss-business-suite
 * Description:       All-in-One WordPress solution for Swiss SMEs: Multilingual (DE/FR/IT/EN) + Booking System + Invoice Generator with QR-Bill
 * Version:           3.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Surya Yousufzai
 * Author URI:        https://suryayousufzai.github.io
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-swiss-business-suite
 * Domain Path:       /languages
 *
 * @package WP_Swiss_Business_Suite
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version.
 */
define( 'WP_SWISS_BUSINESS_SUITE_VERSION', '3.2.0' );

/**
 * Plugin directory path.
 */
define( 'WP_SWISS_BUSINESS_SUITE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'WP_SWISS_BUSINESS_SUITE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'WP_SWISS_BUSINESS_SUITE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_swiss_business_suite() {
    require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/core/class-activator.php';
    WP_Swiss_Business_Suite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_swiss_business_suite() {
    require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/core/class-deactivator.php';
    WP_Swiss_Business_Suite_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_swiss_business_suite' );
register_deactivation_hook( __FILE__, 'deactivate_wp_swiss_business_suite' );

/**
 * The core plugin class.
 */
require WP_SWISS_BUSINESS_SUITE_PATH . 'includes/core/class-plugin.php';

/**
 * Load download handler (fixes headers already sent issue)
 */
require WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-download-handler.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_swiss_business_suite() {
    $plugin = new WP_Swiss_Business_Suite();
    $plugin->run();
}

run_wp_swiss_business_suite();
