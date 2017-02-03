<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Validation\ValidationModel;
use BusinessObjects\CreateTicketByCustomerModel;

class TicketCreator {
    /**
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $heskSettings array HESK settings
     * @param $modsForHeskSettings array Mods for HESK settings
     * @throws ValidationException When a required field in $ticket_request is missing
     */
    static function createTicketByCustomer($ticketRequest, $heskSettings, $modsForHeskSettings) {
        $validationModel = validate($ticketRequest, false, $heskSettings, $modsForHeskSettings);

        if (count($validationModel->errorKeys) > 0) {
            // Validation failed
            throw new ValidationException($validationModel);
        }

        // Create the ticket
    }

    /**
     * @param $ticketRequest CreateTicketByCustomerModel
     * @param $staff bool
     * @param $heskSettings array HESK settings
     * @param $modsForHeskSettings array Mods for HESK settings
     * @return ValidationModel If errorKeys is empty, validation successful. Otherwise invalid ticket
     */
    function validate($ticketRequest, $staff, $heskSettings, $modsForHeskSettings) {
        $TICKET_PRIORITY_CRITICAL = 0;

        $validationModel = new ValidationModel();

        if ($ticketRequest->name === NULL || $ticketRequest->name == '') {
            $validationModel->errorKeys[] = 'NO_NAME';
        }

        if (hesk_validateEmail($ticketRequest->email, $heskSettings['multi_eml'], false)) {
            $validationModel->errorKeys[] = 'INVALID_OR_MISSING_EMAIL';
        }

        if (intval($ticketRequest->category) === 0) {
            $allCategories = null;
            $validationModel->errorKeys[] = 'NO_CATEGORY';
        }

        // Don't allow critical priority tickets
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
            if ($value['use'] == 1 && hesk_is_custom_field_in_category($key, intval($ticketRequest->category))) {
                $custom_field_value = $ticketRequest->customFields[$key];
                if (empty($custom_field_value)) {
                    $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::NO_VALUE';
                    continue;
                }
                switch($value['type']) {
                    case 'date':
                        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $custom_field_value)) {
                            $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::INVALID_DATE';
                        } else {
                            // Actually validate based on range
                            $date = strtotime($custom_field_value . ' t00:00:00');
                            $dmin = strlen($value['value']['dmin']) ? strtotime($value['value']['dmin'] . ' t00:00:00') : false;
                            $dmax = strlen($value['value']['dmax']) ? strtotime($value['value']['dmax'] . ' t00:00:00') : false;

                            if ($dmin && $dmin > $date) {
                                $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::DATE_BEFORE_MIN::MIN-' . $dmin . '::ENTERED-' . $date;
                            } elseif ($dmax && $dmax < $date) {
                                $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::DATE_AFTER_MAX::MAX-' . $dmax . '::ENTERED-' . $date;
                            }
                        }
                        break;
                    case 'email':
                        if (!hesk_validateEmail($custom_field_value, $value['value']['multiple'], false)) {
                            $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::INVALID_OR_MISSING_EMAIL';
                        }
                        break;
                }
            }
        }

        // TODO Check bans (email only; don't check IP on REST requests as they'll most likely be sent via servers)
        // TODO     submit_ticket.php:320-322

        // TODO Check if we're at the max number of tickets
        // TODO     submit_ticket.php:325-334

        return $validationModel;
    }
}