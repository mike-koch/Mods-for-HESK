<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/22/2017
 * Time: 9:25 PM
 */

namespace BusinessLogic\Emails;


class ValidEmailTemplates {
    static function getValidEmailTemplates() {
        return array(
            'forgot_ticket_id' => 'forgot_ticket_id',
            'new_reply_by_staff' => 'new_reply_by_staff',
            'new_ticket' => 'ticket_received',
            'verify_email' => 'verify_email',
            'ticket_closed' => 'ticket_closed',
            'category_moved' => 'category_moved',
            'new_reply_by_customer' => 'new_reply_by_customer',
            'new_ticket_staff' => 'new_ticket_staff',
            'ticket_assigned_to_you' => 'ticket_assigned_to_you',
            'new_pm' => 'new_pm',
            'new_note' => 'new_note',
            'reset_password' => 'reset_password',
            'calendar_reminder' => 'calendar_reminder',
            'overdue_ticket' => 'overdue_ticket',
        );
    }
}