<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(function () {
    ExtensionManagementUtility::addStaticFile(
        'contacts_manager',
        'Configuration/TypoScript/',
        'StarterTeam - Contacts manager'
    );
})();
