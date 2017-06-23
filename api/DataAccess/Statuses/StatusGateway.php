<?php

namespace DataAccess\Statuses;


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

    /**
     * @param $heskSettings array
     * @return Status[]
     */
    function getStatuses($heskSettings) {
        $this->init();

        $metaRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "statuses`");

        $statuses = array();
        while ($row = hesk_dbFetchAssoc($metaRs)) {
            $languageRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "text_to_status_xref`
                WHERE `status_id` = " . intval($row['ID']));

            $statuses[] = Status::fromDatabase($row, $languageRs);
        }

        $this->close();

        return $statuses;
    }
}