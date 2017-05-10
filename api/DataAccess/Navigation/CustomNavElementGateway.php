<?php

namespace DataAccess\Navigation;


use BusinessLogic\Navigation\CustomNavElement;
use DataAccess\CommonDao;

class CustomNavElementGateway extends CommonDao {
    function getAllCustomNavElements($heskSettings) {
        $this->init();

        $columns = '`t1`.`id`, `t1`.`image_url`, `t1`.`font_icon`, `t1`.`place`, `t2`.`language`, `t2`.`text`, `t2`.`subtext`';

        $rs = hesk_dbQuery("SELECT {$columns} FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element` AS `t1`
            INNER JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text` AS `t2`
                ON `t1`.`id` = `t2`.`nav_element_id`");

        $elements = array();
        $element = null;
        $previousId = -1;
        while ($row = hesk_dbFetchAssoc($rs)) {
            $id = intval($row['id']);
            if ($previousId !== $id) {
                if ($element !== null) {
                    $elements[] = $element;
                }
                $element = new CustomNavElement();
                $element->id = $id;
                $element->place = intval($row['place']);
                $element->imageUrl = $row['image_url'];
                $element->fontIcon = $row['font_icon'];
                $element->text = array();
                $element->subtext = array();
            }

            $element->text[$row['language']] = $row['text'];
            $element->subtext[$row['language']] = $row['subtext'];

            $previousId = $id;
        }

        if ($element !== null) {
            $elements[] = $element;
        }

        $this->close();

        return $elements;
    }
}