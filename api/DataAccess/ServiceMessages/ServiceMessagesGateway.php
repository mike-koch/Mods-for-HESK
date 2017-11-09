<?php

namespace DataAccess\ServiceMessages;


use BusinessLogic\ServiceMessages\ServiceMessage;
use BusinessLogic\ServiceMessages\ServiceMessageStyle;
use DataAccess\CommonDao;

class ServiceMessagesGateway extends CommonDao {
    /**
     * @param $serviceMessage ServiceMessage
     * @return ServiceMessage
     */
    function createServiceMessage($serviceMessage, $heskSettings) {
        $this->init();

        // Get the latest service message order
        $res = hesk_dbQuery("SELECT `order` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "service_messages` ORDER BY `order` DESC LIMIT 1");
        $row = hesk_dbFetchRow($res);
        $myOrder = intval($row[0]) + 10;

        $style = ServiceMessageStyle::getIdForStyle($serviceMessage->style);
        $type = $serviceMessage->published ? 0 : 1;

        // Insert service message into database
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "service_messages` (`author`,`title`,`message`,`style`,`type`,`order`, `icon`) VALUES (
            '" . intval($serviceMessage->createdBy) . "',
            '" . hesk_dbEscape($serviceMessage->title) . "',
            '" . hesk_dbEscape($serviceMessage->message) . "',
            '" . hesk_dbEscape($style) . "',
            '{$type}',
            '{$myOrder}',
            '" . hesk_dbEscape($serviceMessage->icon) . "'
            )");

        $serviceMessage->id = hesk_dbInsertID();

        $this->close();

        return $serviceMessage;
    }
}