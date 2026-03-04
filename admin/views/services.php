<?php
/**
 * Services Management - Clean Layout
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$services_table = $wpdb->prefix . 'sbs_services';

// Handle form submissions
if ( isset( $_POST['action'] ) ) {
    check_admin_referer( 'wp_sbs_service_action' );
    
    if ( $_POST['action'] === 'add' || $_POST['action'] === 'edit' ) {
        $service_data = array(
            'service_name' => sanitize_text_field( $_POST['service_name'] ),
            'service_description' => sanitize_textarea_field( $_POST['service_description'] ),
            'duration' => intval( $_POST['duration'] ),
            'price' => floatval( $_POST['price'] ),
            'currency' => sanitize_text_field( $_POST['currency'] ),
            'is_active' => isset( $_POST['is_active'] ) ? 1 : 0
        );
        
        if ( $_POST['action'] === 'add' ) {
            $wpdb->insert( $services_table, $service_data );
            echo '<div class="notice notice-success"><p>Service added successfully!</p></div>';
        } else {
            $wpdb->update( $services_table, $service_data, array( 'id' => intval( $_POST['service_id'] ) ) );
            echo '<div class="notice notice-success"><p>Service updated successfully!</p></div>';
        }
    }
    
    if ( $_POST['action'] === 'delete' ) {
        $wpdb->delete( $services_table, array( 'id' => intval( $_POST['service_id'] ) ) );
        echo '<div class="notice notice-success"><p>Service deleted successfully!</p></div>';
    }
}

$services = $wpdb->get_results( "SELECT * FROM $services_table ORDER BY id ASC" );
$editing_service = null;

if ( isset( $_GET['edit'] ) ) {
    $editing_service = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $services_table WHERE id = %d", intval( $_GET['edit'] ) ) );
}
?>

<div class="wrap">
    <h1>Services Management</h1>
    
    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 20px; margin-top: 20px;">
        
        <!-- Add/Edit Form -->
        <div class="card" style="padding: 20px;">
            <h2><?php echo $editing_service ? 'Edit Service' : 'Add New Service'; ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'wp_sbs_service_action' ); ?>
                <input type="hidden" name="action" value="<?php echo $editing_service ? 'edit' : 'add'; ?>">
                <?php if ( $editing_service ) : ?>
                    <input type="hidden" name="service_id" value="<?php echo esc_attr( $editing_service->id ); ?>">
                <?php endif; ?>
                
                <table class="form-table" style="margin-top: 0;">
                    <tr>
                        <th style="padding-left: 0;"><label>Service Name</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <input type="text" name="service_name" class="regular-text" 
                                   value="<?php echo $editing_service ? esc_attr( $editing_service->service_name ) : ''; ?>" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th style="padding-left: 0;"><label>Description</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <textarea name="service_description" class="large-text" rows="3" required><?php echo $editing_service ? esc_textarea( $editing_service->service_description ) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th style="padding-left: 0;"><label>Duration (minutes)</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <input type="number" name="duration" class="regular-text" 
                                   value="<?php echo $editing_service ? esc_attr( $editing_service->duration ) : '60'; ?>" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th style="padding-left: 0;"><label>Price</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <input type="number" step="0.01" name="price" class="regular-text" 
                                   value="<?php echo $editing_service ? esc_attr( $editing_service->price ) : '0.00'; ?>" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th style="padding-left: 0;"><label>Currency</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <select name="currency" class="regular-text">
                                <option value="CHF" <?php echo ($editing_service && $editing_service->currency === 'CHF') ? 'selected' : ''; ?>>CHF</option>
                                <option value="EUR" <?php echo ($editing_service && $editing_service->currency === 'EUR') ? 'selected' : ''; ?>>EUR</option>
                                <option value="USD" <?php echo ($editing_service && $editing_service->currency === 'USD') ? 'selected' : ''; ?>>USD</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th style="padding-left: 0;"><label>Status</label></th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0;">
                            <label>
                                <input type="checkbox" name="is_active" value="1" 
                                       <?php echo (!$editing_service || $editing_service->is_active) ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit" style="margin-top: 20px; padding-left: 0;">
                    <button type="submit" class="button button-primary">
                        <?php echo $editing_service ? 'Update Service' : 'Add Service'; ?>
                    </button>
                    <?php if ( $editing_service ) : ?>
                        <a href="?page=wp-swiss-business-suite-services" class="button">Cancel</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- Services List -->
        <div>
            <div class="card">
                <h2>All Services</h2>
                
                <?php if ( $services ) : ?>
                    <div style="overflow-x: auto;">
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">ID</th>
                                    <th style="min-width: 120px;">Service Name</th>
                                    <th style="min-width: 200px;">Description</th>
                                    <th style="width: 90px;">Duration</th>
                                    <th style="width: 110px;">Price</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $services as $service ) : ?>
                                    <tr>
                                        <td><?php echo esc_html( $service->id ); ?></td>
                                        <td><strong><?php echo esc_html( $service->service_name ); ?></strong></td>
                                        <td style="word-wrap: break-word; max-width: 300px;">
                                            <?php echo esc_html( $service->service_description ); ?>
                                        </td>
                                        <td><?php echo esc_html( $service->duration ); ?> min</td>
                                        <td><?php echo esc_html( $service->currency . ' ' . number_format( $service->price, 2 ) ); ?></td>
                                        <td>
                                            <?php if ( $service->is_active ) : ?>
                                                <span style="color: #00a32a; font-weight: 500;">Active</span>
                                            <?php else : ?>
                                                <span style="color: #999;">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?page=wp-swiss-business-suite-services&edit=<?php echo esc_attr( $service->id ); ?>" 
                                               class="button button-small">Edit</a>
                                            
                                            <form method="post" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this service?');">
                                                <?php wp_nonce_field( 'wp_sbs_service_action' ); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="service_id" value="<?php echo esc_attr( $service->id ); ?>">
                                                <button type="submit" class="button button-small" style="color: #b32d2e;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p style="padding: 20px;">No services found. Add your first service using the form on the left.</p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<style>
.wrap table.wp-list-table {
    border-collapse: collapse;
}
.wrap table.wp-list-table td {
    padding: 12px 10px;
    vertical-align: top;
    white-space: normal;
    word-break: break-word;
}
.wrap table.wp-list-table th {
    padding: 12px 10px;
    white-space: nowrap;
    font-weight: 600;
}
.wrap .form-table th {
    font-weight: 600;
}
</style>
