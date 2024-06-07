<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Events;

use StarterTeam\ContactsManager\Domain\Model\ContactEdit;

class AfterUpdateContactEvent
{
    protected ContactEdit $contact;

    protected array $pluginSettings;

    public function __construct(
        ContactEdit $contact,
        array $pluginSettings
    ) {
        $this->contact = $contact;
        $this->pluginSettings = $pluginSettings;
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
