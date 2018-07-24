<?php

namespace DataAccess\AuditTrail;


use BusinessLogic\DateTimeHelpers;
use BusinessLogic\Helpers;
use DataAccess\CommonDao;

class AuditTrailGateway extends CommonDao {
    function insertAuditTrailRecord($entityId, $entityType, $languageKey, $date, $replacementValues, $heskSettings) {
        $this->init();

        $oldTimeFormat = $heskSettings['timeformat'];
        $heskSettings['timeformat'] = 'Y-m-d H:i:s';
        $date = DateTimeHelpers::heskDate($heskSettings);


        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "audit_trail` (`entity_id`, `entity_type`, 
        `language_key`, `date`) VALUES (" . intval($entityId) . ", '" . hesk_dbEscape($entityType) . "',
            '" . hesk_dbEscape($languageKey) . "', '" . hesk_dbEscape($date) . "')");

        $auditId = hesk_dbInsertID();

        foreach ($replacementValues as $replacementIndex => $replacementValue) {
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "audit_trail_to_replacement_values`
            (`audit_trail_id`, `replacement_index`, `replacement_value`) VALUES (" . intval($auditId) . ", 
                " . intval($replacementIndex) . ", '" . hesk_dbEscape($replacementValue) . "')");
        }
        $heskSettings['timeformat'] = $oldTimeFormat;

        $this->close();

        return $auditId;
    }
}