<?php

class SC_mod_tools_kickstarter_index {
	/**
	 * The backend document
	 *
	 * @var    Object
	 */
	var $doc;

	/**
	 * The main method of the Plugin
	 *
	 * @return    Mixed        Either returns an error or sends a redirect header
	 */
	public function main() {
		// Set the path to kickstarter
		$extPath = t3lib_extMgm::extPath('kickstarter');
		require_once $extPath . 'modfunc1/class.tx_kickstarter_modfunc1.php';
		$modfunc1 = t3lib_div::makeInstance('tx_kickstarter_modfunc1');
		$modfunc1->pObj->doc = t3lib_div::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		return $modfunc1->main();
	}
}

// Proceed if BE loaded
if (!defined("TYPO3_MODE")) {
	die ("Access denied.");
}
if (TYPO3_MODE == "BE") {
	// Apply access restrictions
	$BE_USER->modAccess($MCONF, 1);
	// Make instance:
	$SOBE = t3lib_div::makeInstance('SC_mod_tools_kickstarter_index');
	echo $SOBE->main();
} else {
	echo '<h1>Error</h1><p>The TYPO3 Backend is required for phpMyAdmin module but was not loaded.</p>';
}
?>