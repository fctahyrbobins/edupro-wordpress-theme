<?php
/**
 * Page Builder Screenshot Generator
 * 
 * This file handles generating screenshots of Moodle pages for the live page builder preview.
 */

if (!defined('ABSPATH')) exit;

class EduPro_Screenshot_Generator {
    private static $instance = null;
    private $screenshot_dir;
    private $screenshot_url;

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
        // Set up screenshot directory and URL
        $upload_dir = wp_upload_dir();
        $this->screenshot_dir = $upload_dir['basedir'] . '/page-builder-screenshots';
        $this->screenshot_url = $upload_dir['baseurl'] . '/page-builder-screenshots';

        // Create screenshot directory if it doesn't exist
        if (!file_exists($this->screenshot_dir)) {
            wp_mkdir_p($this->screenshot_dir);
        }

        // Add AJAX handlers
        add_action('wp_ajax_generate_page_screenshot', array($this, 'generate_screenshot'));
        add_action('wp_ajax_delete_page_screenshot', array($this, 'delete_screenshot'));
    }

    /**
     * Generate screenshot via Puppeteer
     */
    public function generate_screenshot() {
        check_ajax_referer('page_builder_screenshot', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'edupro')));
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $html_content = isset($_POST['html_content']) ? $_POST['html_content'] : '';

        if (!$page_id || !$html_content) {
            wp_send_json_error(array('message' => __('Invalid data.', 'edupro')));
        }

        try {
            // Create temporary HTML file
            $temp_file = $this->screenshot_dir . "/temp-{$page_id}.html";
            file_put_contents($temp_file, $html_content);

            // Generate screenshot using Puppeteer
            $screenshot_path = $this->screenshot_dir . "/page-{$page_id}.png";
            $command = sprintf(
                'node %s/assets/js/puppeteer-screenshot.js %s %s',
                get_template_directory(),
                escapeshellarg("file://{$temp_file}"),
                escapeshellarg($screenshot_path)
            );

            exec($command, $output, $return_var);

            // Clean up temporary file
            unlink($temp_file);

            if ($return_var !== 0) {
                throw new Exception(__('Failed to generate screenshot.', 'edupro'));
            }

            // Return screenshot URL
            wp_send_json_success(array(
                'screenshot_url' => $this->screenshot_url . "/page-{$page_id}.png"
            ));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Delete screenshot
     */
    public function delete_screenshot() {
        check_ajax_referer('page_builder_screenshot', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'edupro')));
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;

        if (!$page_id) {
            wp_send_json_error(array('message' => __('Invalid page ID.', 'edupro')));
        }

        $screenshot_path = $this->screenshot_dir . "/page-{$page_id}.png";

        if (file_exists($screenshot_path)) {
            unlink($screenshot_path);
        }

        wp_send_json_success();
    }

    /**
     * Get screenshot URL for a page
     */
    public function get_screenshot_url($page_id) {
        $screenshot_path = $this->screenshot_dir . "/page-{$page_id}.png";
        
        if (file_exists($screenshot_path)) {
            return $this->screenshot_url . "/page-{$page_id}.png";
        }

        return '';
    }

    /**
     * Clean up old screenshots
     */
    public function cleanup_screenshots() {
        $files = glob($this->screenshot_dir . "/*.png");
        $now = time();

        foreach ($files as $file) {
            // Delete files older than 24 hours
            if ($now - filemtime($file) > 86400) {
                unlink($file);
            }
        }
    }
}

// Initialize screenshot generator
EduPro_Screenshot_Generator::get_instance();

// Add cleanup schedule
if (!wp_next_scheduled('cleanup_page_screenshots')) {
    wp_schedule_event(time(), 'daily', 'cleanup_page_screenshots');
}
add_action('cleanup_page_screenshots', array(EduPro_Screenshot_Generator::get_instance(), 'cleanup_screenshots'));
