<?php

function get_user_for_token_hash($hash, $hesk_settings) {
    $user_id_sql = "SELECT `user_id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens`
        WHERE `token` = '" . hesk_dbEscape($hash) . "'";

    $user_id_rs = hesk_dbQuery($user_id_sql);
    if (hesk_dbNumRows($user_id_rs) == 0) {
        return http_response_code(422);
    }
    $user_id = hesk_dbFetchAssoc($user_id_rs);

    $user_sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id` = ".intval($user_id['user_id']);
    $user_rs = hesk_dbQuery($user_sql);

    return hesk_dbFetchAssoc($user_rs);
}