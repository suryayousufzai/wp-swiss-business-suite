<?php
/**
 * Settings View - Professional Translation
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Handle form submission
if ( isset( $_POST['wp_sbs_save_settings'] ) && check_admin_referer( 'wp_sbs_settings_nonce' ) ) {
    update_option( 'wp_sbs_translation_enabled', isset( $_POST['translation_enabled'] ) ? '1' : '0' );
    update_option( 'wp_sbs_translation_style', sanitize_text_field( $_POST['translation_style'] ) );
    update_option( 'wp_sbs_menu_location', sanitize_text_field( $_POST['menu_location'] ) );
    echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully!</strong></p></div>';
}

$translation_enabled = get_option( 'wp_sbs_translation_enabled', '1' );
$translation_style = get_option( 'wp_sbs_translation_style', 'text' );
$menu_location = get_option( 'wp_sbs_menu_location', 'primary' );
?>

<div class="wrap">
    <h1>🌍 Professional Translation System</h1>
    <p class="description">Swiss-style multilingual integration for your website</p>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'wp_sbs_settings_nonce' ); ?>
        
        <div class="card" style="max-width: 800px;">
            <h2>Translation Configuration</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="translation_enabled">Enable Translation</label>
                    </th>
                    <td>
                        <label class="wp-sbs-toggle-switch">
                            <input type="checkbox" 
                                   name="translation_enabled" 
                                   id="translation_enabled" 
                                   value="1" 
                                   <?php checked( $translation_enabled, '1' ); ?>>
                            <span class="wp-sbs-toggle-slider"></span>
                        </label>
                        <p class="description">Turn multilingual translation ON or OFF globally</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="translation_style">Display Style</label>
                    </th>
                    <td>
                        <select name="translation_style" id="translation_style" class="regular-text">
                            <option value="text" <?php selected( $translation_style, 'text' ); ?>>
                                Text Only (DE | FR | IT | EN) - Swiss Professional Style
                            </option>
                            <option value="text-flags" <?php selected( $translation_style, 'text-flags' ); ?>>
                                Text with Flags (🇩🇪 DE | 🇫🇷 FR | 🇮🇹 IT | 🇬🇧 EN)
                            </option>
                            <option value="flags-only" <?php selected( $translation_style, 'flags-only' ); ?>>
                                Flags Only (🇩🇪 | 🇫🇷 | 🇮🇹 | 🇬🇧)
                            </option>
                        </select>
                        <p class="description">Choose how languages appear in your menu</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="menu_location">Menu Location</label>
                    </th>
                    <td>
                        <select name="menu_location" id="menu_location" class="regular-text">
                            <option value="primary" <?php selected( $menu_location, 'primary' ); ?>>Primary Menu</option>
                            <option value="header" <?php selected( $menu_location, 'header' ); ?>>Header Menu</option>
                            <option value="footer" <?php selected( $menu_location, 'footer' ); ?>>Footer Menu</option>
                            <option value="all" <?php selected( $menu_location, 'all' ); ?>>All Menus</option>
                        </select>
                        <p class="description">Where the language switcher appears in your navigation</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" 
                       name="wp_sbs_save_settings" 
                       class="button button-primary" 
                       value="Save Settings">
            </p>
        </div>
    </form>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>✨ Professional Features</h2>
        <ul style="line-height: 2;">
            <li>✅ <strong>Menu Integration:</strong> Seamlessly integrated into WordPress navigation</li>
            <li>✅ <strong>Automatic Translation:</strong> All pages translated automatically</li>
            <li>✅ <strong>Swiss Professional Style:</strong> Clean, minimal design like Swiss corporate sites</li>
            <li>✅ <strong>Theme Adaptive:</strong> Matches your theme colors and fonts automatically</li>
            <li>✅ <strong>Mobile Responsive:</strong> Perfect on all devices</li>
            <li>✅ <strong>Zero Manual Work:</strong> No page-by-page translation needed</li>
            <li>✅ <strong>SEO Friendly:</strong> Search engines index all language versions</li>
        </ul>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>🇨🇭 Swiss Corporate Website Style</h2>
        <p>Your language switcher will appear like professional Swiss websites:</p>
        <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 10px;">
            <?php if ( $translation_style == 'text' ) : ?>
                <strong>DE</strong> | <strong>FR</strong> | <strong>IT</strong> | <strong>EN</strong>
            <?php elseif ( $translation_style == 'text-flags' ) : ?>
                🇩🇪 <strong>DE</strong> | 🇫🇷 <strong>FR</strong> | 🇮🇹 <strong>IT</strong> | 🇬🇧 <strong>EN</strong>
            <?php else : ?>
                🇩🇪 | 🇫🇷 | 🇮🇹 | 🇬🇧
            <?php endif; ?>
        </div>
        <p style="margin-top: 15px; color: #666;">
            Clean, minimal, and professional - just like SBB.ch, Swiss.com, and other Swiss corporate sites.
        </p>
    </div>
    
    <?php if ( $translation_enabled == '1' ) : ?>
    <div class="notice notice-info" style="margin-top: 20px;">
        <p><strong>✓ Translation is ACTIVE</strong> - Language switcher is visible in your navigation menu.</p>
    </div>
    <?php else : ?>
    <div class="notice notice-warning" style="margin-top: 20px;">
        <p><strong>⚠ Translation is DISABLED</strong> - Enable it above to activate multilingual features.</p>
    </div>
    <?php endif; ?>
</div>

<style>
.wp-sbs-toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}
.wp-sbs-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.wp-sbs-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}
.wp-sbs-toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}
.wp-sbs-toggle-switch input:checked + .wp-sbs-toggle-slider {
    background-color: #00a32a;
}
.wp-sbs-toggle-switch input:checked + .wp-sbs-toggle-slider:before {
    transform: translateX(26px);
}
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}
.card h2 {
    margin-top: 0;
}
</style>
