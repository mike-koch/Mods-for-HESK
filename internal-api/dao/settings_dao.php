<?php

function update_setting($key, $value, $hesk_settings) {
    $sql = "UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "settings` SET
        `Value` = '" . hesk_dbEscape($value) . "' WHERE `Key` = '" . hesk_dbEscape($key) . "'";

    hesk_dbQuery($sql);
}