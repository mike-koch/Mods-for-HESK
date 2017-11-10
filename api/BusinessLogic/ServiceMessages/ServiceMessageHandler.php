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

        return $this->serviceMessageGateway->createServiceMessage($serviceMessage, $heskSettings);
    }

    /**
     * @param $serviceMessage ServiceMessage
     * @throws ValidationException
     */
    private function validate($serviceMessage) {
        $validationModel = new ValidationModel();
        if ($serviceMessage->createdBy < 1) {
            $validationModel->errorKeys[] = 'MISSING_CREATOR';
        }
        if ($serviceMessage->icon === null || trim($serviceMessage->icon) === '') {
            $validationModel->errorKeys[] = 'MISSING_ICON';
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