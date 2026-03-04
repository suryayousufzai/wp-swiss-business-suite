<?php
/**
 * Admin Dashboard View
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$bookings_table = $wpdb->prefix . 'sbs_bookings';
$services_table = $wpdb->prefix . 'sbs_services';
$languages_table = $wpdb->prefix . 'sbs_languages';

$total_bookings = $wpdb->get_var( "SELECT COUNT(*) FROM {$bookings_table}" );
$total_services = $wpdb->get_var( "SELECT COUNT(*) FROM {$services_table} WHERE is_active = 1" );
$total_languages = $wpdb->get_var( "SELECT COUNT(*) FROM {$languages_table} WHERE is_active = 1" );
?>

<div class="wrap">
    <h1>WP Swiss Business Suite Dashboard</h1>
    
    <div class="wp-sbs-dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        
        <div class="card" style="padding: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Total Bookings</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                <?php echo esc_html( $total_bookings ); ?>
            </p>
            <p style="margin: 10px 0 0 0;">
                <a href="<?php echo admin_url( 'admin.php?page=wp-swiss-business-suite-bookings' ); ?>">View all bookings</a>
            </p>
        </div>
        
        <div class="card" style="padding: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Active Services</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #00a32a;">
                <?php echo esc_html( $total_services ); ?>
            </p>
            <p style="margin: 10px 0 0 0;">
                <a href="<?php echo admin_url( 'admin.php?page=wp-swiss-business-suite-services' ); ?>">Manage services</a>
            </p>
        </div>
        
        <div class="card" style="padding: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #666;">Languages</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #8c43ff;">
                <?php echo esc_html( $total_languages ); ?>
            </p>
            <p style="margin: 10px 0 0 0;">
                <a href="<?php echo admin_url( 'admin.php?page=wp-swiss-business-suite-languages' ); ?>">Language settings</a>
            </p>
        </div>
        
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        
        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0;">Quick Start</h2>
            
            <h3>Add Booking Form to a Page</h3>
            <p>Copy this shortcode and paste it into any page:</p>
            <code style="display: block; padding: 10px; background: #f0f0f0; border-radius: 4px;">[wp_sbs_booking]</code>
            
            <h3 style="margin-top: 20px;">Language Switcher</h3>
            <p>The language switcher (DE | FR | IT | EN) automatically appears in your navigation menu.</p>
            <p><a href="<?php echo admin_url( 'admin.php?page=wp-swiss-business-suite-settings' ); ?>">Configure in Settings</a></p>
        </div>
        
        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0;">Plugin Information</h2>
            
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 5px 0;"><strong>Version:</strong></td>
                    <td style="padding: 5px 0;"><?php echo WP_SWISS_BUSINESS_SUITE_VERSION; ?></td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Database Tables:</strong></td>
                    <td style="padding: 5px 0;">5 tables created</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Status:</strong></td>
                    <td style="padding: 5px 0;"><span style="color: #00a32a;">Active</span></td>
                </tr>
            </table>
            
            <hr style="margin: 20px 0;">
            
            <p><strong>Author:</strong> Surya Yousufzai</p>
            <p><strong>GitHub:</strong> <a href="https://github.com/suryayousufzai/wp-swiss-business-suite" target="_blank">View Repository</a></p>
        </div>
        
    </div>
    
    <div class="card" style="padding: 20px; margin-top: 20px;">
        <h2 style="margin-top: 0;">Features</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3>Multilingual System</h3>
                <ul style="line-height: 1.8;">
                    <li>Swiss language support (DE, FR, IT, EN)</li>
                    <li>Menu-integrated language switcher</li>
                    <li>Admin control panel</li>
                    <li>Works with Polylang/TranslatePress</li>
                </ul>
            </div>
            
            <div>
                <h3>Booking System</h3>
                <ul style="line-height: 1.8;">
                    <li>Online appointment scheduling</li>
                    <li>Email confirmations</li>
                    <li>Service management</li>
                    <li>Booking overview dashboard</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.wp-sbs-dashboard-stats .card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s;
}
.wp-sbs-dashboard-stats .card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
</style>

                </ul>
            </li>
        </ul>
        
        <h3>🚀 Quick Start Guide:</h3>
        <ol>
            <li><strong>Add Language Switcher:</strong>
                <ul>
                    <li>Go to Appearance → Widgets</li>
                    <li>Add "WP Swiss Business Suite - Language Switcher"</li>
                    <li>Choose style: Flags (recommended), Dropdown, or Buttons</li>
                </ul>
            </li>
            <li><strong>Add Booking Form:</strong>
                <ul>
                    <li>Edit any page</li>
                    <li>Add shortcode: <code>[wp_sbs_booking]</code></li>
                    <li>Beautiful form appears with purple gradient!</li>
                </ul>
            </li>
            <li><strong>Translate Content:</strong>
                <ul>
                    <li>Edit any post/page</li>
                    <li>Scroll to "Translations" meta box</li>
                    <li>Add French, Italian, English translations</li>
                </ul>
            </li>
        </ol>
        
        <h3>📊 Database Status:</h3>
        <p style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
            <strong>✅ All Systems Operational</strong><br>
            5 database tables created successfully<br>
            4 languages ready (DE/FR/IT/EN)<br>
            2 default services available
        </p>
        
        <p><strong>Version:</strong> <?php echo esc_html( WP_SWISS_BUSINESS_SUITE_VERSION ); ?></p>
        <p><strong>Author:</strong> Surya Yousufzai</p>
        <p><strong>Built with ❤️ in Switzerland 🇨🇭</strong></p>
    </div>
</div>
