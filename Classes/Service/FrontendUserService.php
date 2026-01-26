<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FrontendUserService
{
    protected FrontendUserAuthentication $frontendUser;

    public function __construct()
    {
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
    }

    public function getFrontendUserIdColumn(): string
    {
        return $this->frontendUser->userid_column;
    }
}
