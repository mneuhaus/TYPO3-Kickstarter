<?php
namespace Tx\kickstarter\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Philipp Gampe <philipp.gampe@typo3.org>
 *  All rights reserved
 *
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * Module 'Tools > Kickstarter' for the 'kickstarter' extension
 *
 * @author 		François Suter <francois@typo3.org>
 * @author 		Christian Jul Jensen <julle@typo3.org>
 * @author 		Ingo Renner <ingo@typo3.org>
 */
class KickstarterModuleController extends \TYPO3\CMS\Backend\Module\BaseScriptClass {

	/**
	 * Back path to typo3 main dir
	 *
	 * @var string $backPath
	 */
	public $backPath;

	/** @var \TYPO3\CMS\Backend\Template\DocumentTemplate */
	public $doc = NULL;

	/**
	 * Array containing all messages issued by the application logic
	 * Contains the error's severity and the message itself
	 *
	 * @var array $messages
	 */
	protected $messages = array();

	/**
	 * @var string Key of the CSH file
	 */
	protected $cshKey;

	/**
	 * @var \tx_kickstarter_wizard Local kickstarter wizard instance
	 */
	protected $kickstarter;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:kickstarter/modfunc1/locallang.xml');
		$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], TRUE);
		$this->backPath = $GLOBALS['BACK_PATH'];
		// Set key for CSH
		$this->cshKey = '_MOD_' . $GLOBALS['MCONF']['name'];
	}

	/**
	 * Initializes the backend module
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		// Initialize document
		$this->doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		$this->doc->setModuleTemplate(ExtensionManagementUtility::extPath('kickstarter') . 'modfunc1/modfunc1_template.html');
		$this->pageRenderer = $this->doc->getPageRenderer();
		$this->doc->backPath = $this->backPath;
		$this->doc->bodyTagId = 'typo3-mod-php';
		$this->doc->bodyTagAdditions = 'class="tx_kickstarter_modfunc1"';
		// Create kickstarter instance
		$this->kickstarter = GeneralUtility::makeInstance('tx_kickstarter_wizard');
		$this->kickstarter->color = array($this->doc->bgColor5, $this->doc->bgColor4, $this->doc->bgColor);
		$this->kickstarter->siteBackPath = $this->doc->backPath . '../';
		$this->kickstarter->pObj = $this;
		$this->kickstarter->EMmode = 1;
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return void
	 */
	public function menuConfig() {
		$this->MOD_MENU = array(
			'function' => array(
				'create' => $GLOBALS['LANG']->getLL('moduleFunction.tx_kickstarter_modfunc1'),
				'edit' => $GLOBALS['LANG']->getLL('moduleFunction.tx_kickstarter_modfunc2'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return void
	 */
	public function main() {
		// Access check!
		// The page will show only if user has admin rights
		if ($GLOBALS['BE_USER']->user['admin']) {
			// Set the form
			//$this->doc->form = '<form name="tx_kickstarter_form" id="tx_kickstarter_form" method="post" action="">';
			// Prepare main content
			$this->content = $this->doc->header($GLOBALS['LANG']->getLL('function.' . $this->MOD_SETTINGS['function']));
			$this->content .= $this->getModuleContent();
		} else {
			// If no access, only display the module's title
			$this->content = $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->spacer(5);
		}
		// Place content inside template
		$content = $this->doc->moduleBody(array(), $this->getDocHeaderButtons(), $this->getTemplateMarkers());
		// Renders the module page
		$this->content = $this->doc->render($GLOBALS['LANG']->getLL('title'), $content);
	}

	/**
	 * Generate the module's content
	 *
	 * @return string HTML of the module's main content
	 */
	protected function getModuleContent() {
		$content = '';
		$sectionTitle = '';
		// Handle chosen action
		switch ((string) $this->MOD_SETTINGS['function']) {
			case 'create':
				$content = $this->kickstarter->mgm_wizard();
				break;
			case 'edit':
				if (!$this->kickstarter->modData['wizArray_ser']) {
					$this->kickstarter->modData['wizArray_ser'] = base64_encode($this->getWizardFormDat());
				}
				$content .= $this->getEditableExtensionsMenu();
				$content .= $this->kickstarter->mgm_wizard();
				break;
		}
		// Wrap the content in a section
		return $this->doc->section($sectionTitle, '<div class="tx_kickstarter_modfunc1">' . $content . '</div>', 0, 1);
	}

	/**
	 * This method actually prints out the module's HTML content
	 *
	 * @return void
	 */
	public function render() {
		echo $this->content;
	}

	/**
	 * Fetch form data from file (doc/wizard_form.dat) if it is present
	 *
	 * @return string Formdata if the file was found, otherwise an empty string
	 */
	protected function getWizardFormDat() {
		$result = '';
		if (!empty($this->CMD['showExt'])) {
			/** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
			$packageManager = GeneralUtility::makeInstance('TYPO3\CMS\Core\Package\PackageManager');
			$package = $packageManager->getPackage($this->CMD['showExt']);
			$absPath = $package->getPackagePath();
			$result = @is_file($absPath . 'doc/wizard_form.dat') ? GeneralUtility::getUrl($absPath . 'doc/wizard_form.dat') : '';
		}
		return $result;
	}

	protected function getEditableExtensionsMenu() {
		$editableExtensions = array();
		$content = '';
		/** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
		$packageManager = GeneralUtility::makeInstance('TYPO3\CMS\Core\Package\PackageManager');
		$packageList = $packageManager->getAvailablePackages();
		 /** @var \TYPO3\Flow\Package\PackageInterface $package */
		foreach ($packageList as $package) {
			if (@is_file($package->getPackagePath() . 'doc/wizard_form.dat')) {
				$editableExtensions[] = $package->getPackageKey();
			}
		}
		$content .= '<form id="tx_kickstarter_edit_select" method="post" action="' . BackendUtility::getModuleUrl(GeneralUtility::_GET('M')) .'">';
		$content .= '<select name="CMD[showExt]">';
		foreach ($editableExtensions as $extension) {
			$content .= '<option value="' . $extension . '"' . (($extension === $this->CMD['showExt']) ? ' selected="selected"' : '')  . '>' . $extension .'</option>';
		}
		$content .= '</select><input type="submit"></form>';
		return $content;
	}

	/*************************
	 *
	 * APPLICATION LOGIC UTILITIES
	 *
	 *************************/
	/**
	 * This method is used to add a message to the internal queue
	 *
	 * @param string $message The message itself
	 * @param integer $severity Message level (according to \TYPO3\CMS\Core\Messaging\FlashMessage class constants)
	 * @return void
	 */
	public function addMessage($message, $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::OK) {
		$flashMessage = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $message, '', $severity);
		/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
		/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
	}

	/*************************
	 *
	 * RENDERING UTILITIES
	 *
	 *************************/
	/**
	 * Gets the filled markers that are used in the HTML template.
	 *
	 * @return array The filled marker array
	 */
	protected function getTemplateMarkers() {
		$markers = array(
			//'CSH' => BackendUtility::wrapInHelp('_MOD_system_txschedulerM1', ''),
			'CSH' => '',
			'FUNC_MENU' => $this->getFunctionMenu(),
			'CONTENT' => $this->content,
			'TITLE' => $GLOBALS['LANG']->getLL('title')
		);
		return $markers;
	}

	/**
	 * Gets the function menu selector for this backend module.
	 *
	 * @return string The HTML representation of the function menu selector
	 */
	protected function getFunctionMenu() {
		$functionMenu = BackendUtility::getFuncMenu(0, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']);
		return $functionMenu;
	}

	/**
	 * Gets the buttons that shall be rendered in the docHeader.
	 *
	 * @return array Available buttons for the docHeader
	 */
	protected function getDocHeaderButtons() {
		$buttons = array(
			'addtask' => '',
			'close' => '',
			'save' => '',
			'saveclose' => '',
			'delete' => '',
			'reload' => '',
			'shortcut' => $this->getShortcutButton()
		);
		$buttons['reload'] = '<a href="' . $GLOBALS['MCONF']['_'] . '" title="'
			. $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.reload', TRUE)
			. '">' . IconUtility::getSpriteIcon('actions-system-refresh') . '</a>';
		return $buttons;
	}

	/**
	 * Gets the button to set a new shortcut in the backend (if current user is allowed to).
	 *
	 * @return string HTML representation of the shortcut button
	 */
	protected function getShortcutButton() {
		$result = '';
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$result = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}
		return $result;
	}

}
