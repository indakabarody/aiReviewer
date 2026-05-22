{**
 * templates/reviewButton.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Template to inject AI Review button into workflow.
 *}

<div
	id="aiReviewContainer"
	class="pkp_controllers_grid pkp_grid_category pkp_context_ai_reviewer_button"
	style="display:none; margin-bottom: 16px;"
>

	<div class="header">
		<h4>
			{translate key="plugins.generic.aiReviewer.displayName"}
		</h4>
	</div>

	<div
		style="
			padding: 16px;
			background: #fff;
			border-top: 1px solid #ddd;
		"
	>

		<p
			style="
				margin: 0 0 16px 0;
				line-height: 1.5;
				color: #444;
				font-size: 13px;
			"
		>
			{translate key="plugins.generic.aiReviewer.button.description"}
		</p>

		<div class="pkp_form_field" style="margin-bottom: 14px;">
			<label
				for="aiReviewLanguage"
				class="pkp_label"
				style="
					display:block;
					margin-bottom:6px;
					font-size:12px;
					font-weight:600;
					color:#222;
				"
			>
				Output Language
			</label>

			<select
				id="aiReviewLanguage"
				class="selectMenu"
				style="width:100%;"
			>
				<option value="English">English</option>
				<option value="Indonesian">Bahasa Indonesia</option>
				<option value="Arabic">العربية (Arabic)</option>
				<option value="Bulgarian">Български (Bulgarian)</option>
				<option value="Catalan">Català (Catalan)</option>
				<option value="Czech">Čeština (Czech)</option>
				<option value="Danish">Dansk (Danish)</option>
				<option value="Dutch">Nederlands (Dutch)</option>
				<option value="Finnish">Suomi (Finnish)</option>
				<option value="French">Français (French)</option>
				<option value="Galician">Galego (Galician)</option>
				<option value="Georgian">ქართული (Georgian)</option>
				<option value="German">Deutsch (German)</option>
				<option value="Armenian">Հայերեն (Armenian)</option>
				<option value="Italian">Italiano (Italian)</option>
				<option value="Macedonian">Македонски (Macedonian)</option>
				<option value="Malay">Bahasa Melayu (Malay)</option>
				<option value="Norwegian">Norsk (Norwegian)</option>
				<option value="Portuguese">Português (Portuguese)</option>
				<option value="Russian">Русский (Russian)</option>
				<option value="Slovenian">Slovenščina (Slovenian)</option>
				<option value="Spanish">Español (Spanish)</option>
				<option value="Swedish">Svenska (Swedish)</option>
				<option value="Turkish">Türkçe (Turkish)</option>
				<option value="Ukrainian">Українська (Ukrainian)</option>
				<option value="Chinese">中文 (Chinese)</option>
			</select>
		</div>

		<div class="pkp_form_field" style="margin-bottom: 18px;">
			<label
				for="aiReviewFormat"
				class="pkp_label"
				style="
					display:block;
					margin-bottom:6px;
					font-size:12px;
					font-weight:600;
					color:#222;
				"
			>
				Format Output
			</label>

			<select
				id="aiReviewFormat"
				class="selectMenu"
				style="width:100%;"
			>
				<option value="txt">.txt (Plain Text)</option>
				<option value="doc">.doc (MS Word)</option>
			</select>
		</div>

		<button
			type="button"
			id="generateAiReviewBtn"
			class="pkp_button submitFormButton"
			data-submission-id="{$aiSubmissionId}"
			style="
				width:100%;
				justify-content:center;
			"
		>
			{translate key="plugins.generic.aiReviewer.button.generate"}
		</button>

		<div
			id="aiReviewLoading"
			style="
				display:none;
				margin-top:14px;
				padding:10px 12px;
				background:#f5f8fa;
				border:1px solid #dce4ea;
				border-radius:3px;
				font-size:12px;
				color:#1e6292;
			"
		>
			<span class="pkp_spinner"></span>
			<em>
				{translate key="plugins.generic.aiReviewer.button.loading"}
			</em>
		</div>

		<div
			id="aiReviewResult"
			style="
				display:none;
				margin-top:16px;
				padding:14px;
				background:#fcfcfc;
				border:1px solid #d9d9d9;
				border-radius:3px;
				max-height:400px;
				overflow-y:auto;
				white-space:pre-wrap;
				word-break:break-word;
				font-size:12px;
				line-height:1.6;
				font-family:Menlo, Monaco, Consolas, monospace;
				box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
			"
		></div>

	</div>
</div>

<script>
    // In OJS 3, workflow tabs are loaded asynchronously via AJAX. 
    // We listen for the Review tab (stageId=3) to finish loading, then inject our button into its sidebar.
    $(document).ajaxComplete(function(event, xhr, settings) {ldelim}
        if (settings.url && settings.url.indexOf('stageId=3') !== -1) {ldelim}
            var container = $('#aiReviewContainer');
            if (container.length && !container.parent().hasClass('pkp_workflow_sidebar')) {ldelim}
                var sidebar = $('.pkp_workflow_sidebar:visible').first();
                if (sidebar.length) {ldelim}
                    container.prependTo(sidebar).show();
                {rdelim}
            {rdelim}
        {rdelim}
        
        // For Reviewer Role (reviewer/step/2616?step=3)
        if (settings.url && settings.url.indexOf('step=3') !== -1 && $('body').hasClass('pkp_page_reviewer')) {ldelim}
            var container = $('#aiReviewContainer');
            if (container.length && !container.parent().is('#reviewStep3')) {ldelim}
                container.prependTo('#reviewStep3').show();
                container.css('margin-bottom', '20px');
            {rdelim}
        {rdelim}
    {rdelim});

    $(function() {ldelim}
        // Initially hide until injected
        $('#aiReviewContainer').hide();
        
        // If already on reviewer step 3
        if ($('body').hasClass('pkp_page_reviewer') && $('#reviewStep3').length) {ldelim}
            $('#aiReviewContainer').prependTo('#reviewStep3').show();
            $('#aiReviewContainer').css('margin-bottom', '20px');
        {rdelim}

        $('#generateAiReviewBtn').on('click', function(e) {ldelim}
            e.preventDefault();
            
            var btn = $(this);
            var submissionId = btn.data('submission-id');
            var match = window.location.pathname.match(/\/step\/(\d+)/);
            var reviewAssignmentId = match ? match[1] : '';
            var responseLanguage = $('#aiReviewLanguage').val();
            var responseFormat = $('#aiReviewFormat').val();

            btn.prop('disabled', true);
            $('#aiReviewLoading').show();
            $('#aiReviewResult').hide();

            $.ajax({ldelim}
                url: '{url router=$smarty.const.ROUTE_PAGE page="aireviewer" op="generate"}',
                type: 'POST',
                data: {ldelim}
                    submissionId: submissionId,
                    reviewAssignmentId: reviewAssignmentId,
                    responseLanguage: responseLanguage,
                    responseFormat: responseFormat,
                    csrfToken: $('meta[name=csrf-token]').attr('content')
                {rdelim},
                success: function(response) {ldelim}
                    $('#aiReviewLoading').hide();
                    btn.prop('disabled', false);
                    
                    if (response.status) {ldelim}
                        $('#aiReviewResult').text(response.content).show();
                        
                        // Add a small notification about the file being saved
                        if ($('#aiFileSavedMsg').length === 0) {ldelim}
                            $('<div id="aiFileSavedMsg" style="margin-top:10px; padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:4px;">' +
                              '{translate key="plugins.generic.aiReviewer.button.saved"}' +
                              ' <a href="javascript:location.reload();" style="font-weight:bold; color:#155724; text-decoration:underline;">Refresh page</a> to see it in your Review Files list.' +
                              '</div>').insertAfter('#aiReviewResult');
                        {rdelim}
                    {rdelim} else {ldelim}
                        alert('Error: ' + response.content);
                    {rdelim}
                {rdelim},
                error: function(xhr, status, error) {ldelim}
                    $('#aiReviewLoading').hide();
                    btn.prop('disabled', false);
                    alert('AJAX Error: ' + error);
                {rdelim}
            {rdelim});
        {rdelim});
    {rdelim});
</script>
