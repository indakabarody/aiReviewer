<?php

/**
 * @file AiReviewerHandler.inc.php
 *
 * @class AiReviewerHandler
 * @brief Handle requests for AI review generation.
 */

import('classes.handler.Handler');

class AiReviewerHandler extends Handler
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		// Require submission access
		$this->addRoleAssignment([ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER], ['generate']);
	}

	/**
	 * @copydoc Handler::authorize()
	 */
	public function authorize($request, &$args, $roleAssignments)
	{
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Generate an AI review for a submission.
	 *
	 * @param array $args
	 * @param Request $request
	 * @return JSONMessage
	 */
	public function generate($args, $request)
	{
		$submissionId = (int) $request->getUserVar('submissionId');
		$context = $request->getContext();

		$plugin = PluginRegistry::getPlugin('generic', 'aireviewerplugin');
		if (!$plugin) {
			return new JSONMessage(false, 'AI Reviewer Plugin not initialized.');
		}

		$apiKey = $plugin->getSetting($context->getId(), 'apiKey');
		$aiModel = $plugin->getSetting($context->getId(), 'aiModel');

		if (empty($apiKey)) {
			return new JSONMessage(false, 'API Key is missing. Please configure it in the plugin settings.');
		}

		$submissionDao = DAORegistry::getDAO('SubmissionDAO');
		$submission = $submissionDao->getById($submissionId, $context->getId());

		if (!$submission) {
			return new JSONMessage(false, 'Submission not found.');
		}

		import('lib.pkp.classes.submission.SubmissionFile');

		// Attempt to get the main submission file (2 = SUBMISSION_FILE_SUBMISSION)
		$submissionFilesIterator = Services::get('submissionFile')->getMany([
			'submissionIds' => [$submissionId],
			'fileStages' => [2], // 2 = SUBMISSION_FILE_SUBMISSION
		]);

		$submissionFiles = iterator_to_array($submissionFilesIterator);

		if (empty($submissionFiles)) {
			// Fallback to review file (4 = SUBMISSION_FILE_REVIEW_FILE)
			$submissionFilesIterator = Services::get('submissionFile')->getMany([
				'submissionIds' => [$submissionId],
				'fileStages' => [4], // 4 = SUBMISSION_FILE_REVIEW_FILE
			]);
			$submissionFiles = iterator_to_array($submissionFilesIterator);
		}

		if (empty($submissionFiles)) {
			return new JSONMessage(false, 'No manuscript file found to analyze.');
		}

		$file = current($submissionFiles);
		$fileId = $file->getData('fileId');
		$pkpFile = Services::get('file')->get($fileId);

		$fileContents = Services::get('file')->fs->read($pkpFile->path);
		$tempFilePath = tempnam(sys_get_temp_dir(), 'aireview_src_');
		file_put_contents($tempFilePath, $fileContents);

		$originalName = $file->getLocalizedData('name') ?: $pkpFile->path;
		$extension = pathinfo($originalName, PATHINFO_EXTENSION);

		require_once dirname(__FILE__) . '/classes/TextExtractor.inc.php';
		$text = TextExtractor::extractText($tempFilePath, $extension);
		unlink($tempFilePath); // Cleanup the temp file

		if (!$text) {
			return new JSONMessage(false, 'Failed to extract text. Supported formats are DOCX and PDF (if composer is installed).');
		}

		$responseLanguage = $request->getUserVar('responseLanguage') ?: 'English';
		$customPrompt = $plugin->getSetting($context->getId(), 'customPrompt');

		require_once dirname(__FILE__) . '/classes/AiService.inc.php';
		$review = AiService::generateReview($text, $aiModel, $apiKey, $responseLanguage, $customPrompt);

		if (strpos($review, 'Error') === 0 || strpos($review, 'API Error') === 0) {
			return new JSONMessage(false, $review);
		}

		$responseFormat = $request->getUserVar('responseFormat') === 'doc' ? 'doc' : 'txt';

		$reviewFileContent = $review;
		if ($responseFormat === 'doc') {
			if (class_exists('\Michelf\Markdown')) {
				$htmlReview = \Michelf\Markdown::defaultTransform($review);
			} else {
				$htmlReview = nl2br(htmlspecialchars($review));
			}
			$reviewFileContent = "<html><head><meta charset='utf-8'></head><body>" . $htmlReview . '</body></html>';
		}

		// Attempt to save review as a Review File for the submission
		try {
			if ($submission) {
				$tempFileName = tempnam(sys_get_temp_dir(), 'aireview_');
				file_put_contents($tempFileName, $reviewFileContent);

				$genreDao = DAORegistry::getDAO('GenreDAO');
				$genres = $genreDao->getByContextId($context->getId());
				$genreId = null;
				while ($genre = $genres->next()) {
					// Try to find a document genre, defaults to the first one if necessary
					if ($genre->getCategory() == GENRE_CATEGORY_DOCUMENT) {
						$genreId = $genre->getId();
						break;
					}
				}

				// If no document genre found, just pick the first available genre to avoid foreign key constraints failing
				if (!$genreId) {
					$genres = $genreDao->getByContextId($context->getId());
					if ($genre = $genres->next()) {
						$genreId = $genre->getId();
					}
				}

				if ($genreId) {
					import('lib.pkp.classes.file.FileManager');

					$submissionDir = Services::get('submissionFile')->getSubmissionDir($context->getId(), $submission->getId());
					$fileExt = $responseFormat === 'doc' ? 'doc' : 'txt';
					$fileName = 'AI_Review_Result.' . $fileExt;

					$fileId = Services::get('file')->add($tempFileName, $submissionDir . '/' . uniqid() . '.' . $fileExt);

					$reviewAssignmentId = (int) $request->getUserVar('reviewAssignmentId');
					$params = [
						'fileId' => $fileId,
						'submissionId' => $submissionId,
						'uploaderUserId' => (int) $request->getUser()->getId(),
						'genreId' => $genreId,
						'fileStage' => 4, // SUBMISSION_FILE_REVIEW_FILE
						'name' => [$submission->getLocale() => $fileName],
					];

					if ($reviewAssignmentId) {
						$params['assocType'] = ASSOC_TYPE_REVIEW_ASSIGNMENT;
						$params['assocId'] = $reviewAssignmentId;
					}

					$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
					$submissionFile = $submissionFileDao->newDataObject();
					$submissionFile->setAllData($params);

					// Check if activity log is enabled
					$enableActivityLog = $plugin->getSetting($context->getId(), 'enableActivityLog');

					// Use Service to insert DB record
					$insertedFile = Services::get('submissionFile')->add($submissionFile, $request);

					if ($insertedFile) {
						if (!$enableActivityLog) {
							// Remove the file upload log
							$submissionFileEventLogDao = DAORegistry::getDAO('SubmissionFileEventLogDAO');
							$fileLogs = $submissionFileEventLogDao->getByAssoc(ASSOC_TYPE_SUBMISSION_FILE, $insertedFile->getId());
							if ($log = $fileLogs->next()) {
								if (in_array($log->getMessage(), ['submission.event.fileUploaded', 'submission.event.fileRevised'])) {
									$submissionFileEventLogDao->deleteById($log->getId());
								}
							}
							
							// Remove the submission upload log
							$submissionEventLogDao = DAORegistry::getDAO('SubmissionEventLogDAO');
							$subLogs = $submissionEventLogDao->getByAssoc(ASSOC_TYPE_SUBMISSION, $submissionId);
							// Since getByAssoc orders by log_id DESC, we just check the first few
							$limit = 3;
							while (($log = $subLogs->next()) && $limit > 0) {
								if (in_array($log->getMessage(), ['submission.event.fileUploaded', 'submission.event.fileRevised'])) {
									$params = $log->getParams();
									if (isset($params['submissionFileId']) && $params['submissionFileId'] == $insertedFile->getId()) {
										$submissionEventLogDao->deleteById($log->getId());
										break;
									}
								}
								$limit--;
							}
						}
						// Link it to the Review Round so it appears in the grid
						$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
						$reviewRound = null;
						if ($reviewAssignmentId) {
							$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
							$reviewAssignment = $reviewAssignmentDao->getById($reviewAssignmentId);
							if ($reviewAssignment) {
								$reviewRound = $reviewRoundDao->getById($reviewAssignment->getReviewRoundId());
							}
						} else {
							$reviewRound = $reviewRoundDao->getLastReviewRoundBySubmissionId($submissionId);
						}

						if ($reviewRound) {
							$submissionFileDao->assignRevisionToReviewRound($insertedFile->getId(), $reviewRound);
						}
					} else {
						error_log('AiReviewerHandler: Failed to insert SubmissionFile to database.');
					}
				} else {
					error_log('AiReviewerHandler: No valid Genre found to attach the file.');
				}

				if (file_exists($tempFileName)) {
					unlink($tempFileName); // Cleanup in case insertObject copied it rather than moving
				}
			} // close if ($submission)
		} catch (Exception $e) {
			error_log('AiReviewerHandler Error saving file: ' . $e->getMessage());
		}

		return new JSONMessage(true, $review);
	}
}
