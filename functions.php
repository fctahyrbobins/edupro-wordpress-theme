<?php
if (!defined('ABSPATH')) exit;

// Theme Setup
function edupro_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'edupro'),
        'quick-links' => __('Quick Links Menu', 'edupro'),
        'mega-menu' => __('Mega Menu', 'edupro')
    ));

    // Load translations
    load_theme_textdomain('edupro', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'edupro_setup');

// Enqueue scripts and styles
function edupro_scripts() {
    // Tailwind CSS
    wp_enqueue_style('tailwindcss', 'https://cdn.tailwindcss.com');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    
    // Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    
    // Theme stylesheet
    wp_enqueue_style('edupro-style', get_stylesheet_uri());
    
    // Theme JavaScript
    wp_enqueue_script('edupro-scripts', get_template_directory_uri() . '/assets/js/scripts.js', array('jquery'), '1.0', true);

    // Localize script for AJAX
    wp_localize_script('edupro-scripts', 'eduproAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('edupro-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'edupro_scripts');

// Register Custom Post Type for Courses
function edupro_register_course_post_type() {
    $labels = array(
        'name' => __('Courses', 'edupro'),
        'singular_name' => __('Course', 'edupro'),
        'menu_name' => __('Courses', 'edupro'),
        'add_new' => __('Add New', 'edupro'),
        'add_new_item' => __('Add New Course', 'edupro'),
        'edit_item' => __('Edit Course', 'edupro'),
        'new_item' => __('New Course', 'edupro'),
        'view_item' => __('View Course', 'edupro'),
        'search_items' => __('Search Courses', 'edupro'),
        'not_found' => __('No courses found', 'edupro'),
        'not_found_in_trash' => __('No courses found in Trash', 'edupro')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'rewrite' => array('slug' => 'course'),
        'show_in_rest' => true,
    );

    register_post_type('course', $args);
}
add_action('init', 'edupro_register_course_post_type');

// Register Course Categories
function edupro_register_course_taxonomy() {
    $labels = array(
        'name' => __('Course Categories', 'edupro'),
        'singular_name' => __('Course Category', 'edupro'),
        'search_items' => __('Search Course Categories', 'edupro'),
        'all_items' => __('All Course Categories', 'edupro'),
        'parent_item' => __('Parent Course Category', 'edupro'),
        'parent_item_colon' => __('Parent Course Category:', 'edupro'),
        'edit_item' => __('Edit Course Category', 'edupro'),
        'update_item' => __('Update Course Category', 'edupro'),
        'add_new_item' => __('Add New Course Category', 'edupro'),
        'new_item_name' => __('New Course Category Name', 'edupro'),
        'menu_name' => __('Course Categories', 'edupro'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'course-category'),
        'show_in_rest' => true,
    );

    register_taxonomy('course_category', array('course'), $args);
}
add_action('init', 'edupro_register_course_taxonomy');

// Add theme options page
function edupro_add_theme_options_page() {
    add_menu_page(
        __('Theme Options', 'edupro'),
        __('Theme Options', 'edupro'),
        'manage_options',
        'edupro-theme-options',
        'edupro_theme_options_page',
        'dashicons-admin-generic',
        60
    );
}
add_action('admin_menu', 'edupro_add_theme_options_page');

// Theme options page callback
function edupro_theme_options_page() {
    // Theme options page content will be implemented later
    echo '<div class="wrap"><h1>' . __('Theme Options', 'edupro') . '</h1></div>';
}

// Add Moodle integration settings
require_once get_template_directory() . '/inc/moodle-integration.php';

// Add accessibility features
require_once get_template_directory() . '/inc/accessibility.php';

// Add course management functions
require_once get_template_directory() . '/inc/course-management.php';
