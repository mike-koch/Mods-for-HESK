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
                ON `t1`.`id` = `t2`.`nav_element_id`
            ORDER BY `t1`.`place` ASC, `t1`.`sort` ASC");

        $elements = array();

        /* @var $element CustomNavElement */
        $element = null;
        $previousId = -1;
        while ($row = hesk_dbFetchAssoc($rs)) {
            $id = intval($row['id']);
            if ($previousId !== $id) {
                if ($element !== null) {
                    $elements[$element->id] = $element;
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

    /**
     * @param $id int
     * @param $heskSettings array
     */
    function deleteCustomNavElement($id, $heskSettings) {
        $this->init();

        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text` 
            WHERE `nav_element_id` = " . intval($id));
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element` 
            WHERE `id` = " . intval($id));

        $this->close();
    }

    /**
     * @param $element CustomNavElement
     * @param $heskSettings array
     */
    function saveCustomNavElement($element, $heskSettings) {
        $this->init();

        //-- Delete previous records - easier than inserting/updating
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text` 
            WHERE `nav_element_id` = " . intval($element->id));

        $languageTextAndSubtext = array();
        foreach ($element->text as $key => $text) {
            $languageTextAndSubtext[$key]['text'] = $text;
        }
        foreach ($element->subtext as $key => $subtext) {
            $languageTextAndSubtext[$key]['subtext'] = $subtext;
        }

        foreach ($languageTextAndSubtext as $key => $values) {
            $subtext = 'NULL';
            if (isset($values['subtext'])) {
                $subtext = "'" . hesk_dbEscape($values['subtext']) . "'";
            }
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text`
                (`nav_element_id`, `language`, `text`, `subtext`) VALUES (" . intval($element->id) . ", 
                '" . hesk_dbEscape($key) . "',
                '" . hesk_dbEscape($values['text']) . "', 
                " . $subtext . ")");
        }

        $imageUrl = $element->imageUrl == null ? 'NULL' : "'" . hesk_dbEscape($element->imageUrl) . "'";
        $fontIcon = $element->fontIcon == null ? 'NULL' : "'" . hesk_dbEscape($element->fontIcon) . "'";
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element`
            SET `image_url` = {$imageUrl},
                `font_icon` = {$fontIcon},
                `place` = " . intval($element->place) .
            " WHERE `id` = " . intval($element->id));

        $this->close();
    }

    /**
     * @param $element CustomNavElement
     * @param $heskSettings array
     * @return CustomNavElement
     */
    function createCustomNavElement($element, $heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT MAX(`sort`) FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element`
            WHERE `place` = " . intval($element->place));
        $maxSort = hesk_dbFetchAssoc($rs);
        $sortValue = intval($maxSort['sort']) + 1;

        $imageUrl = $element->imageUrl == null ? 'NULL' : "'" . hesk_dbEscape($element->imageUrl) . "'";
        $fontIcon = $element->fontIcon == null ? 'NULL' : "'" . hesk_dbEscape($element->fontIcon) . "'";
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element`
            (`image_url`, `font_icon`, `place`, `sort`) 
            VALUES ({$imageUrl}, {$fontIcon}, " . intval($element->place) . ", " . $sortValue . ")");

        $element->id = hesk_dbInsertID();


        $languageTextAndSubtext = array();
        foreach ($element->text as $key => $text) {
            $languageTextAndSubtext[$key]['text'] = $text;
        }
        foreach ($element->subtext as $key => $subtext) {
            $languageTextAndSubtext[$key]['subtext'] = $subtext;
        }

        foreach ($languageTextAndSubtext as $key => $values) {
            $subtext = 'NULL';
            if (isset($values['subtext'])) {
                $subtext = "'" . hesk_dbEscape($values['subtext']) . "'";
            }
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element_to_text`
                (`nav_element_id`, `language`, `text`, `subtext`) VALUES (" . intval($element->id) . ", 
                '" . hesk_dbEscape($key) . "',
                '" . hesk_dbEscape($values['text']) . "', 
                " . $subtext . ")");
        }

        $this->close();

        return $element;
    }


    function resortAllElements($heskSettings) {
        $this->init();

        $rs = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element`
            ORDER BY `place` ASC, `sort` ASC");

        $sortValue = 10;
        while ($row = hesk_dbFetchAssoc($rs)) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($heskSettings['db_pfix']) . "custom_nav_element`
                SET `sort` = " . intval($sortValue) . "
                WHERE `id` = " . intval($row['id']));

            $sortValue += 10;
        }

        $this->close();
    }
}