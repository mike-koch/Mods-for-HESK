<?php

namespace DataAccess\Tickets;


use BusinessLogic\Tickets\Ticket;
use DataAccess\CommonDao;

class TicketGateway extends CommonDao {
    /**
     * @param $id int
     * @param $heskSettings array
     * @return Ticket|null
     */
    function getTicketById($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `id` = " . intval($id));

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);
        $linkedTicketsRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($id));

        $ticket = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $heskSettings);

        $this->close();

        return $ticket;
    }

    /**
     * @param $emailAddress string
     * @param $heskSettings array
     * @return array|null
     */
    function getTicketsByEmail($emailAddress, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` 
            WHERE `email` = '" . hesk_dbEscape($emailAddress) . "'");

        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $tickets = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $linkedTicketsRs =
                hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($row['id']));

            $tickets[] = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $heskSettings);
        }

        $this->close();

        return $tickets;
    }

    /**
     * @param $trackingId string
     * @param $heskSettings array
     * @return Ticket|null
     */
    function getTicketByTrackingId($trackingId, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `id` = " . intval($trackingId));
        if (hesk_dbNumRows($rs) === 0) {
            return null;
        }

        $row = hesk_dbFetchAssoc($rs);
        $linkedTicketsRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` WHERE `parent` = " . intval($trackingId));

        $ticket = Ticket::fromDatabaseRow($row, $linkedTicketsRs, $heskSettings);

        $this->close();

        return $ticket;
    }
}