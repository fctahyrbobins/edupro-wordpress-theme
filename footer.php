</main><!-- #content -->

<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Site Info -->
            <div class="col-span-1">
                <?php if (has_custom_logo()) : ?>
                    <div class="mb-4 filter brightness-0 invert">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <h3 class="text-xl font-bold mb-4"><?php bloginfo('name'); ?></h3>
                <?php endif; ?>
                <p class="text-gray-400 mb-4"><?php bloginfo('description'); ?></p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-span-1">
                <h3 class="text-lg font-semibold mb-4"><?php _e('Quick Links', 'edupro'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'quick-links',
                    'container' => false,
                    'menu_class' => 'space-y-2',
                    'fallback_cb' => false,
                ));
                ?>
            </div>

            <!-- Courses -->
            <div class="col-span-1">
                <h3 class="text-lg font-semibold mb-4"><?php _e('Popular Courses', 'edupro'); ?></h3>
                <?php
                $courses = new WP_Query(array(
                    'post_type' => 'course',
                    'posts_per_page' => 5,
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_course_students',
                    'order' => 'DESC'
                ));

                if ($courses->have_posts()) :
                    echo '<ul class="space-y-2">';
                    while ($courses->have_posts()) : $courses->the_post();
                        echo '<li><a href="' . get_permalink() . '" class="text-gray-400 hover:text-white">' . get_the_title() . '</a></li>';
                    endwhile;
                    echo '</ul>';
                    wp_reset_postdata();
                endif;
                ?>
            </div>

            <!-- Contact Info -->
            <div class="col-span-1">
                <h3 class="text-lg font-semibold mb-4"><?php _e('Contact Us', 'edupro'); ?></h3>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-2"></i>
                        <span><?php echo esc_html(get_theme_mod('edupro_address', '123 Education St, City, Country')); ?></span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        <span><?php echo esc_html(get_theme_mod('edupro_phone', '+1 234 567 890')); ?></span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope mr-2"></i>
                        <span><?php echo esc_html(get_theme_mod('edupro_email', 'info@eduprotheme.com')); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="mt-12 pt-8 border-t border-gray-800">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. 
                    <?php _e('All rights reserved.', 'edupro'); ?>
                </p>
                <div class="mt-4 md:mt-0">
                    <ul class="flex space-x-6 text-sm text-gray-400">
                        <li><a href="<?php echo get_privacy_policy_url(); ?>" class="hover:text-white">
                            <?php _e('Privacy Policy', 'edupro'); ?>
                        </a></li>
                        <li><a href="#" class="hover:text-white">
                            <?php _e('Terms of Service', 'edupro'); ?>
                        </a></li>
                        <li><a href="#" class="hover:text-white">
                            <?php _e('Cookie Policy', 'edupro'); ?>
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<!-- Accessibility & Dark Mode Scripts -->
<script>
function toggleHighContrast() {
    document.body.classList.toggle('high-contrast');
    localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
}

function increaseFontSize() {
    let currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    document.documentElement.style.fontSize = (currentSize + 2) + 'px';
    localStorage.setItem('fontSize', document.documentElement.style.fontSize);
}

function decreaseFontSize() {
    let currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    if (currentSize > 12) {
        document.documentElement.style.fontSize = (currentSize - 2) + 'px';
        localStorage.setItem('fontSize', document.documentElement.style.fontSize);
    }
}

// Mobile Menu Toggle
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Dark Mode Toggle
document.getElementById('dark-mode-toggle').addEventListener('click', function() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
});

// Accessibility Widget Toggle
document.getElementById('accessibility-toggle').addEventListener('click', function() {
    document.getElementById('accessibility-widget').classList.toggle('translate-x-full');
});

// Load saved preferences
document.addEventListener('DOMContentLoaded', function() {
    // High Contrast
    if (localStorage.getItem('highContrast') === 'true') {
        document.body.classList.add('high-contrast');
    }

    // Font Size
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        document.documentElement.style.fontSize = savedFontSize;
    }

    // Dark Mode
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    }
});
</script>

</body>
</html>
