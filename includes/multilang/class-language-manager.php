<?php
/**
 * Language Manager - Core language handling
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Language_Manager {

    /**
     * Current active language code.
     *
     * @var string
     */
    private $current_language;

    /**
     * Available languages.
     *
     * @var array
     */
    private $languages;

    /**
     * Initialize the language manager.
     */
    public function __construct() {
        $this->current_language = $this->detect_language();
        $this->languages = $this->get_active_languages();
        
        // Set cookie for language persistence
        add_action( 'init', array( $this, 'set_language_cookie' ) );
    }

    /**
     * Detect current language from URL, cookie, or browser.
     *
     * @return string Language code (de, fr, it, en)
     */
    public function detect_language() {
        
        // 1. Check URL parameter
        if ( isset( $_GET['lang'] ) && $this->is_valid_language( $_GET['lang'] ) ) {
            return sanitize_text_field( $_GET['lang'] );
        }
        
        // 2. Check cookie
        if ( isset( $_COOKIE['wp_sbs_language'] ) && $this->is_valid_language( $_COOKIE['wp_sbs_language'] ) ) {
            return sanitize_text_field( $_COOKIE['wp_sbs_language'] );
        }
        
        // 3. Check browser language
        $browser_lang = $this->get_browser_language();
        if ( $browser_lang && $this->is_valid_language( $browser_lang ) ) {
            return $browser_lang;
        }
        
        // 4. Default to German
        return 'de';
    }

    /**
     * Get browser's preferred language.
     *
     * @return string|false
     */
    private function get_browser_language() {
        if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            return false;
        }
        
        $lang = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
        return strtolower( $lang );
    }

    /**
     * Check if language code is valid.
     *
     * @param string $lang Language code.
     * @return bool
     */
    private function is_valid_language( $lang ) {
        $valid_languages = array( 'de', 'fr', 'it', 'en' );
        return in_array( strtolower( $lang ), $valid_languages, true );
    }

    /**
     * Set language cookie.
     */
    public function set_language_cookie() {
        if ( isset( $_GET['lang'] ) && $this->is_valid_language( $_GET['lang'] ) ) {
            $lang = sanitize_text_field( $_GET['lang'] );
            setcookie( 'wp_sbs_language', $lang, time() + ( 86400 * 365 ), '/' ); // 1 year
        }
    }

    /**
     * Get active languages from database.
     *
     * @return array
     */
    public function get_active_languages() {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_languages';
        
        $languages = wp_cache_get( 'active_languages', 'wp_sbs' );
        
        if ( false === $languages ) {
            $languages = $wpdb->get_results(
                "SELECT * FROM $table WHERE is_active = 1 ORDER BY display_order ASC"
            );
            wp_cache_set( 'active_languages', $languages, 'wp_sbs', 3600 );
        }
        
        return $languages;
    }

    /**
     * Get current language code.
     *
     * @return string
     */
    public function get_current_language() {
        return $this->current_language;
    }

    /**
     * Get language by code.
     *
     * @param string $code Language code.
     * @return object|null
     */
    public function get_language( $code ) {
        foreach ( $this->languages as $language ) {
            if ( $language->code === $code ) {
                return $language;
            }
        }
        return null;
    }

    /**
     * Get all active languages.
     *
     * @return array
     */
    public function get_languages() {
        return $this->languages;
    }

    /**
     * Switch to a different language.
     *
     * @param string $lang_code Language code.
     * @return bool
     */
    public function switch_language( $lang_code ) {
        if ( ! $this->is_valid_language( $lang_code ) ) {
            return false;
        }
        
        $this->current_language = $lang_code;
        setcookie( 'wp_sbs_language', $lang_code, time() + ( 86400 * 365 ), '/' );
        
        return true;
    }

    /**
     * Get language URL.
     *
     * @param string $lang_code Language code.
     * @param string $url       Optional URL to append language to.
     * @return string
     */
    public function get_language_url( $lang_code, $url = '' ) {
        if ( empty( $url ) ) {
            $url = home_url( $_SERVER['REQUEST_URI'] );
        }
        
        return add_query_arg( 'lang', $lang_code, $url );
    }

    /**
     * Get translated content for a post.
     *
     * @param int    $post_id      Post ID.
     * @param string $lang_code    Language code.
     * @param string $content_type Type: 'title', 'content', 'excerpt'.
     * @return string|null
     */
    public function get_translation( $post_id, $lang_code, $content_type = 'content' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_translations';
        
        $field_map = array(
            'title'   => 'translated_title',
            'content' => 'translated_content',
            'excerpt' => 'translated_excerpt',
        );
        
        if ( ! isset( $field_map[ $content_type ] ) ) {
            return null;
        }
        
        $field = $field_map[ $content_type ];
        
        $translation = $wpdb->get_var( $wpdb->prepare(
            "SELECT $field FROM $table WHERE post_id = %d AND language_code = %s",
            $post_id,
            $lang_code
        ) );
        
        return $translation;
    }

    /**
     * Save translation for a post.
     *
     * @param int    $post_id   Post ID.
     * @param string $lang_code Language code.
     * @param array  $data      Translation data.
     * @return bool
     */
    public function save_translation( $post_id, $lang_code, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_translations';
        
        $translation_data = array(
            'post_id'            => $post_id,
            'language_code'      => $lang_code,
            'translated_title'   => isset( $data['title'] ) ? $data['title'] : '',
            'translated_content' => isset( $data['content'] ) ? $data['content'] : '',
            'translated_excerpt' => isset( $data['excerpt'] ) ? $data['excerpt'] : '',
        );
        
        // Check if translation exists
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table WHERE post_id = %d AND language_code = %s",
            $post_id,
            $lang_code
        ) );
        
        if ( $exists ) {
            // Update existing translation
            $result = $wpdb->update(
                $table,
                $translation_data,
                array(
                    'post_id'       => $post_id,
                    'language_code' => $lang_code,
                )
            );
        } else {
            // Insert new translation
            $result = $wpdb->insert( $table, $translation_data );
        }
        
        return false !== $result;
    }
}
