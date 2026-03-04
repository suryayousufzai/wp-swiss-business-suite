<?php
/**
 * Language Switcher Widget
 *
 * @package WP_Swiss_Business_Suite
 * @since   1.0.0
 */

class WP_Swiss_Business_Suite_Language_Switcher extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'wp_sbs_language_switcher',
            __( 'WP Swiss Business Suite - Language Switcher', 'wp-swiss-business-suite' ),
            array(
                'description' => __( 'Allow visitors to switch between languages (DE/FR/IT/EN)', 'wp-swiss-business-suite' ),
            )
        );
    }

    /**
     * Register the widget.
     */
    public function register_widget() {
        register_widget( 'WP_Swiss_Business_Suite_Language_Switcher' );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Language', 'wp-swiss-business-suite' );
        $style = ! empty( $instance['style'] ) ? $instance['style'] : 'flags';
        
        echo $args['before_widget'];
        
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        
        $this->render_language_switcher( $style );
        
        echo $args['after_widget'];
    }

    /**
     * Render the language switcher.
     *
     * @param string $style Display style: 'flags', 'dropdown', 'buttons'.
     */
    private function render_language_switcher( $style = 'flags' ) {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-language-manager.php';
        $lang_manager = new WP_Swiss_Business_Suite_Language_Manager();
        
        $current_lang = $lang_manager->get_current_language();
        $languages = $lang_manager->get_languages();
        
        if ( empty( $languages ) ) {
            return;
        }
        
        echo '<div class="wp-sbs-language-switcher wp-sbs-style-' . esc_attr( $style ) . '">';
        
        switch ( $style ) {
            case 'dropdown':
                $this->render_dropdown( $languages, $current_lang, $lang_manager );
                break;
            
            case 'buttons':
                $this->render_buttons( $languages, $current_lang, $lang_manager );
                break;
            
            case 'flags':
            default:
                $this->render_flags( $languages, $current_lang, $lang_manager );
                break;
        }
        
        echo '</div>';
    }

    /**
     * Render flags style switcher.
     *
     * @param array  $languages    Available languages.
     * @param string $current_lang Current language code.
     * @param object $lang_manager Language manager instance.
     */
    private function render_flags( $languages, $current_lang, $lang_manager ) {
        echo '<ul class="wp-sbs-lang-flags">';
        
        foreach ( $languages as $language ) {
            $is_current = ( $language->code === $current_lang );
            $url = $lang_manager->get_language_url( $language->code );
            
            printf(
                '<li class="%s"><a href="%s" title="%s" data-lang="%s"><span class="flag">%s</span><span class="code">%s</span></a></li>',
                $is_current ? 'current-language' : '',
                esc_url( $url ),
                esc_attr( $language->native_name ),
                esc_attr( $language->code ),
                $language->flag_icon,
                strtoupper( $language->code )
            );
        }
        
        echo '</ul>';
    }

    /**
     * Render dropdown style switcher.
     *
     * @param array  $languages    Available languages.
     * @param string $current_lang Current language code.
     * @param object $lang_manager Language manager instance.
     */
    private function render_dropdown( $languages, $current_lang, $lang_manager ) {
        echo '<select class="wp-sbs-lang-dropdown" onchange="window.location.href=this.value;">';
        
        foreach ( $languages as $language ) {
            $is_current = ( $language->code === $current_lang );
            $url = $lang_manager->get_language_url( $language->code );
            
            printf(
                '<option value="%s" %s>%s %s</option>',
                esc_url( $url ),
                selected( $is_current, true, false ),
                $language->flag_icon,
                esc_html( $language->native_name )
            );
        }
        
        echo '</select>';
    }

    /**
     * Render buttons style switcher.
     *
     * @param array  $languages    Available languages.
     * @param string $current_lang Current language code.
     * @param object $lang_manager Language manager instance.
     */
    private function render_buttons( $languages, $current_lang, $lang_manager ) {
        echo '<div class="wp-sbs-lang-buttons">';
        
        foreach ( $languages as $language ) {
            $is_current = ( $language->code === $current_lang );
            $url = $lang_manager->get_language_url( $language->code );
            
            printf(
                '<a href="%s" class="wp-sbs-lang-button %s" data-lang="%s">%s %s</a>',
                esc_url( $url ),
                $is_current ? 'current' : '',
                esc_attr( $language->code ),
                $language->flag_icon,
                esc_html( $language->native_name )
            );
        }
        
        echo '</div>';
    }

    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Language', 'wp-swiss-business-suite' );
        $style = ! empty( $instance['style'] ) ? $instance['style'] : 'flags';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'wp-swiss-business-suite' ); ?>
            </label>
            <input 
                class="widefat" 
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                type="text" 
                value="<?php echo esc_attr( $title ); ?>"
            >
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>">
                <?php esc_html_e( 'Display Style:', 'wp-swiss-business-suite' ); ?>
            </label>
            <select 
                class="widefat" 
                id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>"
            >
                <option value="flags" <?php selected( $style, 'flags' ); ?>><?php esc_html_e( 'Flags', 'wp-swiss-business-suite' ); ?></option>
                <option value="dropdown" <?php selected( $style, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'wp-swiss-business-suite' ); ?></option>
                <option value="buttons" <?php selected( $style, 'buttons' ); ?>><?php esc_html_e( 'Buttons', 'wp-swiss-business-suite' ); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['style'] = ( ! empty( $new_instance['style'] ) ) ? sanitize_text_field( $new_instance['style'] ) : 'flags';
        
        return $instance;
    }
}
