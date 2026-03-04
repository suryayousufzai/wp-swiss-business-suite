<?php
/**
 * Email Handler - Booking notifications
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Email_Handler {

    /**
     * Send booking confirmation email to customer.
     *
     * @param int $booking_id Booking ID.
     * @return bool
     */
    public function send_booking_confirmation( $booking_id ) {
        $booking = $this->get_booking_details( $booking_id );
        
        if ( ! $booking ) {
            return false;
        }
        
        $to      = $booking->customer_email;
        $subject = sprintf( 'Booking Confirmation - %s', $booking->booking_number );
        $message = $this->get_confirmation_email_body( $booking );
        $headers = $this->get_email_headers();
        
        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Send booking notification to admin.
     *
     * @param int $booking_id Booking ID.
     * @return bool
     */
    public function send_admin_notification( $booking_id ) {
        $booking = $this->get_booking_details( $booking_id );
        
        if ( ! $booking ) {
            return false;
        }
        
        $to      = get_option( 'admin_email' );
        $subject = sprintf( 'New Booking Received - %s', $booking->booking_number );
        $message = $this->get_admin_notification_body( $booking );
        $headers = $this->get_email_headers();
        
        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Send booking cancellation email.
     *
     * @param int $booking_id Booking ID.
     * @return bool
     */
    public function send_cancellation_email( $booking_id ) {
        $booking = $this->get_booking_details( $booking_id );
        
        if ( ! $booking ) {
            return false;
        }
        
        $to      = $booking->customer_email;
        $subject = sprintf( 'Booking Cancelled - %s', $booking->booking_number );
        $message = $this->get_cancellation_email_body( $booking );
        $headers = $this->get_email_headers();
        
        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Get booking details.
     *
     * @param int $booking_id Booking ID.
     * @return object|null
     */
    private function get_booking_details( $booking_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $booking_id
        ) );
    }

    /**
     * Get confirmation email body.
     *
     * @param object $booking Booking object.
     * @return string
     */
    private function get_confirmation_email_body( $booking ) {
        $date_formatted = date( 'l, F j, Y', strtotime( $booking->booking_date ) );
        $time_formatted = date( 'g:i A', strtotime( $booking->booking_time ) );
        
        $message = "Dear {$booking->customer_name},\n\n";
        $message .= "Your booking has been confirmed!\n\n";
        $message .= "BOOKING DETAILS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Booking Number: {$booking->booking_number}\n";
        $message .= "Service: {$booking->service_type}\n";
        $message .= "Date: {$date_formatted}\n";
        $message .= "Time: {$time_formatted}\n";
        $message .= "Duration: {$booking->duration} minutes\n";
        
        if ( ! empty( $booking->notes ) ) {
            $message .= "Notes: {$booking->notes}\n";
        }
        
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "If you need to cancel or reschedule, please contact us as soon as possible.\n\n";
        $message .= "We look forward to seeing you!\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo( 'name' ) . "\n";
        $message .= get_bloginfo( 'url' );
        
        return $message;
    }

    /**
     * Get admin notification email body.
     *
     * @param object $booking Booking object.
     * @return string
     */
    private function get_admin_notification_body( $booking ) {
        $date_formatted = date( 'l, F j, Y', strtotime( $booking->booking_date ) );
        $time_formatted = date( 'g:i A', strtotime( $booking->booking_time ) );
        
        $message = "New booking received!\n\n";
        $message .= "BOOKING DETAILS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Booking Number: {$booking->booking_number}\n";
        $message .= "Status: {$booking->status}\n\n";
        $message .= "CUSTOMER INFORMATION:\n";
        $message .= "Name: {$booking->customer_name}\n";
        $message .= "Email: {$booking->customer_email}\n";
        $message .= "Phone: {$booking->customer_phone}\n\n";
        $message .= "SERVICE INFORMATION:\n";
        $message .= "Service: {$booking->service_type}\n";
        $message .= "Date: {$date_formatted}\n";
        $message .= "Time: {$time_formatted}\n";
        $message .= "Duration: {$booking->duration} minutes\n";
        
        if ( ! empty( $booking->notes ) ) {
            $message .= "\nCustomer Notes:\n{$booking->notes}\n";
        }
        
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Manage this booking in your WordPress admin:\n";
        $message .= admin_url( 'admin.php?page=wp-swiss-business-suite-bookings' );
        
        return $message;
    }

    /**
     * Get cancellation email body.
     *
     * @param object $booking Booking object.
     * @return string
     */
    private function get_cancellation_email_body( $booking ) {
        $date_formatted = date( 'l, F j, Y', strtotime( $booking->booking_date ) );
        $time_formatted = date( 'g:i A', strtotime( $booking->booking_time ) );
        
        $message = "Dear {$booking->customer_name},\n\n";
        $message .= "Your booking has been cancelled.\n\n";
        $message .= "CANCELLED BOOKING:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Booking Number: {$booking->booking_number}\n";
        $message .= "Service: {$booking->service_type}\n";
        $message .= "Date: {$date_formatted}\n";
        $message .= "Time: {$time_formatted}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "If you would like to book another appointment, please visit:\n";
        $message .= get_bloginfo( 'url' ) . "\n\n";
        $message .= "We hope to serve you in the future!\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo( 'name' );
        
        return $message;
    }

    /**
     * Get email headers.
     *
     * @return array
     */
    private function get_email_headers() {
        $from_name  = get_bloginfo( 'name' );
        $from_email = get_option( 'admin_email' );
        
        return array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        );
    }

    /**
     * Send reminder email before appointment.
     *
     * @param int $booking_id Booking ID.
     * @param int $hours      Hours before appointment.
     * @return bool
     */
    public function send_reminder_email( $booking_id, $hours = 24 ) {
        $booking = $this->get_booking_details( $booking_id );
        
        if ( ! $booking || $booking->status !== 'confirmed' ) {
            return false;
        }
        
        $date_formatted = date( 'l, F j, Y', strtotime( $booking->booking_date ) );
        $time_formatted = date( 'g:i A', strtotime( $booking->booking_time ) );
        
        $to      = $booking->customer_email;
        $subject = 'Appointment Reminder - Tomorrow';
        
        $message = "Dear {$booking->customer_name},\n\n";
        $message .= "This is a friendly reminder about your upcoming appointment.\n\n";
        $message .= "APPOINTMENT DETAILS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Date: {$date_formatted}\n";
        $message .= "Time: {$time_formatted}\n";
        $message .= "Service: {$booking->service_type}\n";
        $message .= "Booking Number: {$booking->booking_number}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "We look forward to seeing you!\n\n";
        $message .= "Best regards,\n";
        $message .= get_bloginfo( 'name' );
        
        $headers = $this->get_email_headers();
        
        return wp_mail( $to, $subject, $message, $headers );
    }
}
