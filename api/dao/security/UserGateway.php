<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/21/17
 * Time: 4:23 PM
 */

namespace DataAccess\Security;


use BusinessLogic\Security\UserContextBuilder;
use DataAccess\CommonDao;
use Exception;

class UserGateway extends CommonDao {
    /**
     * @param $hashedToken string The pre-hashed token from Helpers::hashToken
     * @param $heskSettings
     * @return array|null User ResultSet if an active user for the token is found, null otherwise
     */
    function getUserForAuthToken($hashedToken, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `id` = (
                SELECT ``
                FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "user_api_tokens`
                WHERE `tokens`.`token` = " . hesk_dbEscape($hashedToken) . "
            ) AND `active` = '1'");

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);

        $this->close();

        return $row;
    }
}