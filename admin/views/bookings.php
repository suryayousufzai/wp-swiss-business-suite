<?php
/**
 * Bookings Management - Clean version without download code
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$bookings_table = $wpdb->prefix . 'sbs_bookings';

// Handle email PDF
if ( isset( $_GET['email_pdf'] ) && isset( $_GET['booking_id'] ) ) {
    $booking_id = intval( $_GET['booking_id'] );
    $booking = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$bookings_table} WHERE id = %d",
        $booking_id
    ), ARRAY_A );
    
    if ( $booking ) {
        $subject = 'Booking Confirmation - ' . $booking['booking_number'];
        $message = "Dear " . $booking['customer_name'] . ",\n\n";
        $message .= "Thank you for your booking!\n\n";
        $message .= "BOOKING DETAILS:\n";
        $message .= "Booking Number: " . $booking['booking_number'] . "\n";
        $message .= "Service: " . $booking['service_type'] . "\n";
        $message .= "Date: " . date('d.m.Y', strtotime($booking['booking_date'])) . "\n";
        $message .= "Time: " . date('H:i', strtotime($booking['booking_time'])) . "\n";
        $message .= "Duration: " . $booking['duration'] . " minutes\n";
        $message .= "Status: " . strtoupper($booking['status']) . "\n\n";
        $message .= "If you have any questions, please contact us.\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo('name');
        
        $sent = wp_mail( $booking['customer_email'], $subject, $message );
        
        if ( $sent ) {
            echo '<div class="notice notice-success"><p>✅ Email sent successfully to ' . esc_html($booking['customer_email']) . '!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Failed to send email. <strong>Local WordPress needs email configuration.</strong> <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">Install WP Mail SMTP plugin</a> or use a real server.</p></div>';
        }
    }
}

// Handle booking actions
if ( isset( $_POST['booking_action'] ) && check_admin_referer( 'wp_sbs_booking_action' ) ) {
    $booking_id = intval( $_POST['booking_id'] );
    $action = sanitize_text_field( $_POST['booking_action'] );
    
    switch ( $action ) {
        case 'approve':
            $wpdb->update( $bookings_table, array( 'status' => 'confirmed' ), array( 'id' => $booking_id ) );
            
            // Auto-send confirmation email
            $booking = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$bookings_table} WHERE id = %d", $booking_id), ARRAY_A );
            if ($booking) {
                $message = "Dear " . $booking['customer_name'] . ",\n\n";
                $message .= "Your booking has been CONFIRMED!\n\n";
                $message .= "Booking Number: " . $booking['booking_number'] . "\n";
                $message .= "Date: " . date('d.m.Y', strtotime($booking['booking_date'])) . "\n";
                $message .= "Time: " . date('H:i', strtotime($booking['booking_time'])) . "\n\n";
                $message .= "We look forward to seeing you!\n\n";
                $message .= get_bloginfo('name');
                
                $email_sent = wp_mail( $booking['customer_email'], 'Booking Confirmed - ' . $booking['booking_number'], $message );
                
                if ($email_sent) {
                    echo '<div class="notice notice-success"><p>✅ Booking approved and customer notified by email!</p></div>';
                } else {
                    echo '<div class="notice notice-warning"><p>⚠️ Booking approved but email failed. <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">Configure email</a> to send automatically.</p></div>';
                }
            } else {
                echo '<div class="notice notice-success"><p>Booking approved!</p></div>';
            }
            break;
        case 'reject':
            $wpdb->update( $bookings_table, array( 'status' => 'rejected' ), array( 'id' => $booking_id ) );
            echo '<div class="notice notice-success"><p>Booking rejected!</p></div>';
            break;
        case 'delete':
            $wpdb->delete( $bookings_table, array( 'id' => $booking_id ) );
            echo '<div class="notice notice-success"><p>Booking deleted!</p></div>';
            break;
    }
}

// Handle create invoice
if ( isset( $_GET['create_invoice'] ) && isset( $_GET['booking_id'] ) ) {
    $booking_id = intval( $_GET['booking_id'] );
    $booking = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$bookings_table} WHERE id = %d", $booking_id), ARRAY_A );
    
    if ($booking) {
        // Get service price
        $services_table = $wpdb->prefix . 'sbs_services';
        $service = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$services_table} WHERE service_name = %s LIMIT 1",
            $booking['service_type']
        ), ARRAY_A );
        
        $amount = $service ? $service['price'] : 150.00;
        $currency = $service ? $service['currency'] : 'CHF';
        
        // Create invoice
        $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        $wpdb->insert(
            $wpdb->prefix . 'sbs_invoices',
            array(
                'invoice_number' => $invoice_number,
                'booking_id' => $booking_id,
                'customer_name' => $booking['customer_name'],
                'customer_email' => $booking['customer_email'],
                'amount' => $amount,
                'currency' => $currency,
                'vat_rate' => 7.7,
                'status' => 'pending',
                'issue_date' => current_time('mysql'),
                'due_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
            )
        );
        
        echo '<div class="notice notice-success"><p>✅ Invoice ' . $invoice_number . ' created! <a href="?page=wp-swiss-business-suite-invoices">View Invoices →</a></p></div>';
    }
}

// Get all bookings
$bookings = $wpdb->get_results( 
    "SELECT * FROM {$bookings_table} ORDER BY created_at DESC LIMIT 100",
    ARRAY_A
);

$total_bookings = $wpdb->get_var( "SELECT COUNT(*) FROM {$bookings_table}" );
$today_bookings = $wpdb->get_var( $wpdb->prepare( 
    "SELECT COUNT(*) FROM {$bookings_table} WHERE booking_date = %s", 
    date('Y-m-d') 
) );
$pending_bookings = $wpdb->get_var( "SELECT COUNT(*) FROM {$bookings_table} WHERE status = 'pending'" );
?>

<div class="wrap">
    <h1>Bookings Management</h1>
    
    <!-- Email Configuration Notice for Local WordPress -->
    <?php if (strpos(get_site_url(), '.local') !== false || strpos(get_site_url(), 'localhost') !== false) : ?>
    <div class="notice notice-info">
        <p><strong>📧 Email Configuration:</strong> You're using local WordPress (Local by Flywheel). Emails won't send without configuration. <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">Install WP Mail SMTP plugin</a> to send emails locally.</p>
    </div>
    <?php endif; ?>
    
    <div class="wp-sbs-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="card" style="padding: 20px; border-left: 4px solid #2271b1;">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Total Bookings</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                <?php echo esc_html( $total_bookings ); ?>
            </p>
        </div>
        
        <div class="card" style="padding: 20px; border-left: 4px solid #00a32a;">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Today's Bookings</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #00a32a;">
                <?php echo esc_html( $today_bookings ); ?>
            </p>
        </div>
        
        <div class="card" style="padding: 20px; border-left: 4px solid #dba617;">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Pending Approval</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0; color: #dba617;">
                <?php echo esc_html( $pending_bookings ); ?>
            </p>
        </div>
    </div>
    
    <?php if ( $total_bookings == 0 ) : ?>
        <div class="card" style="padding: 20px;">
            <h2>No bookings yet</h2>
            <p>Add booking form to a page: <code>[wp_sbs_booking]</code></p>
        </div>
    <?php else : ?>
        <div class="card">
            <h2 style="padding: 20px; margin: 0; border-bottom: 1px solid #ddd;">
                All Bookings (<?php echo esc_html( $total_bookings ); ?>)
            </h2>
            
            <div style="overflow-x: auto;">
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th style="width: 40px;">ID</th>
                            <th style="min-width: 130px;">Booking #</th>
                            <th style="min-width: 150px;">Customer</th>
                            <th style="min-width: 180px;">Contact</th>
                            <th style="min-width: 120px;">Service</th>
                            <th style="width: 100px;">Date</th>
                            <th style="width: 80px;">Time</th>
                            <th style="width: 100px;">Status</th>
                            <th style="min-width: 300px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $bookings as $booking ) : 
                            $status_colors = array(
                                'pending' => '#dba617',
                                'confirmed' => '#00a32a',
                                'rejected' => '#d63638',
                                'completed' => '#2271b1'
                            );
                            $status_color = isset( $status_colors[$booking['status']] ) ? $status_colors[$booking['status']] : '#999';
                        ?>
                            <tr>
                                <td><?php echo esc_html( $booking['id'] ); ?></td>
                                <td><strong><?php echo esc_html( $booking['booking_number'] ); ?></strong></td>
                                <td><strong><?php echo esc_html( $booking['customer_name'] ); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr( $booking['customer_email'] ); ?>">
                                        <?php echo esc_html( $booking['customer_email'] ); ?>
                                    </a>
                                    <?php if ( $booking['customer_phone'] ) : ?>
                                        <br>
                                        <a href="tel:<?php echo esc_attr( $booking['customer_phone'] ); ?>">
                                            <?php echo esc_html( $booking['customer_phone'] ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $booking['service_type'] ? $booking['service_type'] : 'N/A' ); ?></td>
                                <td><?php echo esc_html( date( 'd.m.Y', strtotime( $booking['booking_date'] ) ) ); ?></td>
                                <td><?php echo esc_html( date( 'H:i', strtotime( $booking['booking_time'] ) ) ); ?></td>
                                <td>
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 3px; background: <?php echo esc_attr( $status_color ); ?>; color: white; font-size: 11px; font-weight: 500;">
                                        <?php echo esc_html( strtoupper( $booking['status'] ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $booking['status'] === 'pending' ) : ?>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'wp_sbs_booking_action' ); ?>
                                            <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking['id'] ); ?>">
                                            <input type="hidden" name="booking_action" value="approve">
                                            <button type="submit" class="button button-small button-primary">✓ Approve</button>
                                        </form>
                                        
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'wp_sbs_booking_action' ); ?>
                                            <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking['id'] ); ?>">
                                            <input type="hidden" name="booking_action" value="reject">
                                            <button type="submit" class="button button-small">✗ Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="?page=wp-swiss-business-suite-bookings&download_pdf=1&booking_id=<?php echo esc_attr( $booking['id'] ); ?>" 
                                       class="button button-small" target="_blank">📄 Download PDF</a>
                                    
                                    <a href="?page=wp-swiss-business-suite-bookings&email_pdf=1&booking_id=<?php echo esc_attr( $booking['id'] ); ?>" 
                                       class="button button-small">📧 Email Customer</a>
                                    
                                    <a href="?page=wp-swiss-business-suite-bookings&create_invoice=1&booking_id=<?php echo esc_attr( $booking['id'] ); ?>" 
                                       class="button button-small" style="background: #00a32a; color: white;">💰 Create Invoice</a>
                                    
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Delete this booking?');">
                                        <?php wp_nonce_field( 'wp_sbs_booking_action' ); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking['id'] ); ?>">
                                        <input type="hidden" name="booking_action" value="delete">
                                        <button type="submit" class="button button-small" style="color: #d63638;">🗑 Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px; padding: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
            <h3 style="margin-top: 0;">🎯 How to Use</h3>
            <ul style="line-height: 2;">
                <li><strong>✓ Approve</strong> - Confirm booking & auto-email customer (if email configured)</li>
                <li><strong>✗ Reject</strong> - Decline the booking</li>
                <li><strong>📄 Download PDF</strong> - Get booking confirmation as HTML file (no headers warning!)</li>
                <li><strong>📧 Email Customer</strong> - Send confirmation email manually</li>
                <li><strong>💰 Create Invoice</strong> - Generate Swiss QR-Bill invoice</li>
                <li><strong>🗑 Delete</strong> - Remove booking permanently</li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<style>
.wp-sbs-stats .card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.wrap table.wp-list-table td {
    padding: 12px 10px;
    vertical-align: top;
}
</style>
