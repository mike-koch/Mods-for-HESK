<?php

namespace DataAccess\Statuses;


use BusinessLogic\Statuses\DefaultStatusForAction;
use BusinessLogic\Statuses\Status;
use DataAccess\CommonDao;

class StatusGateway extends CommonDao {

    /**
     * @param $defaultAction string
     * @return Status
     */
    function getStatusForDefaultAction($defaultAction, $heskSettings) {
        $this->init();

        $metaRs = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($heskSettings['db_pfix']) . 'statuses` 
            WHERE `' . $defaultAction . '` = 1');
        if (hesk_dbNumRows($metaRs) === 0) {
            return null;
        }
        $row = hesk_dbFetchAssoc($metaRs);

        $languageRs = hesk_dbQuery('SELECT * FROM `' . hesk_dbEscape($heskSettings['db_pfix']) . 'text_to_status_xref`
            WHERE `status_id` = ' . intval($row['ID']));

        $status = Status::fromDatabase($row, $languageRs);

        $this->close();

        return $status;
    }
}