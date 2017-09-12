<?php

namespace BusinessLogic\Emails;


class EmailTemplateRetriever extends \BaseClass {
    /**
     * @var $validTemplates EmailTemplate[]
     */
    private $validTemplates;

    function __construct() {
        $this->validTemplates = array();
        $this->initializeArray();
    }

    const FORGOT_TICKET_ID = 0;
    const NEW_REPLY_BY_STAFF = 1;
    const NEW_TICKET = 2;
    const VERIFY_EMAIL = 3;
    const TICKET_CLOSED = 4;
    const CATEGORY_MOVED = 5;
    const NEW_REPLY_BY_CUSTOMER = 6;
    const NEW_TICKET_STAFF = 7;
    const TICKET_ASSIGNED_TO_YOU = 8;
    const NEW_PM = 9;
    const NEW_NOTE = 10;
    const RESET_PASSWORD = 11;
    const CALENDAR_REMINDER = 12;
    const OVERDUE_TICKET = 13;

    function initializeArray() {
        if (count($this->validTemplates) > 0) {
            //-- Map already built
            return;
        }

        $this->validTemplates[self::FORGOT_TICKET_ID] = new EmailTemplate(false, 'forgot_ticket_id');
        $this->validTemplates[self::NEW_REPLY_BY_STAFF] = new EmailTemplate(false, 'new_reply_by_staff');
        $this->validTemplates[self::NEW_TICKET] = new EmailTemplate(false, 'new_ticket', 'ticket_received');
        $this->validTemplates[self::VERIFY_EMAIL] = new EmailTemplate(false, 'verify_email');
        $this->validTemplates[self::TICKET_CLOSED] = new EmailTemplate(false, 'ticket_closed');
        $this->validTemplates[self::CATEGORY_MOVED] = new EmailTemplate(true, 'category_moved');
        $this->validTemplates[self::NEW_REPLY_BY_CUSTOMER] = new EmailTemplate(true, 'new_reply_by_customer');
        $this->validTemplates[self::NEW_TICKET_STAFF] = new EmailTemplate(true, 'new_ticket_staff');
        $this->validTemplates[self::TICKET_ASSIGNED_TO_YOU] = new EmailTemplate(true, 'ticket_assigned_to_you');
        $this->validTemplates[self::NEW_PM] = new EmailTemplate(true, 'new_pm');
        $this->validTemplates[self::NEW_NOTE] = new EmailTemplate(true, 'new_note');
        $this->validTemplates[self::RESET_PASSWORD] = new EmailTemplate(true, 'reset_password');
        $this->validTemplates[self::CALENDAR_REMINDER] = new EmailTemplate(true, 'reset_password');
        $this->validTemplates[self::OVERDUE_TICKET] = new EmailTemplate(true, 'overdue_ticket');
    }

    /**
     * @param $templateId
     * @return EmailTemplate|null
     */
    function getTemplate($templateId) {
        if (isset($this->validTemplates[$templateId])) {
            return $this->validTemplates[$templateId];
        }

        return null;
    }
}