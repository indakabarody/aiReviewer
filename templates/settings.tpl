{**
 * templates/settings.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the aiReviewer plugin.
 *}
<script>
	$(function() {ldelim}
		$('#aiReviewerSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form
	class="pkp_form"
	id="aiReviewerSettings"
	method="POST"
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	<!-- Always add the csrf token to secure your form -->
	{csrf}

	{fbvFormArea id="aiReviewerSettingsArea"}
		{fbvFormSection label="plugins.generic.aiReviewer.settings.apiKey"}
			{fbvElement
				type="text"
				id="apiKey"
				value=$apiKey
				description="plugins.generic.aiReviewer.settings.apiKey.description"
			}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.aiReviewer.settings.aiModel"}
			{fbvElement type="select" id="aiModel" from=array(
				"gemini-1.5-pro"=>"Gemini 1.5 Pro",
				"gemini-1.5-flash"=>"Gemini 1.5 Flash",
				"gemini-2.0-flash"=>"Gemini 2.0 Flash",
				"gemini-2.5-flash"=>"Gemini 2.5 Flash",
				"gemini-2.5-pro"=>"Gemini 2.5 Pro",
				"claude-3-haiku-20240307"=>"Claude 3 Haiku",
				"claude-3-sonnet-20240229"=>"Claude 3 Sonnet",
				"claude-3-opus-20240229"=>"Claude 3 Opus",
				"claude-3-5-sonnet-20240620"=>"Claude 3.5 Sonnet",
				"gpt-3.5-turbo"=>"OpenAI GPT-3.5 Turbo",
				"gpt-4-turbo"=>"OpenAI GPT-4 Turbo",
				"gpt-4o"=>"OpenAI GPT-4o",
				"gpt-4o-mini"=>"OpenAI GPT-4o Mini",
				"o1-mini"=>"OpenAI o1 Mini",
				"o3-mini"=>"OpenAI o3 Mini"
			) selected=$aiModel translate=false}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="enableActivityLog" value=1 checked=$enableActivityLog label="plugins.generic.aiReviewer.settings.enableActivityLog"}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.aiReviewer.settings.customPrompt"}
			{fbvElement type="textarea" id="customPrompt" value=$customPrompt rich=false description="plugins.generic.aiReviewer.settings.customPrompt.description" rows=10}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
