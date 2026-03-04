<?php
/**
 * Payment System Manager
 * Handles Stripe payments for bookings
 */

class WP_Swiss_Business_Suite_Payment_Manager {

    /**
     * Initialize payment system
     */
    public static function init() {
        // Stripe API key (set in settings)
        if ( ! defined( 'WP_SBS_STRIPE_KEY' ) ) {
            define( 'WP_SBS_STRIPE_KEY', get_option( 'wp_sbs_stripe_secret_key', '' ) );
        }
    }
    
    /**
     * Create payment intent for booking
     */
    public static function create_payment_intent( $booking_id, $amount, $currency = 'CHF' ) {
        global $wpdb;
        
        $booking = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbs_bookings WHERE id = %d",
            $booking_id
        ), ARRAY_A );
        
        if ( ! $booking ) {
            return array( 'success' => false, 'error' => 'Booking not found' );
        }
        
        // Stripe API call (simplified structure)
        $stripe_key = get_option( 'wp_sbs_stripe_secret_key' );
        
        if ( empty( $stripe_key ) ) {
            return array( 
                'success' => false, 
                'error' => 'Stripe not configured. Please add your Stripe API key in Settings.' 
            );
        }
        
        // In production, this would call Stripe API
        // For now, return structure showing how it would work
        
        $payment_intent_data = array(
            'id' => 'pi_' . uniqid(),
            'amount' => $amount * 100, // Stripe uses cents
            'currency' => strtolower( $currency ),
            'customer_email' => $booking['customer_email'],
            'metadata' => array(
                'booking_id' => $booking_id,
                'booking_number' => $booking['booking_number'],
            ),
            'status' => 'requires_payment_method'
        );
        
        // Store payment intent ID in booking meta
        self::store_payment_meta( $booking_id, $payment_intent_data );
        
        return array(
            'success' => true,
            'payment_intent' => $payment_intent_data,
            'client_secret' => $payment_intent_data['id'] . '_secret_' . uniqid()
        );
    }
    
    /**
     * Process payment confirmation
     */
    public static function confirm_payment( $booking_id, $payment_intent_id ) {
        global $wpdb;
        
        // Update booking status to paid
        $wpdb->update(
            $wpdb->prefix . 'sbs_bookings',
            array( 'status' => 'confirmed' ),
            array( 'id' => $booking_id )
        );
        
        // Store payment confirmation
        self::store_payment_meta( $booking_id, array(
            'payment_intent_id' => $payment_intent_id,
            'payment_status' => 'succeeded',
            'payment_date' => current_time( 'mysql' )
        ) );
        
        return true;
    }
    
    /**
     * Store payment metadata
     */
    private static function store_payment_meta( $booking_id, $data ) {
        global $wpdb;
        
        foreach ( $data as $key => $value ) {
            $wpdb->replace(
                $wpdb->prefix . 'sbs_booking_meta',
                array(
                    'booking_id' => $booking_id,
                    'meta_key' => 'payment_' . $key,
                    'meta_value' => maybe_serialize( $value )
                )
            );
        }
    }
    
    /**
     * Get payment status for booking
     */
    public static function get_payment_status( $booking_id ) {
        global $wpdb;
        
        $status = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}sbs_booking_meta 
            WHERE booking_id = %d AND meta_key = 'payment_payment_status'",
            $booking_id
        ) );
        
        return $status ? maybe_unserialize( $status ) : 'unpaid';
    }
    
    /**
     * Generate payment link for booking
     */
    public static function generate_payment_link( $booking_id ) {
        $site_url = get_site_url();
        return add_query_arg( array(
            'wp_sbs_payment' => 'true',
            'booking_id' => $booking_id,
            'token' => wp_create_nonce( 'wp_sbs_payment_' . $booking_id )
        ), $site_url );
    }
}
