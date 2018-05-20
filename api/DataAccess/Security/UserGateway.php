<?php
namespace DataAccess\Security;


use BusinessLogic\Security\UserContext;
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
                SELECT `user_id`
                FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "user_api_tokens`
                WHERE `token` = '" . hesk_dbEscape($hashedToken) . "'
            ) AND `active` = '1'");

        if (hesk_dbNumRows($rs) === 0) {
            $this->close();
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);

        $this->close();

        return $row;
    }

    function getUserById($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `id` = " . intval($id));

        if (hesk_dbNumRows($rs) === 0) {
            $this->close();
            return null;
        }

        $user = UserContext::fromDataRow(hesk_dbFetchAssoc($rs));

        $this->close();

        return $user;
    }

    /**
     * @param $heskSettings array
     * @return UserContext[]
     */
    function getUsersByNumberOfOpenTicketsForAutoassign($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `t1`.*,
					        (SELECT COUNT(*) FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets`
					            WHERE `owner`=`t1`.`id` 
					            AND `status` IN (
					                SELECT `ID` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "statuses` 
					                WHERE `IsClosed` = 0
					            ) 
					        ) AS `open_tickets`
						FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` AS `t1`
						WHERE `t1`.`autoassign` = '1' ORDER BY `open_tickets` ASC, RAND()");

        $users = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $user = UserContext::fromDataRow($row);
            $users[] = $user;
        }

        $this->close();

        return $users;
    }

    /**
     * @param $heskSettings array
     * @return UserContext[]
     */
    function getUsersForNewTicketNotification($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `notify_new_unassigned` = '1' AND `active` = '1'");

        $users = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            $users[] = UserContext::fromDataRow($row);
        }

        $this->close();

        return $users;
    }

    function getUsersForUnassignedReplyNotification($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `notify_reply_unassigned` = '1' AND `active` = '1'");

        $users = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            $users[] = UserContext::fromDataRow($row);
        }

        $this->close();

        return $users;
    }

    function getManagerForCategory($categoryId, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` 
            WHERE `id` = (
                SELECT `manager` 
                FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories`
                WHERE `id` = " . intval($categoryId) . ")");

        if (hesk_dbNumRows($rs) === 0) {
            $this->close();
            return null;
        }

        $user = UserContext::fromDataRow(hesk_dbFetchAssoc($rs));

        $this->close();

        return $user;
    }
}