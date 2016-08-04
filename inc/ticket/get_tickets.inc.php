<?php

/**
 * @param $search_filter Filter to search by. Valid criteria:
 *     //TODO
 */
function get_tickets($search_filter, $hesk_settings) {
    $sql = "SELECT `id`, `trackid`, `name`, `email`, `category`, `priority`, `subject`, LEFT(`message`, 400) AS `message`,
        `dt`, `lastchange`, `firstreply`, `closedat`, `status`, `openedby`, `firstreplyby`, `closedby`, `replies`, `staffreplies`, `owner`,
        `time_worked`, `lastreplier`, `replierid`, `archive`, `locked`, `merged`, `due_date`, `latitude`, `longitude`, `user_agent`, 
        `screen_resolution_width`, `screen_resolution_height`";

    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use']) {
            $sql_final .= ", `" . $k . "`";
        }
    }

    $sql_final .= " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE ";
}