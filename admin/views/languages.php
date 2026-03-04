<?php
/**
 * Languages Management with Toggle
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$languages_table = $wpdb->prefix . 'sbs_languages';

// Handle toggle action
if ( isset( $_POST['toggle_language'] ) ) {
    check_admin_referer( 'wp_sbs_language_toggle' );
    
    $lang_id = intval( $_POST['language_id'] );
    $current_status = intval( $_POST['current_status'] );
    $new_status = $current_status ? 0 : 1;
    
    $wpdb->update( 
        $languages_table, 
        array( 'is_active' => $new_status ),
        array( 'id' => $lang_id )
    );
    
    $action = $new_status ? 'enabled' : 'disabled';
    echo '<div class="notice notice-success"><p>Language ' . $action . ' successfully!</p></div>';
}

$languages = $wpdb->get_results( "SELECT * FROM $languages_table ORDER BY display_order" );
?>

<div class="wrap">
    <h1>Languages Management</h1>
    
    <p>Manage Swiss official languages for your website.</p>
    
    <div class="card" style="margin-top: 20px;">
        <h2>Supported Languages</h2>
        
        <?php if ( $languages ) : ?>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Code</th>
                        <th>Name</th>
                        <th>Native Name</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $languages as $lang ) : ?>
                        <tr>
                            <td>
                                <strong style="font-size: 18px;"><?php echo esc_html( $lang->flag_icon ); ?></strong>
                                <?php echo esc_html( strtoupper( $lang->code ) ); ?>
                            </td>
                            <td><?php echo esc_html( $lang->name ); ?></td>
                            <td><?php echo esc_html( $lang->native_name ); ?></td>
                            <td>
                                <?php if ( $lang->is_active ) : ?>
                                    <span style="display: inline-block; padding: 4px 12px; background: #00a32a; color: white; border-radius: 3px; font-size: 12px; font-weight: 500;">
                                        ACTIVE
                                    </span>
                                <?php else : ?>
                                    <span style="display: inline-block; padding: 4px 12px; background: #999; color: white; border-radius: 3px; font-size: 12px; font-weight: 500;">
                                        INACTIVE
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field( 'wp_sbs_language_toggle' ); ?>
                                    <input type="hidden" name="toggle_language" value="1">
                                    <input type="hidden" name="language_id" value="<?php echo esc_attr( $lang->id ); ?>">
                                    <input type="hidden" name="current_status" value="<?php echo esc_attr( $lang->is_active ); ?>">
                                    
                                    <?php if ( $lang->is_active ) : ?>
                                        <button type="submit" class="button button-small" 
                                                onclick="return confirm('Disable this language?');">
                                            Disable
                                        </button>
                                    <?php else : ?>
                                        <button type="submit" class="button button-small button-primary">
                                            Enable
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No languages found.</p>
        <?php endif; ?>
    </div>
    
    <div class="card" style="margin-top: 20px; padding: 20px;">
        <h2>Language Switcher</h2>
        <p>The language switcher (DE | FR | IT | EN) is automatically integrated into your navigation menu.</p>
        <p><strong>Current status:</strong> 
            <span style="color: #00a32a; font-weight: 500;">Active and visible in menu</span>
        </p>
        
        <h3 style="margin-top: 20px;">Configuration</h3>
        <p>To customize the language switcher:</p>
        <ul>
            <li>Go to <a href="<?php echo admin_url( 'admin.php?page=wp-swiss-business-suite-settings' ); ?>">Swiss Business → Settings</a></li>
            <li>Choose display style (Text, Flags, or both)</li>
            <li>Select menu location</li>
            <li>Enable or disable translation features</li>
        </ul>
        
        <h3 style="margin-top: 20px;">Translation Integration</h3>
        <p>For automatic content translation, we recommend:</p>
        <ul>
            <li><strong>Polylang</strong> - Free, manual translation control</li>
            <li><strong>TranslatePress</strong> - Free & Pro, visual editor</li>
        </ul>
        <p>The language switcher works seamlessly with either plugin.</p>
    </div>
    
    <div class="card" style="margin-top: 20px; padding: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
        <h3 style="margin-top: 0;">How Language Status Works</h3>
        <ul style="margin: 10px 0;">
            <li><strong>Active languages</strong> appear in the menu language switcher</li>
            <li><strong>Inactive languages</strong> are hidden from the menu</li>
            <li>You need at least one active language for the site to work properly</li>
            <li>Changes take effect immediately</li>
        </ul>
    </div>
</div>

<style>
.wrap table.wp-list-table td {
    padding: 12px 10px;
    vertical-align: middle;
}
</style>
