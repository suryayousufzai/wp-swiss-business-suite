<?php
/**
 * Download Handler with Professional Swiss QR-Bill
 */

// Load Swiss QR-Bill Generator
require_once __DIR__ . '/../invoice/class-swiss-qr-bill-generator.php';

class WP_Swiss_Business_Suite_Download_Handler {
    
    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'handle_downloads' ) );
    }
    
    public static function handle_downloads() {
        global $wpdb;
        
        // Handle booking PDF download
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wp-swiss-business-suite-bookings' 
             && isset( $_GET['download_pdf'] ) && isset( $_GET['booking_id'] ) ) {
            
            $booking_id = intval( $_GET['booking_id'] );
            $booking = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbs_bookings WHERE id = %d",
                $booking_id
            ), ARRAY_A );
            
            if ( $booking ) {
                $html = self::generate_booking_pdf_html( $booking );
                
                header('Content-Type: text/html; charset=UTF-8');
                header('Content-Disposition: inline; filename="booking-' . $booking['booking_number'] . '.html"');
                echo $html;
                exit;
            }
        }
        
        // Handle invoice PDF download with REAL Swiss QR-Bill
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wp-swiss-business-suite-invoices' 
             && isset( $_GET['download_invoice'] ) && isset( $_GET['invoice_id'] ) ) {
            
            $invoice_id = intval( $_GET['invoice_id'] );
            $invoice = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbs_invoices WHERE id = %d",
                $invoice_id
            ), ARRAY_A );
            
            if ( $invoice ) {
                // Use professional Swiss QR-Bill generator
                $html = Swiss_QR_Bill_Generator::generate_qr_bill_html( $invoice );
                
                header('Content-Type: text/html; charset=UTF-8');
                header('Content-Disposition: inline; filename="invoice-' . $invoice['invoice_number'] . '.html"');
                echo $html;
                exit;
            }
        }
    }
    
    private static function generate_booking_pdf_html( $booking ) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Booking ' . esc_html($booking['booking_number']) . '</title><style>
            @media print {
                body { margin: 0; padding: 20px; }
                .no-print { display: none; }
            }
            body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; background: white; }
            .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2271b1; padding-bottom: 20px; }
            .title { font-size: 28px; color: #2271b1; font-weight: bold; }
            .booking-number { font-size: 18px; margin-top: 10px; color: #666; }
            .section { margin: 30px 0; }
            .section-title { font-size: 18px; font-weight: bold; color: #2271b1; margin-bottom: 15px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
            .info-row { margin: 12px 0; font-size: 14px; }
            .label { font-weight: bold; display: inline-block; width: 180px; }
            .value { display: inline-block; }
            .status-badge { padding: 8px 20px; background: #00a32a; color: white; border-radius: 5px; font-weight: bold; }
            .footer { margin-top: 60px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
            .print-btn { background: #2271b1; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin: 20px 0; }
            .print-btn:hover { background: #135e96; }
        </style></head><body>';
        
        $html .= '<div class="no-print" style="text-align: center;">';
        $html .= '<button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>';
        $html .= '</div>';
        
        $html .= '<div class="header">';
        $html .= '<div class="title">' . esc_html(get_bloginfo('name')) . '</div>';
        $html .= '<div class="title" style="margin-top:10px;">BOOKING CONFIRMATION</div>';
        $html .= '<div class="booking-number">Booking Number: ' . esc_html($booking['booking_number']) . '</div>';
        $html .= '</div>';
        
        $html .= '<div class="section">';
        $html .= '<div class="section-title">Customer Information</div>';
        $html .= '<div class="info-row"><span class="label">Name:</span><span class="value">' . esc_html($booking['customer_name']) . '</span></div>';
        $html .= '<div class="info-row"><span class="label">Email:</span><span class="value">' . esc_html($booking['customer_email']) . '</span></div>';
        $html .= '<div class="info-row"><span class="label">Phone:</span><span class="value">' . esc_html($booking['customer_phone']) . '</span></div>';
        $html .= '</div>';
        
        $html .= '<div class="section">';
        $html .= '<div class="section-title">Booking Details</div>';
        $html .= '<div class="info-row"><span class="label">Service:</span><span class="value">' . esc_html($booking['service_type']) . '</span></div>';
        $html .= '<div class="info-row"><span class="label">Date:</span><span class="value">' . date('d.m.Y', strtotime($booking['booking_date'])) . '</span></div>';
        $html .= '<div class="info-row"><span class="label">Time:</span><span class="value">' . date('H:i', strtotime($booking['booking_time'])) . '</span></div>';
        $html .= '<div class="info-row"><span class="label">Duration:</span><span class="value">' . esc_html($booking['duration']) . ' minutes</span></div>';
        $html .= '<div class="info-row"><span class="label">Status:</span><span class="status-badge">' . strtoupper($booking['status']) . '</span></div>';
        $html .= '</div>';
        
        if (!empty($booking['notes'])) {
            $html .= '<div class="section">';
            $html .= '<div class="section-title">Notes</div>';
            $html .= '<div>' . nl2br(esc_html($booking['notes'])) . '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="footer">';
        $html .= '<p>Generated: ' . date('d.m.Y H:i') . '</p>';
        $html .= '<p>' . esc_html(get_bloginfo('name')) . ' | ' . esc_html(get_bloginfo('url')) . '</p>';
        $html .= '<p>For questions: ' . get_option('admin_email') . '</p>';
        $html .= '</div>';
        
        $html .= '</body></html>';
        
        return $html;
    }
}

WP_Swiss_Business_Suite_Download_Handler::init();
