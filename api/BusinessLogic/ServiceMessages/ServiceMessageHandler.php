<?php

namespace BusinessLogic\ServiceMessages;


// TODO Test
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\ValidationModel;
use DataAccess\ServiceMessages\ServiceMessagesGateway;

class ServiceMessageHandler extends \BaseClass {
    /* @var $serviceMessageGateway ServiceMessagesGateway */
    private $serviceMessageGateway;

    function __construct(ServiceMessagesGateway $serviceMessagesGateway) {
        $this->serviceMessageGateway = $serviceMessagesGateway;
    }

    function createServiceMessage($serviceMessage, $heskSettings) {
        $this->validate($serviceMessage);

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

    function getServiceMessages($heskSettings) {
        return $this->serviceMessageGateway->getServiceMessages($heskSettings);
    }

    function editServiceMessage($serviceMessage, $heskSettings) {
        $this->validate($serviceMessage, false);

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

    /**
     * @param $serviceMessage ServiceMessage
     * @param bool $isNew
     * @throws ValidationException
     */
    private function validate($serviceMessage, $isNew = true) {
        $validationModel = new ValidationModel();
        if ($isNew && $serviceMessage->createdBy < 1) {
            $validationModel->errorKeys[] = 'MISSING_CREATOR';
        }
        if ($serviceMessage->message === null || trim($serviceMessage->message) === '') {
            $validationModel->errorKeys[] = 'MISSING_MESSAGE';
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

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            throw new ValidationException($validationModel);
        }
    }
}