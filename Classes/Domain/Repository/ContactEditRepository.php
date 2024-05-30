<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Domain\Repository;

use StarterTeam\ContactsManager\Domain\Model\ContactEdit;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ContactEditRepository extends Repository
{
    /**
     * @param int $frontendUserId
     * @return object[]|QueryResultInterface
     */
    public function findAllContactRecordsOfFrontendUser(int $frontendUserId)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals('assignedFrontendUser', $frontendUserId)
        );

        return $query->execute();
    }

    public function findAllAllowedContactsOfFrontendUser(int $frontendUserId): string
    {
        $allowContactUuidsToEdit = [];
        $queryResult = $this->findAllContactRecordsOfFrontendUser($frontendUserId);

        foreach ($queryResult as $resultRow) {
            if ($resultRow instanceof ContactEdit) {
                $allowContactUuidsToEdit[] = $resultRow->getUid();
            }
        }

        return implode(',', $allowContactUuidsToEdit);
    }
}
