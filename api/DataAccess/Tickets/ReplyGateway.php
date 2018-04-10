<?php

namespace DataAccess\Tickets;


use BusinessLogic\Tickets\CustomerCreatedReplyModel;
use DataAccess\CommonDao;

class ReplyGateway extends CommonDao {
    function insertReply($ticketId, $name, $message, $html, $heskSettings) {
        $this->init();

        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`, `html`) 
            VALUES ({$ticketId},'" . hesk_dbEscape($name) . "','" . hesk_dbEscape($message) . "',NOW(),'','" . $html . "')");

        $customerCreatedReplyModel = new CustomerCreatedReplyModel();
        $id = hesk_dbInsertID();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "replies` WHERE `id` = " . intval($id));
        $row = hesk_dbFetchAssoc($rs);

        $customerCreatedReplyModel->id = $row['id'];
        $customerCreatedReplyModel->message = $row['message'];
        $customerCreatedReplyModel->ticketId = $row['replyto'];
        $customerCreatedReplyModel->dateCreated = hesk_date($row['dt'], true);
        $customerCreatedReplyModel->html = $row['html'] === '1';
        $customerCreatedReplyModel->replierName = $row['name'];

        $this->close();

        return $customerCreatedReplyModel;
    }
}