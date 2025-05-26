<?php get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4"><?php _e('Our Courses', 'edupro'); ?></h1>
        <p class="text-xl text-gray-600">
            <?php _e('Explore our wide range of courses and start your learning journey today.', 'edupro'); ?>
        </p>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form id="course-filters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php _e('Category', 'edupro'); ?>
                </label>
                <select id="category" name="course_category" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value=""><?php _e('All Categories', 'edupro'); ?></option>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'course_category',
                        'hide_empty' => true
                    ));
                    foreach ($categories as $category) :
                        $selected = isset($_GET['course_category']) && $_GET['course_category'] === $category->slug ? 'selected' : '';
                    ?>
                        <option value="<?php echo esc_attr($category->slug); ?>" <?php echo $selected; ?>>
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div>
                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php _e('Difficulty Level', 'edupro'); ?>
                </label>
                <select id="difficulty" name="difficulty" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value=""><?php _e('All Levels', 'edupro'); ?></option>
                    <option value="beginner" <?php selected(isset($_GET['difficulty']), 'beginner'); ?>>
                        <?php _e('Beginner', 'edupro'); ?>
                    </option>
                    <option value="intermediate" <?php selected(isset($_GET['difficulty']), 'intermediate'); ?>>
                        <?php _e('Intermediate', 'edupro'); ?>
                    </option>
                    <option value="advanced" <?php selected(isset($_GET['difficulty']), 'advanced'); ?>>
                        <?php _e('Advanced', 'edupro'); ?>
                    </option>
                </select>
            </div>

            <!-- Price Range Filter -->
            <div>
                <label for="price_range" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php _e('Price Range', 'edupro'); ?>
                </label>
                <select id="price_range" name="price_range" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value=""><?php _e('All Prices', 'edupro'); ?></option>
                    <option value="free" <?php selected(isset($_GET['price_range']), 'free'); ?>>
                        <?php _e('Free', 'edupro'); ?>
                    </option>
                    <option value="0-50" <?php selected(isset($_GET['price_range']), '0-50'); ?>>
                        <?php _e('$0 - $50', 'edupro'); ?>
                    </option>
                    <option value="51-100" <?php selected(isset($_GET['price_range']), '51-100'); ?>>
                        <?php _e('$51 - $100', 'edupro'); ?>
                    </option>
                    <option value="101+" <?php selected(isset($_GET['price_range']), '101+'); ?>>
                        <?php _e('$101+', 'edupro'); ?>
                    </option>
                </select>
            </div>

            <!-- Search Input -->
            <div>
                <label for="course_search" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php _e('Search Courses', 'edupro'); ?>
                </label>
                <div class="relative">
                    <input type="text" id="course_search" name="course_search" 
                           value="<?php echo isset($_GET['course_search']) ? esc_attr($_GET['course_search']) : ''; ?>"
                           placeholder="<?php esc_attr_e('Search by keyword...', 'edupro'); ?>"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Course Grid -->
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while (have_posts()) : the_post(); 
                $course_id = get_the_ID();
                $price = get_post_meta($course_id, '_course_price', true);
                $students = get_post_meta($course_id, '_course_students', true);
                $duration = get_post_meta($course_id, '_course_duration', true);
                $difficulty = get_post_meta($course_id, '_course_difficulty', true);
            ?>
                <article class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                    <!-- Course Image -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="aspect-w-16 aspect-h-9">
                            <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Course Content -->
                    <div class="p-6">
                        <!-- Category -->
                        <?php
                        $categories = get_the_terms($course_id, 'course_category');
                        if ($categories && !is_wp_error($categories)) :
                            $category = $categories[0];
                            $category_color = get_term_meta($category->term_id, 'category_color', true);
                        ?>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mb-4"
                                  style="background-color: <?php echo esc_attr($category_color); ?>20; color: <?php echo esc_attr($category_color); ?>">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php endif; ?>

                        <!-- Title -->
                        <h2 class="text-xl font-bold mb-4">
                            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <!-- Meta Information -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <span class="flex items-center">
                                <i class="far fa-user mr-2"></i>
                                <?php 
                                printf(
                                    _n('%s Student', '%s Students', $students, 'edupro'),
                                    number_format_i18n($students)
                                );
                                ?>
                            </span>
                            <span class="mx-3">•</span>
                            <span class="flex items-center">
                                <i class="far fa-clock mr-2"></i>
                                <?php echo esc_html($duration); ?>
                            </span>
                        </div>

                        <!-- Excerpt -->
                        <div class="text-gray-600 mb-4">
                            <?php the_excerpt(); ?>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between mt-4 pt-4 border-t">
                            <div class="text-lg font-bold text-blue-600">
                                <?php
                                if ($price) {
                                    echo esc_html('$' . $price);
                                } else {
                                    _e('Free', 'edupro');
                                }
                                ?>
                            </div>
                            <div class="flex items-center">
                                <?php if ($difficulty) : ?>
                                    <span class="text-sm text-gray-500 mr-4">
                                        <i class="fas fa-signal mr-1"></i>
                                        <?php echo esc_html(ucfirst($difficulty)); ?>
                                    </span>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <?php _e('View Course', 'edupro'); ?>
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => '<i class="fas fa-arrow-left mr-2"></i>' . __('Previous', 'edupro'),
                'next_text' => __('Next', 'edupro') . '<i class="fas fa-arrow-right ml-2"></i>',
                'class' => 'flex justify-center space-x-2',
            ));
            ?>
        </div>

    <?php else : ?>
        <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold mb-2"><?php _e('No Courses Found', 'edupro'); ?></h2>
            <p class="text-gray-600">
                <?php _e('Try adjusting your search or filter criteria.', 'edupro'); ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle filter form submission
    $('#course-filters select, #course-filters input').on('change', function() {
        $('#course-filters').submit();
    });

    // Debounce search input
    let searchTimeout;
    $('#course_search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#course-filters').submit();
        }, 500);
    });
});
</script>

<?php get_footer(); ?>
