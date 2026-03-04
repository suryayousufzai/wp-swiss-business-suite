<?php
/**
 * PDF Generator for Booking Confirmations
 * Using TCPDF library (WordPress compatible)
 */

class WP_Swiss_Business_Suite_PDF_Generator {

    /**
     * Generate booking confirmation PDF
     */
    public static function generate_booking_pdf( $booking_id ) {
        global $wpdb;
        
        // Get booking data
        $booking = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_bookings WHERE id = %d",
            $booking_id
        ), ARRAY_A );
        
        if ( ! $booking ) {
            return false;
        }
        
        // Check if TCPDF is available
        if ( ! class_exists('TCPDF') ) {
            require_once( WP_SWISS_BUSINESS_SUITE_PLUGIN_DIR . 'includes/lib/tcpdf/tcpdf.php' );
        }
        
        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        
        // Set document information
        $pdf->SetCreator('WP Swiss Business Suite');
        $pdf->SetAuthor( get_bloginfo('name') );
        $pdf->SetTitle('Booking Confirmation - ' . $booking['booking_number']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);
        
        // Add page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Build PDF content
        $html = self::get_booking_pdf_html( $booking );
        
        // Output PDF content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Return PDF as string or save to file
        $filename = 'booking-' . $booking['booking_number'] . '.pdf';
        
        return array(
            'pdf' => $pdf,
            'filename' => $filename,
            'content' => $pdf->Output($filename, 'S') // Return as string
        );
    }
    
    /**
     * Get HTML content for booking PDF
     */
    private static function get_booking_pdf_html( $booking ) {
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        
        $html = '
        <style>
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 24px; font-weight: bold; color: #2271b1; }
            .booking-number { font-size: 18px; margin-top: 10px; }
            .section { margin: 20px 0; }
            .section-title { font-size: 14px; font-weight: bold; color: #2271b1; margin-bottom: 10px; border-bottom: 2px solid #2271b1; padding-bottom: 5px; }
            .info-row { margin: 8px 0; }
            .label { font-weight: bold; width: 150px; display: inline-block; }
            .value { display: inline-block; }
            .status { padding: 5px 15px; background: #00a32a; color: white; border-radius: 3px; display: inline-block; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 9px; color: #666; }
        </style>
        
        <div class="header">
            <div class="logo">' . esc_html($site_name) . '</div>
            <div class="booking-number">Booking Confirmation</div>
            <div class="booking-number">' . esc_html($booking['booking_number']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Customer Information</div>
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">' . esc_html($booking['customer_name']) . '</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">' . esc_html($booking['customer_email']) . '</span>
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value">' . esc_html($booking['customer_phone']) . '</span>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Booking Details</div>
            <div class="info-row">
                <span class="label">Service:</span>
                <span class="value">' . esc_html($booking['service_type'] ? $booking['service_type'] : 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">' . date('d.m.Y', strtotime($booking['booking_date'])) . '</span>
            </div>
            <div class="info-row">
                <span class="label">Time:</span>
                <span class="value">' . date('H:i', strtotime($booking['booking_time'])) . '</span>
            </div>
            <div class="info-row">
                <span class="label">Duration:</span>
                <span class="value">' . esc_html($booking['duration']) . ' minutes</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status">' . esc_html(strtoupper($booking['status'])) . '</span>
            </div>
        </div>
        
        ' . (!empty($booking['notes']) ? '
        <div class="section">
            <div class="section-title">Notes</div>
            <div>' . nl2br(esc_html($booking['notes'])) . '</div>
        </div>
        ' : '') . '
        
        <div class="footer">
            <p>Generated: ' . date('d.m.Y H:i') . '</p>
            <p>' . esc_html($site_name) . ' | ' . esc_html($site_url) . '</p>
            <p>For questions, please contact: ' . get_option('admin_email') . '</p>
        </div>
        ';
        
        return $html;
    }
    
    /**
     * Download PDF directly
     */
    public static function download_booking_pdf( $booking_id ) {
        $result = self::generate_booking_pdf( $booking_id );
        
        if ( $result ) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            echo $result['content'];
            exit;
        }
        
        return false;
    }
    
    /**
     * Email PDF to customer
     */
    public static function email_booking_pdf( $booking_id ) {
        global $wpdb;
        
        $booking = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_bookings WHERE id = %d",
            $booking_id
        ), ARRAY_A );
        
        if ( ! $booking ) {
            return false;
        }
        
        $pdf_result = self::generate_booking_pdf( $booking_id );
        
        if ( ! $pdf_result ) {
            return false;
        }
        
        // Save PDF temporarily
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/booking-pdfs/';
        
        if ( ! file_exists( $pdf_path ) ) {
            wp_mkdir_p( $pdf_path );
        }
        
        $pdf_file = $pdf_path . $pdf_result['filename'];
        file_put_contents( $pdf_file, $pdf_result['content'] );
        
        // Email subject and message
        $subject = 'Booking Confirmation - ' . $booking['booking_number'];
        $message = "Dear " . $booking['customer_name'] . ",\n\n";
        $message .= "Thank you for your booking. Please find your booking confirmation attached.\n\n";
        $message .= "Booking Details:\n";
        $message .= "- Date: " . date('d.m.Y', strtotime($booking['booking_date'])) . "\n";
        $message .= "- Time: " . date('H:i', strtotime($booking['booking_time'])) . "\n";
        $message .= "- Service: " . $booking['service_type'] . "\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo('name');
        
        // Send email with PDF attachment
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $sent = wp_mail( $booking['customer_email'], $subject, $message, $headers, array($pdf_file) );
        
        // Clean up
        if ( file_exists( $pdf_file ) ) {
            unlink( $pdf_file );
        }
        
        return $sent;
    }
}
