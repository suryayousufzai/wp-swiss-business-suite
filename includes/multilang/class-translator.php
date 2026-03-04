<?php
/**
 * Content Translator
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Translator {

    /**
     * Language manager instance.
     *
     * @var WP_Swiss_Business_Suite_Language_Manager
     */
    private $language_manager;

    /**
     * Initialize the translator.
     */
    public function __construct() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-language-manager.php';
        $this->language_manager = new WP_Swiss_Business_Suite_Language_Manager();
        
        // Filter post content
        add_filter( 'the_title', array( $this, 'translate_title' ), 10, 2 );
        add_filter( 'the_content', array( $this, 'translate_content' ) );
        add_filter( 'the_excerpt', array( $this, 'translate_excerpt' ) );
        
        // Add meta box to posts/pages
        add_action( 'add_meta_boxes', array( $this, 'add_translation_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_translations' ) );
    }

    /**
     * Translate post title.
     *
     * @param string $title   Post title.
     * @param int    $post_id Post ID.
     * @return string
     */
    public function translate_title( $title, $post_id = null ) {
        if ( ! $post_id || is_admin() ) {
            return $title;
        }
        
        $current_lang = $this->language_manager->get_current_language();
        
        // Return original if current language is default
        if ( $current_lang === 'de' ) {
            return $title;
        }
        
        $translation = $this->language_manager->get_translation( $post_id, $current_lang, 'title' );
        
        return $translation ? $translation : $title;
    }

    /**
     * Translate post content.
     *
     * @param string $content Post content.
     * @return string
     */
    public function translate_content( $content ) {
        global $post;
        
        if ( ! $post || is_admin() ) {
            return $content;
        }
        
        $current_lang = $this->language_manager->get_current_language();
        
        // Return original if current language is default
        if ( $current_lang === 'de' ) {
            return $content;
        }
        
        $translation = $this->language_manager->get_translation( $post->ID, $current_lang, 'content' );
        
        return $translation ? $translation : $content;
    }

    /**
     * Translate post excerpt.
     *
     * @param string $excerpt Post excerpt.
     * @return string
     */
    public function translate_excerpt( $excerpt ) {
        global $post;
        
        if ( ! $post || is_admin() ) {
            return $excerpt;
        }
        
        $current_lang = $this->language_manager->get_current_language();
        
        // Return original if current language is default
        if ( $current_lang === 'de' ) {
            return $excerpt;
        }
        
        $translation = $this->language_manager->get_translation( $post->ID, $current_lang, 'excerpt' );
        
        return $translation ? $translation : $excerpt;
    }

    /**
     * Add translation meta box to posts and pages.
     */
    public function add_translation_meta_box() {
        $post_types = array( 'post', 'page' );
        
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'wp_sbs_translations',
                __( 'Translations', 'wp-swiss-business-suite' ),
                array( $this, 'render_translation_meta_box' ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render translation meta box content.
     *
     * @param WP_Post $post Post object.
     */
    public function render_translation_meta_box( $post ) {
        wp_nonce_field( 'wp_sbs_save_translations', 'wp_sbs_translations_nonce' );
        
        $languages = $this->language_manager->get_languages();
        
        echo '<div class="wp-sbs-translations-wrapper">';
        echo '<p class="description">' . esc_html__( 'Add translations for this content in different languages.', 'wp-swiss-business-suite' ) . '</p>';
        
        foreach ( $languages as $language ) {
            // Skip German (default language)
            if ( $language->code === 'de' ) {
                continue;
            }
            
            $title_translation = $this->language_manager->get_translation( $post->ID, $language->code, 'title' );
            $content_translation = $this->language_manager->get_translation( $post->ID, $language->code, 'content' );
            $excerpt_translation = $this->language_manager->get_translation( $post->ID, $language->code, 'excerpt' );
            
            echo '<div class="wp-sbs-translation-section" style="margin-bottom: 30px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">';
            echo '<h3>' . $language->flag_icon . ' ' . esc_html( $language->native_name ) . ' (' . strtoupper( $language->code ) . ')</h3>';
            
            // Title field
            echo '<p>';
            echo '<label for="wp_sbs_title_' . esc_attr( $language->code ) . '"><strong>' . esc_html__( 'Title:', 'wp-swiss-business-suite' ) . '</strong></label><br>';
            echo '<input type="text" id="wp_sbs_title_' . esc_attr( $language->code ) . '" name="wp_sbs_translations[' . esc_attr( $language->code ) . '][title]" value="' . esc_attr( $title_translation ) . '" class="large-text" />';
            echo '</p>';
            
            // Content field
            echo '<p>';
            echo '<label for="wp_sbs_content_' . esc_attr( $language->code ) . '"><strong>' . esc_html__( 'Content:', 'wp-swiss-business-suite' ) . '</strong></label><br>';
            wp_editor(
                $content_translation,
                'wp_sbs_content_' . $language->code,
                array(
                    'textarea_name' => 'wp_sbs_translations[' . $language->code . '][content]',
                    'media_buttons' => true,
                    'textarea_rows' => 10,
                )
            );
            echo '</p>';
            
            // Excerpt field
            echo '<p>';
            echo '<label for="wp_sbs_excerpt_' . esc_attr( $language->code ) . '"><strong>' . esc_html__( 'Excerpt:', 'wp-swiss-business-suite' ) . '</strong></label><br>';
            echo '<textarea id="wp_sbs_excerpt_' . esc_attr( $language->code ) . '" name="wp_sbs_translations[' . esc_attr( $language->code ) . '][excerpt]" rows="3" class="large-text">' . esc_textarea( $excerpt_translation ) . '</textarea>';
            echo '</p>';
            
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Save translations when post is saved.
     *
     * @param int $post_id Post ID.
     */
    public function save_translations( $post_id ) {
        // Check nonce
        if ( ! isset( $_POST['wp_sbs_translations_nonce'] ) || 
             ! wp_verify_nonce( $_POST['wp_sbs_translations_nonce'], 'wp_sbs_save_translations' ) ) {
            return;
        }
        
        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Save translations
        if ( isset( $_POST['wp_sbs_translations'] ) && is_array( $_POST['wp_sbs_translations'] ) ) {
            foreach ( $_POST['wp_sbs_translations'] as $lang_code => $translation_data ) {
                $this->language_manager->save_translation( $post_id, $lang_code, $translation_data );
            }
        }
    }

    /**
     * Get available languages for shortcode/template use.
     *
     * @return array
     */
    public function get_available_languages() {
        return $this->language_manager->get_languages();
    }

    /**
     * Get current language code for shortcode/template use.
     *
     * @return string
     */
    public function get_current_language() {
        return $this->language_manager->get_current_language();
    }
}
