<?php

namespace DataAccess\Navigation;


use DataAccess\CommonDao;

class CustomNavElementGateway extends CommonDao {
    function getAllCustomNavElements($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `t2`.`id` AS `xref_id`, `t2`.*, `t1`.* FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element` AS `t1`
            INNER JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text` AS `t2`
                ON `t1`.`id` = `t2`.`nav_element_id`");

        while ($row = hesk_dbFetchAssoc($rs)) {
            var_dump($row);
        }

        $this->close();
    }
}