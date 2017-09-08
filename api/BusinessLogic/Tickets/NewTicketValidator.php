<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Security\BanRetriever;
use BusinessLogic\Tickets\CustomFields\CustomFieldValidator;
use BusinessLogic\ValidationModel;
use BusinessLogic\Validators;
use Core\Constants\CustomField;

class NewTicketValidator {
    /**
     * @var $categoryRetriever CategoryRetriever
     */
    private $categoryRetriever;
    /**
     * @var $banRetriever BanRetriever
     */
    private $banRetriever;
    /**
     * @var $ticketValidators TicketValidators
     */
    private $ticketValidators;

    function __construct(CategoryRetriever $categoryRetriever,
                         BanRetriever $banRetriever,
                         TicketValidators $ticketValidators) {
        $this->categoryRetriever = $categoryRetriever;
        $this->banRetriever = $banRetriever;
        $this->ticketValidators = $ticketValidators;
    }

    /**
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $heskSettings array HESK settings
     * @return ValidationModel If errorKeys is empty, validation successful. Otherwise invalid ticket
     */
    function validateNewTicketForCustomer($ticketRequest, $heskSettings, $userContext) {
        $TICKET_PRIORITY_CRITICAL = 0;

        $validationModel = new ValidationModel();

        if ($ticketRequest->name === NULL || $ticketRequest->name == '') {
            $validationModel->errorKeys[] = 'NO_NAME';
        }

        if (!Validators::validateEmail($ticketRequest->email, $heskSettings['multi_eml'], false)) {
            $validationModel->errorKeys[] = 'INVALID_OR_MISSING_EMAIL';
        }

        $categoryId = intval($ticketRequest->category);
        if ($categoryId < 1) {
            $validationModel->errorKeys[] = 'NO_CATEGORY';
        } else {
            $categoryExists = array_key_exists($categoryId, $this->categoryRetriever->getAllCategories($heskSettings, $userContext));
            if (!$categoryExists) {
                $validationModel->errorKeys[] = 'CATEGORY_DOES_NOT_EXIST';
            }
        }

        //-- TODO assert priority exists

        if ($heskSettings['cust_urgency'] && intval($ticketRequest->priority) === $TICKET_PRIORITY_CRITICAL) {
            $validationModel->errorKeys[] = 'CRITICAL_PRIORITY_FORBIDDEN';
        }

        if ($heskSettings['require_subject'] === 1 &&
            ($ticketRequest->subject === NULL || $ticketRequest->subject === '')) {
            $validationModel->errorKeys[] = 'SUBJECT_REQUIRED';
        }

        if ($heskSettings['require_message'] === 1 &&
            ($ticketRequest->message === NULL || $ticketRequest->message === '')) {
            $validationModel->errorKeys[] = 'MESSAGE_REQUIRED';
        }

        foreach ($heskSettings['custom_fields'] as $key => $value) {
            $customFieldNumber = intval(str_replace('custom', '', $key));

            //TODO test this
            if (!array_key_exists($customFieldNumber, $ticketRequest->customFields)) {
                continue;
            }

            if ($value['use'] == 1 && CustomFieldValidator::isCustomFieldInCategory($customFieldNumber, intval($ticketRequest->category), false, $heskSettings)) {
                $custom_field_value = $ticketRequest->customFields[$customFieldNumber];
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

        if ($this->banRetriever->isEmailBanned($ticketRequest->email, $heskSettings)) {
            $validationModel->errorKeys[] = 'EMAIL_BANNED';
        }

        if ($this->ticketValidators->isCustomerAtMaxTickets($ticketRequest->email, $heskSettings)) {
            $validationModel->errorKeys[] = 'EMAIL_AT_MAX_OPEN_TICKETS';
        }

        if ($ticketRequest->language === null ||
            $ticketRequest->language === '') {
            $validationModel->errorKeys[] = 'MISSING_LANGUAGE';
        }

        return $validationModel;
    }
}