<?php
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

// Proceed if BE is loaded
if (!defined("TYPO3_MODE")) {
	die ("Access denied.");
}

/** @var \Tx\kickstarter\Controller\KickstarterModuleController $SOBE */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\kickstarter\\Controller\\KickstarterModuleController');
$SOBE->init();
$SOBE->main();
$SOBE->render();
