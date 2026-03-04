<?php
/**
 * WP Swiss Business Suite - Public functionality
 * FINAL WORKING VERSION with Google Translate
 * 
 * @package WP_Swiss_Business_Suite
 * @since   1.4.1
 */

class WP_Swiss_Business_Suite_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            WP_SWISS_BUSINESS_SUITE_URL . 'public/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            WP_SWISS_BUSINESS_SUITE_URL . 'public/js/public.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        
        wp_localize_script(
            $this->plugin_name,
            'wpSwissBizSuitePublic',
            array(
                'ajax_url'         => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'wp-swiss-business-suite-public' ),
                'current_language' => $this->get_current_language(),
            )
        );
    }

    private function get_current_language() {
        if ( isset( $_COOKIE['wp_sbs_current_lang'] ) ) {
            return sanitize_text_field( $_COOKIE['wp_sbs_current_lang'] );
        }
        return 'de';
    }

    public function register_shortcodes() {
        add_shortcode( 'wp_sbs_language_switcher', array( $this, 'language_switcher_shortcode' ) );
    }

    public function language_switcher_shortcode() {
        require_once WP_SWISS_BUSINESS_SUITE_PATH . 'includes/multilang/class-language-manager.php';
        $lang_manager = new WP_Swiss_Business_Suite_Language_Manager();
        
        $current_lang = $lang_manager->get_current_language();
        $languages = $lang_manager->get_languages();
        
        if ( empty( $languages ) ) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="wp-sbs-language-switcher wp-sbs-style-flags">
            <ul class="wp-sbs-lang-flags">
                <?php foreach ( $languages as $language ) : 
                    $is_current = ( $language->code === $current_lang );
                    $url = $lang_manager->get_language_url( $language->code );
                ?>
                    <li class="<?php echo $is_current ? 'current-language' : ''; ?>">
                        <a href="<?php echo esc_url( $url ); ?>" 
                           title="<?php echo esc_attr( $language->native_name ); ?>" 
                           data-lang="<?php echo esc_attr( $language->code ); ?>">
                            <span class="flag"><?php echo $language->flag_icon; ?></span>
                            <span class="code"><?php echo strtoupper( $language->code ); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public function add_language_switcher_to_menu( $items, $args ) {
        $enabled = get_option( 'wp_sbs_translation_enabled', '1' );
        if ( $enabled != '1' ) {
            return $items;
        }
        
        $menu_location = get_option( 'wp_sbs_menu_location', 'primary' );
        if ( $menu_location != 'all' && $args->theme_location != $menu_location ) {
            return $items;
        }
        
        $style = get_option( 'wp_sbs_translation_style', 'text' );
        $current_lang = $this->get_current_language();
        
        $languages = array(
            'de' => array( 'name' => 'DE', 'flag' => '🇩🇪', 'full' => 'Deutsch' ),
            'fr' => array( 'name' => 'FR', 'flag' => '🇫🇷', 'full' => 'Français' ),
            'it' => array( 'name' => 'IT', 'flag' => '🇮🇹', 'full' => 'Italiano' ),
            'en' => array( 'name' => 'EN', 'flag' => '🇬🇧', 'full' => 'English' )
        );
        
        $lang_html = '<li class="menu-item wp-sbs-lang-switcher">';
        
        foreach ( $languages as $code => $lang ) {
            $is_current = ( $code === $current_lang );
            $class = $is_current ? 'wp-sbs-lang-current' : 'wp-sbs-lang-link';
            
            $lang_html .= '<a href="#" class="' . $class . '" data-lang="' . $code . '" title="' . $lang['full'] . '">';
            
            if ( $style == 'text' ) {
                $lang_html .= $lang['name'];
            } elseif ( $style == 'text-flags' ) {
                $lang_html .= $lang['flag'] . ' ' . $lang['name'];
            } else {
                $lang_html .= $lang['flag'];
            }
            
            $lang_html .= '</a>';
            
            if ( $code != 'en' ) {
                $lang_html .= '<span class="wp-sbs-lang-sep">|</span>';
            }
        }
        
        $lang_html .= '</li>';
        
        return $items . $lang_html;
    }

    public function enqueue_translation_assets() {
        $enabled = get_option( 'wp_sbs_translation_enabled', '1' );
        if ( $enabled != '1' ) {
            return;
        }
        
        $custom_css = "
        .wp-sbs-lang-switcher {display: inline-flex !important; align-items: center; gap: 8px; margin-left: 20px;}
        .wp-sbs-lang-switcher a {text-decoration: none !important; padding: 5px 8px; transition: all 0.3s ease; font-weight: 500; font-size: 14px;}
        .wp-sbs-lang-current {font-weight: 700 !important; border-bottom: 2px solid currentColor;}
        .wp-sbs-lang-link:hover {opacity: 0.7;}
        .wp-sbs-lang-sep {margin: 0 5px; opacity: 0.5; font-weight: 300;}
        
        /* Hide/minimize Google Translate branding */
        .goog-te-banner-frame.skiptranslate {display: none !important;}
        .goog-te-gadget {font-size: 0 !important;}
        .goog-te-gadget img {display: none !important;}
        .goog-logo-link {display: none !important;}
        body {top: 0 !important;}
        #goog-gt-tt {display: none !important;}
        .goog-te-balloon-frame {display: none !important;}
        
        @media (max-width: 768px) {.wp-sbs-lang-switcher {flex-wrap: wrap; margin: 10px 0;}}
        ";
        wp_add_inline_style( $this->plugin_name, $custom_css );
        
        $translation_js = "
        jQuery(document).ready(function(\$) {
            
            // Add Google Translate element
            if (!$('#google_translate_element').length) {
                \$('body').append('<div id=\"google_translate_element\" style=\"position:fixed;top:-9999px;left:-9999px;\"></div>');
            }
            
            // Initialize Google Translate
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'de',
                    includedLanguages: 'de,fr,it,en',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                }, 'google_translate_element');
            }
            
            // Load Google Translate script
            if (typeof google === 'undefined' || typeof google.translate === 'undefined') {
                window.googleTranslateElementInit = googleTranslateElementInit;
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.getElementsByTagName('head')[0].appendChild(script);
            }
            
            // Get current language from cookie
            var currentLang = getCookie('wp_sbs_current_lang') || 'de';
            
            // Update menu to show current language
            setTimeout(function() {
                \$('.wp-sbs-lang-switcher a[data-lang=\"' + currentLang + '\"]')
                    .removeClass('wp-sbs-lang-link')
                    .addClass('wp-sbs-lang-current');
            }, 100);
            
            // Auto-translate if language is saved
            if (currentLang && currentLang !== 'de') {
                setTimeout(function() {
                    translateToLanguage(currentLang);
                }, 1500);
            }
            
            // Language switcher click handler
            \$('.wp-sbs-lang-switcher a').on('click', function(e) {
                e.preventDefault();
                var targetLang = \$(this).data('lang');
                
                // Save language choice
                document.cookie = 'wp_sbs_current_lang=' + targetLang + '; path=/; max-age=31536000';
                
                // Update active state
                \$('.wp-sbs-lang-switcher a').removeClass('wp-sbs-lang-current').addClass('wp-sbs-lang-link');
                \$(this).removeClass('wp-sbs-lang-link').addClass('wp-sbs-lang-current');
                
                // Translate
                if (targetLang === 'de') {
                    location.reload();
                } else {
                    translateToLanguage(targetLang);
                }
            });
            
            function translateToLanguage(lang) {
                var attempts = 0;
                var maxAttempts = 30;
                
                var checkTranslator = setInterval(function() {
                    attempts++;
                    var selectField = \$('.goog-te-combo');
                    
                    if (selectField.length > 0) {
                        clearInterval(checkTranslator);
                        selectField.val(lang);
                        selectField.trigger('change');
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkTranslator);
                        console.log('Translation widget not ready');
                    }
                }, 200);
            }
            
            function getCookie(name) {
                var nameEQ = name + '=';
                var ca = document.cookie.split(';');
                for(var i=0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
        });
        ";
        wp_add_inline_script( $this->plugin_name, $translation_js );
    }
}
