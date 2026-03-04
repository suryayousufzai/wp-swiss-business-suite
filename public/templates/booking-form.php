<?php
/**
 * Booking Form Template
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get services
require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-booking-manager.php';
$booking_manager = new WP_Swiss_Business_Suite_Booking_Manager();
$services = $booking_manager->get_services();

// Get calendar
require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/booking/class-calendar.php';
$calendar = new WP_Swiss_Business_Suite_Calendar();
$next_available = $calendar->get_next_available_date();
?>

<div class="wp-sbs-booking-form-wrapper">
    <form id="wp-sbs-booking-form" class="wp-sbs-booking-form" method="post">
        
        <h2><?php echo esc_html( $atts['title'] ); ?></h2>
        
        <div id="wp-sbs-booking-message"></div>
        
        <!-- Personal Information -->
        <div class="form-section">
            <h3><?php _e( 'Personal Information', 'wp-swiss-business-suite' ); ?></h3>
            
            <div class="form-group">
                <label for="customer_name">
                    <?php _e( 'Full Name', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="customer_name" 
                    name="customer_name" 
                    required 
                    placeholder="<?php esc_attr_e( 'Enter your full name', 'wp-swiss-business-suite' ); ?>"
                />
            </div>
            
            <div class="form-group">
                <label for="customer_email">
                    <?php _e( 'Email Address', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <input 
                    type="email" 
                    id="customer_email" 
                    name="customer_email" 
                    required 
                    placeholder="<?php esc_attr_e( 'your@email.com', 'wp-swiss-business-suite' ); ?>"
                />
            </div>
            
            <div class="form-group">
                <label for="customer_phone">
                    <?php _e( 'Phone Number', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <input 
                    type="tel" 
                    id="customer_phone" 
                    name="customer_phone" 
                    required 
                    placeholder="<?php esc_attr_e( '+41...', 'wp-swiss-business-suite' ); ?>"
                />
            </div>
        </div>
        
        <!-- Service Selection -->
        <div class="form-section">
            <h3><?php _e( 'Service Selection', 'wp-swiss-business-suite' ); ?></h3>
            
            <div class="form-group">
                <label for="service_type">
                    <?php _e( 'Select Service', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <select id="service_type" name="service_type" required>
                    <option value=""><?php _e( '-- Select a Service --', 'wp-swiss-business-suite' ); ?></option>
                    <?php foreach ( $services as $service ) : ?>
                        <option value="<?php echo esc_attr( $service->service_name ); ?>">
                            <?php echo esc_html( $service->service_name ); ?> 
                            (<?php echo esc_html( $service->duration ); ?> <?php _e( 'min', 'wp-swiss-business-suite' ); ?> - 
                            <?php echo esc_html( $service->currency . ' ' . number_format( $service->price, 2 ) ); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Date & Time Selection -->
        <div class="form-section">
            <h3><?php _e( 'Date & Time', 'wp-swiss-business-suite' ); ?></h3>
            
            <div class="form-group">
                <label for="booking_date">
                    <?php _e( 'Select Date', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <input 
                    type="date" 
                    id="booking_date" 
                    name="booking_date" 
                    required 
                    min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"
                    value="<?php echo esc_attr( $next_available ); ?>"
                />
                <small class="form-hint">
                    <?php _e( 'Select a date to see available time slots', 'wp-swiss-business-suite' ); ?>
                </small>
            </div>
            
            <div class="form-group">
                <label for="booking_time">
                    <?php _e( 'Select Time', 'wp-swiss-business-suite' ); ?> <span class="required">*</span>
                </label>
                <select id="booking_time" name="booking_time" required disabled>
                    <option value=""><?php _e( 'First select a date...', 'wp-swiss-business-suite' ); ?></option>
                </select>
                <div id="time-slots-loading" class="loading-spinner" style="display: none;">
                    <?php _e( 'Loading available times...', 'wp-swiss-business-suite' ); ?>
                </div>
            </div>
        </div>
        
        <!-- Additional Notes -->
        <div class="form-section">
            <h3><?php _e( 'Additional Information', 'wp-swiss-business-suite' ); ?></h3>
            
            <div class="form-group">
                <label for="notes">
                    <?php _e( 'Notes (Optional)', 'wp-swiss-business-suite' ); ?>
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="4" 
                    placeholder="<?php esc_attr_e( 'Any special requests or notes...', 'wp-swiss-business-suite' ); ?>"
                ></textarea>
            </div>
        </div>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="language" value="<?php echo esc_attr( $current_lang ); ?>" />
        <input type="hidden" name="action" value="submit_booking" />
        <?php wp_nonce_field( 'wp_sbs_booking_nonce', 'nonce' ); ?>
        
        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" id="submit-booking" class="wp-sbs-submit-button">
                <span class="button-text"><?php _e( 'Confirm Booking', 'wp-swiss-business-suite' ); ?></span>
                <span class="button-loading" style="display: none;">
                    <?php _e( 'Processing...', 'wp-swiss-business-suite' ); ?>
                </span>
            </button>
        </div>
        
        <p class="form-note">
            <small>
                <span class="required">*</span> <?php _e( 'Required fields', 'wp-swiss-business-suite' ); ?>
            </small>
        </p>
    </form>
</div>

<style>
.required {
    color: #d63638;
    font-weight: bold;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    margin-top: 0;
    color: #0073aa;
    font-size: 18px;
}

.form-hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-style: italic;
}

.loading-spinner {
    padding: 10px;
    color: #0073aa;
    font-style: italic;
}

.wp-sbs-submit-button {
    position: relative;
}

.wp-sbs-submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#wp-sbs-booking-message {
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 4px;
    display: none;
}

#wp-sbs-booking-message.success {
    display: block;
    background: #00a32a;
    color: white;
}

#wp-sbs-booking-message.error {
    display: block;
    background: #d63638;
    color: white;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load available times when date changes
    $('#booking_date').on('change', function() {
        const selectedDate = $(this).val();
        const $timeSelect = $('#booking_time');
        const $loading = $('#time-slots-loading');
        
        if (!selectedDate) return;
        
        // Show loading
        $timeSelect.prop('disabled', true).html('<option value="">Loading...</option>');
        $loading.show();
        
        // Fetch available times
        $.ajax({
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            type: 'POST',
            data: {
                action: 'get_available_times',
                nonce: '<?php echo wp_create_nonce( 'wp_sbs_booking_nonce' ); ?>',
                date: selectedDate
            },
            success: function(response) {
                $loading.hide();
                
                if (response.success && response.data.times.length > 0) {
                    let options = '<option value="">-- Select a time --</option>';
                    response.data.times.forEach(function(slot) {
                        options += '<option value="' + slot.time + '">' + slot.display + '</option>';
                    });
                    $timeSelect.html(options).prop('disabled', false);
                } else {
                    $timeSelect.html('<option value="">No times available</option>');
                }
            },
            error: function() {
                $loading.hide();
                $timeSelect.html('<option value="">Error loading times</option>');
            }
        });
    });
    
    // Handle form submission
    $('#wp-sbs-booking-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $('#submit-booking');
        const $message = $('#wp-sbs-booking-message');
        
        // Disable submit button
        $submitBtn.prop('disabled', true);
        $submitBtn.find('.button-text').hide();
        $submitBtn.find('.button-loading').show();
        $message.hide().removeClass('success error');
        
        // Submit booking
        $.ajax({
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html(response.data.message).show();
                    $form[0].reset();
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $message.offset().top - 100
                    }, 500);
                } else {
                    $message.addClass('error').html(response.data.message).show();
                }
            },
            error: function() {
                $message.addClass('error').html('<?php _e( 'An error occurred. Please try again.', 'wp-swiss-business-suite' ); ?>').show();
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.button-text').show();
                $submitBtn.find('.button-loading').hide();
            }
        });
    });
});
</script>
