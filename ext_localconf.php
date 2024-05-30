<?php

declare(strict_types=1);

$GLOBALS['TYPO3_CONF_VARS']['EXT']['contacts']['classes']['Domain/Model/Contact']['contacts_manager'] = 'contacts_manager';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['contacts'][] = 'StarterTeam\\ContactsManager\\ViewHelpers';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '@import \'EXT:contacts_manager/Configuration/TSConfig/PageTs.typoscript\''
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'StarterTeam.ContactsManager',
    'ContactList',
    [
        \StarterTeam\ContactsManager\Controller\ContactEditController::class => 'list',
    ],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'StarterTeam.ContactsManager',
    'ContactEdit',
    [
        \StarterTeam\ContactsManager\Controller\ContactEditController::class => 'edit,update,deletePhoto',
    ],
    [
        \StarterTeam\ContactsManager\Controller\ContactEditController::class => 'edit,update,deletePhoto',
    ],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
