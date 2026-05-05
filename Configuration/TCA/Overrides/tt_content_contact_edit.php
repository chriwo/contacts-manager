<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

ExtensionUtility::registerPlugin(
    'ContactsManager',
    'ContactEdit',
    'LLL:EXT:contacts_manager/Resources/Private/Language/locallang_backend.xlf:modWizard.contact_edit.title',
    'ctype-contactsmanager_contactedit',
    'contacts',
    'LLL:EXT:contacts_manager/Resources/Private/Language/locallang_backend.xlf:modWizard.contact_edit.description',
);
