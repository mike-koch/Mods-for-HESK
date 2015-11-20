<?php

function store_token($user_id, $token_hash, $hesk_settings) {
    $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` (`user_id`, `token`)
        VALUES (" . intval($user_id) . ", '" . hesk_dbEscape($token_hash) . "')";
    hesk_dbQuery($sql);
}

function reset_tokens($user_id, $hesk_settings) {
    $sql = "DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` WHERE `user_id` = ".intval($user_id);
    hesk_dbQuery($sql);
}