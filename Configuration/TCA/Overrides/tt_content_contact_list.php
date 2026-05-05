<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

ExtensionUtility::registerPlugin(
    'ContactsManager',
    'ContactList',
    'LLL:EXT:contacts_manager/Resources/Private/Language/locallang_backend.xlf:modWizard.contact_list.title',
    'ctype-contactsmanager_contactlist',
    'contacts',
    'LLL:EXT:contacts_manager/Resources/Private/Language/locallang_backend.xlf:modWizard.contact_list.description',
);
