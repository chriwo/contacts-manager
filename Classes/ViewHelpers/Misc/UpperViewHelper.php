<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\ViewHelpers\Misc;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UpperViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'string', false);
    }

    public function render(): string
    {
        $value = $this->arguments['string'] ?? '';
        return is_string($value) ? ucfirst($value) : '';
    }
}
