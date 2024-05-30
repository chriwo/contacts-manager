<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use StarterTeam\ContactsManager\Domain\Model\FrontendModelInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class FileService
{
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
        GeneralUtility::makeInstance(ConnectionPool::class)
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

    private function deleteFileFromFileSystem(FileReference $file): void
    {
        $file->getOriginalResource()->getOriginalFile()->delete();
    }

    private function deleteFileReference(FileReference $file): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference')
            ->delete(
                'sys_file_reference',
                ['uid' => $file->getUid()]
            );
    }
}
