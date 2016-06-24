<?php

function search_log($hesk_settings, $location, $from_date, $to_date, $severity_id) {
    if (!function_exists('hesk_date')) {
        return null;
    }

    $sql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "logging` WHERE 1=1 ";

    if ($location != NULL) {
        $sql .= "AND `location` LIKE '%" . hesk_dbEscape(hesk_dbLike($location)) . "%' ";
    }
    $from_date_format = preg_match("/\d{4}-\d{2}-\d{2}/", $from_date);
    if ($from_date != NULL
        && $from_date_format === 1) {
        $sql .= "AND `timestamp` >= '" . hesk_dbEscape($from_date) . " 00:00:00' ";
    }
    $to_date_format = preg_match("/\d{4}-\d{2}-\d{2}/", $to_date);
    if ($to_date != NULL
        && $to_date_format === 1) {
        $sql .= "AND `timestamp` <= '" . hesk_dbEscape($to_date) . " 23:59:59' ";
    }
    if ($severity_id != NULL) {
        $sql .= "AND `severity` = " . intval($severity_id);
    }

    $rs = hesk_dbQuery($sql);

    $results = array();
    while ($row = hesk_dbFetchAssoc($rs)) {
        $row['timestamp'] = hesk_date($row['timestamp'], true);
        $results[] = $row;
    }

    return $results;
}