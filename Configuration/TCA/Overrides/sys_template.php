<?php

defined('TYPO3') or die();

(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'contacts_manager',
        'Configuration/TypoScript/',
        'StarterTeam - Contacts manager'
    );
})();
