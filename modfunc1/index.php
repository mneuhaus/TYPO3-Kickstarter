<?php
// Proceed if BE loaded
if (!defined("TYPO3_MODE")) {
	die ("Access denied.");
}
if (TYPO3_MODE == "BE") {
	// Apply access restrictions
	$BE_USER->modAccess($MCONF, 1);
	/** @var tx_kickstarter_modfunc1 $SOBE */
	$SOBE = t3lib_div::makeInstance('tx_kickstarter_modfunc1');
	$SOBE->pObj->doc = t3lib_div::makeInstance('template');
	echo $SOBE->main();
}
?>