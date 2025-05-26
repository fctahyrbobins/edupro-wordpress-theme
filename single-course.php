<?php get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <!-- Course Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <?php if (has_post_thumbnail()) : ?>
                <div class="aspect-w-16 aspect-h-6">
                    <?php the_post_thumbnail('full', ['class' => 'w-full h-full object-cover']); ?>
                </div>
            <?php endif; ?>
            
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between">
                    <div class="w-full lg:w-2/3">
                        <!-- Course Category -->
                        <?php
                        $categories = get_the_terms(get_the_ID(), 'course_category');
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                        ?>
                            <span class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full mb-4">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php
                            endforeach;
                        endif;
                        ?>

                        <h1 class="text-3xl font-bold mb-4"><?php the_title(); ?></h1>
                        
                        <!-- Course Meta -->
                        <div class="flex flex-wrap items-center text-sm text-gray-500 space-x-4 mb-4">
                            <span class="flex items-center">
                                <i class="far fa-user mr-2"></i>
                                <?php 
                                $student_count = get_post_meta(get_the_ID(), '_course_students', true);
                                printf(
                                    _n('%s Student', '%s Students', $student_count, 'edupro'),
                                    number_format_i18n($student_count)
                                );
                                ?>
                            </span>
                            <span class="flex items-center">
                                <i class="far fa-clock mr-2"></i>
                                <?php 
                                $duration = get_post_meta(get_the_ID(), '_course_duration', true);
                                echo esc_html($duration);
                                ?>
                            </span>
                            <span class="flex items-center">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <?php echo get_the_date(); ?>
                            </span>
                        </div>

                        <!-- Course Instructor -->
                        <?php
                        $instructor_id = get_post_meta(get_the_ID(), '_course_instructor', true);
                        if ($instructor_id) :
                            $instructor = get_userdata($instructor_id);
                        ?>
                            <div class="flex items-center space-x-4">
                                <?php echo get_avatar($instructor_id, 48, '', '', ['class' => 'rounded-full']); ?>
                                <div>
                                    <h3 class="font-medium"><?php echo esc_html($instructor->display_name); ?></h3>
                                    <p class="text-sm text-gray-500"><?php _e('Course Instructor', 'edupro'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="w-full lg:w-1/3 mt-6 lg:mt-0">
                        <!-- Course Actions -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <?php
                            $price = get_post_meta(get_the_ID(), '_course_price', true);
                            if ($price) :
                            ?>
                                <div class="text-center mb-6">
                                    <span class="text-3xl font-bold text-blue-600">
                                        <?php echo esc_html($price); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <button class="w-full bg-blue-600 text-white rounded-lg px-6 py-3 font-medium hover:bg-blue-700 mb-4">
                                <?php _e('Enroll Now', 'edupro'); ?>
                            </button>

                            <button class="w-full bg-gray-200 text-gray-700 rounded-lg px-6 py-3 font-medium hover:bg-gray-300">
                                <?php _e('Add to Wishlist', 'edupro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Main Content -->
            <main class="lg:col-span-8">
                <!-- Course Tabs -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="border-b">
                        <nav class="flex" aria-label="Tabs">
                            <button class="px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600" id="overview-tab">
                                <?php _e('Overview', 'edupro'); ?>
                            </button>
                            <button class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" id="curriculum-tab">
                                <?php _e('Curriculum', 'edupro'); ?>
                            </button>
                            <button class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" id="instructor-tab">
                                <?php _e('Instructor', 'edupro'); ?>
                            </button>
                            <button class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" id="reviews-tab">
                                <?php _e('Reviews', 'edupro'); ?>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Overview -->
                        <div id="overview-content">
                            <div class="prose max-w-none">
                                <?php the_content(); ?>
                            </div>

                            <!-- Course Features -->
                            <?php
                            $features = get_post_meta(get_the_ID(), '_course_features', true);
                            if ($features) :
                            ?>
                                <div class="mt-8">
                                    <h3 class="text-lg font-semibold mb-4"><?php _e('Course Features', 'edupro'); ?></h3>
                                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php foreach ($features as $feature) : ?>
                                            <li class="flex items-center">
                                                <i class="fas fa-check text-green-500 mr-2"></i>
                                                <?php echo esc_html($feature); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Curriculum -->
                        <div id="curriculum-content" class="hidden">
                            <?php
                            $curriculum = get_post_meta(get_the_ID(), '_course_curriculum', true);
                            if ($curriculum) :
                            ?>
                                <div class="space-y-4">
                                    <?php foreach ($curriculum as $section) : ?>
                                        <div class="border rounded-lg overflow-hidden">
                                            <button class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100">
                                                <span class="font-medium"><?php echo esc_html($section['title']); ?></span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="p-4 border-t hidden">
                                                <ul class="space-y-2">
                                                    <?php foreach ($section['lessons'] as $lesson) : ?>
                                                        <li class="flex items-center justify-between">
                                                            <span class="flex items-center">
                                                                <i class="far fa-play-circle mr-2"></i>
                                                                <?php echo esc_html($lesson['title']); ?>
                                                            </span>
                                                            <span class="text-sm text-gray-500">
                                                                <?php echo esc_html($lesson['duration']); ?>
                                                            </span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Instructor -->
                        <div id="instructor-content" class="hidden">
                            <?php if ($instructor_id) : ?>
                                <div class="flex items-start space-x-6">
                                    <?php echo get_avatar($instructor_id, 96, '', '', ['class' => 'rounded-lg']); ?>
                                    <div>
                                        <h3 class="text-xl font-semibold mb-2">
                                            <?php echo esc_html($instructor->display_name); ?>
                                        </h3>
                                        <p class="text-gray-600 mb-4">
                                            <?php echo get_user_meta($instructor_id, 'description', true); ?>
                                        </p>
                                        <!-- Social Links -->
                                        <div class="flex space-x-4">
                                            <?php
                                            $social_links = array(
                                                'facebook' => get_user_meta($instructor_id, 'facebook', true),
                                                'twitter' => get_user_meta($instructor_id, 'twitter', true),
                                                'linkedin' => get_user_meta($instructor_id, 'linkedin', true)
                                            );
                                            foreach ($social_links as $platform => $link) :
                                                if ($link) :
                                            ?>
                                                <a href="<?php echo esc_url($link); ?>" class="text-gray-400 hover:text-gray-600">
                                                    <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                                                </a>
                                            <?php
                                                endif;
                                            endforeach;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Reviews -->
                        <div id="reviews-content" class="hidden">
                            <?php
                            if (comments_open() || get_comments_number()) :
                                comments_template();
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-8">
                <!-- Course Progress (for enrolled students) -->
                <?php if (is_user_enrolled(get_current_user_id(), get_the_ID())) : ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4"><?php _e('Course Progress', 'edupro'); ?></h3>
                        <div class="mb-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: 45%"></div>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">45% <?php _e('Complete', 'edupro'); ?></p>
                        </div>
                        <button class="w-full bg-blue-600 text-white rounded-lg px-6 py-3 font-medium hover:bg-blue-700">
                            <?php _e('Continue Learning', 'edupro'); ?>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Related Courses -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4"><?php _e('Related Courses', 'edupro'); ?></h3>
                    <?php
                    $related_courses = new WP_Query(array(
                        'post_type' => 'course',
                        'posts_per_page' => 3,
                        'post__not_in' => array(get_the_ID()),
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'course_category',
                                'field' => 'term_id',
                                'terms' => wp_get_post_terms(get_the_ID(), 'course_category', array('fields' => 'ids'))
                            )
                        )
                    ));

                    if ($related_courses->have_posts()) :
                        while ($related_courses->have_posts()) : $related_courses->the_post();
                    ?>
                        <div class="mb-4 last:mb-0">
                            <a href="<?php the_permalink(); ?>" class="flex items-center space-x-4 hover:bg-gray-50 p-2 rounded">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="flex-shrink-0 w-16 h-16">
                                        <?php the_post_thumbnail('thumbnail', ['class' => 'w-full h-full object-cover rounded']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow">
                                    <h4 class="font-medium text-gray-900 mb-1"><?php the_title(); ?></h4>
                                    <span class="text-sm text-gray-500">
                                        <?php
                                        $student_count = get_post_meta(get_the_ID(), '_course_students', true);
                                        printf(
                                            _n('%s Student', '%s Students', $student_count, 'edupro'),
                                            number_format_i18n($student_count)
                                        );
                                        ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </aside>
        </div>
    <?php endwhile; ?>
</div>

<!-- Course Notes Modal -->
<div id="course-notes-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="absolute inset-y-0 right-0 max-w-2xl w-full bg-white shadow-xl">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold"><?php _e('Course Notes', 'edupro'); ?></h3>
            <button class="text-gray-400 hover:text-gray-600" onclick="toggleNotesModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <textarea id="course-notes" class="w-full h-96 p-4 border rounded-lg" placeholder="<?php esc_attr_e('Take notes here...', 'edupro'); ?>"></textarea>
            <button class="mt-4 bg-blue-600 text-white rounded-lg px-6 py-2 font-medium hover:bg-blue-700">
                <?php _e('Save Notes', 'edupro'); ?>
            </button>
        </div>
    </div>
</div>

<script>
// Tab Switching
document.querySelectorAll('[id$="-tab"]').forEach(tab => {
    tab.addEventListener('click', () => {
        // Update tab styles
        document.querySelectorAll('[id$="-tab"]').forEach(t => {
            t.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
            t.classList.add('text-gray-500');
        });
        tab.classList.remove('text-gray-500');
        tab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');

        // Show corresponding content
        const contentId = tab.id.replace('-tab', '-content');
        document.querySelectorAll('[id$="-content"]').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(contentId).classList.remove('hidden');
    });
});

// Curriculum Accordion
document.querySelectorAll('.border.rounded-lg button').forEach(button => {
    button.addEventListener('click', () => {
        const content = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        content.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    });
});

// Notes Modal
function toggleNotesModal() {
    const modal = document.getElementById('course-notes-modal');
    modal.classList.toggle('hidden');
}

// Save notes to localStorage
document.getElementById('course-notes').addEventListener('input', (e) => {
    localStorage.setItem('course-notes-<?php echo get_the_ID(); ?>', e.target.value);
});

// Load saved notes
document.addEventListener('DOMContentLoaded', () => {
    const savedNotes = localStorage.getItem('course-notes-<?php echo get_the_ID(); ?>');
    if (savedNotes) {
        document.getElementById('course-notes').value = savedNotes;
    }
});
</script>

<?php get_footer(); ?>
