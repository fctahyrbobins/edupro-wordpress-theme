(function($) {
    'use strict';

    const AccessibilityTools = {
        settings: {
            fontSize: parseInt(localStorage.getItem('fontSize')) || 16,
            highContrast: localStorage.getItem('highContrast') === 'true',
            dyslexicFont: localStorage.getItem('dyslexicFont') === 'true',
            reducedMotion: localStorage.getItem('reducedMotion') === 'true'
        },

        init: function() {
            this.applyStoredSettings();
            this.bindEvents();
            this.initializeKeyboardNav();
            this.setupSkipLinks();
            this.setupARIALandmarks();
        },

        applyStoredSettings: function() {
            // Apply font size
            document.documentElement.style.fontSize = this.settings.fontSize + 'px';

            // Apply high contrast
            if (this.settings.highContrast) {
                document.body.classList.add('high-contrast');
            }

            // Apply dyslexic font
            if (this.settings.dyslexicFont) {
                document.body.classList.add('dyslexic-font');
            }

            // Apply reduced motion
            if (this.settings.reducedMotion) {
                document.body.classList.add('reduced-motion');
            }
        },

        bindEvents: function() {
            // Font size controls
            $('#increase-font').on('click', () => this.changeFontSize(2));
            $('#decrease-font').on('click', () => this.changeFontSize(-2));
            $('#reset-font').on('click', () => this.resetFontSize());

            // High contrast toggle
            $('#high-contrast-toggle').on('click', () => this.toggleHighContrast());

            // Dyslexic font toggle
            $('#dyslexic-font-toggle').on('click', () => this.toggleDyslexicFont());

            // Reduced motion toggle
            $('#reduced-motion-toggle').on('click', () => this.toggleReducedMotion());

            // Screen reader guidance
            $('#screen-reader-guidance').on('click', () => {
                $('#screen-reader-info').toggleClass('hidden');
            });

            // Save settings when changed
            this.bindSettingsSave();
        },

        changeFontSize: function(delta) {
            this.settings.fontSize = Math.min(Math.max(12, this.settings.fontSize + delta), 24);
            document.documentElement.style.fontSize = this.settings.fontSize + 'px';
            localStorage.setItem('fontSize', this.settings.fontSize);
            this.announceChange(`Font size ${delta > 0 ? 'increased' : 'decreased'} to ${this.settings.fontSize}px`);
        },

        resetFontSize: function() {
            this.settings.fontSize = 16;
            document.documentElement.style.fontSize = '16px';
            localStorage.setItem('fontSize', 16);
            this.announceChange('Font size reset to default');
        },

        toggleHighContrast: function() {
            this.settings.highContrast = !this.settings.highContrast;
            document.body.classList.toggle('high-contrast');
            localStorage.setItem('highContrast', this.settings.highContrast);
            this.announceChange(`High contrast mode ${this.settings.highContrast ? 'enabled' : 'disabled'}`);
        },

        toggleDyslexicFont: function() {
            this.settings.dyslexicFont = !this.settings.dyslexicFont;
            document.body.classList.toggle('dyslexic-font');
            localStorage.setItem('dyslexicFont', this.settings.dyslexicFont);
            this.announceChange(`Dyslexic font ${this.settings.dyslexicFont ? 'enabled' : 'disabled'}`);
        },

        toggleReducedMotion: function() {
            this.settings.reducedMotion = !this.settings.reducedMotion;
            document.body.classList.toggle('reduced-motion');
            localStorage.setItem('reducedMotion', this.settings.reducedMotion);
            this.announceChange(`Reduced motion ${this.settings.reducedMotion ? 'enabled' : 'disabled'}`);
        },

        initializeKeyboardNav: function() {
            // Add focus indicators
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-nav');
                }
            });

            document.addEventListener('mousedown', () => {
                document.body.classList.remove('keyboard-nav');
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Alt + A to toggle accessibility menu
                if (e.altKey && e.key === 'a') {
                    e.preventDefault();
                    $('#accessibility-toolbar').toggleClass('hidden');
                }

                // Alt + H to toggle high contrast
                if (e.altKey && e.key === 'h') {
                    e.preventDefault();
                    this.toggleHighContrast();
                }

                // Alt + F to toggle dyslexic font
                if (e.altKey && e.key === 'f') {
                    e.preventDefault();
                    this.toggleDyslexicFont();
                }
            });
        },

        setupSkipLinks: function() {
            // Make skip links visible on focus
            $('.skip-link').on('focus', function() {
                $(this).css('top', '0');
            }).on('blur', function() {
                $(this).css('top', '-100px');
            });

            // Ensure target elements are focusable
            $('[id^="main"], [id^="nav"]').attr('tabindex', '-1');
        },

        setupARIALandmarks: function() {
            // Add ARIA landmarks if missing
            if (!$('[role="main"]').length) {
                $('#content').attr('role', 'main');
            }
            if (!$('[role="navigation"]').length) {
                $('nav').attr('role', 'navigation');
            }
            if (!$('[role="complementary"]').length) {
                $('aside').attr('role', 'complementary');
            }

            // Add aria-current to current navigation items
            $('.current-menu-item > a').attr('aria-current', 'page');
        },

        announceChange: function(message) {
            // Announce changes to screen readers
            const announcement = document.getElementById('a11y-announcer');
            if (!announcement) {
                const div = document.createElement('div');
                div.id = 'a11y-announcer';
                div.className = 'sr-only';
                div.setAttribute('aria-live', 'polite');
                document.body.appendChild(div);
            }
            document.getElementById('a11y-announcer').textContent = message;
        },

        bindSettingsSave: function() {
            // Save settings to user account if logged in
            const saveSettings = this.debounce(() => {
                $.ajax({
                    url: eduproAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_accessibility_preferences',
                        nonce: eduproAjax.nonce,
                        settings: this.settings
                    },
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to save accessibility settings:', response.data);
                        }
                    }
                });
            }, 1000);

            // Bind to all setting changes
            ['fontSize', 'highContrast', 'dyslexicFont', 'reducedMotion'].forEach(setting => {
                Object.defineProperty(this.settings, setting, {
                    get: function() {
                        return this['_' + setting];
                    },
                    set: function(value) {
                        this['_' + setting] = value;
                        saveSettings();
                    }
                });
            });
        },

        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize accessibility tools when document is ready
    $(document).ready(function() {
        AccessibilityTools.init();
    });

})(jQuery);
