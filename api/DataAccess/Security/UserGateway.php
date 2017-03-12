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
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);

        $this->close();

        return $row;
    }
    
    // TODO Replace this with a basic User retrieval
    function getNameForId($id, $heskSettings) {
        $this->init();
        
        $rs = hesk_dbQuery("SELECT `name` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `id` = " . intval($id));
        
        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }
        
        $row = hesk_dbFetchAssoc($rs);
        
        return $row['name'];
    }

    // TODO Replace this with a basic User retriever
    function getEmailForId($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `email` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` WHERE `id` = " . intval($id));

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);

        return $row['email'];
    }

    /**
     * @param $heskSettings array
     * @return UserContext[]
     */
    function getUsersByNumberOfOpenTickets($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `t1`.`id`,`t1`.`user`,`t1`.`name`, `t1`.`email`, `t1`.`language`, `t1`.`isadmin`, 
                            `t1`.`categories`, `t1`.`notify_assigned`, `t1`.`heskprivileges`,
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
}