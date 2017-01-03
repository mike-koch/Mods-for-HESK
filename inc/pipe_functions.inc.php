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

// Include all functions needed for email piping
hesk_load_database_functions();
require(HESK_PATH . 'inc/email_functions.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');
require(HESK_PATH . 'inc/htmLawed.php');
require(HESK_PATH . 'inc/mail/rfc822_addresses.php');
require(HESK_PATH . 'inc/mail/mime_parser.php');
require(HESK_PATH . 'inc/mail/email_parser.php');

/*** FUNCTIONS ***/

function hesk_email2ticket($results, $pop3 = 0, $set_category = 1, $set_priority = -1)
{
    global $hesk_settings, $hesklang, $hesk_db_link, $ticket;

    $modsForHesk_settings = mfh_getSettings();

    // Process "Reply-To:" or "From:" email
    $tmpvar['email'] = isset($results['reply-to'][0]['address']) ? hesk_validateEmail($results['reply-to'][0]['address'], 'ERR', 0) : hesk_validateEmail($results['from'][0]['address'], 'ERR', 0);

    // Email missing, invalid or banned?
    if (!$tmpvar['email'] || hesk_isBannedEmail($tmpvar['email'])) {
        return hesk_cleanExit();
    }

    // Process "Reply-To:" or "From:" name, convert to UTF-8, set to "[Customer]" if not set
    if (isset($results['reply-to'][0]['name']) && strlen($results['reply-to'][0]['name'])) {
        $tmpvar['name'] = $results['reply-to'][0]['name'];
        if (!empty($results['reply-to'][0]['encoding'])) {
            $tmpvar['name'] = hesk_encodeUTF8($tmpvar['name'], $results['reply-to'][0]['encoding']);
        }
    } else {
        $tmpvar['name'] = isset($results['from'][0]['name']) ? $results['from'][0]['name'] : $hesklang['pde'];
        if (!empty($results['from'][0]['encoding'])) {
            $tmpvar['name'] = hesk_encodeUTF8($tmpvar['name'], $results['from'][0]['encoding']);
        }
    }
    $tmpvar['name'] = hesk_input($tmpvar['name'], '', '', 1, 50) or $tmpvar['name'] = $hesklang['pde'];

    // Process "To:" email (not yet implemented, for future use)
    // $tmpvar['to_email']	= hesk_validateEmail($results['to'][0]['address'],'ERR',0);

    // Process email subject, convert to UTF-8, set to "[Piped email]" if none set
    $tmpvar['subject'] = isset($results['subject']) ? $results['subject'] : $hesklang['pem'];
    if (!empty($results['subject_encoding'])) {
        $tmpvar['subject'] = hesk_encodeUTF8($tmpvar['subject'], $results['subject_encoding']);
    }
    $tmpvar['subject'] = hesk_input($tmpvar['subject'], '', '', 1, 70) or $tmpvar['subject'] = $hesklang['pem'];

    // Process email message, convert to UTF-8
    $tmpvar['message'] = isset($results['message']) ? $results['message'] : '';
    if (!empty($results['encoding'])) {
        $tmpvar['message'] = hesk_encodeUTF8($tmpvar['message'], $results['encoding']);
    }
    $tmpvar['message'] = hesk_input($tmpvar['message'], '', '', 1);

    // Message missing?
    if (strlen($tmpvar['message']) == 0) {
        // Message required? Ignore this email.
        if ($hesk_settings['eml_req_msg']) {
            return hesk_cleanExit();
        }

        // Message not required? Assign a default message
        $tmpvar['message'] = $hesklang['def_msg'];

        // Track duplicate emails based on subject
        $message_hash = md5($tmpvar['subject']);
    } else {
        $message_hash = md5($tmpvar['message']);
    }

    // Strip quoted reply from email
    $tmpvar['message'] = hesk_stripQuotedText($tmpvar['message']);

    // Convert URLs to links, change newlines to <br />
    $tmpvar['message'] = hesk_makeURL($tmpvar['message']);
    $tmpvar['message'] = nl2br($tmpvar['message']);

    # For debugging purposes
    # die( bin2hex($tmpvar['message']) );
    # die($tmpvar['message']);

    // Try to detect "delivery failed" and "noreply" emails - ignore if detected
    if (hesk_isReturnedEmail($tmpvar)) {
        return hesk_cleanExit();
    }

    // Check for email loops
    if (hesk_isEmailLoop($tmpvar['email'], $message_hash)) {
        return hesk_cleanExit();
    }

    // OK, everything seems OK. Now determine if this is a reply to a ticket or a new ticket
    if (preg_match('/\[#([A-Z0-9]{3}\-[A-Z0-9]{3}\-[A-Z0-9]{4})\]/', str_replace(' ', '', $tmpvar['subject']), $matches)) {
        // We found a possible tracking ID
        $tmpvar['trackid'] = $matches[1];

        // Does it match one in the database?
        $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($tmpvar['trackid']) . "' LIMIT 1");
        if (hesk_dbNumRows($res)) {
            $ticket = hesk_dbFetchAssoc($res);

            // Do email addresses match?
            if (strpos(strtolower($ticket['email']), strtolower($tmpvar['email'])) === false) {
                $tmpvar['trackid'] = '';
            }

            // Is this ticket locked? Force create a new one if it is
            if ($ticket['locked']) {
                $tmpvar['trackid'] = '';
            }
        } else {
            $tmpvar['trackid'] = '';
        }
    }

    // If tracking ID is empty, generate a new one
    if (empty($tmpvar['trackid'])) {
        $tmpvar['trackid'] = hesk_createID();
        $is_reply = 0;
    } else {
        $is_reply = 1;
    }

    // Process attachments
    $tmpvar['attachmment_notices'] = '';
    $tmpvar['attachments'] = '';
    $num = 0;
    if ($hesk_settings['attachments']['use'] && isset($results['attachments'][0])) {
        foreach ($results['attachments'] as $k => $v) {

            // Clean attachment names
            $myatt['real_name'] = hesk_cleanFileName($v['orig_name']);

            // Check number of attachments, delete any over max number
            if ($num >= $hesk_settings['attachments']['max_number']) {
                $tmpvar['attachmment_notices'] .= sprintf($hesklang['attnum'], $myatt['real_name']) . "\n";
                continue;
            }

            // Check file extension
            $ext = strtolower(strrchr($myatt['real_name'], "."));
            if (!in_array($ext, $hesk_settings['attachments']['allowed_types'])) {
                $tmpvar['attachmment_notices'] .= sprintf($hesklang['atttyp'], $myatt['real_name']) . "\n";
                continue;
            }

            // Check file size
            $myatt['size'] = $v['size'];
            if ($myatt['size'] > ($hesk_settings['attachments']['max_size'])) {
                $tmpvar['attachmment_notices'] .= sprintf($hesklang['attsiz'], $myatt['real_name']) . "\n";
                continue;
            }

            // Generate a random file name
            $useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
            $tmp = $useChars{mt_rand(0, 29)};
            for ($j = 1; $j < 10; $j++) {
                $tmp .= $useChars{mt_rand(0, 29)};
            }
            $myatt['saved_name'] = substr($tmpvar['trackid'] . '_' . md5($tmp . $myatt['real_name']), 0, 200) . $ext;

            // Rename the temporary file
            rename($v['stored_name'], HESK_PATH . $hesk_settings['attach_dir'] . '/' . $myatt['saved_name']);

            // Insert into database
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` (`ticket_id`,`saved_name`,`real_name`,`size`) VALUES ('" . hesk_dbEscape($tmpvar['trackid']) . "','" . hesk_dbEscape($myatt['saved_name']) . "','" . hesk_dbEscape($myatt['real_name']) . "','" . intval($myatt['size']) . "')");
            $tmpvar['attachments'] .= hesk_dbInsertID() . '#' . $myatt['real_name'] . '#' . $myatt['saved_name'] . ',';

            $num++;
        }

        if (strlen($tmpvar['attachmment_notices'])) {
            $tmpvar['message'] .= "<br /><br />" . hesk_input($hesklang['attrem'], '', '', 1) . "<br />" . nl2br(hesk_input($tmpvar['attachmment_notices'], '', '', 1));
        }
    }

    // Delete the temporary files
    deleteAll($results['tempdir']);

    // If this is a reply add a new reply
    if ($is_reply) {
        // Set last replier name to customer name
        $ticket['lastreplier'] = ($tmpvar['name'] == $hesklang['pde']) ? $tmpvar['email'] : $tmpvar['name'];;

        // If staff hasn't replied yet, keep ticket status "New", otherwise set it to "Waiting reply from staff"
        $new_status_rs = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsNewTicketStatus` = 1");
        $waiting_reply_rs = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsCustomerReplyStatus` = 1");
        $new_status = hesk_dbFetchAssoc($new_status_rs);
        $waiting_reply_rs = hesk_dbFetchAssoc($waiting_reply_rs);
        $ticket['status'] = $ticket['status'] ? $waiting_reply_rs['id'] : $new_status['id'];

        // Update ticket as necessary
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET `lastchange`=NOW(),`status`='{$ticket['status']}',`replies`=`replies`+1,`lastreplier`='0' WHERE `id`='" . intval($ticket['id']) . "'");

        // If customer replied, we assume staff replies have been read (no way to be sure if ticket.php hasn't been opened)
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` SET `read` = '1' WHERE `replyto` = '" . intval($ticket['id']) . "' AND `staffid` != '0' ");

        // Insert reply into database
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`) VALUES ('" . intval($ticket['id']) . "','" . hesk_dbEscape($ticket['lastreplier']) . "','" . hesk_dbEscape($tmpvar['message']) . "',NOW(),'" . hesk_dbEscape($tmpvar['attachments']) . "')");

        // --> Prepare reply message

        // 1. Generate the array with ticket info that can be used in emails
        $info = array(
            'email' => $ticket['email'],
            'category' => $ticket['category'],
            'priority' => $ticket['priority'],
            'owner' => $ticket['owner'],
            'trackid' => $ticket['trackid'],
            'status' => $ticket['status'],
            'name' => $ticket['name'],
            'lastreplier' => $ticket['lastreplier'],
            'subject' => $ticket['subject'],
            'message' => stripslashes($tmpvar['message']),
            'attachments' => $tmpvar['attachments'],
            'dt' => hesk_date($ticket['dt'], true),
            'lastchange' => hesk_date($ticket['lastchange'], true),
            'id' => $ticket['id'],
        );

        // 2. Add custom fields to the array
        foreach ($hesk_settings['custom_fields'] as $k => $v) {
            $info[$k] = $v['use'] ? $ticket[$k] : '';
        }

        // 3. Make sure all values are properly formatted for email
        $ticket = hesk_ticketToPlain($info, 1, 0);

        // --> Process custom fields before sending
        foreach ($hesk_settings['custom_fields'] as $k => $v) {
            $ticket[$k] = $v['use'] ? hesk_msgToPlain($ticket[$k], 1) : '';
        }

        // --> If ticket is assigned just notify the owner
        if ($ticket['owner']) {
            hesk_notifyAssignedStaff(false, 'new_reply_by_customer', $modsForHesk_settings, 'notify_reply_my');
        } // --> No owner assigned, find and notify appropriate staff
        else {
            hesk_notifyStaff('new_reply_by_customer', "`notify_reply_unassigned`='1'", $modsForHesk_settings);
        }

        return $ticket['trackid'];

    } // END REPLY

    // Not a reply, but a new ticket. Add it to the database
    $tmpvar['category'] = $set_category;
    $tmpvar['priority'] = $set_priority < 0 ? hesk_getCategoryPriority($tmpvar['category']) : $set_priority;
    $_SERVER['REMOTE_ADDR'] = $hesklang['unknown'];

    // Auto assign tickets if aplicable
    $tmpvar['owner'] = 0;
    $tmpvar['history'] = $pop3 ? sprintf($hesklang['thist16'], hesk_date()) : sprintf($hesklang['thist11'], hesk_date());
    $tmpvar['openedby'] = $pop3 ? -2 : -1;

    $autoassign_owner = hesk_autoAssignTicket($tmpvar['category']);

    #print_r($autoassign_owner);

    if ($autoassign_owner) {
        $tmpvar['owner'] = $autoassign_owner['id'];
        $tmpvar['history'] .= sprintf($hesklang['thist10'], hesk_date(), $autoassign_owner['name'] . ' (' . $autoassign_owner['user'] . ')');
    }

    // Custom fields will be empty as there is no reliable way of detecting them
    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        $tmpvar[$k] = '';
    }

    $tmpvar['latitude'] = NULL;
    $tmpvar['longitude'] = NULL;
    $tmpvar['html'] = 0;
    $tmpvar['user_agent'] = NULL;
    $tmpvar['screen_resolution_width'] = "NULL";
    $tmpvar['screen_resolution_height'] = "NULL";
    $tmpvar['due_date'] = "";

    // Insert ticket to database
    $ticket = hesk_newTicket($tmpvar);

    // Notify the customer
    if ($hesk_settings['notify_new']) {
        $possible_SPAM = false;

        // Do we need to check subject for SPAM tags?
        if ($hesk_settings['notify_skip_spam']) {
            foreach ($hesk_settings['notify_spam_tags'] as $tag) {
                if (strpos($tmpvar['subject'], $tag) !== false) {
                    $possible_SPAM = true;
                    break;
                }
            }
        }

        // SPAM tags not found or not checked, send email
        if ($possible_SPAM === false) {
            hesk_notifyCustomer($modsForHesk_settings);
        }
    }

    // Need to notify staff?
    // --> From autoassign?
    if ($tmpvar['owner'] && $autoassign_owner['notify_assigned']) {
        hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you', $modsForHesk_settings);
    } // --> No autoassign, find and notify appropriate staff
    elseif (!$tmpvar['owner']) {
        hesk_notifyStaff('new_ticket_staff', " `notify_new_unassigned` = '1' ", $modsForHesk_settings);
    }

    return $ticket['trackid'];
} // END hesk_email2ticket()


function hesk_encodeUTF8($in, $encoding)
{
    $encoding = strtoupper($encoding);

    switch ($encoding) {
        case 'UTF-8':
            return $in;
            break;
        case 'ISO-8859-1':
            return utf8_encode($in);
            break;
        default:
            return iconv($encoding, 'UTF-8', $in);
            break;
    }
} // END hesk_encodeUTF8()


function hesk_stripQuotedText($message)
{
    global $hesk_settings, $hesklang;

    // Stripping quoted text disabled?
    if (!$hesk_settings['strip_quoted']) {
        return $message;
    }

    // Loop through available languages and ty to find the tag
    foreach ($hesk_settings['languages'] as $language => $settings) {
        if (($found = strpos($message, $settings['hr'])) !== false) {
            // "Reply above this line" tag found, strip quoted reply
            $message = substr($message, 0, $found);
            $message .= "\n" . $hesklang['qrr'];

            // Set language to the detected language
            hesk_setLanguage($language);
            break;
        }
    }

    return $message;
} // END hesk_stripQuotedText()


function hesk_isReturnedEmail($tmpvar)
{
    // Check noreply email addresses
    if (preg_match('/not?[\-_\.]?reply@/i', $tmpvar['email'])) {
        return true;
    }

    // Check mailer daemon email addresses
    if (preg_match('/mail(er)?[\-_\.]?daemon@/i', $tmpvar['email'])) {
        return true;
    }

    // Check autoreply subjects
    if (preg_match('/^[\[\(]?Auto(mat(ic|ed))?[ \-]?reply/i', $tmpvar['subject'])) {
        return true;
    }

    // Check out of office subjects
    if (preg_match('/^Out of Office/i', $tmpvar['subject'])) {
        return true;
    }

    // Check delivery failed email subjects
    if (
        preg_match('/DELIVERY FAILURE/i', $tmpvar['subject']) ||
        preg_match('/Undelivered Mail Returned to Sender/i', $tmpvar['subject']) ||
        preg_match('/Delivery Status Notification \(Failure\)/i', $tmpvar['subject']) ||
        preg_match('/Returned mail\: see transcript for details/i', $tmpvar['subject'])
    ) {
        return true;
    }

    // Check Mail Delivery sender name
    if (preg_match('/Mail[ \-_]?Delivery/i', $tmpvar['name'])) {
        return true;
    }

    // Check Delivery failed message
    if (preg_match('/postmaster@/i', $tmpvar['email']) && preg_match('/Delivery has failed to these recipients/i', $tmpvar['message'])) {
        return true;
    }

    // No pattern detected, seems like this is not a returned email
    return false;

} // END hesk_isReturnedEmail()


function hesk_isEmailLoop($email, $message_hash)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    // If $hesk_settings['loop_hits'] is set to 0 this function is disabled
    if (!$hesk_settings['loop_hits']) {
        return false;
    }

    // Escape wildcards in email
    $email_like = hesk_dbEscape(hesk_dbLike($email));

    // Delete expired DB entries
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pipe_loops` WHERE `dt` < (NOW() - INTERVAL " . intval($hesk_settings['loop_time']) . " SECOND) ");

    // Check current entry
    $res = hesk_dbQuery("SELECT `hits`, `message_hash` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pipe_loops` WHERE `email` LIKE '{$email_like}' LIMIT 1");

    // Any active entry*
    if (hesk_dbNumRows($res)) {
        list($num, $md5) = hesk_dbFetchRow($res);

        $num++;

        // Number of emails in a time period reached?
        if ($num >= $hesk_settings['loop_hits']) {
            return true;
        }

        // Message exactly the same as in previous email?
        if ($message_hash == $md5) {
            return true;
        }

        // Update DB entry
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pipe_loops` SET `hits` = `hits` + 1, `message_hash` = '" . hesk_dbEscape($message_hash) . "' WHERE `email` LIKE '{$email_like}'");
    } else {
        // First instance, insert a new database row
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "pipe_loops` (`email`, `message_hash`) VALUES ('" . hesk_dbEscape($email) . "', '" . hesk_dbEscape($message_hash) . "')");
    }

    // No loop rule trigered
    return false;

} // END hesk_isEmailLoop()


function hesk_cleanExit()
{
    global $results;

    // Delete the temporary files
    deleteAll($results['tempdir']);

    // Return NULL
    return NULL;
} // END hesk_cleanExit()
