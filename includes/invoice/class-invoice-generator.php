<?php
/**
 * Swiss QR-Bill Invoice Generator
 * Generates Swiss-compliant invoices with QR codes
 */

class WP_Swiss_Business_Suite_Invoice_Generator {

    /**
     * Create invoice for booking
     */
    public static function create_invoice( $booking_id, $service_id = null ) {
        global $wpdb;
        
        // Get booking data
        $booking = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_bookings WHERE id = %d",
            $booking_id
        ), ARRAY_A );
        
        if ( ! $booking ) {
            return false;
        }
        
        // Get service data if service_id provided
        $service = null;
        if ( $service_id ) {
            $service = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbs_services WHERE id = %d",
                $service_id
            ), ARRAY_A );
        }
        
        // Generate invoice number
        $invoice_number = self::generate_invoice_number();
        
        // Create invoice record
        $invoice_data = array(
            'invoice_number' => $invoice_number,
            'booking_id' => $booking_id,
            'customer_name' => $booking['customer_name'],
            'customer_email' => $booking['customer_email'],
            'amount' => $service ? $service['price'] : 0,
            'currency' => $service ? $service['currency'] : 'CHF',
            'vat_rate' => 7.7, // Swiss VAT rate
            'status' => 'pending',
            'issue_date' => current_time( 'mysql' ),
            'due_date' => date( 'Y-m-d H:i:s', strtotime( '+30 days' ) )
        );
        
        $wpdb->insert( $wpdb->prefix . 'sbs_invoices', $invoice_data );
        $invoice_id = $wpdb->insert_id;
        
        return $invoice_id;
    }
    
    /**
     * Generate Swiss QR-Bill
     */
    public static function generate_qr_bill( $invoice_id ) {
        global $wpdb;
        
        // Get invoice data
        $invoice = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_invoices WHERE id = %d",
            $invoice_id
        ), ARRAY_A );
        
        if ( ! $invoice ) {
            return false;
        }
        
        // Swiss QR-Bill data structure
        $qr_data = self::build_qr_data( $invoice );
        
        // Generate QR code
        $qr_code = self::generate_qr_code( $qr_data );
        
        // Generate PDF with QR-Bill
        $pdf_result = self::generate_invoice_pdf( $invoice, $qr_code );
        
        return $pdf_result;
    }
    
    /**
     * Build Swiss QR-Bill data string
     */
    private static function build_qr_data( $invoice ) {
        $company_name = get_bloginfo( 'name' );
        $company_address = get_option( 'wp_sbs_company_address', '' );
        $iban = get_option( 'wp_sbs_iban', '' );
        
        // Swiss QR-Bill format
        $qr_lines = array(
            'SPC',                              // QRType
            '0200',                             // Version
            '1',                                // Coding
            $iban,                              // IBAN
            'K',                                // Creditor Address Type
            $company_name,                      // Creditor Name
            $company_address,                   // Creditor Street
            '',                                 // Creditor Building Number
            '',                                 // Creditor Postal Code
            '',                                 // Creditor Town
            'CH',                               // Creditor Country
            '',                                 // Ultimate Creditor (empty)
            '',                                 '',                                 '',                                 '',                                 '',                                 '',                                 $invoice['currency'],               // Currency
            number_format( $invoice['amount'], 2, '.', '' ),  // Amount
            'K',                                // Debtor Address Type
            $invoice['customer_name'],          // Debtor Name
            '',                                 // Debtor Street
            '',                                 // Debtor Building Number
            '',                                 // Debtor Postal Code
            '',                                 // Debtor Town
            'CH',                               // Debtor Country
            'QRR',                              // Reference Type
            self::generate_qr_reference( $invoice['invoice_number'] ),  // Reference
            'Invoice ' . $invoice['invoice_number'],  // Unstructured Message
            'EPD',                              // Trailer
            ''                                  // Bill Information
        );
        
        return implode( "\r\n", $qr_lines );
    }
    
    /**
     * Generate QR reference number (Swiss format)
     */
    private static function generate_qr_reference( $invoice_number ) {
        // Remove non-numeric characters
        $number = preg_replace( '/[^0-9]/', '', $invoice_number );
        
        // Pad to 26 digits
        $reference = str_pad( $number, 26, '0', STR_PAD_LEFT );
        
        // Calculate check digit (Modulo 10, recursive)
        $checksum = self::calculate_modulo10( $reference );
        
        return $reference . $checksum;
    }
    
    /**
     * Calculate Modulo 10 check digit
     */
    private static function calculate_modulo10( $number ) {
        $table = array( 0, 9, 4, 6, 8, 2, 7, 1, 3, 5 );
        $carry = 0;
        
        for ( $i = 0; $i < strlen( $number ); $i++ ) {
            $carry = $table[($carry + intval( $number[$i] )) % 10];
        }
        
        return ( 10 - $carry ) % 10;
    }
    
    /**
     * Generate QR code image
     */
    private static function generate_qr_code( $data ) {
        // Use PHP QR Code library or API
        // For now, return placeholder structure
        
        return array(
            'data' => $data,
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'size' => 46 // QR-Bill requires 46x46mm
        );
    }
    
    /**
     * Generate invoice PDF with QR-Bill
     */
    private static function generate_invoice_pdf( $invoice, $qr_code ) {
        // Similar to booking PDF but with Swiss QR-Bill section
        
        if ( ! class_exists('TCPDF') ) {
            require_once( WP_SWISS_BUSINESS_SUITE_PLUGIN_DIR . 'includes/lib/tcpdf/tcpdf.php' );
        }
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        
        $html = self::get_invoice_pdf_html( $invoice, $qr_code );
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $filename = 'invoice-' . $invoice['invoice_number'] . '.pdf';
        
        return array(
            'pdf' => $pdf,
            'filename' => $filename,
            'content' => $pdf->Output($filename, 'S')
        );
    }
    
    /**
     * Get invoice HTML with QR-Bill
     */
    private static function get_invoice_pdf_html( $invoice, $qr_code ) {
        $company = get_bloginfo('name');
        $vat_amount = $invoice['amount'] * ($invoice['vat_rate'] / 100);
        $total = $invoice['amount'] + $vat_amount;
        
        $html = '
        <style>
            .header { text-align: center; margin-bottom: 30px; }
            .invoice-title { font-size: 24px; font-weight: bold; }
            .invoice-number { font-size: 14px; margin-top: 10px; }
            .section { margin: 20px 0; }
            .section-title { font-size: 14px; font-weight: bold; color: #2271b1; margin-bottom: 10px; border-bottom: 2px solid #2271b1; padding-bottom: 5px; }
            table.invoice-table { width: 100%; border-collapse: collapse; }
            table.invoice-table th { background: #f0f0f0; padding: 10px; text-align: left; border: 1px solid #ddd; }
            table.invoice-table td { padding: 10px; border: 1px solid #ddd; }
            .total-row { font-weight: bold; background: #f9f9f9; }
            .qr-section { margin-top: 40px; padding-top: 20px; border-top: 1px dashed #666; }
            .qr-bill-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
        </style>
        
        <div class="header">
            <div class="invoice-title">INVOICE / RECHNUNG</div>
            <div class="invoice-number">' . esc_html($invoice['invoice_number']) . '</div>
        </div>
        
        <div class="section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <strong>' . esc_html($company) . '</strong><br>
                        ' . nl2br(esc_html(get_option('wp_sbs_company_address', ''))) . '
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong>Bill To:</strong><br>
                        ' . esc_html($invoice['customer_name']) . '<br>
                        ' . esc_html($invoice['customer_email']) . '
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <table class="invoice-table">
                <tr>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>' . date('d.m.Y', strtotime($invoice['issue_date'])) . '</td>
                    <td>' . date('d.m.Y', strtotime($invoice['due_date'])) . '</td>
                    <td>' . esc_html(strtoupper($invoice['status'])) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <table class="invoice-table">
                <tr>
                    <th>Description</th>
                    <th style="width: 120px; text-align: right;">Amount</th>
                </tr>
                <tr>
                    <td>Service Fee</td>
                    <td style="text-align: right;">' . $invoice['currency'] . ' ' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>VAT (7.7%)</td>
                    <td style="text-align: right;">' . $invoice['currency'] . ' ' . number_format($vat_amount, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td style="text-align: right;"><strong>' . $invoice['currency'] . ' ' . number_format($total, 2) . '</strong></td>
                </tr>
            </table>
        </div>
        
        <div class="qr-section">
            <div class="qr-bill-title">Swiss QR-Bill / Zahlteil</div>
            <p><em>Scan the QR code below to pay this invoice via e-banking or mobile banking app.</em></p>
            <div style="text-align: center; margin-top: 20px;">
                <img src="' . $qr_code['image'] . '" width="200" height="200" alt="QR Code">
            </div>
            <p style="text-align: center; margin-top: 10px; font-size: 10px;">
                Reference: ' . self::generate_qr_reference($invoice['invoice_number']) . '
            </p>
        </div>
        ';
        
        return $html;
    }
    
    /**
     * Generate invoice number
     */
    private static function generate_invoice_number() {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    /**
     * Email invoice to customer
     */
    public static function email_invoice( $invoice_id ) {
        global $wpdb;
        
        $invoice = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_invoices WHERE id = %d",
            $invoice_id
        ), ARRAY_A );
        
        if ( ! $invoice ) {
            return false;
        }
        
        $pdf_result = self->generate_qr_bill( $invoice_id );
        
        if ( ! $pdf_result ) {
            return false;
        }
        
        // Save PDF temporarily
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/invoices/';
        
        if ( ! file_exists( $pdf_path ) ) {
            wp_mkdir_p( $pdf_path );
        }
        
        $pdf_file = $pdf_path . $pdf_result['filename'];
        file_put_contents( $pdf_file, $pdf_result['content'] );
        
        // Email
        $subject = 'Invoice ' . $invoice['invoice_number'];
        $message = "Dear " . $invoice['customer_name'] . ",\n\n";
        $message .= "Please find your invoice attached.\n\n";
        $message .= "Amount: " . $invoice['currency'] . ' ' . number_format($invoice['amount'], 2) . "\n";
        $message .= "Due Date: " . date('d.m.Y', strtotime($invoice['due_date'])) . "\n\n";
        $message .= "You can pay using the Swiss QR-Bill included in the PDF.\n\n";
        $message .= "Best regards,\n" . get_bloginfo('name');
        
        $sent = wp_mail( $invoice['customer_email'], $subject, $message, array(), array($pdf_file) );
        
        if ( file_exists( $pdf_file ) ) {
            unlink( $pdf_file );
        }
        
        return $sent;
    }
}
