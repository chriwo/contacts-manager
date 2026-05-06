<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\FrontendLogin\Domain\Repository\FrontendUserRepository;

class FrontendUserService
{
    public function __construct(
        private readonly Context $context,
        protected FrontendUserRepository $frontendUserRepository,
    ) {
    }

    /**
     * Get the current logged in frontend user or null
     * if the current visitor is not logged in.
     */
    public function getCurrentFrontendUser(): ?UserAspect
    {
        $frontendUser = $this->context->getAspect('frontend.user');
        if ($frontendUser instanceof UserAspect) {
            return $frontendUser;
        }

        return null;
    }

    public function getCurrentFrontendUserId(): int
    {
        $frontendUserId = $this->getFrontendUserProperty('id');
        if (!is_int($frontendUserId) || $frontendUserId < 0) {
            throw new \RuntimeException('Could not get current frontend user id', 1778058445);
        }

        return $frontendUserId;
    }

    public function isFrontendUserLoggedIn(): bool
    {
        $isLoggedIn = $this->getFrontendUserProperty('isLoggedIn');
        if (is_bool($isLoggedIn)) {
            return $isLoggedIn;
        }

        return false;
    }

    public function isFrontendUserInUserGroup(int $uidFrontendUserGroup): bool
    {
        $groupIds = $this->getFrontendUserGroups();
        if (empty($groupIds)) {
            return false;
        }

        return in_array($uidFrontendUserGroup, $groupIds);
    }

    public function getFrontendUserGroups(): array
    {
        $groupIds = $this->getFrontendUserProperty('groupIds');
        if (is_array($groupIds)) {
            return $groupIds;
        }

        return [];
    }

    /**
     * @param non-empty-string $propertyName
     * @return mixed
     * @throws AspectNotFoundException
     */
    public function getFrontendUserProperty(string $propertyName): mixed
    {
        return $this->context->getPropertyFromAspect('frontend.user', $propertyName);
    }
}
