<?php

namespace DataAccess\Tickets;


use DataAccess\CommonDao;

class ReplyGateway extends CommonDao {
    function insertReply($ticketId, $name, $message, $html, $heskSettings) {
        $this->init();

        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`, `html`) 
            VALUES ({$ticketId},'" . hesk_dbEscape($name) . "','" . hesk_dbEscape($message) . "',NOW(),'','" . $html . "')");

        $this->close();
    }
}