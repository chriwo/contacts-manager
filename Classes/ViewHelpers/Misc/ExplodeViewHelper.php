<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\ViewHelpers\Misc;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ExplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'Any list (e.g. "a,b,c,d")', false);
        $this->registerArgument('seperator', 'string', 'Separator sign (e.g. ",")', false, ',');
        $this->registerArgument('trim', 'bool', 'Should be trimmed?', false, true);
    }

    /**
     * @return false|string[]
     */
    public function render()
    {
        $string = $this->arguments['string'];
        if (!is_string($string)) {
            throw new InvalidArgumentException(
                'The argument "string" was registered with type "string", but is of type "' .
                gettype($string) . '" in view helper "' . static::class . '".',
                1715085850
            );
        }

        $separator = $this->arguments['seperator'];
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'The argument "string" was registered with type "string", but is of type "' .
                gettype($separator) . '" in view helper "' . static::class . '".',
                1715085850
            );
        }

        $trim = $this->arguments['trim'];
        if (!is_bool($trim)) {
            throw new InvalidArgumentException(
                'The argument "trim" was registered with type "bool", but is of type "' .
                gettype($trim) . '" in view helper "' . static::class . '".',
                1715085850
            );
        }

        return $trim
            ? GeneralUtility::trimExplode($separator, $string, true)
            : explode($separator, $string);
    }
}
