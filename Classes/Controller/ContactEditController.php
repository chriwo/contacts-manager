<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Controller;

use Psr\Http\Message\ResponseInterface;
use StarterTeam\ContactsManager\Domain\Model\ContactEdit;
use StarterTeam\ContactsManager\Domain\Model\FrontendModelInterface;
use StarterTeam\ContactsManager\Domain\Repository\ContactEditRepository;
use StarterTeam\ContactsManager\Service\FileService;
use StarterTeam\ContactsManager\Service\FormObjectService;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ContactEditController extends AbstractFrontendController
{
    protected AspectInterface $userAspect;

    protected PersistenceManager $persistenceManager;

    protected FileService $fileService;

    protected FormObjectService $formObjectService;

    protected ContactEditRepository $contactEditRepository;

    public function __construct(
        Context $context,
        PersistenceManager $persistenceManager,
        FileService $fileService,
        FormObjectService $formObjectService,
        ContactEditRepository $contactEditRepository
    ) {
        $this->userAspect = $context->getAspect('frontend.user');
        $this->persistenceManager = $persistenceManager;
        $this->fileService = $fileService;
        $this->formObjectService = $formObjectService;
        $this->contactEditRepository = $contactEditRepository;
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

        $this->view->assignMultiple([
            'contacts' => $contactRecords,
        ]);

        return $this->htmlResponse();
    }

    public function editAction(ContactEdit $contact): ResponseInterface
    {
        // @todo: check if user currently logged in
        $this->view->assignMultiple([
            'contact' => $contact,
            'token' => GeneralUtility::hmac($this->userAspect->get('id'), (string)$this->userAspect->get('username')),
        ]);

        return $this->htmlResponse();
    }

    /**
     * @throws NoSuchArgumentException
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(ContactEdit $contact): ForwardResponse
    {
        $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser($this->userAspect->get('id'));
        $contactValues = $this->request->hasArgument('contact') ? $this->request->getArgument('contact') : [];
        $token = $this->request->hasArgument('token') ? $this->request->getArgument('token') : '';

        if (empty($contactValues) ||
            (int)$contactValues['__identity'] === null ||
            $token === '' ||
            $this->isSpoof((int)$contactValues['__identity'], $allowedContactsToEditByUser, $token)
        ) {
            // add log entry
            $this->addFlashMessage(
                'Contact update security failure',
                '',
                AbstractMessage::WARNING
            );
            return new ForwardResponse('edit');
        }

        if (!$this->hasObjectChanges($contact)) {
            return $this->redirect('edit', 'ContactEdit', 'ContactsManager', ['contact' => $contact]);
        }

        $uploadedFile = $this->processUploadedPhoto();
        if ($uploadedFile instanceof FileInterface) {
            if ($contact->getPhoto() instanceof FileReference) {
                $this->fileService->deletePhoto($contact->getPhoto());
            }

            $this->fileService->addFileReference($contact, $uploadedFile, 'tx_contacts_domain_model_contact');
        }

        $this->contactEditRepository->update($contact);
        $this->persistenceManager->persistAll();

        return $this->redirect('edit', 'ContactEdit', 'ContactsManager', ['contact' => $contact]);
    }

    public function deletePhotoAction(ContactEdit $contact): ResponseInterface
    {
        $allowedContactsToEditByUser = $this->contactEditRepository->findAllAllowedContactsOfFrontendUser($this->userAspect->get('id'));
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
