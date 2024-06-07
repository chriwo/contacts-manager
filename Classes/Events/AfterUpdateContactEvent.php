<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Events;

use StarterTeam\ContactsManager\Domain\Model\ContactEdit;

class AfterUpdateContactEvent
{
    protected ContactEdit $contact;

    public function __construct(
        ContactEdit $contact
    ) {
        $this->contact = $contact;
    }

    public function getContact(): ContactEdit
    {
        return $this->contact;
    }
}
