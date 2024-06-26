<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Domain\Model;

interface FrontendModelInterface
{
    public function getPid(): ?int;
}
