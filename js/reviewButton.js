$(function() {
    // Initially hide until injected
    $('#aiReviewContainer').hide();
    
    // Function to inject button to the correct place
    function injectAiButton() {
        var container = $('#aiReviewContainer');
        if (!container.length) return;
        
        // Editor Workflow
        if (container.length && !container.parent().hasClass('pkp_workflow_sidebar')) {
            var sidebar = $('.pkp_workflow_sidebar:visible').first();
            if (sidebar.length) {
                container.prependTo(sidebar).show();
            }
        }
        
        // Reviewer Workflow (reviewer/step/... step=3)
        if ($('body').hasClass('pkp_page_reviewer')) {
            // Because OJS re-renders the tabs, we need to clone the original or just move it.
            // But since the original might have been destroyed if it was inside a tab, we should keep it safe.
            // Wait, if it's already moved into the DOM, it's fine.
            if ($('#reviewStep3').length) {
                // We only move it if it's not already there
                if ($('#reviewStep3 #aiReviewContainer').length === 0) {
                    container.prependTo('#reviewStep3').show();
                    container.css('margin-bottom', '20px');
                }
            }
        }
    }

    // Run initially for any direct page loads
    setTimeout(injectAiButton, 500);

    // Also run on AJAX complete (when tabs are loaded)
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && (settings.url.indexOf('stageId=3') !== -1 || settings.url.indexOf('step=3') !== -1)) {
            setTimeout(injectAiButton, 500);
        }
    });

    // Delegate click handler to body so it survives DOM moving/re-rendering
    $('body').on('click', '#generateAiReviewBtn', function(e) {
        e.preventDefault();
        
        var btn = $(this);
        var submissionId = btn.data('submission-id');
        var match = window.location.pathname.match(/\/step\/(\d+)/);
        var reviewAssignmentId = match ? match[1] : '';
        var responseLanguage = $('#aiReviewLanguage').val();
        var responseFormat = $('#aiReviewFormat').val();
        
        // The URL is now passed via data attribute on the container
        var generateUrl = $('#aiReviewContainer').data('generate-url');

        btn.prop('disabled', true);
        $('#aiReviewLoading').show();
        $('#aiReviewResult').hide();

        $.ajax({
            url: generateUrl,
            type: 'POST',
            data: {
                submissionId: submissionId,
                reviewAssignmentId: reviewAssignmentId,
                responseLanguage: responseLanguage,
                responseFormat: responseFormat,
                csrfToken: $('meta[name=csrf-token]').attr('content')
            },
            success: function(response) {
                btn.prop('disabled', false);
                $('#aiReviewLoading').hide();
                $('#aiReviewResult').show();
                
                if (response.status) {
                    $('#aiReviewResult').html(
                        '<div style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:4px;">' +
                        '<strong>Review generated successfully!</strong><br>' +
                        'The AI Review has been saved as a Submission File.<br>' +
                        '<a href="javascript:location.reload();" style="font-weight:bold; color:#155724; text-decoration:underline; display:inline-block; margin-top:5px;">Click here to refresh the page</a> to view it in your Review Files list.' +
                        '</div>'
                    );
                } else {
                    $('#aiReviewResult').css('color', 'red').text('Error: ' + response.content);
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false);
                $('#aiReviewLoading').hide();
                $('#aiReviewResult').show().css('color', 'red').text('Error: ' + error);
            }
        });
    });
});
