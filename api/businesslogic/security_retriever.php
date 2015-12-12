<?php
require_once(API_PATH . 'dao/security_dao.php');

function get_user_for_token($token, $hesk_settings) {

    $hash = hash('sha512', $token);

    return get_user_for_token_hash($hash, $hesk_settings);
}