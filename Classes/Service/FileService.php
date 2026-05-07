<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

readonly class FileService
{
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {
    }

    public function deletePhoto(FileReference $file): void
    {
        $this->deleteFileFromFileSystem($file);
        $this->deleteFileReference($file);
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
