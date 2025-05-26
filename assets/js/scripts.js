(function($) {
    'use strict';

    // Course Notes Feature
    const CourseNotes = {
        init: function() {
            this.notesModal = document.getElementById('course-notes-modal');
            this.notesTextarea = document.getElementById('course-notes');
            this.bindEvents();
            this.loadNotes();
        },

        bindEvents: function() {
            // Toggle notes modal
            document.getElementById('toggle-notes').addEventListener('click', () => {
                this.notesModal.classList.toggle('hidden');
            });

            // Save notes automatically
            this.notesTextarea.addEventListener('input', this.debounce(() => {
                this.saveNotes();
            }, 500));

            // Close modal on outside click
            this.notesModal.addEventListener('click', (e) => {
                if (e.target === this.notesModal) {
                    this.notesModal.classList.add('hidden');
                }
            });
        },

        loadNotes: function() {
            const courseId = this.notesTextarea.dataset.courseId;
            const notes = localStorage.getItem(`course-notes-${courseId}`);
            if (notes) {
                this.notesTextarea.value = notes;
            }
        },

        saveNotes: function() {
            const courseId = this.notesTextarea.dataset.courseId;
            localStorage.setItem(`course-notes-${courseId}`, this.notesTextarea.value);
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

    // Course Focus Mode
    const CourseFocusMode = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            document.getElementById('toggle-focus-mode')?.addEventListener('click', () => {
                document.body.classList.toggle('focus-mode');
                this.updateFocusMode();
            });
        },

        updateFocusMode: function() {
            const isFocusMode = document.body.classList.contains('focus-mode');
            localStorage.setItem('focus-mode', isFocusMode);

            // Hide distracting elements
            const elementsToHide = document.querySelectorAll('.hide-in-focus-mode');
            elementsToHide.forEach(element => {
                element.style.display = isFocusMode ? 'none' : '';
            });

            // Expand main content
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.style.maxWidth = isFocusMode ? '100%' : '';
            }
        }
    };

    // Course Progress Tracking
    const CourseProgress = {
        init: function() {
            this.bindEvents();
            this.loadProgress();
        },

        bindEvents: function() {
            document.querySelectorAll('.mark-complete').forEach(button => {
                button.addEventListener('click', (e) => {
                    const lessonId = e.target.dataset.lessonId;
                    this.markLessonComplete(lessonId);
                });
            });
        },

        loadProgress: function() {
            const courseId = document.body.dataset.courseId;
            const progress = JSON.parse(localStorage.getItem(`course-progress-${courseId}`)) || {};

            // Update UI for completed lessons
            Object.entries(progress).forEach(([lessonId, completed]) => {
                if (completed) {
                    this.updateLessonUI(lessonId);
                }
            });

            // Update progress bar
            this.updateProgressBar();
        },

        markLessonComplete: function(lessonId) {
            const courseId = document.body.dataset.courseId;
            const progress = JSON.parse(localStorage.getItem(`course-progress-${courseId}`)) || {};
            
            progress[lessonId] = true;
            localStorage.setItem(`course-progress-${courseId}`, JSON.stringify(progress));

            this.updateLessonUI(lessonId);
            this.updateProgressBar();
            this.saveToDB(lessonId);
        },

        updateLessonUI: function(lessonId) {
            const lessonElement = document.querySelector(`[data-lesson-id="${lessonId}"]`);
            if (lessonElement) {
                lessonElement.classList.add('completed');
                const button = lessonElement.querySelector('.mark-complete');
                if (button) {
                    button.innerHTML = '<i class="fas fa-check"></i> Completed';
                    button.disabled = true;
                }
            }
        },

        updateProgressBar: function() {
            const courseId = document.body.dataset.courseId;
            const progress = JSON.parse(localStorage.getItem(`course-progress-${courseId}`)) || {};
            const totalLessons = document.querySelectorAll('[data-lesson-id]').length;
            const completedLessons = Object.values(progress).filter(Boolean).length;
            const percentage = (completedLessons / totalLessons) * 100;

            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                progressBar.setAttribute('aria-valuenow', percentage);
            }

            const progressText = document.querySelector('.progress-text');
            if (progressText) {
                progressText.textContent = `${Math.round(percentage)}% Complete`;
            }
        },

        saveToDB: function(lessonId) {
            // Save progress to database via AJAX
            $.ajax({
                url: eduproAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_lesson_progress',
                    lesson_id: lessonId,
                    nonce: eduproAjax.nonce
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to save progress:', response.data);
                    }
                }
            });
        }
    };

    // Course Table of Contents
    const CourseTableOfContents = {
        init: function() {
            this.toc = document.querySelector('.course-toc');
            if (this.toc) {
                this.buildTOC();
                this.bindEvents();
            }
        },

        buildTOC: function() {
            const headings = document.querySelectorAll('.course-content h2, .course-content h3');
            const tocList = document.createElement('ul');
            tocList.className = 'toc-list';

            headings.forEach((heading, index) => {
                // Add ID to heading if not present
                if (!heading.id) {
                    heading.id = `toc-heading-${index}`;
                }

                const listItem = document.createElement('li');
                listItem.className = `toc-item toc-${heading.tagName.toLowerCase()}`;

                const link = document.createElement('a');
                link.href = `#${heading.id}`;
                link.textContent = heading.textContent;
                link.className = 'toc-link';

                listItem.appendChild(link);
                tocList.appendChild(listItem);
            });

            this.toc.appendChild(tocList);
        },

        bindEvents: function() {
            // Smooth scroll to section
            this.toc.querySelectorAll('.toc-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Highlight current section
            window.addEventListener('scroll', this.debounce(() => {
                this.highlightCurrentSection();
            }, 100));
        },

        highlightCurrentSection: function() {
            const headings = document.querySelectorAll('.course-content h2, .course-content h3');
            let currentHeading = null;

            headings.forEach(heading => {
                const rect = heading.getBoundingClientRect();
                if (rect.top <= 100) {
                    currentHeading = heading;
                }
            });

            if (currentHeading) {
                this.toc.querySelectorAll('.toc-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${currentHeading.id}`) {
                        link.classList.add('active');
                    }
                });
            }
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

    // Initialize features when document is ready
    $(document).ready(function() {
        // Initialize course features if on a course page
        if ($('body').hasClass('single-course')) {
            CourseNotes.init();
            CourseFocusMode.init();
            CourseProgress.init();
            CourseTableOfContents.init();
        }

        // Course filters
        $('#course-filters select, #course-filters input').on('change', function() {
            $('#course-filters').submit();
        });

        // Mobile menu toggle
        $('#mobile-menu-button').on('click', function() {
            $('#mobile-menu').toggleClass('hidden');
        });

        // Curriculum accordion
        $('.curriculum-section-header').on('click', function() {
            $(this).next('.curriculum-section-content').slideToggle();
            $(this).find('.accordion-icon').toggleClass('rotate-180');
        });

        // Course preview modal
        $('.preview-course').on('click', function(e) {
            e.preventDefault();
            const previewUrl = $(this).data('preview-url');
            $('#course-preview-frame').attr('src', previewUrl);
            $('#course-preview-modal').removeClass('hidden');
        });

        $('#close-preview-modal').on('click', function() {
            $('#course-preview-modal').addClass('hidden');
            $('#course-preview-frame').attr('src', '');
        });

        // Course reviews
        $('.course-rating-input').on('change', function() {
            const rating = $(this).val();
            $('.rating-stars').removeClass('active');
            $(`.rating-stars[data-rating="${rating}"]`).addClass('active');
        });

        // Handle review form submission
        $('#course-review-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: eduproAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Refresh reviews section
                        $('#course-reviews').html(response.data.html);
                        $('#course-review-form').trigger('reset');
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
    });

})(jQuery);
