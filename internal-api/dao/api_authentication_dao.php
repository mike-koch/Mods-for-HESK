<?php

function store_token($user_id, $token_hash, $hesk_settings) {
    $sql = "INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens` (`user_id`, `token`)
        VALUES ('" . hesk_dbEscape($user_id) . "', '" . hesk_dbEscape($token_hash) . "')";
    hesk_dbQuery($sql);
}