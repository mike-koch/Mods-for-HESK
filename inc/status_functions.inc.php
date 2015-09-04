<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.5 from 28th August 2015
 *  Author: Klemen Stirn
 *  Website: http://www.hesk.com
 ********************************************************************************
 *  COPYRIGHT AND TRADEMARK NOTICE
 *  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
 *  HESK is a registered trademark of Klemen Stirn.

 *  The HESK may be used and modified free of charge by anyone
 *  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
 *  By using this code you agree to indemnify Klemen Stirn from any
 *  liability that might arise from it's use.

 *  Selling the code for this program, in part or full, without prior
 *  written consent is expressly forbidden.

 *  Using this code, in part or full, to create derivate work,
 *  new scripts or products is expressly forbidden. Obtain permission
 *  before redistributing this software over the Internet or in
 *  any other medium. In all cases copyright and header must remain intact.
 *  This Copyright is in full effect in any country that has International
 *  Trade Agreements with the United States of America or
 *  with the European Union.

 *  Removing any of the copyright notices without purchasing a license
 *  is expressly forbidden. To remove HESK copyright notice you must purchase
 *  a license for this script. For more information on how to obtain
 *  a license please visit the page below:
 *  https://www.hesk.com/buy.php
 *******************************************************************************/

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}


function mfh_getAllStatuses() {
    global $hesk_settings, $modsForHesk_settings;

    $statusesSql = 'SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'statuses` ORDER BY `sort` ASC';
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