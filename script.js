$(document).ready(function() {

    // Load saved theme when the page opens
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        $('.dark-toggle').text('☀️');
    }

    // Dark mode toggle button
    $(document).on('click', '.dark-toggle', function() {
        var html = document.documentElement;
        var current = html.getAttribute('data-theme');

        if (current === 'dark') {
            html.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            $(this).text('🌙');
        } else {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            $(this).text('☀️');
        }
    });

    // Toast message function for success and error alerts
    window.showToast = function(message, type) {
        var toast = $('<div class="toast toast-' + type + '">' + message + '</div>');

        $('body').append(toast);

        // Small delay to trigger animation
        setTimeout(function() {
            toast.addClass('show');
        }, 100);

        // Remove toast after few seconds
        setTimeout(function() {
            toast.removeClass('show');

            setTimeout(function() {
                toast.remove();
            }, 400);
        }, 3500);
    };

    // Common form validation function
    function validateForm($form) {
        var valid = true;

        $form.find('[required]').each(function() {
            var $input = $(this);
            var $group = $input.closest('.form-group');

            // Check empty required fields
            if (!$input.val() || $input.val().trim() === '') {
                $group.addClass('error');
                valid = false;
            } else {
                $group.removeClass('error');
            }

            // Basic email format validation
            if ($input.attr('type') === 'email' && $input.val()) {
                var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!re.test($input.val())) {
                    $group.addClass('error');
                    valid = false;
                }
            }
        });

        return valid;
    }

    // Remove error style when user starts typing
    $(document).on('input', '.form-group input, .form-group select, .form-group textarea', function() {
        var $group = $(this).closest('.form-group');

        if ($(this).val().trim() !== '') {
            $group.removeClass('error');
        }
    });

    // Switch between login form and register form
    $(document).on('click', '.toggle-register', function(e) {
        e.preventDefault();

        $('#loginForm').toggle();
        $('#registerForm').toggle();

        $('.auth-title').text($('#loginForm').is(':visible') ? 'Welcome Back' : 'Create Account');
        $('.auth-subtitle').text($('#loginForm').is(':visible') ? 'Sign in to your account' : 'Register as a new adopter');
    });

    // Login form submit using AJAX
    $(document).on('submit', '#loginForm', function(e) {
        e.preventDefault();

        if (!validateForm($(this))) return;

        $.ajax({
            url: 'process_ajax.php',
            type: 'POST',
            data: $(this).serialize() + '&action=login',
            dataType: 'json',

            beforeSend: function() {
                $('#loginForm .btn')
                    .prop('disabled', true)
                    .html('<span class="spinner"></span> Signing in...');
            },

            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');

                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 800);
                } else {
                    showToast(res.message, 'error');
                }
            },

            error: function() {
                showToast('Server error. Please try again.', 'error');
            },

            complete: function() {
                $('#loginForm .btn')
                    .prop('disabled', false)
                    .html('Sign In');
            }
        });
    });

    // Register form submit using AJAX
    $(document).on('submit', '#registerForm', function(e) {
        e.preventDefault();

        if (!validateForm($(this))) return;

        var password = $('#reg_password').val();
        var confirmPwd = $('#reg_confirm_password').val();

        // Check password confirmation
        if (password !== confirmPwd) {
            showToast('Passwords do not match!', 'error');
            return;
        }

        // Simple password length check
        if (password.length < 6) {
            showToast('Password must be at least 6 characters!', 'error');
            return;
        }

        $.ajax({
            url: 'process_ajax.php',
            type: 'POST',
            data: $(this).serialize() + '&action=register',
            dataType: 'json',

            beforeSend: function() {
                $('#registerForm .btn')
                    .prop('disabled', true)
                    .html('<span class="spinner"></span> Registering...');
            },

            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');

                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 800);
                } else {
                    showToast(res.message, 'error');
                }
            },

            error: function() {
                showToast('Server error. Please try again.', 'error');
            },

            complete: function() {
                $('#registerForm .btn')
                    .prop('disabled', false)
                    .html('Register');
            }
        });
    });

    // Filter pets by selected category tab
    $(document).on('click', '.filter-tab', function() {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');

        var category = $(this).data('category');

        if (category === 'all') {
            $('.pet-card').fadeIn(300);
        } else {
            $('.pet-card').each(function() {
                if ($(this).data('category') === category) {
                    $(this).fadeIn(300);
                } else {
                    $(this).fadeOut(200);
                }
            });
        }
    });

    // Search pets by name or breed
    $(document).on('input', '#searchPets', function() {
        var q = $(this).val().toLowerCase();

        $('.pet-card').each(function() {
            var name = $(this).data('name').toLowerCase();
            var breed = $(this).data('breed').toLowerCase();

            if (name.indexOf(q) > -1 || breed.indexOf(q) > -1) {
                $(this).fadeIn(200);
            } else {
                $(this).fadeOut(150);
            }
        });
    });

    // Open pets page by selected category
    $(document).on('click', '.category-card', function() {
        window.location.href = 'pets.php?category=' + $(this).data('category');
    });

    // Open adoption modal and set selected pet details
    $(document).on('click', '.btn-adopt', function() {
        $('#adoptPetId').val($(this).data('pet-id'));
        $('#adoptModal .modal-title').text('Adopt ' + $(this).data('pet-name'));
        $('#adoptModal').addClass('active');
    });

    // Close modal when clicking close button or outside modal content
    $(document).on('click', '.modal-close, .modal', function(e) {
        if (e.target === this) {
            $('.modal').removeClass('active');
        }
    });

    // Submit adoption request
    $(document).on('submit', '#adoptForm', function(e) {
        e.preventDefault();

        if (!validateForm($(this))) return;

        $.ajax({
            url: 'process_ajax.php',
            type: 'POST',
            data: $(this).serialize() + '&action=submit_adoption',
            dataType: 'json',

            beforeSend: function() {
                $('#adoptForm .btn')
                    .prop('disabled', true)
                    .html('<span class="spinner"></span> Submitting...');
            },

            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');

                    $('#adoptModal').removeClass('active');
                    $('#adoptForm')[0].reset();

                    if (res.redirect) {
                        setTimeout(function() {
                            window.location.href = res.redirect;
                        }, 1000);
                    }
                } else {
                    showToast(res.message, 'error');
                }
            },

            error: function() {
                showToast('Server error. Please try again.', 'error');
            },

            complete: function() {
                $('#adoptForm .btn')
                    .prop('disabled', false)
                    .html('Submit Request');
            }
        });
    });

    // Admin action for approving or rejecting adoption requests
    $(document).on('click', '.btn-accept, .btn-reject', function() {
        var requestId = $(this).data('id');
        var status = $(this).hasClass('btn-accept') ? 'Approved' : 'Rejected';

        $.ajax({
            url: 'process_ajax.php',
            type: 'POST',
            data: {
                action: 'update_request',
                request_id: requestId,
                status: status
            },
            dataType: 'json',

            beforeSend: function() {
                $('.btn-accept, .btn-reject').prop('disabled', true);
            },

            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    location.reload();
                } else {
                    showToast(res.message, 'error');
                }
            },

            error: function() {
                showToast('Server error.', 'error');
            },

            complete: function() {
                $('.btn-accept, .btn-reject').prop('disabled', false);
            }
        });
    });

});