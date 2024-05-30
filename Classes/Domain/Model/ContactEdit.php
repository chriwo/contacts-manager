<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Domain\Model;

use Extcode\Contacts\Domain\Model\Contact;

class ContactEdit extends Contact implements FrontendModelInterface
{
    protected int $assignedFrontendUser = 0;

    public function getAssignedFrontendUser(): int
    {
        return $this->assignedFrontendUser;
    }
}
