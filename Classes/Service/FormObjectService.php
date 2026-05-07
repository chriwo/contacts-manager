<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Core\Crypto\HashService;

readonly class FormObjectService
{
    public function __construct(
        private FrontendUserService $frontendUserService,
        private HashService $hashService,
    ) {
    }

    public function isDirtyObject($object): bool
    {
        foreach (array_keys($object->_getProperties()) as $propertyName) {
            try {
                $property = ObjectAccess::getProperty($object, $propertyName);
            } catch (PropertyNotAccessibleException $exception) {
                // if property can not be accessed
                continue;
            }

            /**
             * std::Property (string, int, etc..),
             * PHP-Objects (DateTime, RecursiveIterator, etc...),
             * TYPO3-Objects (user, page, etc...)
             */
            if (!$property instanceof ObjectStorage) {
                if ($object->_isDirty($propertyName)) {
                    return true;
                }
            } else {
                /**
                 * ObjectStorage
                 */
                if ($property->_isDirty()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function generateTokenFromUserAspect(): string
    {
        $userId = $this->frontendUserService->getCurrentFrontendUserId();
        $username = $this->frontendUserService->getFrontendUserProperty('username');

        return $this->hashService->hmac(StringUtility::cast($userId), StringUtility::cast($username));
    }

    public function isRecordUpdateAllowed(
        RequestInterface $request,
        string $formArgument,
        string $allowedRecordsUuidsToEdit
    ): void {
        $formValues = $request->hasArgument($formArgument) ? $request->getArgument($formArgument) : [];
        $formToken = $request->hasArgument('token') ? $request->getArgument('token') : '';

        if (empty($formValues) ||
            (int)$formValues['__identity'] === null ||
            $formToken === '' ||
            $this->isSpoof((int)$formValues['__identity'], $allowedRecordsUuidsToEdit, $formToken)
        ) {
            throw new RuntimeException('You are not allowed to update this record', 1719292424);
        }
    }

    public function isSpoof(int $contactIdentity, string $allowedRecordsUuidsToEdit, string $receivedToken): bool
    {
        $errorOnProfileUpdate = false;
        $knownToken = $this->generateTokenFromUserAspect();

        if ($receivedToken === '' || !hash_equals($knownToken, $receivedToken)) {
            $errorOnProfileUpdate = true;
        }

        //check if the logged user is allowed to edit / delete this record
        if ($contactIdentity > 0
            && !GeneralUtility::inList($allowedRecordsUuidsToEdit, StringUtility::cast($contactIdentity))
        ) {
            $errorOnProfileUpdate = false;
        }

        return $errorOnProfileUpdate;
    }
}
