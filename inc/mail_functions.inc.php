<?php

function mfh_get_mail_headers_for_dropdown($user_id, $hesk_settings, $hesklang) {
    $deleted_user_text = hesk_dbEscape($hesklang['deleted_user_title_case']);
    $sql = "SELECT `mail`.`id` AS `id`, `mail`.`subject` AS `subject`, 
              `users`.`name` AS `from`, `mail`.`dt` AS `date`, `mail`.`from` AS `from_id`
        FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` AS `mail`
        LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `users`
            ON `mail`.`from` = `users`.`id`
        WHERE `to` = " . hesk_dbEscape($user_id) . "
            AND `read` = '0'
        ORDER BY `mail`.`dt` DESC";
    
    $rs = hesk_dbQuery($sql);

    $mails = array();
    while ($row = hesk_dbFetchAssoc($rs)) {
        if ($row['from'] == null) {
            if ($row['from_id'] == 9999) {
                $row['from'] = 'HESK.com';
            } else {
                $row['from'] = $deleted_user_text;
            }
        }

        $mails[] = $row;
    }

    return $mails;
}