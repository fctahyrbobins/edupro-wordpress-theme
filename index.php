<?php get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        <!-- Main Content -->
        <main class="col-span-1 md:col-span-8">
            <?php if (have_posts()) : ?>
                <div class="grid grid-cols-1 gap-8">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="aspect-w-16 aspect-h-9">
                                    <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <header class="mb-4">
                                    <h2 class="text-2xl font-bold mb-2">
                                        <a href="<?php the_permalink(); ?>" class="hover:text-blue-600">
                                            <?php the_title(); ?>
                                        </a>
                                    </h2>
                                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                                        <span class="flex items-center">
                                            <i class="far fa-calendar-alt mr-2"></i>
                                            <?php echo get_the_date(); ?>
                                        </span>
                                        <?php if (get_post_type() === 'course') : ?>
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
                                        <?php endif; ?>
                                        <?php if (has_category()) : ?>
                                            <span class="flex items-center">
                                                <i class="far fa-folder mr-2"></i>
                                                <?php the_category(', '); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </header>

                                <div class="prose max-w-none mb-4">
                                    <?php the_excerpt(); ?>
                                </div>

                                <footer class="flex items-center justify-between">
                                    <a href="<?php the_permalink(); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                        <?php 
                                        if (get_post_type() === 'course') {
                                            _e('View Course', 'edupro');
                                        } else {
                                            _e('Read More', 'edupro');
                                        }
                                        ?>
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                    <?php if (comments_open()) : ?>
                                        <span class="flex items-center text-sm text-gray-500">
                                            <i class="far fa-comment mr-2"></i>
                                            <?php comments_number(); ?>
                                        </span>
                                    <?php endif; ?>
                                </footer>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-8">
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
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-bold mb-2"><?php _e('Nothing Found', 'edupro'); ?></h2>
                    <p class="text-gray-600 mb-4"><?php _e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'edupro'); ?></p>
                    <?php get_search_form(); ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Sidebar -->
        <aside class="col-span-1 md:col-span-4 space-y-8">
            <?php if (is_active_sidebar('sidebar-1')) : ?>
                <?php dynamic_sidebar('sidebar-1'); ?>
            <?php endif; ?>

            <!-- Featured Courses Widget -->
            <?php if (get_post_type() === 'course' || is_post_type_archive('course')) : ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4"><?php _e('Featured Courses', 'edupro'); ?></h3>
                    <?php
                    $featured_courses = new WP_Query(array(
                        'post_type' => 'course',
                        'posts_per_page' => 3,
                        'meta_key' => '_course_featured',
                        'meta_value' => 'yes'
                    ));

                    if ($featured_courses->have_posts()) :
                        while ($featured_courses->have_posts()) : $featured_courses->the_post();
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
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
