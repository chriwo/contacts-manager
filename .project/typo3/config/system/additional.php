<?php

(function () {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'BE' => [
                'cookieDomain' => getenv('TYPO3_BE_COOKIEDOMAIN'),
                'installToolPassword' => getenv('TYPO3_INSTALL_TOOL_PASSWORD'),
            ],
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => getenv('TYPO3_DB_DEFAULT_DBNAME'),
                        'host' => getenv('TYPO3_DB_DEFAULT_HOST'),
                        'password' => getenv('TYPO3_DB_DEFAULT_PASSWORD'),
                        'port' => getenv('TYPO3_DB_DEFAULT_PORT'),
                        'user' => getenv('TYPO3_DB_DEFAULT_USER')
                    ]
                ]
            ],
            'GFX' => [
                'processor' => getenv('TYPO3_GFX_PROCESSOR'),
                'processor_path' => getenv('TYPO3_GFX_PROCESSOR_PATH'),
                'processor_path_lzw' => getenv('TYPO3_GFX_PROCESSOR_PATH_LZW'),
                'processor_colorspace' => getenv('TYPO3_GFX_PROCESSOR_COLORSPACE'),
            ],
            'MAIL' => [
                'transport' => getenv('TYPO3_MAIL_TRANSPORT'),
                'transport_smtp_server' => getenv('TYPO3_MAIL_TRANSPORT_SMTP_SERVER'),
                'transport_sendmail_command' => getenv('TYPO3_MAIL_TRANSPORT_SENDMAIL_COMMAND'),
            ],
            'SYS' => [
                'devIPmask' => '*',
                'displayErrors' => 1,
                'phpTimeZone' => getenv('TYPO3_SYS_PHP_TIME_ZONE'),
                'systemLocale' => getenv('TYPO3_SYS_SYSTEM_LOCALE'),
                'trustedHostsPattern' => getenv('TYPO3_SYS_TRUSTED_HOST_PATTERN'),
            ],
        ]
    );

    $siteName = empty(getenv('TYPO3_SITENAME')) ? 'New TYPO3 Website' : getenv('TYPO3_SITENAME');
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = $siteName . ' [' . getenv('TYPO3_CONTEXT') . ']';
})();
