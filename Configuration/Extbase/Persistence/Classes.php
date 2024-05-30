<?php

declare(strict_types=1);

return [
    \StarterTeam\ContactsManager\Domain\Model\ContactEdit::class => [
        'tableName' => 'tx_contacts_domain_model_contact',
        'properties' => [
            'assignedFrontendUser' => [
                'fieldName' => 'fe_user',
            ],
        ],
    ],
];
