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
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// Ticket priority
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

// Set last replier name
if ($ticket['lastreplier']) {
    if (empty($ticket['repliername'])) {
        $ticket['repliername'] = $hesklang['staff'];
    }
} else {
    $ticket['repliername'] = $ticket['name'];
}

// Other variables that need processing
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
echo '<span style="page-break-after: always;">' . $hesklang['end_ticket'] . "</span>";
