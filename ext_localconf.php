<?php

declare(strict_types=1);
use StarterTeam\ContactsManager\Controller\ContactEditController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$GLOBALS['TYPO3_CONF_VARS']['EXT']['contacts']['classes']['Domain/Model/Contact']['contacts_manager'] = 'contacts_manager';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['contacts'][] = 'StarterTeam\\ContactsManager\\ViewHelpers';

ExtensionUtility::configurePlugin(
    'ContactsManager',
    'ContactList',
    [
        ContactEditController::class => 'list',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'ContactsManager',
    'ContactEdit',
    [
        ContactEditController::class => 'edit,update,deletePhoto',
    ],
    [
        ContactEditController::class => 'edit,update,deletePhoto',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
