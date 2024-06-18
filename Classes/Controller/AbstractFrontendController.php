<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Controller;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class AbstractFrontendController extends ActionController
{
    protected function isSpoof(int $contactIdentity, string $allowedRecordsUuidsToEdit, string $receivedToken): bool
    {
        $errorOnProfileUpdate = false;
        $knownToken = GeneralUtility::hmac($this->userAspect->get('id'), (string)$this->userAspect->get('username'));

        if ($receivedToken === '' || !hash_equals($knownToken, $receivedToken)) {
            $errorOnProfileUpdate = true;
        }

        //check if the logged user is allowed to edit / delete this record
        if ($contactIdentity > 0
            && !GeneralUtility::inList($allowedRecordsUuidsToEdit, $contactIdentity)
        ) {
            $errorOnProfileUpdate = false;
        }

        return $errorOnProfileUpdate;
    }

    protected function processUploadedPhoto(): ?FileInterface
    {
        $uploadedPhoto = $this->request->getUploadedFiles()['contact_edit_edit'] ?? [];
        $uploadFolderString = $this->settings['edit']['file']['uploadFolder'];
        $allowedFileExtensions = GeneralUtility::trimExplode(',', $this->settings['edit']['file']['uploadFileExtension'], true);
        $allowedMimeTypes = GeneralUtility::trimExplode(',', $this->settings['edit']['file']['uploadMimeTypes'], true);

        if (count($uploadedPhoto) > 0 && $uploadedPhoto['contact']['photo'][0] instanceof UploadedFile) {
            $newPhoto = $uploadedPhoto['contact']['photo'][0];

            if (in_array($newPhoto->getClientMediaType(), $allowedMimeTypes) &&
                in_array(pathinfo((string)$newPhoto->getClientFilename())['extension'], $allowedFileExtensions)
            ) {
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                $storage = $resourceFactory->getStorageObjectFromCombinedIdentifier($uploadFolderString);
                $parts = GeneralUtility::trimExplode(':', $uploadFolderString);
                if (!$storage->hasFolder($parts[1])) {
                    $storage->createFolder($parts[1]);
                }
                $uploadFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderString);

                return $storage->addUploadedFile(
                    $newPhoto,
                    $uploadFolder,
                    null,
                    DuplicationBehavior::RENAME
                );
            }
        }

        return null;
    }
}
