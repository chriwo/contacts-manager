<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use StarterTeam\ContactsManager\Domain\Model\FrontendModelInterface;
use StarterTeam\ContactsManager\Exception\InvalidFileUploadException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Mvc\Request;

class FileService
{
    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool, private readonly \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory)
    {
    }
    public function deletePhoto(FileReference $file): void
    {
        $this->deleteFileFromFileSystem($file);
        $this->deleteFileReference($file);
    }

    public function addFileReference(
        FrontendModelInterface $object,
        FileInterface $file,
        string $tableName,
        string $property = 'photo'
    ): void {
        $this->connectionPool
            ->getConnectionForTable('sys_file_reference')
            ->insert(
                'sys_file_reference',
                [
                    'pid' => $object->getPid(),
                    'tstamp' => time(),
                    'crdate' => time(),
                    'uid_local' => $file->getUid(),
                    'uid_foreign' => $object->getUid(),
                    'tablenames' => $tableName,
                    'fieldname' => $property,
                    'sorting_foreign' => 1,
                    'table_local' => 'sys_file',
                ]
            );
    }

    public function processUploadedPhoto(array $settings, Request $request): FileInterface
    {
        $uploadedPhoto = $request->getUploadedFiles()['contact_edit_edit'] ?? [];
        $uploadFolderString = $settings['edit']['file']['uploadFolder'];
        $allowedFileExtensions = GeneralUtility::trimExplode(',', $settings['edit']['file']['uploadFileExtension'], true);
        $allowedMimeTypes = GeneralUtility::trimExplode(',', $settings['edit']['file']['uploadMimeTypes'], true);

        if (count($uploadedPhoto) <= 0) {
            throw new InvalidFileUploadException('No photo is uploaded', 1719230754);
        }

        if (!$uploadedPhoto['contact']['photo'][0] instanceof UploadedFile) {
            throw new InvalidFileUploadException('Uploaded photo is not instance of UploadedFile', 1719230879);
        }

        /**@var UploadedFile $newPhoto*/
        $newPhoto = $uploadedPhoto['contact']['photo'][0];
        if (!in_array($newPhoto->getClientMediaType(), $allowedMimeTypes)) {
            throw new InvalidFileUploadException(
                sprintf('File mime type "%s" is not allowed', $newPhoto->getClientMediaType()),
                1719230980
            );
        }

        if (!in_array(pathinfo((string)$newPhoto->getClientFilename())['extension'], $allowedFileExtensions)) {
            throw new InvalidFileUploadException(
                sprintf('File extension "%s" is not allowed', pathinfo((string)$newPhoto->getClientFilename())['extension']),
                1719230980
            );
        }

        $resourceFactory = $this->resourceFactory;
        $storage = $resourceFactory->getStorageObjectFromCombinedIdentifier($uploadFolderString);
        $parts = GeneralUtility::trimExplode(':', $uploadFolderString);
        if (!$storage->hasFolder($parts[1])) {
            $storage->createFolder($parts[1]);
        }
        $uploadFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderString);

        return $storage->addUploadedFile(
            $request->getArgument('contact')['photo'][0],
            $uploadFolder,
            null,
            DuplicationBehavior::RENAME
        );
    }

    private function deleteFileFromFileSystem(FileReference $file): void
    {
        $file->getOriginalResource()->getOriginalFile()->delete();
    }

    private function deleteFileReference(FileReference $file): void
    {
        $this->connectionPool
            ->getConnectionForTable('sys_file_reference')
            ->delete(
                'sys_file_reference',
                ['uid' => $file->getUid()]
            );
    }
}
