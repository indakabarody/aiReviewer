<?php
/**
 * @file AiReviewerPlugin.inc.php
 *
 * Copyright (c) 2017-2021 Simon Fraser University
 * Copyright (c) 2017-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AiReviewerPlugin
 * @brief Plugin class for the AI Reviewer plugin.
 */

$autoload = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once($autoload);
} else {
    // Fallback if composer not run
    require_once(dirname(__FILE__) . '/classes/TextExtractor.inc.php');
}

import('lib.pkp.classes.plugins.GenericPlugin');

class AiReviewerPlugin extends GenericPlugin {

	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if ($success && $this->getEnabled()) {
			// Inject button HTML via HeaderActions
			HookRegistry::register('Template::Layout::Backend::HeaderActions', [$this, 'addReviewButtonHtml']);
			
			// Inject JS securely by hooking into display
			HookRegistry::register('TemplateManager::display', [$this, 'addReviewButtonJs']);
			
			// Register handler for backend AI requests
			HookRegistry::register('LoadHandler', [$this, 'setupCallbackHandler']);
		}
		return $success;
	}

	/**
	 * Route requests to the AiReviewerHandler
	 */
	public function setupCallbackHandler($hookName, $params) {
		$page = $params[0];
		if ($page == 'aireviewer') {
			$this->import('AiReviewerHandler');
			define('HANDLER_CLASS', 'AiReviewerHandler');
			return true;
		}
		return false;
	}

	/**
	 * Provide a name for this plugin
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return __('plugins.generic.aiReviewer.displayName');
	}

	/**
	 * Provide a description for this plugin
	 *
	 * @return string
	 */
	public function getDescription() {
		return __('plugins.generic.aiReviewer.description');
	}

	/**
	 * Add a settings action to the plugin's entry in the
	 * plugins list.
	 *
	 * @param Request $request
	 * @param array $actionArgs
	 * @return array
	 */
	public function getActions($request, $actionArgs) {
		// Get the existing actions
		$actions = parent::getActions($request, $actionArgs);

		// Only add the settings action when the plugin is enabled
		if (!$this->getEnabled()) {
			return $actions;
		}

		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$linkAction = new LinkAction(
			'settings',
			new AjaxModal(
				$router->url(
					$request,
					null,
					null,
					'manage',
					null,
					[
						'verb' => 'settings',
						'plugin' => $this->getName(),
						'category' => 'generic'
					]
				),
				$this->getDisplayName()
			),
			__('manager.plugins.settings'),
			null
		);

		array_unshift($actions, $linkAction);
		return $actions;
	}

	/**
	 * Show and save the settings form when the settings action
	 * is clicked.
	 *
	 * @param array $args
	 * @param Request $request
	 * @return JSONMessage
	 */
	public function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				// Load the custom form
				$this->import('AiReviewerSettingsForm');
				$form = new AiReviewerSettingsForm($this);

				if (!$request->getUserVar('save')) {
					$form->initData();
					return new JSONMessage(true, $form->fetch($request));
				}

				$form->readInputData();
				if ($form->validate()) {
					$form->execute();
					return new JSONMessage(true);
				}
		}
		return parent::manage($args, $request);
	}

	/**
	 * Inject the JS file before the page is rendered.
	 */
	function addReviewButtonJs($hookName, $params) {
		$templateMgr = $params[0];
		$template = $params[1];
		
		// Only inject on Reviewer or Editor workflow pages
		if (strpos($template, 'reviewer/review/reviewStepHeader.tpl') !== false || 
		    strpos($template, 'workflow/workflow.tpl') !== false) {
			$request = Application::get()->getRequest();
			$templateMgr->addJavaScript(
				'aiReviewerJs',
				$request->getBaseUrl() . '/' . $this->getPluginPath() . '/js/reviewButton.js',
				['contexts' => 'backend']
			);
		}
		
		return false;
	}

	/**
	 * Add the review button HTML to the header actions.
	 *
	 * @param string $hookName string
	 * @param array $params
	 * @return boolean
	 */
	function addReviewButtonHtml($hookName, $params) {
		$smarty = $params[1];
		$output =& $params[2];
		
		$request = Application::get()->getRequest();
		$router = $request->getRouter();
		
		$submissionId = $request->getUserVar('submissionId');
		if (!$submissionId) {
			$args = $router->getRequestedArgs($request);
			if (!empty($args) && is_numeric($args[0])) {
				$submissionId = $args[0];
			}
		}

		if (!$submissionId) return false;
		
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('aiSubmissionId', $submissionId);
		$pluginTemplate = $this->getTemplateResource('reviewButton.tpl');
		
		$injectedHtml = "\n<!-- AiReviewerPlugin Injection Start -->\n";
		$injectedHtml .= $templateMgr->fetch($pluginTemplate);
		$injectedHtml .= "\n<!-- AiReviewerPlugin Injection End -->\n";

		$output .= $injectedHtml;

		return false;
	}
}
