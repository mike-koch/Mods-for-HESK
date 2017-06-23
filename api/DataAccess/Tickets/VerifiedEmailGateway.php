<?php

namespace DataAccess\Tickets;


use DataAccess\CommonDao;

class VerifiedEmailGateway extends CommonDao {
    function isEmailVerified($emailAddress, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT 1 FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "verified_emails` WHERE `Email` = '" . hesk_dbEscape($emailAddress) . "'");

        return hesk_dbNumRows($rs) > 0;
    }
}