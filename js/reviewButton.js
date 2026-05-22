$(function() {
    // Save a copy of the HTML so we can recreate it if OJS destroys the DOM element during AJAX tab switching
    var buttonHtml = $('#aiReviewContainer')[0] ? $('#aiReviewContainer')[0].outerHTML : '';
    
    // Initially hide until injected
    $('#aiReviewContainer').hide();
    
    // Function to inject button to the correct place
    function injectAiButton() {
        // If the container was destroyed (e.g. by tab switch) but we have the HTML saved, recreate it
        if ($('#aiReviewContainer').length === 0 && buttonHtml) {
            $('body').append(buttonHtml);
            $('#aiReviewContainer').hide(); // hide initially before we move it
        }
        
        var container = $('#aiReviewContainer');
        if (!container.length) return;
        
        // Editor Workflow (All tabs: Submission, Review, Copyediting, Production)
        if ($('.pkp_workflow_sidebar:visible').length) {
            var sidebar = $('.pkp_workflow_sidebar:visible').first();
            if (!container.parent().is(sidebar)) {
                container.prependTo(sidebar).show();
            }
        }
        
        // Reviewer Workflow (reviewer/step/... step=3)
        if ($('body').hasClass('pkp_page_reviewer')) {
            if ($('#reviewStep3').length) {
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
        // Run on ANY stage load or tab change (stageId=1,2,3,4,5) or step=3
        if (settings.url && (settings.url.indexOf('stageId=') !== -1 || settings.url.indexOf('step=3') !== -1)) {
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
        if (!generateUrl) {
            alert("Error: Generate URL is missing.");
            return;
        }

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
