<?php
if (!defined('ABSPATH')) exit;

/**
 * Course Management Class
 * Handles all course-related functionality and administration
 */
class EduPro_Course_Management {
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
        // Add course management settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add course filters
        add_action('pre_get_posts', array($this, 'apply_course_filters'));
        
        // Add course category color picker
        add_action('course_category_add_form_fields', array($this, 'add_category_color_field'));
        add_action('course_category_edit_form_fields', array($this, 'edit_category_color_field'));
        add_action('created_course_category', array($this, 'save_category_color'));
        add_action('edited_course_category', array($this, 'save_category_color'));

        // Add course management panel
        add_action('admin_menu', array($this, 'add_course_management_page'));

        // AJAX handlers for course management
        add_action('wp_ajax_update_course_status', array($this, 'update_course_status'));
        add_action('wp_ajax_bulk_update_courses', array($this, 'bulk_update_courses'));
        add_action('wp_ajax_get_course_stats', array($this, 'get_course_stats'));
    }

    /**
     * Register course management settings
     */
    public function register_settings() {
        register_setting('edupro_course_settings', 'edupro_enable_course_reviews');
        register_setting('edupro_course_settings', 'edupro_enable_course_progress');
        register_setting('edupro_course_settings', 'edupro_enable_certificates');
        register_setting('edupro_course_settings', 'edupro_course_permalink_base');
    }

    /**
     * Apply course filters to query
     */
    public function apply_course_filters($query) {
        if (!is_admin() && $query->is_main_query() && is_post_type_archive('course')) {
            // Category filter
            if (isset($_GET['course_category'])) {
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => 'course_category',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['course_category'])
                    )
                ));
            }

            // Difficulty level filter
            if (isset($_GET['difficulty'])) {
                $query->set('meta_query', array(
                    array(
                        'key' => '_course_difficulty',
                        'value' => sanitize_text_field($_GET['difficulty'])
                    )
                ));
            }

            // Price filter
            if (isset($_GET['price_range'])) {
                $price_range = explode('-', $_GET['price_range']);
                if (count($price_range) === 2) {
                    $query->set('meta_query', array(
                        array(
                            'key' => '_course_price',
                            'value' => array($price_range[0], $price_range[1]),
                            'type' => 'NUMERIC',
                            'compare' => 'BETWEEN'
                        )
                    ));
                }
            }

            // Duration filter
            if (isset($_GET['duration'])) {
                $query->set('meta_query', array(
                    array(
                        'key' => '_course_duration',
                        'value' => sanitize_text_field($_GET['duration'])
                    )
                ));
            }

            // Search filter
            if (isset($_GET['course_search'])) {
                $query->set('s', sanitize_text_field($_GET['course_search']));
            }
        }
    }

    /**
     * Add category color field
     */
    public function add_category_color_field() {
        ?>
        <div class="form-field">
            <label for="category_color"><?php _e('Category Color', 'edupro'); ?></label>
            <input type="color" name="category_color" id="category_color" value="#000000">
            <p><?php _e('Choose a color for this category.', 'edupro'); ?></p>
        </div>
        <?php
    }

    /**
     * Edit category color field
     */
    public function edit_category_color_field($term) {
        $color = get_term_meta($term->term_id, 'category_color', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="category_color"><?php _e('Category Color', 'edupro'); ?></label></th>
            <td>
                <input type="color" name="category_color" id="category_color" 
                       value="<?php echo esc_attr($color ? $color : '#000000'); ?>">
                <p class="description"><?php _e('Choose a color for this category.', 'edupro'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save category color
     */
    public function save_category_color($term_id) {
        if (isset($_POST['category_color'])) {
            update_term_meta(
                $term_id,
                'category_color',
                sanitize_hex_color($_POST['category_color'])
            );
        }
    }

    /**
     * Add course management page
     */
    public function add_course_management_page() {
        add_menu_page(
            __('Course Management', 'edupro'),
            __('Course Management', 'edupro'),
            'manage_options',
            'edupro-course-management',
            array($this, 'render_course_management_page'),
            'dashicons-welcome-learn-more',
            30
        );
    }

    /**
     * Render course management page
     */
    public function render_course_management_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Course Management', 'edupro'); ?></h1>

            <!-- Course Stats -->
            <div class="course-stats grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="stat-card bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2"><?php _e('Total Courses', 'edupro'); ?></h3>
                    <p class="text-3xl font-bold text-blue-600" id="total-courses">
                        <?php echo wp_count_posts('course')->publish; ?>
                    </p>
                </div>
                <div class="stat-card bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2"><?php _e('Active Students', 'edupro'); ?></h3>
                    <p class="text-3xl font-bold text-green-600" id="active-students">
                        <?php echo $this->get_active_students_count(); ?>
                    </p>
                </div>
                <div class="stat-card bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2"><?php _e('Total Categories', 'edupro'); ?></h3>
                    <p class="text-3xl font-bold text-purple-600" id="total-categories">
                        <?php echo wp_count_terms('course_category'); ?>
                    </p>
                </div>
                <div class="stat-card bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2"><?php _e('Total Revenue', 'edupro'); ?></h3>
                    <p class="text-3xl font-bold text-yellow-600" id="total-revenue">
                        <?php echo $this->get_total_revenue(); ?>
                    </p>
                </div>
            </div>

            <!-- Course Filters -->
            <div class="course-filters bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-semibold mb-4"><?php _e('Course Filters', 'edupro'); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Category', 'edupro'); ?>
                        </label>
                        <?php
                        wp_dropdown_categories(array(
                            'taxonomy' => 'course_category',
                            'name' => 'category-filter',
                            'id' => 'category-filter',
                            'show_option_all' => __('All Categories', 'edupro'),
                            'class' => 'w-full rounded-md border-gray-300'
                        ));
                        ?>
                    </div>
                    <div>
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Status', 'edupro'); ?>
                        </label>
                        <select id="status-filter" class="w-full rounded-md border-gray-300">
                            <option value=""><?php _e('All Statuses', 'edupro'); ?></option>
                            <option value="publish"><?php _e('Published', 'edupro'); ?></option>
                            <option value="draft"><?php _e('Draft', 'edupro'); ?></option>
                            <option value="pending"><?php _e('Pending Review', 'edupro'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="instructor-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Instructor', 'edupro'); ?>
                        </label>
                        <?php
                        wp_dropdown_users(array(
                            'name' => 'instructor-filter',
                            'id' => 'instructor-filter',
                            'show_option_all' => __('All Instructors', 'edupro'),
                            'class' => 'w-full rounded-md border-gray-300',
                            'role' => 'instructor'
                        ));
                        ?>
                    </div>
                    <div>
                        <label for="price-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Price Range', 'edupro'); ?>
                        </label>
                        <select id="price-filter" class="w-full rounded-md border-gray-300">
                            <option value=""><?php _e('All Prices', 'edupro'); ?></option>
                            <option value="free"><?php _e('Free', 'edupro'); ?></option>
                            <option value="paid"><?php _e('Paid', 'edupro'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Course Table -->
            <div class="course-table bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold"><?php _e('Course List', 'edupro'); ?></h2>
                        <div class="flex space-x-4">
                            <button id="bulk-publish" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                <?php _e('Bulk Publish', 'edupro'); ?>
                            </button>
                            <button id="bulk-unpublish" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                <?php _e('Bulk Unpublish', 'edupro'); ?>
                            </button>
                            <a href="<?php echo admin_url('post-new.php?post_type=course'); ?>" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <?php _e('Add New Course', 'edupro'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all-courses">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Title', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Category', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Instructor', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Students', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Price', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Status', 'edupro'); ?>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php _e('Actions', 'edupro'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="course-list">
                            <?php
                            $courses = get_posts(array(
                                'post_type' => 'course',
                                'posts_per_page' => -1
                            ));

                            foreach ($courses as $course) :
                                $category = get_the_terms($course->ID, 'course_category');
                                $instructor_id = get_post_meta($course->ID, '_course_instructor', true);
                                $students = get_post_meta($course->ID, '_course_students', true);
                                $price = get_post_meta($course->ID, '_course_price', true);
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="course-checkbox" value="<?php echo $course->ID; ?>">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if (has_post_thumbnail($course->ID)) : ?>
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <?php echo get_the_post_thumbnail($course->ID, 'thumbnail', array('class' => 'h-10 w-10 rounded-full')); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $course->post_title; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        if ($category) {
                                            $category_color = get_term_meta($category[0]->term_id, 'category_color', true);
                                            echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: ' . esc_attr($category_color) . '">';
                                            echo esc_html($category[0]->name);
                                            echo '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        if ($instructor_id) {
                                            $instructor = get_userdata($instructor_id);
                                            echo esc_html($instructor->display_name);
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $students ? number_format($students) : '0'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $price ? esc_html($price) : __('Free', 'edupro'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                     <?php echo $course->post_status === 'publish' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo get_post_status_object($course->post_status)->label; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?php echo get_edit_post_link($course->ID); ?>" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            <?php _e('Edit', 'edupro'); ?>
                                        </a>
                                        <a href="<?php echo get_permalink($course->ID); ?>" 
                                           class="ml-3 text-blue-600 hover:text-blue-900">
                                            <?php _e('View', 'edupro'); ?>
                                        </a>
                                        <button class="ml-3 text-red-600 hover:text-red-900 delete-course" 
                                                data-id="<?php echo $course->ID; ?>">
                                            <?php _e('Delete', 'edupro'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Select all courses
            $('#select-all-courses').on('change', function() {
                $('.course-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Bulk actions
            $('#bulk-publish').on('click', function() {
                bulkUpdateCourses('publish');
            });

            $('#bulk-unpublish').on('click', function() {
                bulkUpdateCourses('draft');
            });

            function bulkUpdateCourses(status) {
                const courseIds = $('.course-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (courseIds.length === 0) {
                    alert('<?php _e('Please select at least one course.', 'edupro'); ?>');
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bulk_update_courses',
                        course_ids: courseIds,
                        status: status,
                        nonce: '<?php echo wp_create_nonce('bulk_update_courses'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            }

            // Delete course
            $('.delete-course').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to delete this course?', 'edupro'); ?>')) {
                    const courseId = $(this).data('id');
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'delete_course',
                            course_id: courseId,
                            nonce: '<?php echo wp_create_nonce('delete_course'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message);
                            }
                        }
                    });
                }
            });

            // Filters
            $('#category-filter, #status-filter, #instructor-filter, #price-filter').on('change', function() {
                filterCourses();
            });

            function filterCourses() {
                const category = $('#category-filter').val();
                const status = $('#status-filter').val();
                const instructor = $('#instructor-filter').val();
                const price = $('#price-filter').val();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'filter_courses',
                        category: category,
                        status: status,
                        instructor: instructor,
                        price: price,
                        nonce: '<?php echo wp_create_nonce('filter_courses'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#course-list').html(response.data.html);
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Get active students count
     */
    private function get_active_students_count() {
        global $wpdb;
        return $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) 
             FROM {$wpdb->prefix}usermeta 
             WHERE meta_key = '_enrolled_courses'"
        );
    }

    /**
     * Get total revenue
     */
    private function get_total_revenue() {
        global $wpdb;
        $revenue = $wpdb->get_var(
            "SELECT SUM(meta_value) 
             FROM {$wpdb->prefix}postmeta 
             WHERE meta_key = '_course_revenue'"
        );
        return number_format($revenue, 2);
    }

    /**
     * Update course status via AJAX
     */
    public function update_course_status() {
        check_ajax_referer('update_course_status', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'edupro')));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$course_id || !$status) {
            wp_send_json_error(array('message' => __('Invalid data.', 'edupro')));
        }

        $result = wp_update_post(array(
            'ID' => $course_id,
            'post_status' => $status
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success();
    }

    /**
     * Bulk update courses via AJAX
     */
    public function bulk_update_courses() {
        check_ajax_referer('bulk_update_courses', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'edupro')));
        }

        $course_ids = isset($_POST['course_ids']) ? array_map('intval', $_POST['course_ids']) : array();
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (empty($course_ids) || !$status) {
            wp_send_json_error(array('message' => __('Invalid data.', 'edupro')));
        }

        foreach ($course_ids as $course_id) {
            wp_update_post(array(
                'ID' => $course_id,
                'post_status' => $status
            ));
        }

        wp_send_json_success();
    }

    /**
     * Get course stats via AJAX
     */
    public function get_course_stats() {
        check_ajax_referer('get_course_stats', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'edupro')));
        }

        wp_send_json_success(array(
            'total_courses' => wp_count_posts('course')->publish,
            'active_students' => $this->get_active_students_count(),
            'total_categories' => wp_count_terms('course_category'),
            'total_revenue' => $this->get_total_revenue()
        ));
    }
}

// Initialize course management
EduPro_Course_Management::get_instance();

// Add AJAX handlers
add_action('wp_ajax_update_course_status', array(EduPro_Course_Management::get_instance(), 'update_course_status'));
add_action('wp_ajax_bulk_update_courses', array(EduPro_Course_Management::get_instance(), 'bulk_update_courses'));
add_action('wp_ajax_get_course_stats', array(EduPro_Course_Management::get_instance(), 'get_course_stats'));
