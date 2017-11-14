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

    function getServiceMessages($heskSettings) {
        return $this->serviceMessageGateway->getServiceMessages($heskSettings);
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

    function sortServiceMessage($id, $direction, $heskSettings) {
        $serviceMessages = $this->serviceMessageGateway->getServiceMessages($heskSettings);
        $serviceMessage = null;
        foreach ($serviceMessages as $innerServiceMessage) {
            if (intval($innerServiceMessage->id) === intval($id)) {
                $serviceMessage = $innerServiceMessage;
                break;
            }
        }

        if ($serviceMessage === null) {
            throw new \BaseException("Could not find service message with ID {$id}!");
        }

        if ($direction === Direction::UP) {
            $serviceMessage->order -= 15;
        } else {
            $serviceMessage->order += 15;
        }

        $this->serviceMessageGateway->updateServiceMessage($serviceMessage, $heskSettings);
        $this->serviceMessageGateway->resortAllServiceMessages($heskSettings);
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