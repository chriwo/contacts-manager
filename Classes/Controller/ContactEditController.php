<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use StarterTeam\ContactsManager\Domain\Model\ContactEdit;
use StarterTeam\ContactsManager\Domain\Repository\ContactEditRepository;
use StarterTeam\ContactsManager\Events\AfterUpdateContactEvent;
use StarterTeam\ContactsManager\Events\BeforeUpdateContactEvent;
use StarterTeam\ContactsManager\Service\FileService;
use StarterTeam\ContactsManager\Service\FormObjectService;
use StarterTeam\ContactsManager\Service\FrontendUserService;
use StarterTeam\ContactsManager\Utility\ArrayUtility;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

class ContactEditController extends AbstractContactController
{
    public function __construct(
        protected readonly FrontendUserService $frontendUserService,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly FileService $fileService,
        protected readonly FormObjectService $formObjectService,
        protected readonly ContactEditRepository $contactEditRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function initializeAction(): void
    {
        if (!$this->frontendUserService->isFrontendUserLoggedIn()) {
            throw new RuntimeException('User is not authenticated', 1719294448);
        }
    }

    public function listAction(): ResponseInterface
    {
        $contactRecords = null;
        $frontendUserId = $this->frontendUserService->getCurrentFrontendUserId();
        if ($frontendUserId > 0) {
            $contactRecords = $this->contactEditRepository->findAllContactRecordsOfFrontendUser($frontendUserId);
        }

        if (is_iterable($contactRecords) && count($contactRecords) === 1) {
            return $this->redirect(
                'edit',
                'ContactEdit',
                'ContactsManager',
                ['contact' => $contactRecords[0]],
                ArrayUtility::getIntegerValueByPathOrNull($this->settings, 'formPageUid')
            );
        }

        $this->view->assignMultiple([
            'contacts' => $contactRecords,
        ]);

        return $this->htmlResponse();
    }

    public function editAction(ContactEdit $contact): ResponseInterface
    {
        $this->view->assignMultiple([
            'contact' => $contact,
            'token' => $this->formObjectService->generateTokenFromUserAspect(),
        ]);

        return $this->htmlResponse();
    }

    public function initializeUpdateAction(): void
    {
        $this->initializeImagePropertyMapping('contact', 'photo');
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws AspectPropertyNotFoundException
     */
    public function updateAction(ContactEdit $contact): ResponseInterface
    {
        $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser(
            $this->frontendUserService->getCurrentFrontendUserId()
        );
        $this->formObjectService->isRecordUpdateAllowed($this->request, 'contact', $allowedContactsToEditByUser);

        $this->eventDispatcher->dispatch(new BeforeUpdateContactEvent($contact, $this->settings));

        $this->contactEditRepository->update($contact);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new AfterUpdateContactEvent($contact, $this->settings));

        $this->addFlashMessage(LocalizationUtility::translate('recordIsUpdated', 'ContactsManager') ?? '');

        return $this->redirect(
            'edit',
            'ContactEdit',
            'ContactsManager',
            ['contact' => $contact],
            ArrayUtility::getIntegerValueByPathOrNull($this->settings, 'formPageUid')
        );
    }

    public function deletePhotoAction(ContactEdit $contact): ResponseInterface
    {
        $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser(
            $this->frontendUserService->getCurrentFrontendUserId()
        );
        $contactValues = $this->request->hasArgument('contact') ? $this->request->getArgument('contact') : [];

        if (empty($contactValues) ||
            !GeneralUtility::inList($allowedContactsToEditByUser, StringUtility::cast($contactValues) ?? '')
        ) {
            throw new UnauthorizedException('You are not allowed to delete this contact photo', 1715067545);
        }

        if ($contact->getPhoto() instanceof FileReference) {
            $this->fileService->deletePhoto($contact->getPhoto());
        }

        $this->contactEditRepository->update($contact);

        return $this->redirect(
            'edit',
            'ContactEdit',
            'ContactsManager',
            ['contact' => $contact],
            ArrayUtility::getIntegerValueByPathOrNull($this->settings, 'formPageUid')
        );
    }
}
