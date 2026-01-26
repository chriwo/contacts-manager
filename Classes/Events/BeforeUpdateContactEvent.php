<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Events;

use StarterTeam\ContactsManager\Domain\Model\ContactEdit;

class BeforeUpdateContactEvent
{
    public function __construct(
        protected ContactEdit $contact,
        protected array $pluginSettings,
    ) {
    }

    public function getContact(): ContactEdit
    {
        return $this->contact;
    }

    public function getPluginSettings(): array
    {
        return $this->pluginSettings;
    }
}
