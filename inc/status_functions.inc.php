<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}


function mfh_getAllStatuses()
{
    global $hesk_settings, $modsForHesk_settings;

    $statusesSql = 'SELECT * FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'statuses` ORDER BY `sort` ASC';
    $statusesRS = hesk_dbQuery($statusesSql);
    $statuses = array();
    while ($row = hesk_dbFetchAssoc($statusesRS)) {
        $row['text'] = mfh_getDisplayTextForStatusId($row['ID']);
        $statuses[$row['text']] = $row;
    }

    if ($modsForHesk_settings['statuses_order_column'] == 'name') {
        ksort($statuses);
    }

    return $statuses;
}