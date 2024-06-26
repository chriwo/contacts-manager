<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use StarterTeam\ContactsManager\Domain\Model\ContactEdit;
use StarterTeam\ContactsManager\Domain\Model\FrontendModelInterface;
use StarterTeam\ContactsManager\Domain\Repository\ContactEditRepository;
use StarterTeam\ContactsManager\Events\AfterUpdateContactEvent;
use StarterTeam\ContactsManager\Events\BeforeUpdateContactEvent;
use StarterTeam\ContactsManager\Exception\InvalidFileUploadException;
use StarterTeam\ContactsManager\Service\FileService;
use StarterTeam\ContactsManager\Service\FormObjectService;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ContactEditController extends ActionController
{
    protected AspectInterface $userAspect;

    protected PersistenceManager $persistenceManager;

    protected FileService $fileService;

    protected FormObjectService $formObjectService;

    protected ContactEditRepository $contactEditRepository;

    protected LoggerInterface $logger;

    public function __construct(
        Context $context,
        PersistenceManager $persistenceManager,
        FileService $fileService,
        FormObjectService $formObjectService,
        ContactEditRepository $contactEditRepository,
        LoggerInterface $logger
    ) {
        $this->userAspect = $context->getAspect('frontend.user');
        $this->persistenceManager = $persistenceManager;
        $this->fileService = $fileService;
        $this->formObjectService = $formObjectService;
        $this->contactEditRepository = $contactEditRepository;
        $this->logger = $logger;
    }

    public function initializeEditAction()
    {
        $this->skipPhotoPropertyFromPropertyMapping();
    }

    public function initializeUpdateAction(): void
    {
        $this->skipPhotoPropertyFromPropertyMapping();
    }

    public function listAction(): ResponseInterface
    {
        $contactRecords = null;
        $frontendUserId = $this->userAspect->get('id');
        if (is_int($frontendUserId) && $frontendUserId > 0) {
            $contactRecords = $this->contactEditRepository->findAllContactRecordsOfFrontendUser($frontendUserId);
        }

        if (is_iterable($contactRecords) && count($contactRecords) === 1) {
            $this->redirect(
                'edit',
                'ContactEdit',
                'ContactsManager',
                ['contact' => $contactRecords[0]],
                (int)$this->settings['formPageUid']
            );
        }

        $this->view->assignMultiple([
            'contacts' => $contactRecords,
        ]);

        return $this->htmlResponse();
    }

    public function editAction(ContactEdit $contact): ResponseInterface
    {
        $userId = $this->userAspect->get('id');
        if (!is_int($userId) || $userId <= 0) {
            throw new RuntimeException('User is not authenticated', 1719294448);
        }

        $username = $this->userAspect->get('username');
        if (!is_string($username)) {
            throw new RuntimeException('User is not authenticated', 1719294457);
        }

        $this->view->assignMultiple([
            'contact' => $contact,
            'token' => $this->formObjectService->generateTokenFromUserAspect($userId, $username),
        ]);

        return $this->htmlResponse();
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnknownObjectException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
     */
    public function updateAction(ContactEdit $contact): ForwardResponse
    {
        try {
            $userId = $this->userAspect->get('id');
            if (!is_int($userId) || $userId <= 0) {
                throw new RuntimeException('User is not authenticated', 1719294448);
            }

            $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser($userId);
            $this->formObjectService->isRecordUpdateAllowed($this->request, $this->userAspect, 'contact', $allowedContactsToEditByUser);

            $uploadedFile = $this->fileService->processUploadedPhoto($this->settings, $this->request);
            if ($contact->getPhoto() instanceof FileReference) {
                $this->fileService->deletePhoto($contact->getPhoto());
            }

            $this->fileService->addFileReference($contact, $uploadedFile, 'tx_contacts_domain_model_contact');

            $this->eventDispatcher->dispatch(new BeforeUpdateContactEvent($contact, $this->settings));

            $this->contactEditRepository->update($contact);
            $this->persistenceManager->persistAll();

            $this->eventDispatcher->dispatch(new AfterUpdateContactEvent($contact, $this->settings));
        } catch (RuntimeException $exception) {
            $this->logger->critical($exception->getMessage());
            $this->addFlashMessage(
                LocalizationUtility::translate('notLoggedIn', 'ContactsManager'),
                'Error',
                AbstractMessage::ERROR
            );
            return new ForwardResponse('edit');
        } catch (InvalidFileUploadException $exception) {
            $this->logger->error(
                $exception->getMessage(),
                [
                    'settings' => $this->settings,
                    'request' => $this->request,
                ]
            );
            $this->addFlashMessage(
                LocalizationUtility::translate('errorInFileUpload', 'ContactsManager'),
                'Error',
                AbstractMessage::ERROR
            );
            return new ForwardResponse('edit');
        }

        $this->addFlashMessage(LocalizationUtility::translate('recordIsUpdated', 'ContactsManager'));

        return $this->redirect(
            'edit',
            'ContactEdit',
            'ContactsManager',
            ['contact' => $contact]
        );
    }

    public function deletePhotoAction(ContactEdit $contact): ResponseInterface
    {
        $userId = $this->userAspect->get('id');
        if (!is_int($userId) || $userId <= 0) {
            throw new RuntimeException('User is not authenticated', 1719294448);
        }

        $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser($userId);
        $contactValues = $this->request->hasArgument('contact') ? $this->request->getArgument('contact') : [];

        if (empty($contactValues) ||
            (int)$contactValues === null ||
            !GeneralUtility::inList($allowedContactsToEditByUser, (int)$contactValues)
        ) {
            throw new UnauthorizedException('You are not allowed to delete this contact photo', 1715067545);
        }

        if ($contact->getPhoto() instanceof FileReference) {
            $this->fileService->deletePhoto($contact->getPhoto());
        }

        $this->contactEditRepository->update($contact);

        return $this->redirect('edit', 'ContactEdit', 'ContactsManager', ['contact' => $contact]);
    }

    private function skipPhotoPropertyFromPropertyMapping(): void
    {
        if ($this->arguments->hasArgument('contact')) {
            $this
                ->arguments
                ->getArgument('contact')
                ->getPropertyMappingConfiguration()
                ->skipProperties('photo');
        }
    }

    private function hasObjectChanges(FrontendModelInterface $object): bool
    {
        if (!$this->formObjectService->isDirtyObject($object)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('noDataChanged', 'ContactsManager'),
                '',
                AbstractMessage::NOTICE
            );
            return false;
        }

        return true;
    }
}
