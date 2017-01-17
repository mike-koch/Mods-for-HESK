<?php

/**
 * @param $ticket_request CreateTicketByCustomerModel
 * @param $hesk_settings array HESK settings
 * @param $modsForHesk_settings array Mods for HESK settings
 * @throws ValidationException When a required field in $ticket_request is missing
 */
function createTicketByCustomer($ticket_request, $hesk_settings, $modsForHesk_settings) {
    $validationModel = validate($ticket_request, false, $hesk_settings, $modsForHesk_settings);

    if (count($validationModel->errorKeys) > 0) {
        require_once(__DIR__ . '/../ValidationException.php');

        // Validation failed
        throw new ValidationException($validationModel);
    }

    // Create the ticket
}

/**
 * @param $ticket_request CreateTicketByCustomerModel
 * @param $staff bool
 * @return ValidationModel If errorKeys is empty, validation successful. Otherwise invalid ticket
 */
function validate($ticket_request, $staff, $hesk_settings, $modsForHesk_settings) {
    require_once(__DIR__ . '/../email_validators.php');
    require_once(__DIR__ . '/../../dao/category_dao.php');
    //require_once('../category/retriever.php');
    //require_once('../bans/retriever.php');

    $TICKET_PRIORITY_CRITICAL = 0;

    $validationModel = new ValidationModel();

    if ($ticket_request->name === NULL || $ticket_request->name == '') {
        $validationModel->errorKeys[] = 'NO_NAME';
    }

    if (hesk_validateEmail($ticket_request->email, $hesk_settings['multi_eml'], false)) {
        $validationModel->errorKeys[] = 'INVALID_OR_MISSING_EMAIL';
    }

    if (intval($ticket_request->category) === 0) {
        $allCategories = null;
        $validationModel->errorKeys[] = 'NO_CATEGORY';
    }

    // Don't allow critical priority tickets
    if ($hesk_settings['cust_urgency'] && intval($ticket_request->priority) === $TICKET_PRIORITY_CRITICAL) {
        $validationModel->errorKeys[] = 'CRITICAL_PRIORITY_FORBIDDEN';
    }

    if ($hesk_settings['require_subject'] === 1 &&
        ($ticket_request->subject === NULL || $ticket_request->subject === '')) {
        $validationModel->errorKeys[] = 'SUBJECT_REQUIRED';
    }

    if ($hesk_settings['require_message'] === 1 &&
        ($ticket_request->message === NULL || $ticket_request->message === '')) {
        $validationModel->errorKeys[] = 'MESSAGE_REQUIRED';
    }

    foreach ($hesk_settings['custom_fields'] as $key => $value) {
        // TODO Only check categories that apply to this custom field
        if ($value['use'] == 1 && hesk_is_custom_field_in_category($key, intval($ticket_request->category))) {
            $custom_field_value = $ticket_request->customFields[$key];
            if (empty($custom_field_value)) {
                $validationModel->errorKeys[] = 'CUSTOM_FIELD_' . $key . '_INVALID::NO_VALUE';
                continue;
            }
            switch($v['type']) {
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