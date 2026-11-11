/* DMR Frontend JS Scripts */
jQuery(document).ready(function($) {
    // Dynamic Registration Form submission
    $('#dmr-dynamic-registration-form').on('submit', function(e) {
        e.preventDefault();
        var alertBox = $('#dmr-alert-box');
        var submitBtn = $('#dmr-submit-btn');

        alertBox.hide().removeClass('dmr-alert-success dmr-alert-error');
        submitBtn.prop('disabled', true).text('Submitting Application...');

        var formData = $(this).serialize() + '&action=dmr_submit_registration&nonce=' + dmrFront.nonce;

        $.post(dmrFront.ajax_url, formData, function(res) {
            submitBtn.prop('disabled', false).text('Submit Application →');

            if (res.success) {
                // Populate modal details
                $('#dmr-modal-assigned-id').text(res.data.member_id || 'Pending');
                
                // Reset form fields
                if ($('#dmr-dynamic-registration-form')[0]) {
                    $('#dmr-dynamic-registration-form')[0].reset();
                }

                // Show In-Page Success Popup Modal
                $('#dmrRegistrationSuccessModal').css('display', 'flex').hide().fadeIn(250);
            } else {
                alertBox.html('<div class="dmr-alert dmr-alert-error" style="background:#FEE2E2; border:1px solid #FECACA; color:#DC2626; padding:12px 16px; border-radius:8px; font-weight:600;">' + (res.data.message || 'Registration failed.') + '</div>').fadeIn();
                $('html, body').animate({ scrollTop: alertBox.offset().top - 100 }, 400);
            }
        }).fail(function() {
            submitBtn.prop('disabled', false).text('Submit Application →');
            alertBox.html('<div class="dmr-alert dmr-alert-error" style="background:#FEE2E2; border:1px solid #FECACA; color:#DC2626; padding:12px 16px; border-radius:8px; font-weight:600;">Server error. Please try again.</div>').fadeIn();
        });
    });

    // Handle OK click on Registration Success Modal -> Redirect to Home
    $(document).on('click', '#dmr-modal-ok-redirect-btn', function(e) {
        e.preventDefault();
        var targetHome = (typeof dmrFront !== 'undefined' && dmrFront.home_url) ? dmrFront.home_url : '/';
        window.location.href = targetHome;
    });

    // Frontend Sign In Form
    $('#dmr-login-form').on('submit', function(e) {
        e.preventDefault();
        var alertBox = $('#dmr-login-alert');
        var submitBtn = $('#dmr-login-btn');

        alertBox.hide().removeClass('dmr-alert-success dmr-alert-error');
        submitBtn.prop('disabled', true).text('Signing in...');

        var formData = $(this).serialize() + '&action=dmr_user_login&nonce=' + dmrFront.nonce;

        $.post(dmrFront.ajax_url, formData, function(res) {
            submitBtn.prop('disabled', false).text('Sign In');

            if (res.success) {
                alertBox.html('<div class="dmr-alert dmr-alert-success">' + res.data.message + '</div>').fadeIn();
                setTimeout(function() {
                    window.location.href = res.data.redirect;
                }, 1000);
            } else {
                alertBox.html('<div class="dmr-alert dmr-alert-error">' + res.data.message + '</div>').fadeIn();
            }
        }).fail(function() {
            submitBtn.prop('disabled', false).text('Sign In');
            alertBox.html('<div class="dmr-alert dmr-alert-error">Failed to communicate with authentication server.</div>').fadeIn();
        });
    });

    // Frontend Contact Us Form
    $('#dmr-contact-form').on('submit', function(e) {
        e.preventDefault();
        var alertBox = $('#dmr-contact-alert');
        var submitBtn = $('#dmr-contact-btn');

        alertBox.hide().removeClass('dmr-alert-success dmr-alert-error');
        submitBtn.prop('disabled', true).text('Sending Message...');

        var formData = $(this).serialize() + '&action=dmr_submit_contact&nonce=' + dmrFront.nonce;

        $.post(dmrFront.ajax_url, formData, function(res) {
            submitBtn.prop('disabled', false).text('Send Message');

            if (res.success) {
                alertBox.html('<div class="dmr-alert dmr-alert-success">' + res.data.message + '</div>').fadeIn();
                $('#dmr-contact-form')[0].reset();
            } else {
                alertBox.html('<div class="dmr-alert dmr-alert-error">' + res.data.message + '</div>').fadeIn();
            }
        }).fail(function() {
            submitBtn.prop('disabled', false).text('Send Message');
            alertBox.html('<div class="dmr-alert dmr-alert-error">Server communication error. Please try again.</div>').fadeIn();
        });
    });
});

