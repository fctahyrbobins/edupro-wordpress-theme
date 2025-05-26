<?php
if (!defined('ABSPATH')) exit;

/**
 * Moodle Integration Class
 * Handles all Moodle-related functionality and integration
 */
class EduPro_Moodle_Integration {
    private static $instance = null;
    private $moodle_url;
    private $moodle_token;

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
        $this->moodle_url = get_option('edupro_moodle_url');
        $this->moodle_token = get_option('edupro_moodle_token');

        // Add settings page
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));

        // Add Moodle integration to courses
        add_action('add_meta_boxes', array($this, 'add_moodle_course_meta_box'));
        add_action('save_post_course', array($this, 'save_moodle_course_meta'));

        // AJAX handlers for live page builder
        add_action('wp_ajax_get_moodle_content', array($this, 'get_moodle_content'));
        add_action('wp_ajax_save_moodle_content', array($this, 'save_moodle_content'));
    }

    /**
     * Add Moodle settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'edupro-theme-options',
            __('Moodle Integration', 'edupro'),
            __('Moodle Integration', 'edupro'),
            'manage_options',
            'edupro-moodle-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register Moodle settings
     */
    public function register_settings() {
        register_setting('edupro_moodle_settings', 'edupro_moodle_url');
        register_setting('edupro_moodle_settings', 'edupro_moodle_token');
        register_setting('edupro_moodle_settings', 'edupro_moodle_sync_interval');
    }

    /**
     * Render Moodle settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Moodle Integration Settings', 'edupro'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('edupro_moodle_settings');
                do_settings_sections('edupro_moodle_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="edupro_moodle_url"><?php _e('Moodle URL', 'edupro'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="edupro_moodle_url" name="edupro_moodle_url" 
                                   value="<?php echo esc_attr(get_option('edupro_moodle_url')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edupro_moodle_token"><?php _e('API Token', 'edupro'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="edupro_moodle_token" name="edupro_moodle_token" 
                                   value="<?php echo esc_attr(get_option('edupro_moodle_token')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edupro_moodle_sync_interval">
                                <?php _e('Sync Interval (minutes)', 'edupro'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="number" id="edupro_moodle_sync_interval" 
                                   name="edupro_moodle_sync_interval" 
                                   value="<?php echo esc_attr(get_option('edupro_moodle_sync_interval', '30')); ?>" 
                                   min="5" step="5" class="small-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <div class="card">
                <h2><?php _e('Test Connection', 'edupro'); ?></h2>
                <p>
                    <?php _e('Click the button below to test your Moodle connection:', 'edupro'); ?>
                </p>
                <button type="button" class="button button-secondary" id="test-moodle-connection">
                    <?php _e('Test Connection', 'edupro'); ?>
                </button>
                <div id="test-connection-result" style="margin-top: 10px;"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#test-moodle-connection').on('click', function() {
                const button = $(this);
                const result = $('#test-connection-result');
                
                button.prop('disabled', true);
                result.html('<?php _e('Testing connection...', 'edupro'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'test_moodle_connection',
                        nonce: '<?php echo wp_create_nonce('test_moodle_connection'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            result.html('<div class="notice notice-success inline"><p>' + 
                                response.data.message + '</p></div>');
                        } else {
                            result.html('<div class="notice notice-error inline"><p>' + 
                                response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        result.html('<div class="notice notice-error inline"><p><?php 
                            _e('Connection test failed. Please check your settings.', 'edupro'); 
                        ?></p></div>');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Add Moodle course meta box
     */
    public function add_moodle_course_meta_box() {
        add_meta_box(
            'moodle_course_meta',
            __('Moodle Course Settings', 'edupro'),
            array($this, 'render_moodle_course_meta_box'),
            'course',
            'side',
            'default'
        );
    }

    /**
     * Render Moodle course meta box
     */
    public function render_moodle_course_meta_box($post) {
        wp_nonce_field('moodle_course_meta', 'moodle_course_meta_nonce');
        $moodle_course_id = get_post_meta($post->ID, '_moodle_course_id', true);
        $sync_enabled = get_post_meta($post->ID, '_moodle_sync_enabled', true);
        ?>
        <p>
            <label for="moodle_course_id"><?php _e('Moodle Course ID:', 'edupro'); ?></label>
            <input type="number" id="moodle_course_id" name="moodle_course_id" 
                   value="<?php echo esc_attr($moodle_course_id); ?>" class="widefat">
        </p>
        <p>
            <label>
                <input type="checkbox" name="moodle_sync_enabled" value="1" 
                       <?php checked($sync_enabled, '1'); ?>>
                <?php _e('Enable Moodle Sync', 'edupro'); ?>
            </label>
        </p>
        <p class="description">
            <?php _e('Enter the Moodle course ID to link this course with Moodle.', 'edupro'); ?>
        </p>
        <?php
    }

    /**
     * Save Moodle course meta
     */
    public function save_moodle_course_meta($post_id) {
        if (!isset($_POST['moodle_course_meta_nonce']) || 
            !wp_verify_nonce($_POST['moodle_course_meta_nonce'], 'moodle_course_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save Moodle course ID
        if (isset($_POST['moodle_course_id'])) {
            update_post_meta(
                $post_id,
                '_moodle_course_id',
                sanitize_text_field($_POST['moodle_course_id'])
            );
        }

        // Save sync setting
        $sync_enabled = isset($_POST['moodle_sync_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_moodle_sync_enabled', $sync_enabled);
    }

    /**
     * Get Moodle course content via API
     */
    public function get_moodle_content() {
        check_ajax_referer('get_moodle_content', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied.', 'edupro'));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_send_json_error(__('Invalid course ID.', 'edupro'));
        }

        try {
            $content = $this->fetch_moodle_content($course_id);
            wp_send_json_success($content);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Save Moodle course content via API
     */
    public function save_moodle_content() {
        check_ajax_referer('save_moodle_content', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied.', 'edupro'));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $content = isset($_POST['content']) ? $_POST['content'] : '';

        if (!$course_id || !$content) {
            wp_send_json_error(__('Invalid data.', 'edupro'));
        }

        try {
            $result = $this->update_moodle_content($course_id, $content);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Fetch content from Moodle API
     */
    private function fetch_moodle_content($course_id) {
        if (!$this->moodle_url || !$this->moodle_token) {
            throw new Exception(__('Moodle API credentials not configured.', 'edupro'));
        }

        $url = sprintf(
            '%s/webservice/rest/server.php?wstoken=%s&wsfunction=core_course_get_contents&courseid=%d&moodlewsrestformat=json',
            $this->moodle_url,
            $this->moodle_token,
            $course_id
        );

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data->exception)) {
            throw new Exception($data->message);
        }

        return $data;
    }

    /**
     * Update content in Moodle via API
     */
    private function update_moodle_content($course_id, $content) {
        if (!$this->moodle_url || !$this->moodle_token) {
            throw new Exception(__('Moodle API credentials not configured.', 'edupro'));
        }

        $url = sprintf(
            '%s/webservice/rest/server.php?wstoken=%s&wsfunction=core_course_update_courses&moodlewsrestformat=json',
            $this->moodle_url,
            $this->moodle_token
        );

        $body = array(
            'courses' => array(
                array(
                    'id' => $course_id,
                    'summary' => $content
                )
            )
        );

        $response = wp_remote_post($url, array(
            'body' => $body,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data->exception)) {
            throw new Exception($data->message);
        }

        return $data;
    }

    /**
     * Test Moodle connection
     */
    public function test_connection() {
        check_ajax_referer('test_moodle_connection', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Permission denied.', 'edupro')
            ));
        }

        try {
            // Try to fetch site info as a connection test
            $url = sprintf(
                '%s/webservice/rest/server.php?wstoken=%s&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json',
                $this->moodle_url,
                $this->moodle_token
            );

            $response = wp_remote_get($url);

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if (isset($data->exception)) {
                throw new Exception($data->message);
            }

            wp_send_json_success(array(
                'message' => sprintf(
                    __('Successfully connected to Moodle site: %s', 'edupro'),
                    $data->sitename
                )
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
}

// Initialize Moodle integration
EduPro_Moodle_Integration::get_instance();
