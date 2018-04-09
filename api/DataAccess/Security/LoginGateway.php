<?php

namespace DataAccess\Security;


use DataAccess\CommonDao;

class LoginGateway extends CommonDao {
    function isIpLockedOut($ipAddress, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `number` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "logins` 
            WHERE `ip` = '" . hesk_dbEscape($ipAddress) . "' 
                AND `last_attempt` IS NOT NULL 
                AND DATE_ADD(`last_attempt`, INTERVAL ".intval($heskSettings['attempt_banmin'])." MINUTE ) > NOW() LIMIT 1");

        $result = hesk_dbNumRows($rs) == 1 &&
                hesk_dbResult($rs) >= $heskSettings['attempt_limit'];

        $this->close();

        return $result;
    }
}