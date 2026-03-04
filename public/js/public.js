/**
 * Public JavaScript
 * 
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

(function( $ ) {
    'use strict';

    $(function() {
        // Public JavaScript loaded
        console.log('WP Swiss Business Suite Public loaded');
        
        // Handle AJAX Get Available Slots
        window.wpSbsGetAvailableSlots = function(date, duration, callback) {
            $.ajax({
                url: wpSwissBizSuitePublic.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_sbs_get_available_slots',
                    nonce: wpSwissBizSuitePublic.nonce,
                    date: date,
                    duration: duration
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback(response.data.slots);
                    }
                },
                error: function() {
                    console.error('Error fetching available slots');
                }
            });
        };
    });

})( jQuery );
