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
	data-generate-url="{url router=$smarty.const.ROUTE_PAGE page="aireviewer" op="generate"}"
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


