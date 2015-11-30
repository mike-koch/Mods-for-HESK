<?php
define('NULL_OR_EMPTY_STRING', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e');
require_once(API_PATH . 'exception/AccessException.php');

function get_user_for_token_hash($hash, $hesk_settings) {
    if ($hash == NULL_OR_EMPTY_STRING) {
        throw new AccessException(400);
    }

    $user_id_sql = "SELECT `user_id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "user_api_tokens`
        WHERE `token` = '" . hesk_dbEscape($hash) . "'";

    $user_id_rs = hesk_dbQuery($user_id_sql);
    if (hesk_dbNumRows($user_id_rs) == 0) {
        throw new AccessException(401);
    }
    $user_id = hesk_dbFetchAssoc($user_id_rs);

    $user_sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `id` = ".intval($user_id['user_id']);
    $user_rs = hesk_dbQuery($user_sql);

    return hesk_dbFetchAssoc($user_rs);
}