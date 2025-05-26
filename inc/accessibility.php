<?php
if (!defined('ABSPATH')) exit;

/**
 * Accessibility Class
 * Handles all accessibility-related functionality
 */
class EduPro_Accessibility {
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add accessibility settings to theme options
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add accessibility scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add accessibility toolbar
        add_action('wp_footer', array($this, 'render_accessibility_toolbar'));

        // Add ARIA roles to navigation
        add_filter('wp_nav_menu_args', array($this, 'add_nav_aria'));

        // Add skip links
        add_action('wp_body_open', array($this, 'add_skip_links'));
    }

    /**
     * Register accessibility settings
     */
    public function register_settings() {
        register_setting('edupro_accessibility_settings', 'edupro_enable_high_contrast');
        register_setting('edupro_accessibility_settings', 'edupro_enable_font_resize');
        register_setting('edupro_accessibility_settings', 'edupro_enable_dyslexic_font');
        register_setting('edupro_accessibility_settings', 'edupro_enable_screen_reader');
    }

    /**
     * Enqueue accessibility scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'edupro-accessibility',
            get_template_directory_uri() . '/assets/js/accessibility.js',
            array('jquery'),
            '1.0',
            true
        );

        // Localize script
        wp_localize_script('edupro-accessibility', 'eduproAccessibility', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('edupro_accessibility'),
            'highContrast' => __('High Contrast', 'edupro'),
            'normalContrast' => __('Normal Contrast', 'edupro'),
            'increaseFontSize' => __('Increase Font Size', 'edupro'),
            'decreaseFontSize' => __('Decrease Font Size', 'edupro'),
            'resetFontSize' => __('Reset Font Size', 'edupro')
        ));

        // Add OpenDyslexic font if enabled
        if (get_option('edupro_enable_dyslexic_font', false)) {
            wp_enqueue_style(
                'opendyslexic',
                'https://fonts.cdnfonts.com/css/opendyslexic',
                array(),
                '1.0'
            );
        }
    }

    /**
     * Render accessibility toolbar
     */
    public function render_accessibility_toolbar() {
        ?>
        <div id="accessibility-toolbar" class="fixed bottom-4 right-4 z-50 bg-white rounded-lg shadow-lg p-4" 
             role="region" aria-label="<?php _e('Accessibility Tools', 'edupro'); ?>">
            
            <!-- High Contrast Toggle -->
            <?php if (get_option('edupro_enable_high_contrast', true)) : ?>
                <button id="high-contrast-toggle" 
                        class="w-full mb-2 px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center"
                        aria-pressed="false">
                    <i class="fas fa-adjust mr-2"></i>
                    <span class="contrast-text">
                        <?php _e('High Contrast', 'edupro'); ?>
                    </span>
                </button>
            <?php endif; ?>

            <!-- Font Size Controls -->
            <?php if (get_option('edupro_enable_font_resize', true)) : ?>
                <div class="mb-2 space-y-2">
                    <button id="increase-font" 
                            class="w-full px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        <?php _e('Increase Text', 'edupro'); ?>
                    </button>
                    <button id="decrease-font" 
                            class="w-full px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-minus mr-2"></i>
                        <?php _e('Decrease Text', 'edupro'); ?>
                    </button>
                    <button id="reset-font" 
                            class="w-full px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-undo mr-2"></i>
                        <?php _e('Reset Text Size', 'edupro'); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Dyslexic Font Toggle -->
            <?php if (get_option('edupro_enable_dyslexic_font', true)) : ?>
                <button id="dyslexic-font-toggle" 
                        class="w-full mb-2 px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center"
                        aria-pressed="false">
                    <i class="fas fa-font mr-2"></i>
                    <?php _e('Dyslexic Font', 'edupro'); ?>
                </button>
            <?php endif; ?>

            <!-- Screen Reader Guidance -->
            <?php if (get_option('edupro_enable_screen_reader', true)) : ?>
                <button id="screen-reader-guidance" 
                        class="w-full px-4 py-2 text-left hover:bg-gray-100 rounded flex items-center"
                        aria-expanded="false">
                    <i class="fas fa-audio-description mr-2"></i>
                    <?php _e('Screen Reader Help', 'edupro'); ?>
                </button>
                <div id="screen-reader-info" class="hidden mt-2 p-4 bg-gray-100 rounded text-sm">
                    <h3 class="font-bold mb-2"><?php _e('Keyboard Shortcuts:', 'edupro'); ?></h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li><?php _e('Tab: Navigate through elements', 'edupro'); ?></li>
                        <li><?php _e('Enter/Space: Activate element', 'edupro'); ?></li>
                        <li><?php _e('H: Jump to headings', 'edupro'); ?></li>
                        <li><?php _e('M: Jump to main content', 'edupro'); ?></li>
                        <li><?php _e('N: Jump to navigation', 'edupro'); ?></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <style>
            /* High Contrast Mode Styles */
            body.high-contrast {
                background: #000 !important;
                color: #fff !important;
            }

            body.high-contrast a {
                color: #ffff00 !important;
            }

            body.high-contrast button,
            body.high-contrast input,
            body.high-contrast select,
            body.high-contrast textarea {
                background: #000 !important;
                color: #fff !important;
                border: 1px solid #fff !important;
            }

            /* Dyslexic Font */
            body.dyslexic-font,
            body.dyslexic-font * {
                font-family: 'OpenDyslexic', sans-serif !important;
            }

            /* Focus Styles */
            *:focus {
                outline: 3px solid #007bff !important;
                outline-offset: 2px !important;
            }

            /* Skip Links */
            .skip-link {
                position: absolute;
                top: -100px;
                left: 0;
                background: #007bff;
                color: white;
                padding: 8px;
                z-index: 100;
            }

            .skip-link:focus {
                top: 0;
            }
        </style>
        <?php
    }

    /**
     * Add ARIA roles to navigation
     */
    public function add_nav_aria($args) {
        if (isset($args['container'])) {
            $args['container_aria_label'] = __('Main navigation', 'edupro');
        }
        return $args;
    }

    /**
     * Add skip links
     */
    public function add_skip_links() {
        ?>
        <a class="skip-link" href="#content">
            <?php _e('Skip to main content', 'edupro'); ?>
        </a>
        <a class="skip-link" href="#primary-menu">
            <?php _e('Skip to primary navigation', 'edupro'); ?>
        </a>
        <?php
    }

    /**
     * Save accessibility preferences via AJAX
     */
    public function save_preferences() {
        check_ajax_referer('edupro_accessibility', 'nonce');

        $preferences = array(
            'highContrast' => isset($_POST['highContrast']) ? (bool) $_POST['highContrast'] : false,
            'fontSize' => isset($_POST['fontSize']) ? intval($_POST['fontSize']) : 16,
            'dyslexicFont' => isset($_POST['dyslexicFont']) ? (bool) $_POST['dyslexicFont'] : false
        );

        // Store in user meta if logged in, otherwise in session
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'accessibility_preferences', $preferences);
        } else {
            if (!session_id()) {
                session_start();
            }
            $_SESSION['accessibility_preferences'] = $preferences;
        }

        wp_send_json_success();
    }

    /**
     * Get accessibility preferences
     */
    public function get_preferences() {
        $defaults = array(
            'highContrast' => false,
            'fontSize' => 16,
            'dyslexicFont' => false
        );

        if (is_user_logged_in()) {
            $preferences = get_user_meta(get_current_user_id(), 'accessibility_preferences', true);
        } else {
            if (!session_id()) {
                session_start();
            }
            $preferences = isset($_SESSION['accessibility_preferences']) ? $_SESSION['accessibility_preferences'] : array();
        }

        return wp_parse_args($preferences, $defaults);
    }
}

// Initialize accessibility features
EduPro_Accessibility::get_instance();

// Add AJAX handlers
add_action('wp_ajax_save_accessibility_preferences', array(EduPro_Accessibility::get_instance(), 'save_preferences'));
add_action('wp_ajax_nopriv_save_accessibility_preferences', array(EduPro_Accessibility::get_instance(), 'save_preferences'));
