<?php
import('lib.pkp.classes.form.Form');
class AiReviewerSettingsForm extends Form {

	/** @var AiReviewerPlugin  */
	public $plugin;

	/**
	 * @copydoc Form::__construct()
	 */
	public function __construct($plugin) {

		// Define the settings template and store a copy of the plugin object
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$this->plugin = $plugin;

		// Always add POST and CSRF validation to secure your form.
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Load settings already saved in the database
	 */
	public function initData() {
		$context = Application::get()->getRequest()->getContext();
		$this->setData('apiKey', $this->plugin->getSetting($context->getId(), 'apiKey'));
		$this->setData('aiModel', $this->plugin->getSetting($context->getId(), 'aiModel'));
		$this->setData('enableActivityLog', $this->plugin->getSetting($context->getId(), 'enableActivityLog'));
		$this->setData('customPrompt', $this->plugin->getSetting($context->getId(), 'customPrompt'));
		parent::initData();
	}

	/**
	 * Load data that was submitted with the form
	 */
	public function readInputData() {
		$this->readUserVars(['apiKey', 'aiModel', 'enableActivityLog', 'customPrompt']);
		parent::readInputData();
	}

	/**
	 * Fetch any additional data needed for your form.
	 *
	 * @return string
	 */
	public function fetch($request, $template = null, $display = false) {

		// Pass the plugin name to the template so that it can be
		// used in the URL that the form is submitted to
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->plugin->getName());

		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save the settings
	 *
	 * @return null|mixed
	 */
	public function execute(...$functionArgs) {
		$context = Application::get()->getRequest()->getContext();
		$this->plugin->updateSetting($context->getId(), 'apiKey', $this->getData('apiKey'));
		$this->plugin->updateSetting($context->getId(), 'aiModel', $this->getData('aiModel'));
		$this->plugin->updateSetting($context->getId(), 'enableActivityLog', $this->getData('enableActivityLog'), 'bool');
		$this->plugin->updateSetting($context->getId(), 'customPrompt', $this->getData('customPrompt'), 'string');

		// Tell the user that the save was successful.
		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification(
			Application::get()->getRequest()->getUser()->getId(),
			NOTIFICATION_TYPE_SUCCESS,
			['contents' => __('common.changesSaved')]
		);

		return parent::execute(...$functionArgs);
	}
}
