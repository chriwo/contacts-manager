<?php

return [
    'BE' => [
        'compressionLevel' => '5',
        'debug' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8mb4',
                'driver' => 'mysqli',
                'defaultTableOptions' => [
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
            ],
        ],
    ],
    'EXTENSIONS' => [
        'backend' => [
            'backendFavicon' => '',
            'backendLogo' => '',
            'loginBackgroundImage' => '',
            'loginFootnote' => '',
            'loginHighlightColor' => '',
            'loginLogo' => '',
            'loginLogoAlt' => '',
        ],
        'extensionmanager' => [
            'automaticInstallation' => '0',
            'offlineMode' => '1',
        ],
    ],
    'FE' => [
        'debug' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
        'pageNotFoundOnCHashError' => false,
    ],
    'GFX' => [
        'processor_allowTemporaryMasksAsPng' => false,
        'processor_effects' => true,
        'processor_enabled' => true,
    ],
    'SYS' => [
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                ],
                'imagesizes' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'pages' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'ddmmyy' => 'd.m.Y',
        'devIPmask' => '*',
        'displayErrors' => 1,
        'encryptionKey' => 'dev-encrypt-key',
        'exceptionalErrors' => 12290,
        'features' => [
            'felogin.extbase' => true,
            'rearrangedRedirectMiddlewares' => true,
            'security.usePasswordPolicyForFrontendUsers' => true,
            'unifiedPageTranslationHandling' => true,
        ],
        'mediafile_ext' => 'jpg,jpeg,png,youtube,vimeo',
        'systemMaintainers' => null,
    ],
];
