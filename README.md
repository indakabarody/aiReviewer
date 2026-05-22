# AI Reviewer Plugin for OJS 3.3

**Version:** 1.0.0  
**Author:** Indaka Barody  
**License:** GPL v3  

## Description
The AI Reviewer Plugin is a generic plugin for Open Journal Systems (OJS) 3.3 that assists editors and reviewers in evaluating submitted manuscripts using Artificial Intelligence (e.g., Gemini, Claude, GPT). It injects a "Generate AI Review" button directly into the review workflow, reads the manuscript text, and saves a comprehensive academic review as a Review File.

## Features
- **Multiple AI Models:** Choose between Gemini, Claude, and OpenAI models.
- **Dynamic Custom Prompts:** Customize the AI's review instructions or rubric directly from the plugin settings, or rely on the default comprehensive 7-point academic review format.
- **27 Output Languages:** Support for generating reviews in 27 different languages (including English, Indonesian, Arabic, French, Russian, Chinese, etc.).
- **Multiple Output Formats:** Save the generated AI review as either a `.txt` (Plain Text) or `.doc` (MS Word) file.
- **Activity Log Control:** Optionally suppress the noise of file upload events in the OJS Activity Log when the AI generates a file.
- **Seamless UI Integration:** A clean, modern interface seamlessly injected into the OJS workflow sidebar without disrupting the user experience.
- **Broad Localization:** The plugin UI itself is localized in 27 languages.

## Installation
1. Upload or clone this directory into your OJS installation at `plugins/generic/aiReviewer`.
2. Ensure the folder name is exactly `aiReviewer`.
3. Log in to OJS as an Administrator.
4. Go to **Settings > Website > Plugins**.
5. Find **AI Reviewer** under the *Generic Plugins* section.
6. Check the box to enable it.

## Configuration
1. After enabling, click the blue arrow next to the plugin name and select **Settings**.
2. **API Key:** Enter your secret API Key for your chosen AI provider.
3. **AI Model:** Select the AI model you wish to use from the dropdown list.
4. **Activity Log:** Check the box if you want AI file generation to appear in the Submission Activity Log.
5. **Custom Prompt Instructions:** (Optional) Enter custom instructions or a specific rubric for the AI Reviewer. If left blank, the default 7-point academic review format will be used.
6. Click **Save**.

## Usage
Once configured, navigate to any active submission and go to the **Review** tab (as an Editor or Reviewer). 
1. Look for the **AI Reviewer** section in the sidebar.
2. Select your desired **Output Language** and **Format** (.txt or .doc).
3. Click **Generate AI Review**.
4. The system will process the manuscript and automatically save the AI's review as a Submission File. Simply refresh the page to view it in the Review Files grid!

## Support
For issues or feature requests, please contact the author.
