<?php
namespace DataAccess\CustomFields;

use BusinessLogic\Tickets\CustomFields\CustomField;
use DataAccess\CommonDao;

class CustomFieldsGateway extends CommonDao {
    public function getCustomField($id, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_fields` WHERE `id` = " . intval($id));

        if ($row = hesk_dbFetchAssoc($rs)) {
            $customField = new CustomField();
            $customField->id = $row['id'];
            $names = json_decode($row['name'], true);
            $customField->name = (isset($names[$heskSettings['language']])) ? $names[$heskSettings['language']] : reset($names);
            $customField->type = $row['type'];
            $customField->properties = json_decode($row['value'], true);

            return $customField;
        } else {
            return null;
        }
    }
}