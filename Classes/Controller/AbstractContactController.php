<?php

declare(strict_types=1);

namespace StarterTeam\ContactsManager\Controller;

use StarterTeam\ContactsManager\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\FileUploadConfiguration;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Validation\Validator\FileSizeValidator;
use TYPO3\CMS\Extbase\Validation\Validator\MimeTypeValidator;

class AbstractContactController extends ActionController
{
    protected function initializeImagePropertyMapping(string $argumentName, string $property): void
    {
        if (!$this->arguments->hasArgument($argumentName)) {
            return;
        }

        try {
            $argument = $this->arguments->getArgument($argumentName);
        } catch (NoSuchArgumentException) {
            return;
        }

        $validators = [];
        $fileStorage = ArrayUtility::getStringValueByPath($this->settings, 'edit/file/uploadFolder');
        if ($fileStorage === '') {
            throw new \RuntimeException('Configuration error: please set settings.edit.file.uploadFolder');
        }

        $allowedMimeTypes = ArrayUtility::getStringValueByPath($this->settings, 'edit/file/uploadMimeTypes');
        if ($allowedMimeTypes !== '') {
            $mimeTypeValidator = GeneralUtility::makeInstance(MimeTypeValidator::class);
            $mimeTypeValidator->setOptions([
                'allowedMimeTypes' => GeneralUtility::trimExplode(',', $allowedMimeTypes, true),
            ]);
            $validators[] = $mimeTypeValidator;
        }

        $maximumFileSize = ArrayUtility::getStringValueByPath($this->settings, 'edit/file/uploadSize');
        if ($maximumFileSize === '') {
            throw new \RuntimeException('Configuration error: please set settings.edit.file.uploadSize to value > 0');
        }

        $fileSizeValidator = GeneralUtility::makeInstance(FileSizeValidator::class);
        $fileSizeValidator->setOptions([
            'maximum' => $maximumFileSize,
        ]);
        $validators[] = $fileSizeValidator;

        $maximumUploadFiles = 1;
        $fileUploadConfiguration = new FileUploadConfiguration($property);
        $fileUploadConfiguration
            ->setMaxFiles($maximumUploadFiles)
            ->setCreateUploadFolderIfNotExist(true)
            ->setUploadFolder($fileStorage);

        foreach ($validators as $validator) {
            $fileUploadConfiguration->addValidator($validator);
        }

        $fileHandlingServiceConfiguration = $argument->getFileHandlingServiceConfiguration();
        $fileHandlingServiceConfiguration->addFileUploadConfiguration($fileUploadConfiguration);

        $this->arguments->getArgument($argumentName)->getPropertyMappingConfiguration()->skipProperties($property);
    }
}
