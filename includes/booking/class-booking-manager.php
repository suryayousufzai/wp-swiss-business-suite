<?php
/**
 * Booking Manager - Core booking functionality
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Booking_Manager {

    /**
     * Initialize the booking manager.
     */
    public function __construct() {
        // Register shortcodes
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_wp_sbs_submit_booking', array( $this, 'handle_booking_submission' ) );
        add_action( 'wp_ajax_nopriv_wp_sbs_submit_booking', array( $this, 'handle_booking_submission' ) );
        add_action( 'wp_ajax_wp_sbs_get_available_slots', array( $this, 'get_available_time_slots' ) );
        add_action( 'wp_ajax_nopriv_wp_sbs_get_available_slots', array( $this, 'get_available_time_slots' ) );
    }

    /**
     * Register booking shortcodes.
     */
    public function register_shortcodes() {
        add_shortcode( 'wp_sbs_booking', array( $this, 'render_booking_form' ) );
    }

    /**
     * Render booking form shortcode.
     *
     * @return string
     */
    public function render_booking_form() {
        ob_start();
        ?>
        <div class="wp-sbs-booking-form">
            <h2><?php esc_html_e( 'Book an Appointment', 'wp-swiss-business-suite' ); ?></h2>
            
            <form id="wp-sbs-booking-form" method="post">
                <div class="form-group">
                    <label for="customer_name"><?php esc_html_e( 'Name *', 'wp-swiss-business-suite' ); ?></label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_email"><?php esc_html_e( 'Email *', 'wp-swiss-business-suite' ); ?></label>
                    <input type="email" id="customer_email" name="customer_email" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_phone"><?php esc_html_e( 'Phone', 'wp-swiss-business-suite' ); ?></label>
                    <input type="tel" id="customer_phone" name="customer_phone">
                </div>
                
                <div class="form-group">
                    <label for="service_type"><?php esc_html_e( 'Service *', 'wp-swiss-business-suite' ); ?></label>
                    <select id="service_type" name="service_type" required>
                        <option value=""><?php esc_html_e( 'Select a service', 'wp-swiss-business-suite' ); ?></option>
                        <?php
                        $services = $this->get_available_services();
                        foreach ( $services as $service ) {
                            printf(
                                '<option value="%s" data-duration="%d">%s (CHF %s)</option>',
                                esc_attr( $service->service_name ),
                                esc_attr( $service->duration ),
                                esc_html( $service->service_name ),
                                esc_html( number_format( $service->price, 2 ) )
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="booking_date"><?php esc_html_e( 'Date *', 'wp-swiss-business-suite' ); ?></label>
                    <input type="date" id="booking_date" name="booking_date" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="booking_time"><?php esc_html_e( 'Time *', 'wp-swiss-business-suite' ); ?></label>
                    <select id="booking_time" name="booking_time" required>
                        <option value=""><?php esc_html_e( 'Select a date first', 'wp-swiss-business-suite' ); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes"><?php esc_html_e( 'Notes', 'wp-swiss-business-suite' ); ?></label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="wp-sbs-submit-btn">
                    <?php esc_html_e( 'Book Appointment', 'wp-swiss-business-suite' ); ?>
                </button>
                
                <div class="wp-sbs-message" style="display:none;"></div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Update time slots when date changes
            $('#booking_date').on('change', function() {
                var date = $(this).val();
                var duration = $('#service_type option:selected').data('duration') || 60;
                
                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: {
                        action: 'wp_sbs_get_available_slots',
                        nonce: '<?php echo wp_create_nonce( 'wp_sbs_booking_nonce' ); ?>',
                        date: date,
                        duration: duration
                    },
                    success: function(response) {
                        if (response.success) {
                            var $timeSelect = $('#booking_time');
                            $timeSelect.html('<option value="">Select a time</option>');
                            
                            $.each(response.data.slots, function(i, slot) {
                                $timeSelect.append('<option value="' + slot + '">' + slot + '</option>');
                            });
                        }
                    }
                });
            });
            
            // Handle form submission
            $('#wp-sbs-booking-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var $message = $form.find('.wp-sbs-message');
                
                $btn.prop('disabled', true).text('Booking...');
                $message.hide();
                
                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: $form.serialize() + '&action=wp_sbs_submit_booking&nonce=<?php echo wp_create_nonce( 'wp_sbs_booking_nonce' ); ?>',
                    success: function(response) {
                        if (response.success) {
                            $message.removeClass('error').addClass('success')
                                .html(response.data.message + '<br>Booking Number: <strong>' + response.data.booking_number + '</strong>')
                                .slideDown();
                            $form[0].reset();
                        } else {
                            $message.removeClass('success').addClass('error')
                                .text(response.data.message)
                                .slideDown();
                        }
                        $btn.prop('disabled', false).text('Book Appointment');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Create a new booking.
     *
     * @param array $booking_data Booking data.
     * @return int|false Booking ID or false on failure.
     */
    public function create_booking( $booking_data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        // Generate booking number
        $booking_number = 'WP-SBS-' . date( 'Ymd' ) . '-' . strtoupper( substr( md5( uniqid( mt_rand(), true ) ), 0, 6 ) );
        
        $data = array(
            'booking_number' => $booking_number,
            'customer_name'  => sanitize_text_field( $booking_data['customer_name'] ),
            'customer_email' => sanitize_email( $booking_data['customer_email'] ),
            'customer_phone' => sanitize_text_field( $booking_data['customer_phone'] ),
            'service_type'   => sanitize_text_field( $booking_data['service_type'] ),
            'booking_date'   => sanitize_text_field( $booking_data['booking_date'] ),
            'booking_time'   => sanitize_text_field( $booking_data['booking_time'] ),
            'duration'       => absint( $booking_data['duration'] ),
            'language'       => 'de',
            'status'         => 'pending',
            'notes'          => sanitize_textarea_field( $booking_data['notes'] ),
        );
        
        $result = $wpdb->insert( $table, $data );
        
        if ( $result ) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Get booking by ID.
     *
     * @param int $booking_id Booking ID.
     * @return object|null
     */
    public function get_booking( $booking_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $booking_id
        ) );
    }

    /**
     * Check if time slot is available.
     *
     * @param string $date     Date in Y-m-d format.
     * @param string $time     Time in H:i:s format.
     * @param int    $duration Duration in minutes.
     * @return bool
     */
    public function is_time_slot_available( $date, $time, $duration ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        $start_datetime = strtotime( $date . ' ' . $time );
        $end_datetime   = $start_datetime + ( $duration * 60 );
        $end_time       = date( 'H:i:s', $end_datetime );
        
        $overlapping = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
            WHERE booking_date = %s 
            AND status NOT IN ('cancelled')
            AND (
                (booking_time <= %s AND TIME_ADD(booking_time, INTERVAL duration MINUTE) > %s)
                OR (booking_time < %s AND TIME_ADD(booking_time, INTERVAL duration MINUTE) >= %s)
                OR (booking_time >= %s AND booking_time < %s)
            )",
            $date, $time, $time, $end_time, $end_time, $time, $end_time
        ) );
        
        return $overlapping == 0;
    }

    /**
     * Get available services.
     *
     * @return array
     */
    public function get_available_services() {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_services';
        
        return $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY service_name ASC"
        );
    }

    /**
     * Handle booking submission via AJAX.
     */
    public function handle_booking_submission() {
        check_ajax_referer( 'wp_sbs_booking_nonce', 'nonce' );
        
        $required_fields = array( 'customer_name', 'customer_email', 'booking_date', 'booking_time', 'service_type' );
        foreach ( $required_fields as $field ) {
            if ( empty( $_POST[ $field ] ) ) {
                wp_send_json_error( array( 'message' => 'Please fill in all required fields.' ) );
            }
        }
        
        if ( ! is_email( $_POST['customer_email'] ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }
        
        $duration = 60;
        if ( ! $this->is_time_slot_available( $_POST['booking_date'], $_POST['booking_time'], $duration ) ) {
            wp_send_json_error( array( 'message' => 'This time slot is no longer available.' ) );
        }
        
        $booking_data = array(
            'customer_name'  => $_POST['customer_name'],
            'customer_email' => $_POST['customer_email'],
            'customer_phone' => isset( $_POST['customer_phone'] ) ? $_POST['customer_phone'] : '',
            'service_type'   => $_POST['service_type'],
            'booking_date'   => $_POST['booking_date'],
            'booking_time'   => $_POST['booking_time'],
            'duration'       => $duration,
            'notes'          => isset( $_POST['notes'] ) ? $_POST['notes'] : '',
        );
        
        $booking_id = $this->create_booking( $booking_data );
        
        if ( $booking_id ) {
            $booking = $this->get_booking( $booking_id );
            
            // Send automatic confirmation email to customer
            $subject = 'Booking Confirmation - ' . $booking->booking_number;
            $message = "Dear " . $booking->customer_name . ",\n\n";
            $message .= "Thank you for your booking! We have received your request.\n\n";
            $message .= "BOOKING DETAILS:\n";
            $message .= "Booking Number: " . $booking->booking_number . "\n";
            $message .= "Service: " . $booking->service_type . "\n";
            $message .= "Date: " . date('d.m.Y', strtotime($booking->booking_date)) . "\n";
            $message .= "Time: " . date('H:i', strtotime($booking->booking_time)) . "\n";
            $message .= "Duration: " . $booking->duration . " minutes\n";
            $message .= "Status: " . strtoupper($booking->status) . "\n\n";
            $message .= "We will review your booking and send you a confirmation soon.\n\n";
            $message .= "If you have any questions, please contact us.\n\n";
            $message .= "Best regards,\n";
            $message .= get_bloginfo('name');
            
            wp_mail( $booking->customer_email, $subject, $message );
            
            wp_send_json_success( array(
                'message'        => 'Booking created successfully! Confirmation email sent.',
                'booking_number' => $booking->booking_number,
                'booking_id'     => $booking_id,
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to create booking. Please try again.' ) );
        }
    }

    /**
     * Get available time slots via AJAX.
     */
    public function get_available_time_slots() {
        check_ajax_referer( 'wp_sbs_booking_nonce', 'nonce' );
        
        if ( empty( $_POST['date'] ) ) {
            wp_send_json_error( array( 'message' => 'Please provide a date.' ) );
        }
        
        $date     = sanitize_text_field( $_POST['date'] );
        $duration = isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : 60;
        
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-calendar.php';
        $calendar = new WP_Swiss_Business_Suite_Calendar();
        
        $available_slots = $calendar->get_available_slots( $date, $duration );
        
        wp_send_json_success( array(
            'slots' => $available_slots,
        ) );
    }
}
