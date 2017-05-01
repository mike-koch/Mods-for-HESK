<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Exceptions\AccessViolationException;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\Security\UserToTicketChecker;
use BusinessLogic\Tickets\CustomFields\CustomFieldValidator;
use BusinessLogic\ValidationModel;
use BusinessLogic\Validators;
use Core\Constants\CustomField;
use DataAccess\Tickets\TicketGateway;

class TicketEditor {
    /* @var $ticketGateway TicketGateway */
    private $ticketGateway;

    /* @var $userToTicketChecker UserToTicketChecker */
    private $userToTicketChecker;

    function __construct($ticketGateway, $userToTicketChecker) {
        $this->ticketGateway = $ticketGateway;
        $this->userToTicketChecker = $userToTicketChecker;
    }


    /**
     * @param $editTicketModel EditTicketModel
     * @param $userContext UserContext
     * @param $heskSettings array
     * @throws ApiFriendlyException When the ticket isn't found for the ID
     * @throws \Exception When the user doesn't have access to the ticket
     */
    // TODO Unit Tests
    function editTicket($editTicketModel, $userContext, $heskSettings) {
        $ticket = $this->ticketGateway->getTicketById($editTicketModel->id, $heskSettings);

        if ($ticket === null) {
            throw new ApiFriendlyException("Ticket with ID {$editTicketModel->id} not found!", "Ticket not found", 404);
        }

        if (!$this->userToTicketChecker->isTicketAccessibleToUser($userContext, $ticket, $heskSettings, array(UserPrivilege::CAN_EDIT_TICKETS))) {
            throw new AccessViolationException("User does not have access to ticket {$editTicketModel->id}");
        }

        $this->validate($editTicketModel, $ticket->categoryId, $heskSettings);

        $ticket->name = $editTicketModel->name;
        $ticket->email = $editTicketModel->email;
        $ticket->subject = $editTicketModel->subject;
        $ticket->message = $editTicketModel->message;
        $ticket->customFields = $editTicketModel->customFields;

        $this->ticketGateway->updateBasicTicketInfo($ticket, $heskSettings);
    }

    /**
     * @param $editTicketModel EditTicketModel
     * @param $categoryId int
     * @param $heskSettings array
     * @throws ValidationException When validation fails
     */
    private function validate($editTicketModel, $categoryId, $heskSettings) {
        $validationModel = new ValidationModel();

        if ($editTicketModel->name === null || trim($editTicketModel->name) === '') {
            $validationModel->errorKeys[] = 'NO_NAME';
        }

        if (!Validators::validateEmail($editTicketModel->email, $heskSettings['multi_eml'], false)) {
            $validationModel->errorKeys[] = 'INVALID_OR_MISSING_EMAIL';
        }

        if ($heskSettings['require_subject'] === 1 &&
            ($editTicketModel->subject === NULL || $editTicketModel->subject === '')) {
            $validationModel->errorKeys[] = 'SUBJECT_REQUIRED';
        }

        if ($heskSettings['require_message'] === 1 &&
            ($editTicketModel->message === NULL || $editTicketModel->message === '')) {
            $validationModel->errorKeys[] = 'MESSAGE_REQUIRED';
        }

        foreach ($heskSettings['custom_fields'] as $key => $value) {
            $customFieldNumber = intval(str_replace('custom', '', $key));

            //TODO test this
            if ($editTicketModel->customFields === null || !array_key_exists($customFieldNumber, $editTicketModel->customFields)) {
                continue;
            }

            if ($value['use'] == 1 && CustomFieldValidator::isCustomFieldInCategory($customFieldNumber, intval($categoryId), false, $heskSettings)) {
                $custom_field_value = $editTicketModel->customFields[$customFieldNumber];
                if (empty($custom_field_value)) {
                    $validationModel->errorKeys[] = "CUSTOM_FIELD_{$customFieldNumber}_INVALID::NO_VALUE";
                    continue;
                }
                switch($value['type']) {
                    case CustomField::DATE:
                        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $custom_field_value)) {
                            $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $customFieldNumber . '_INVALID::INVALID_DATE';
                        } else {
                            // Actually validate based on range
                            $date = strtotime($custom_field_value . ' t00:00:00');
                            $dmin = strlen($value['value']['dmin']) ? strtotime($value['value']['dmin'] . ' t00:00:00') : false;
                            $dmax = strlen($value['value']['dmax']) ? strtotime($value['value']['dmax'] . ' t00:00:00') : false;

                            if ($dmin && $dmin > $date) {
                                $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $customFieldNumber . '_INVALID::DATE_BEFORE_MIN::MIN:' . date('Y-m-d', $dmin) . '::ENTERED:' . date('Y-m-d', $date);
                            } elseif ($dmax && $dmax < $date) {
                                $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $customFieldNumber . '_INVALID::DATE_AFTER_MAX::MAX:' . date('Y-m-d', $dmax) . '::ENTERED:' . date('Y-m-d', $date);
                            }
                        }
                        break;
                    case CustomField::EMAIL:
                        if (!Validators::validateEmail($custom_field_value, $value['value']['multiple'], false)) {
                            $validationModel->errorKeys[] = "CUSTOM_FIELD_{$customFieldNumber}_INVALID::INVALID_EMAIL";
                        }
                        break;
                }
            }
        }

        if ($editTicketModel->language === null ||
            $editTicketModel->language === '') {
            $validationModel->errorKeys[] = 'MISSING_LANGUAGE';
        }

        if (count($validationModel->errorKeys) > 0) {
            throw new ValidationException($validationModel);
        }
    }
}