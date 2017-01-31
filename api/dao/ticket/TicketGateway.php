<?php

namespace DataAccess\Tickets;


class TicketGateway {
    function getTicketsByEmail($emailAddress, $heskSettings) {
        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            WHERE `email` = '" . hesk_dbEscape($emailAddress) . "'");


    }
}