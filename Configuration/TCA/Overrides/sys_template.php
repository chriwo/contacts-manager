<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addStaticFile(
    'contacts_manager',
    'Configuration/TypoScript/',
    'StarterTeam - Contacts manager'
);
