<?php
/**
 * Calendar - Time slot management
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Calendar {

    /**
     * Business hours configuration.
     *
     * @var array
     */
    private $business_hours;

    /**
     * Initialize the calendar.
     */
    public function __construct() {
        // Default business hours: 9 AM - 5 PM
        $this->business_hours = array(
            'start' => '09:00',
            'end'   => '17:00',
        );
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param string $date     Date in Y-m-d format.
     * @param int    $duration Duration in minutes.
     * @return array
     */
    public function get_available_slots( $date, $duration = 60 ) {
        $all_slots      = $this->generate_time_slots( $duration );
        $booked_slots   = $this->get_booked_slots( $date );
        $available_slots = array();
        
        foreach ( $all_slots as $slot ) {
            if ( ! $this->is_slot_booked( $slot, $booked_slots, $duration ) ) {
                $available_slots[] = $slot;
            }
        }
        
        return $available_slots;
    }

    /**
     * Generate all possible time slots for a day.
     *
     * @param int $duration Slot duration in minutes.
     * @return array
     */
    private function generate_time_slots( $duration ) {
        $slots = array();
        $start = strtotime( $this->business_hours['start'] );
        $end   = strtotime( $this->business_hours['end'] );
        
        $current = $start;
        while ( $current + ( $duration * 60 ) <= $end ) {
            $slots[] = date( 'H:i', $current );
            $current += ( $duration * 60 );
        }
        
        return $slots;
    }

    /**
     * Get booked time slots for a specific date.
     *
     * @param string $date Date in Y-m-d format.
     * @return array
     */
    private function get_booked_slots( $date ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        $bookings = $wpdb->get_results( $wpdb->prepare(
            "SELECT booking_time, duration FROM $table 
            WHERE booking_date = %s 
            AND status NOT IN ('cancelled')",
            $date
        ) );
        
        return $bookings;
    }

    /**
     * Check if a time slot is booked.
     *
     * @param string $slot          Time slot to check (H:i format).
     * @param array  $booked_slots  Array of booked slots.
     * @param int    $duration      Duration in minutes.
     * @return bool
     */
    private function is_slot_booked( $slot, $booked_slots, $duration ) {
        $slot_start = strtotime( $slot );
        $slot_end   = $slot_start + ( $duration * 60 );
        
        foreach ( $booked_slots as $booking ) {
            $booking_start = strtotime( $booking->booking_time );
            $booking_end   = $booking_start + ( $booking->duration * 60 );
            
            // Check for overlap
            if ( ( $slot_start >= $booking_start && $slot_start < $booking_end ) ||
                 ( $slot_end > $booking_start && $slot_end <= $booking_end ) ||
                 ( $slot_start <= $booking_start && $slot_end >= $booking_end ) ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get bookings for a specific date range.
     *
     * @param string $start_date Start date (Y-m-d).
     * @param string $end_date   End date (Y-m-d).
     * @return array
     */
    public function get_bookings_in_range( $start_date, $end_date ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_bookings';
        
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table 
            WHERE booking_date BETWEEN %s AND %s 
            ORDER BY booking_date ASC, booking_time ASC",
            $start_date,
            $end_date
        ) );
    }

    /**
     * Get bookings for current month.
     *
     * @return array
     */
    public function get_current_month_bookings() {
        $start_date = date( 'Y-m-01' );
        $end_date   = date( 'Y-m-t' );
        
        return $this->get_bookings_in_range( $start_date, $end_date );
    }

    /**
     * Check if a date is a weekend.
     *
     * @param string $date Date in Y-m-d format.
     * @return bool
     */
    public function is_weekend( $date ) {
        $day_of_week = date( 'N', strtotime( $date ) );
        return $day_of_week >= 6; // Saturday (6) or Sunday (7)
    }

    /**
     * Check if a date is a holiday.
     *
     * @param string $date Date in Y-m-d format.
     * @return bool
     */
    public function is_holiday( $date ) {
        // Swiss national holidays
        $holidays = array(
            date( 'Y' ) . '-01-01', // New Year's Day
            date( 'Y' ) . '-08-01', // Swiss National Day
            date( 'Y' ) . '-12-25', // Christmas Day
            date( 'Y' ) . '-12-26', // Boxing Day
        );
        
        return in_array( $date, $holidays, true );
    }

    /**
     * Check if a date is available for booking.
     *
     * @param string $date Date in Y-m-d format.
     * @return bool
     */
    public function is_date_available( $date ) {
        // Check if date is in the past
        if ( strtotime( $date ) < strtotime( 'today' ) ) {
            return false;
        }
        
        // Check if weekend
        if ( $this->is_weekend( $date ) ) {
            return false;
        }
        
        // Check if holiday
        if ( $this->is_holiday( $date ) ) {
            return false;
        }
        
        return true;
    }
}
