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

define('IN_SCRIPT', 1);
define('HESK_PATH', './');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();

hesk_session_start();

// Do we have parameters in query string? If yes, store them in session and redirect
if ( isset($_GET['track']) || isset($_GET['e']) )
{
    $_SESSION['p_track'] = hesk_GET('track');
    $_SESSION['p_email'] = hesk_GET('e');

    header('Location: print.php');
    die();
}


/* Get the tracking ID */
$trackingID = hesk_cleanID('p_track') or die("$hesklang[int_error]: $hesklang[no_trackID]");

/* Connect to database */
hesk_dbConnect();

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// Perform additional checks for customers
if (empty($_SESSION['id'])) {
    // Are we in maintenance mode?
    hesk_check_maintenance();

    // Verify email address match
    hesk_verifyEmailMatch($trackingID);
    $my_email = hesk_getCustomerEmail(0, 'p_email');
    hesk_verifyEmailMatch($trackingID, $my_email);
}

/* Clean ticket parameters from the session data, we don't need them anymore */
hesk_cleanSessionVars( array('p_track', 'p_email') );

/* Get ticket info */
$res = hesk_dbQuery("SELECT `t1`.* , `ticketStatus`.`IsClosed` AS `isClosed`, `ticketStatus`.`Key` AS `statusKey`, `t2`.name AS `repliername`
					FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` AS `t1` LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` AS `t2` ON `t1`.`replierid` = `t2`.`id`
					INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` AS `ticketStatus` ON `t1`.`status` = `ticketStatus`.`ID`
					WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");

if (hesk_dbNumRows($res) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($res);

// Demo mode
if (defined('HESK_DEMO')) {
    $ticket['email'] = 'hidden@demo.com';
    $ticket['ip'] = '127.0.0.1';
}

/* Get category name and ID */
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='{$ticket['category']}' LIMIT 1");

/* If this category has been deleted use the default category with ID 1 */
if (hesk_dbNumRows($res) != 1) {
    $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='1' LIMIT 1");
}
$category = hesk_dbFetchAssoc($res);

/* Get replies */
$res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='{$ticket['id']}' ORDER BY `id` ASC");
$replies = hesk_dbNumRows($res);

$modsForHesk_settings = mfh_getSettings();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title><?php echo $hesk_settings['hesk_title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $hesklang['ENCODING']; ?>">
    <style type="text/css">
        body, table, td {
            color: black;
            font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
            font-size: <?php echo $hesk_settings['print_font_size']; ?>px;
        }

        p {
            color: black;
            font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
            font-size: <?php echo $hesk_settings['print_font_size']; ?>px;
            margin-top: 0;
            margin-bottom: 0;
        }

        table {
            border-collapse: collapse;
        }

        hr {
            border: 0;
            color: #9e9e9e;
            background-color: #9e9e9e;
            height: 1px;
            width: 100%;
            text-align: <?php if ($modsForHesk_settings['rtl']) {echo 'right';} else {echo 'left';} ?>;
        }
    </style>
</head>
<body onload="window.print()">

<?php

/* Ticket priority */
switch ($ticket['priority']) {
    case 0:
        $ticket['priority'] = '<b>' . $hesklang['critical'] . '</b>';
        break;
    case 1:
        $ticket['priority'] = '<b>' . $hesklang['high'] . '</b>';
        break;
    case 2:
        $ticket['priority'] = $hesklang['medium'];
        break;
    default:
        $ticket['priority'] = $hesklang['low'];
}

/* Set last replier name */
if ($ticket['lastreplier']) {
    if (empty($ticket['repliername'])) {
        $ticket['repliername'] = $hesklang['staff'];
    }
} else {
    $ticket['repliername'] = $ticket['name'];
}

/* Other variables that need processing */
$ticket['dt'] = hesk_date($ticket['dt'], true);
$ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
$random = mt_rand(10000, 99999);

// Print ticket head
echo '
<h3>' . $ticket['subject'] . '</h3>
<hr/>
<table border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="2" width="100%">

<tr>
	<td bgcolor="#EEE"><b>' . $hesklang['trackID'] . ':</b></td><td bgcolor="#DDD">' . $trackingID . '</td>
	<td bgcolor="#EEE"><b>' . $hesklang['ticket_status'] . ':</b></td><td bgcolor="#DDD">' . $hesklang[$ticket['statusKey']] . '</td>
	<td bgcolor="#EEE"><b>' . $hesklang['created_on'] . ':</b></td><td bgcolor="#DDD">' . $ticket['dt'] . '</td>
</tr>
<tr>
	<td bgcolor="#EEE"><b>' . $hesklang['last_update'] . ':</b></td><td bgcolor="#DDD">' . $ticket['lastchange'] . '</td>
    <td bgcolor="#EEE"><b>' . $hesklang['last_replier'] . ':</b></td><td bgcolor="#DDD">' . $ticket['repliername'] . '</td>
    <td bgcolor="#EEE"><b>' . $hesklang['category'] . ':</b></td><td bgcolor="#DDD">' . $category['name'] . '</td>
</tr>
';

// Show IP and time worked to staff
if (!empty($_SESSION['id'])) {
    echo '
	<tr>
		<td bgcolor="#EEE"><b>' . $hesklang['ts'] . ':</b></td><td bgcolor="#DDD">' . $ticket['time_worked'] . '</td>
        <td bgcolor="#EEE"><b>' . $hesklang['ip'] . ':</b></td><td bgcolor="#DDD">' . $ticket['ip'] . '</td>
		<td bgcolor="#EEE"><b>' . $hesklang['email'] . ':</b></td><td bgcolor="#DDD">' . $ticket['email'] . '</td>
	</tr>
	';
}

echo '<tr>';
// Assigned to?
if ($ticket['owner'] && !empty($_SESSION['id'])) {
    $ticket['owner'] = hesk_getOwnerName($ticket['owner']);
    echo '
		<td bgcolor="#EEE"><b>' . $hesklang['taso3'] . '</b></td>
		<td bgcolor="#DDD">' . $ticket['owner'] . '</td>
	';
}


echo '
		<td bgcolor="#EEE"><b>' . $hesklang['name'] . ':</b></td>
		<td bgcolor="#DDD">' . $ticket['name'] . '</td>
    ';
echo '</tr>';

// Custom fields
$num_cols = 0;
echo '<tr>';
foreach ($hesk_settings['custom_fields'] as $k => $v) {
    if (($v['use'] == 1 || (! empty($_SESSION['id']) && $v['use'] == 2)) && hesk_is_custom_field_in_category($k, $ticket['category'])) {
        if ($num_cols == 3) {
            echo '</tr><tr>';
            $num_cols = 0;
        }

        switch ($v['type']) {
            case 'date':
                $ticket[$k] = hesk_custom_date_display_format($ticket[$k], $v['value']['date_format']);
                break;
        }
        ?>
        <td bgcolor="#EEE"><b><?php echo $v['name']; ?>:</b></td>
        <td bgcolor="#DDD"><?php echo hesk_unhortenUrl($ticket[$k]); ?></td>
        <?php
        $num_cols++;
    }
}

// Close ticket head table
echo '</table><br>';

// Print initial ticket message
if ($ticket['message'] != '') {
    $newMessage = hesk_unhortenUrl($ticket['message']);
    if ($ticket['html']) {
        $newMessage = hesk_html_entity_decode($newMessage);
    }
    echo '<p>' . $newMessage . '</p>';
}


// Print replies
while ($reply = hesk_dbFetchAssoc($res)) {
    $reply['dt'] = hesk_date($reply['dt'], true);
    $theReply = hesk_unhortenUrl($reply['message']);
    if ($reply['html']) {
        $theReply = hesk_html_entity_decode($theReply);
    }

    echo '
    <hr />

	<table border="1" bordercolor="#FFFFFF" cellspacing="0" cellpadding="2" width="100%">
	<tr>
		<td bgcolor="#EEE"><b>' . $hesklang['date'] . ':</b></td><td bgcolor="#DDD">' . $reply['dt'] . '</td>
		<td bgcolor="#EEE"><b>' . $hesklang['name'] . ':</b></td><td bgcolor="#DDD">' . $reply['name'] . '</td>
	</tr>
	</table>

    <div class="message">' . $theReply . '</div>
    ';
}

// Print "end of ticket" message
echo $hesklang['end_ticket'];
?>

</body>
</html>
