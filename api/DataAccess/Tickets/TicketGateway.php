<?php

namespace DataAccess\Tickets;


use BusinessLogic\Tickets\Ticket;
use DataAccess\CommonDao;

class TicketGateway extends CommonDao {
    function getTicketById($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `id` = " . intval($id));
        $row = hesk_dbFetchAssoc($rs);
        $linkedTicketsRs = hesk_dbQuery("SELECT * FROM `hesk_tickets` WHERE `parent` = " . intval($id));

        $ticket = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $heskSettings);

        $this->close();

        return $ticket;
    }

    function getTicketsByEmail($emailAddress, $heskSettings) {
        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            WHERE `email` = '" . hesk_dbEscape($emailAddress) . "'");

        $tickets = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $ticket = new Ticket();

            //-- TODO Finish this!
        }
    }
}