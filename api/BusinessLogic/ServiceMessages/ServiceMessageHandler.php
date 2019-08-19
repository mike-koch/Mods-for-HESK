<?php

namespace BusinessLogic\ServiceMessages;


// TODO Test
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Navigation\Direction;
use BusinessLogic\ValidationModel;
use DataAccess\ServiceMessages\ServiceMessagesGateway;

class ServiceMessageHandler extends \BaseClass {
    /* @var $serviceMessageGateway ServiceMessagesGateway */
    private $serviceMessageGateway;

    function __construct(ServiceMessagesGateway $serviceMessagesGateway) {
        $this->serviceMessageGateway = $serviceMessagesGateway;
    }

    function createServiceMessage($serviceMessage, $heskSettings) {
        $this->validate($serviceMessage, $heskSettings);

        if ($serviceMessage->icon === null) {
            switch ($serviceMessage->style) {
                case ServiceMessageStyle::NONE:
                    $serviceMessage->icon = '';
                    break;
                case ServiceMessageStyle::INFO:
                    $serviceMessage->icon = 'fa fa-comment';
                    break;
                case ServiceMessageStyle::NOTICE:
                    $serviceMessage->icon = 'fa fa-exclamation-triangle';
                    break;
                case ServiceMessageStyle::ERROR:
                    $serviceMessage->icon = 'fa fa-times-circle';
                    break;
                case ServiceMessageStyle::SUCCESS:
                    $serviceMessage->icon = 'fa fa-check-circle';
                    break;
            }
        }

        return $this->serviceMessageGateway->createServiceMessage($serviceMessage, $heskSettings);
    }

    function getServiceMessages($heskSettings, $searchFilter) {
        return $this->serviceMessageGateway->getServiceMessages($heskSettings, $searchFilter);
    }

    function editServiceMessage($serviceMessage, $heskSettings) {
        $this->validate($serviceMessage, $heskSettings, false);

        if ($serviceMessage->icon === null) {
            switch ($serviceMessage->style) {
                case ServiceMessageStyle::NONE:
                    $serviceMessage->icon = '';
                    break;
                case ServiceMessageStyle::INFO:
                    $serviceMessage->icon = 'fa fa-comment';
                    break;
                case ServiceMessageStyle::NOTICE:
                    $serviceMessage->icon = 'fa fa-exclamation-triangle';
                    break;
                case ServiceMessageStyle::ERROR:
                    $serviceMessage->icon = 'fa fa-times-circle';
                    break;
                case ServiceMessageStyle::SUCCESS:
                    $serviceMessage->icon = 'fa fa-check-circle';
                    break;
            }
        }

        return $this->serviceMessageGateway->updateServiceMessage($serviceMessage, $heskSettings);
    }

    function deleteServiceMessage($id, $heskSettings) {
        $this->serviceMessageGateway->deleteServiceMessage($id, $heskSettings);
    }

    function sortServiceMessages($order, $heskSettings) {
        $this->serviceMessageGateway->sortServiceMessages($order, $heskSettings);
    }

    /**
     * @param $serviceMessage ServiceMessage
     * @param bool $isNew
     * @throws ValidationException
     */
    private function validate($serviceMessage, $heskSettings, $isNew = true) {
        $validationModel = new ValidationModel();
        if ($isNew && $serviceMessage->createdBy < 1) {
            $validationModel->errorKeys[] = 'MISSING_CREATOR';
        }

        if ($serviceMessage->message === null || trim($serviceMessage->message) === '') {
            $validationModel->errorKeys[] = 'MISSING_MESSAGE';
        } else {
            $htmlPurifier = new \HeskHTMLPurifier($heskSettings['cache_dir']);
            $serviceMessage->message = $htmlPurifier->heskPurify($serviceMessage->message);
        }
        if ($serviceMessage->language === null || trim($serviceMessage->language) === '') {
            $validationModel->errorKeys[] = 'MISSING_LANGUAGE';
        }

        $languageFound = false;
        foreach ($heskSettings['languages'] as $key => $value) {
            if ($value['folder'] === $serviceMessage->language || $serviceMessage->language === 'ALL') {
                $languageFound = true;
                break;
            }
        }
        if (!$languageFound && !in_array('MISSING_LANGUAGE', $validationModel->errorKeys)) {
            $validationModel->errorKeys[] = 'LANGUAGE_NOT_INSTALLED';
        }

        if ($serviceMessage->title === null || trim($serviceMessage->title) === '') {
            $validationModel->errorKeys[] = 'MISSING_TITLE';
        }
        if ($serviceMessage->style === null || trim($serviceMessage->style) === '') {
            $validationModel->errorKeys[] = 'MISSING_STYLE';
        }
        try {
            ServiceMessageStyle::getIdForStyle($serviceMessage->style);
        } catch (\Exception $e) {
            $validationModel->errorKeys[] = 'INVALID_STYLE';
        }
        if ($serviceMessage->locations === null || count($serviceMessage->locations) === 0) {
            $validationModel->errorKeys[] = 'MISSING_LOCATIONS';
        } else {
            $locations = ServiceMessageLocation::getAll();
            foreach ($serviceMessage->locations as $location) {
                if (!in_array($location, $locations)) {
                    $validationModel->errorKeys[] = 'INVALID_LOCATION';
                    break;
                }
            }
        }

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            throw new ValidationException($validationModel);
        }
    }
}