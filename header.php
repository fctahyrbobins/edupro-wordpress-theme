<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="accessibility-widget" class="fixed right-4 top-20 z-50 bg-white shadow-lg rounded-lg p-4">
    <button class="text-sm mb-2 w-full text-left hover:bg-gray-100 p-2 rounded" onclick="toggleHighContrast()">
        <i class="fas fa-adjust mr-2"></i> <?php _e('High Contrast', 'edupro'); ?>
    </button>
    <button class="text-sm mb-2 w-full text-left hover:bg-gray-100 p-2 rounded" onclick="increaseFontSize()">
        <i class="fas fa-plus mr-2"></i> <?php _e('Increase Text', 'edupro'); ?>
    </button>
    <button class="text-sm mb-2 w-full text-left hover:bg-gray-100 p-2 rounded" onclick="decreaseFontSize()">
        <i class="fas fa-minus mr-2"></i> <?php _e('Decrease Text', 'edupro'); ?>
    </button>
    <div class="language-switcher mt-4 pt-4 border-t">
        <?php
        $languages = apply_filters('wpml_active_languages', NULL, 'skip_missing=0&orderby=code');
        if (!empty($languages)) :
            foreach ($languages as $language) :
                $active_class = $language['active'] ? 'bg-gray-100' : '';
        ?>
                <a href="<?php echo esc_url($language['url']); ?>" 
                   class="block text-sm mb-2 hover:bg-gray-100 p-2 rounded <?php echo $active_class; ?>">
                    <?php echo esc_html($language['native_name']); ?>
                </a>
        <?php
            endforeach;
        endif;
        ?>
    </div>
</div>

<header class="bg-white shadow-md">
    <!-- Top Bar -->
    <div class="bg-gray-900 text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <button id="accessibility-toggle" class="text-sm hover:text-gray-300">
                    <i class="fas fa-universal-access mr-1"></i> <?php _e('Accessibility', 'edupro'); ?>
                </button>
                <button id="dark-mode-toggle" class="text-sm hover:text-gray-300">
                    <i class="fas fa-moon mr-1"></i> <?php _e('Dark Mode', 'edupro'); ?>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="text-sm hover:text-gray-300">
                        <i class="fas fa-sign-out-alt mr-1"></i> <?php _e('Logout', 'edupro'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="text-sm hover:text-gray-300">
                        <i class="fas fa-sign-in-alt mr-1"></i> <?php _e('Login', 'edupro'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="text-xl font-bold">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Quick Links (Desktop) -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'quick-links',
                    'container' => false,
                    'menu_class' => 'flex items-center space-x-6',
                    'fallback_cb' => false,
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                ));
                ?>
            </nav>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="container mx-auto px-4 py-4">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'quick-links',
                'container' => false,
                'menu_class' => 'space-y-4',
                'fallback_cb' => false,
            ));
            ?>
        </div>
    </div>

    <!-- Mega Menu -->
    <div class="border-t">
        <div class="container mx-auto px-4">
            <nav class="hidden md:block">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'mega-menu',
                    'container' => false,
                    'menu_class' => 'flex justify-between py-4',
                    'fallback_cb' => false,
                ));
                ?>
            </nav>
        </div>
    </div>
</header>

<main id="content" class="site-content">
