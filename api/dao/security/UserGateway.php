<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/21/17
 * Time: 4:23 PM
 */

namespace DataAccess\Security;


use BusinessLogic\Security\UserContextBuilder;
use Exception;

class UserGateway {
    static function getUserForAuthToken($hashedToken, $hesk_settings) {
        require_once(__DIR__ . '/../../businesslogic/security/UserContextBuilder.php');

        if (!function_exists('hesk_dbConnect')) {
            throw new Exception('Database not loaded!');
        }
        hesk_dbConnect();

        $rs = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'users` WHERE `id` = (
                SELECT ``
                FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'user_api_tokens`
                WHERE `token` = ' . hesk_dbEscape($hashedToken) . '
            )');

        $row = hesk_dbFetchAssoc($rs);

        return UserContextBuilder::fromDataRow($row);
    }
}