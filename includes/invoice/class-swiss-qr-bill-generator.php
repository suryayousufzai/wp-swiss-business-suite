<?php
/**
 * Swiss QR-Bill Generator v5 - Using QRious library (more reliable)
 */

class Swiss_QR_Bill_Generator {
    
    public static function generate_qr_bill_html( $invoice ) {
        $vat_amount = $invoice['amount'] * ($invoice['vat_rate'] / 100);
        $total = $invoice['amount'] + $vat_amount;
        $iban = 'CH4431999123000889012';
        $iban_formatted = 'CH44 3199 9123 0008 8901 2';
        
        // Generate proper QRR reference with checksum
        $reference = self::generate_qrr_reference( $invoice['invoice_number'] );
        
        // Build proper Swiss QR data
        $qr_data = self::build_swiss_qr_data( $invoice, $total, $iban, $reference );
        $qr_data_escaped = htmlspecialchars($qr_data, ENT_QUOTES, 'UTF-8');
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice ' . esc_html($invoice['invoice_number']) . '</title>';
        
        // Include QRious library (more reliable than QRCode.js)
        $html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>';
        
        $html .= '<style>
            @media print {
                body { margin: 0; padding: 20mm; }
                .no-print { display: none; }
            }
            body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; background: white; line-height: 1.6; }
            .header { text-align: center; margin-bottom: 40px; }
            .invoice-title { font-size: 32px; font-weight: bold; color: #2271b1; }
            .invoice-number { font-size: 16px; margin-top: 10px; color: #666; }
            table { width: 100%; margin: 20px 0; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f0f0f0; font-weight: bold; }
            .total-row { background: #f9f9f9; font-weight: bold; font-size: 18px; }
            .qr-bill-section { margin-top: 60px; padding: 30px; background: white; border: 3px solid #000; page-break-before: always; }
            .qr-bill-title { font-size: 24px; font-weight: bold; margin-bottom: 20px; text-align: center; }
            .qr-bill-container { display: flex; gap: 30px; margin-top: 20px; }
            .qr-receipt { flex: 1; border-right: 1px dashed #666; padding-right: 20px; }
            .qr-payment { flex: 2; padding-left: 20px; }
            .qr-code-wrapper { position: relative; text-align: center; margin: 20px auto; width: 250px; height: 280px; }
            #qrcode { width: 250px; height: 250px; margin: 0 auto; display: block; background: white; }
            .swiss-cross { position: absolute; top: 105px; left: 107px; width: 35px; height: 35px; background: white; border: 1px solid #000; z-index: 10; }
            .swiss-cross:before, .swiss-cross:after { content: ""; position: absolute; background: #FF0000; }
            .swiss-cross:before { width: 25px; height: 7px; top: 14px; left: 5px; }
            .swiss-cross:after { width: 7px; height: 25px; top: 5px; left: 14px; }
            .payment-amount { font-size: 18px; font-weight: bold; margin: 10px 0; }
            .payment-info { font-size: 12px; line-height: 1.8; }
            .section-title { font-weight: bold; font-size: 10px; margin-top: 15px; margin-bottom: 5px; text-transform: uppercase; }
            .print-btn { background: #2271b1; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin: 20px 0; }
            .print-btn:hover { background: #135e96; }
            .error { color: red; padding: 10px; background: #ffeeee; border: 1px solid red; margin: 10px 0; }
        </style></head><body>';
        
        $html .= '<div class="no-print" style="text-align: center;">';
        $html .= '<button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>';
        $html .= '<div id="debug" style="margin-top: 10px; font-size: 12px; color: #666;"></div>';
        $html .= '</div>';
        
        // Invoice Header
        $html .= '<div class="header">';
        $html .= '<div class="invoice-title">🇨🇭 INVOICE / RECHNUNG</div>';
        $html .= '<div class="invoice-number">' . esc_html($invoice['invoice_number']) . '</div>';
        $html .= '</div>';
        
        // Addresses
        $html .= '<table style="border: none; margin-bottom: 30px;">';
        $html .= '<tr style="border: none;"><td style="border: none; width: 50%;"><strong>' . get_bloginfo('name') . '</strong><br>Musterstrasse 1<br>8000 Zürich<br>Switzerland</td>';
        $html .= '<td style="border: none; text-align: right;"><strong>Bill To:</strong><br>' . esc_html($invoice['customer_name']) . '<br>' . esc_html($invoice['customer_email']) . '</td></tr>';
        $html .= '</table>';
        
        // Invoice Details
        $html .= '<table>';
        $html .= '<tr><th>Date</th><th>Due Date</th><th>Status</th></tr>';
        $html .= '<tr><td>' . date('d.m.Y', strtotime($invoice['issue_date'])) . '</td>';
        $html .= '<td>' . date('d.m.Y', strtotime($invoice['due_date'])) . '</td>';
        $html .= '<td><strong>' . strtoupper($invoice['status']) . '</strong></td></tr>';
        $html .= '</table>';
        
        // Line Items
        $html .= '<table>';
        $html .= '<tr><th>Description</th><th style="width: 150px; text-align: right;">Amount</th></tr>';
        $html .= '<tr><td>Service Fee</td><td style="text-align: right;">' . $invoice['currency'] . ' ' . number_format($invoice['amount'], 2) . '</td></tr>';
        $html .= '<tr><td>VAT (7.7%)</td><td style="text-align: right;">' . $invoice['currency'] . ' ' . number_format($vat_amount, 2) . '</td></tr>';
        $html .= '<tr class="total-row"><td>TOTAL</td><td style="text-align: right;">' . $invoice['currency'] . ' ' . number_format($total, 2) . '</td></tr>';
        $html .= '</table>';
        
        // Swiss QR-Bill Section
        $html .= '<div class="qr-bill-section">';
        $html .= '<div class="qr-bill-title">Payment Part / Zahlteil</div>';
        
        $html .= '<div class="qr-bill-container">';
        
        // Receipt Section (left)
        $html .= '<div class="qr-receipt">';
        $html .= '<div style="font-size: 11px; font-weight: bold; margin-bottom: 10px;">Receipt</div>';
        $html .= '<div class="section-title">Account / Payable to</div>';
        $html .= '<div class="payment-info">';
        $html .= '<div>' . $iban_formatted . '</div>';
        $html .= '<div>' . get_bloginfo('name') . '</div>';
        $html .= '<div>Musterstrasse 1</div>';
        $html .= '<div>8000 Zürich</div>';
        $html .= '</div>';
        $html .= '<div class="section-title">Reference</div>';
        $html .= '<div class="payment-info" style="font-family: monospace; font-size: 10px;">' . chunk_split($reference, 5, ' ') . '</div>';
        $html .= '<div class="section-title">Payable by</div>';
        $html .= '<div class="payment-info">';
        $html .= '<div>' . esc_html($invoice['customer_name']) . '</div>';
        $html .= '</div>';
        $html .= '<div class="section-title">Currency & Amount</div>';
        $html .= '<div class="payment-amount">' . $invoice['currency'] . ' ' . number_format($total, 2) . '</div>';
        $html .= '</div>';
        
        // Payment Section (right) with QR code
        $html .= '<div class="qr-payment">';
        $html .= '<div style="font-size: 11px; font-weight: bold; margin-bottom: 10px;">Payment part</div>';
        
        // QR Code Container with Swiss Cross
        $html .= '<div class="qr-code-wrapper">';
        $html .= '<canvas id="qrcode"></canvas>';
        $html .= '<div class="swiss-cross"></div>';
        $html .= '<div style="font-size: 10px; margin-top: 10px;">Swiss QR Code</div>';
        $html .= '</div>';
        
        $html .= '<div class="section-title">Currency & Amount</div>';
        $html .= '<div class="payment-amount">' . $invoice['currency'] . ' ' . number_format($total, 2) . '</div>';
        
        $html .= '<div class="section-title">Account / Payable to</div>';
        $html .= '<div class="payment-info">';
        $html .= '<div>' . $iban_formatted . '</div>';
        $html .= '<div>' . get_bloginfo('name') . '</div>';
        $html .= '<div>Musterstrasse 1</div>';
        $html .= '<div>8000 Zürich</div>';
        $html .= '</div>';
        
        $html .= '<div class="section-title">Reference</div>';
        $html .= '<div class="payment-info" style="font-family: monospace;">' . chunk_split($reference, 5, ' ') . '</div>';
        
        $html .= '<div class="section-title">Additional information</div>';
        $html .= '<div class="payment-info">Invoice ' . esc_html($invoice['invoice_number']) . '</div>';
        
        $html .= '<div class="section-title">Payable by</div>';
        $html .= '<div class="payment-info">';
        $html .= '<div>' . esc_html($invoice['customer_name']) . '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div style="margin-top: 20px; font-size: 10px; text-align: center; color: #666;">';
        $html .= 'Scan with PostFinance, TWINT, UBS, Credit Suisse, Raiffeisen or any Swiss banking app';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // JavaScript to generate QR code with QRious
        $html .= '<script>';
        $html .= 'var debugDiv = document.getElementById("debug");';
        $html .= 'try {';
        $html .= '  debugDiv.innerHTML = "Loading QRious library...";';
        $html .= '  if (typeof QRious === "undefined") {';
        $html .= '    throw new Error("QRious library not loaded!");';
        $html .= '  }';
        $html .= '  debugDiv.innerHTML = "Generating QR code...";';
        $html .= '  var qrData = ' . json_encode($qr_data) . ';';
        $html .= '  var qr = new QRious({';
        $html .= '    element: document.getElementById("qrcode"),';
        $html .= '    value: qrData,';
        $html .= '    size: 250,';
        $html .= '    level: "H"';
        $html .= '  });';
        $html .= '  debugDiv.innerHTML = "✅ QR code generated! (" + qrData.length + " chars)";';
        $html .= '  setTimeout(function() { debugDiv.innerHTML = ""; }, 3000);';
        $html .= '} catch(e) {';
        $html .= '  debugDiv.innerHTML = "<div class=\'error\'>❌ ERROR: " + e.message + "</div>";';
        $html .= '  console.error("QR generation error:", e);';
        $html .= '}';
        $html .= '</script>';
        
        $html .= '</body></html>';
        
        return $html;
    }
    
    private static function generate_qrr_reference( $invoice_number ) {
        $numbers = preg_replace('/[^0-9]/', '', $invoice_number);
        $reference_base = str_pad($numbers, 26, '0', STR_PAD_LEFT);
        $checksum = self::calculate_qrr_checksum( $reference_base );
        return $reference_base . $checksum;
    }
    
    private static function calculate_qrr_checksum( $number ) {
        $table = array(0, 9, 4, 6, 8, 2, 7, 1, 3, 5);
        $carry = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            $carry = $table[($carry + intval($number[$i])) % 10];
        }
        return (10 - $carry) % 10;
    }
    
    private static function build_swiss_qr_data( $invoice, $total, $iban, $reference ) {
        $lines = array();
        $lines[] = 'SPC';
        $lines[] = '0200';
        $lines[] = '1';
        $lines[] = $iban;
        $lines[] = 'S';
        $lines[] = get_bloginfo('name');
        $lines[] = 'Musterstrasse 1';
        $lines[] = '8000 Zürich';
        $lines[] = '';
        $lines[] = '';
        $lines[] = 'CH';
        for ($i = 0; $i < 7; $i++) { $lines[] = ''; }
        $lines[] = number_format($total, 2, '.', '');
        $lines[] = $invoice['currency'];
        $lines[] = 'S';
        $lines[] = $invoice['customer_name'];
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';
        $lines[] = 'CH';
        $lines[] = 'QRR';
        $lines[] = $reference;
        $lines[] = 'Invoice ' . $invoice['invoice_number'];
        $lines[] = 'EPD';
        $lines[] = '';
        $lines[] = '';
        return implode("\r\n", $lines);
    }
}
