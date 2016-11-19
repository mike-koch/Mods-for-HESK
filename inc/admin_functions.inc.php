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

// Possible fields to be displayed in ticket list
$hesk_settings['possible_ticket_list'] = array(
    'id' => $hesklang['id'],
    'trackid' => $hesklang['trackID'],
    'dt' => $hesklang['submitted'],
    'lastchange' => $hesklang['last_update'],
    'category' => $hesklang['category'],
    'name' => $hesklang['name'],
    'email' => $hesklang['email'],
    'subject' => $hesklang['subject'],
    'status' => $hesklang['status'],
    'owner' => $hesklang['owner'],
    'replies' => $hesklang['replies'],
    'staffreplies' => $hesklang['replies'] . ' (' . $hesklang['staff'] . ')',
    'lastreplier' => $hesklang['last_replier'],
    'time_worked' => $hesklang['ts'],
);

/*** FUNCTIONS ***/


function hesk_show_column($column)
{
    global $hesk_settings;

    return in_array($column, $hesk_settings['ticket_list']) ? true : false;

} // END hesk_show_column()


function hesk_getHHMMSS($in)
{
    $in = hesk_getTime($in);
    return explode(':', $in);
} // END hesk_getHHMMSS();


function hesk_getTime($in)
{
    $in = trim($in);

    /* If everything is OK this simple check should return true */
    if (preg_match('/^([0-9]{2,3}):([0-5][0-9]):([0-5][0-9])$/', $in)) {
        return $in;
    }

    /* No joy, let's try to figure out the correct values to use... */
    $h = 0;
    $m = 0;
    $s = 0;

    /* How many parts do we have? */
    $parts = substr_count($in, ':');

    switch ($parts) {
        /* Only two parts, let's assume minutes and seconds */
        case 1:
            list($m, $s) = explode(':', $in);
            break;

        /* Three parts, so explode to hours, minutes and seconds */
        case 2:
            list($h, $m, $s) = explode(':', $in);
            break;

        /* Something other was entered, let's assume just minutes */
        default:
            $m = $in;
    }

    /* Make sure all inputs are integers */
    $h = intval($h);
    $m = intval($m);
    $s = intval($s);

    /* Convert seconds to minutes if 60 or more seconds */
    if ($s > 59) {
        $m = floor($s / 60) + $m;
        $s = intval($s % 60);
    }

    /* Convert minutes to hours if 60 or more minutes */
    if ($m > 59) {
        $h = floor($m / 60) + $h;
        $m = intval($m % 60);
    }

    /* MySQL accepts max time value of 838:59:59 */
    if ($h > 838) {
        return '838:59:59';
    }

    /* That's it, let's send out formatted time string */
    return str_pad($h, 2, "0", STR_PAD_LEFT) . ':' . str_pad($m, 2, "0", STR_PAD_LEFT) . ':' . str_pad($s, 2, "0", STR_PAD_LEFT);

} // END hesk_getTime();


function hesk_mergeTickets($merge_these, $merge_into)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    /* Target ticket must not be in the "merge these" list */
    if (in_array($merge_into, $merge_these)) {
        $merge_these = array_diff($merge_these, array($merge_into));
    }

    /* At least 1 ticket needs to be merged with target ticket */
    if (count($merge_these) < 1) {
        $_SESSION['error'] = $hesklang['merr1'];
        return false;
    }

    /* Make sure target ticket exists */
    $res = hesk_dbQuery("SELECT `id`,`trackid`,`category` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($merge_into) . "' LIMIT 1");
    if (hesk_dbNumRows($res) != 1) {
        $_SESSION['error'] = $hesklang['merr2'];
        return false;
    }
    $ticket = hesk_dbFetchAssoc($res);

    /* Make sure user has access to ticket category */
    if (!hesk_okCategory($ticket['category'], 0)) {
        $_SESSION['error'] = $hesklang['merr3'];
        return false;
    }

    /* Set some variables for later */
    $merge['attachments'] = '';
    $merge['replies'] = array();
    $merge['notes'] = array();
    $sec_worked = 0;
    $history = '';
    $merged = '';

    /* Get messages, replies, notes and attachments of tickets that will be merged */
    foreach ($merge_these as $this_id) {
        /* Validate ID */
        if (is_array($this_id)) {
            continue;
        }
        $this_id = intval($this_id) or hesk_error($hesklang['id_not_valid']);

        /* Get required ticket information */
        $res = hesk_dbQuery("SELECT `id`,`trackid`,`category`,`name`,`message`,`dt`,`time_worked`,`attachments` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($this_id) . "' LIMIT 1");
        if (hesk_dbNumRows($res) != 1) {
            continue;
        }
        $row = hesk_dbFetchAssoc($res);

        /* Has this user access to the ticket category? */
        if (!hesk_okCategory($row['category'], 0)) {
            continue;
        }

        /* Insert ticket message as a new reply to target ticket */
        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`) VALUES ('" . intval($ticket['id']) . "','" . hesk_dbEscape($row['name']) . "','" . hesk_dbEscape($row['message']) . "','" . hesk_dbEscape($row['dt']) . "','" . hesk_dbEscape($row['attachments']) . "')");

        /* Update attachments  */
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` SET `ticket_id`='" . hesk_dbEscape($ticket['trackid']) . "' WHERE `ticket_id`='" . hesk_dbEscape($row['trackid']) . "'");

        /* Get old ticket replies and insert them as new replies */
        $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($row['id']) . "' ORDER BY `id` ASC");
        while ($reply = hesk_dbFetchAssoc($res)) {
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` (`replyto`,`name`,`message`,`dt`,`attachments`,`staffid`,`rating`,`read`) VALUES ('" . intval($ticket['id']) . "','" . hesk_dbEscape($reply['name']) . "','" . hesk_dbEscape($reply['message']) . "','" . hesk_dbEscape($reply['dt']) . "','" . hesk_dbEscape($reply['attachments']) . "','" . intval($reply['staffid']) . "','" . intval($reply['rating']) . "','" . intval($reply['read']) . "')");
        }

        /* Delete replies to the old ticket */
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`='" . intval($row['id']) . "'");

        /* Get old ticket notes and insert them as new notes */
        $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `ticket`='" . intval($row['id']) . "' ORDER BY `id` ASC");
        while ($note = hesk_dbFetchAssoc($res)) {
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` (`ticket`,`who`,`dt`,`message`,`attachments`) VALUES ('" . intval($ticket['id']) . "','" . intval($note['who']) . "','" . hesk_dbEscape($note['dt']) . "','" . hesk_dbEscape($note['message']) . "','" . hesk_dbEscape($note['attachments']) . "')");
        }

        /* Delete replies to the old ticket */
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `ticket`='" . intval($row['id']) . "'");

        /* Delete old ticket */
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `id`='" . intval($row['id']) . "'");

        /* Log that ticket has been merged */
        $history .= sprintf($hesklang['thist13'], hesk_date(), $row['trackid'], $_SESSION['name'] . ' (' . $_SESSION['user'] . ')');

        /* Add old ticket ID to target ticket "merged" field */
        $merged .= '#' . $row['trackid'];

        /* Convert old ticket "time worked" to seconds and add to $sec_worked variable */
        list ($hr, $min, $sec) = explode(':', $row['time_worked']);
        $sec_worked += (((int)$hr) * 3600) + (((int)$min) * 60) + ((int)$sec);
    }

    /* Convert seconds to HHH:MM:SS */
    $sec_worked = hesk_getTime('0:' . $sec_worked);

    // Get number of replies
    $total = 0;
    $staffreplies = 0;

    $res = hesk_dbQuery("SELECT COUNT(*) as `cnt`, `staffid` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`=" . intval($ticket['id']) . " GROUP BY CASE WHEN `staffid` = 0 THEN 0 ELSE 1 END ASC");
    while ($row = hesk_dbFetchAssoc($res)) {
        $total += $row['cnt'];
        $staffreplies += ($row['staffid'] ? $row['cnt'] : 0);
    }

    $replies_sql = " `replies`={$total}, `staffreplies`={$staffreplies} , ";

    // Get first staff reply
    if ($staffreplies) {
        $res = hesk_dbQuery("SELECT `dt`, `staffid` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "replies` WHERE `replyto`=" . intval($ticket['id']) . " AND `staffid`>0 ORDER BY `dt` ASC LIMIT 1");
        $reply = hesk_dbFetchAssoc($res);
        $replies_sql .= " `firstreply`='" . hesk_dbEscape($reply['dt']) . "', `firstreplyby`=" . intval($reply['staffid']) . " , ";
    }

    /* Update history (log) and merged IDs of target ticket */
    hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` SET $replies_sql `time_worked`=ADDTIME(`time_worked`, '" . hesk_dbEscape($sec_worked) . "'), `merged`=CONCAT(`merged`,'" . hesk_dbEscape($merged . '#') . "'), `history`=CONCAT(`history`,'" . hesk_dbEscape($history) . "') WHERE `id`='" . intval($merge_into) . "'");

    return true;

} // END hesk_mergeTickets()


function hesk_updateStaffDefaults()
{
    global $hesk_settings, $hesklang;

    // Demo mode
    if (defined('HESK_DEMO')) {
        return true;
    }
    // Remove the part that forces saving as default - we don't need it every time
    $default_list = str_replace('&def=1', '', $_SERVER['QUERY_STRING']);

    // Update database
    $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `default_list`='" . hesk_dbEscape($default_list) . "' WHERE `id`='" . intval($_SESSION['id']) . "'");

    // Update session values so the changes take effect immediately
    $_SESSION['default_list'] = $default_list;

    return true;

} // END hesk_updateStaffDefaults()


function hesk_makeJsString($in)
{
    return addslashes(preg_replace("/\s+/", ' ', $in));
} // END hesk_makeJsString()


function hesk_checkNewMail()
{
    global $hesk_settings, $hesklang;

    $res = hesk_dbQuery("SELECT COUNT(*) FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mail` WHERE `to`='" . intval($_SESSION['id']) . "' AND `read`='0' AND `deletedby`!='" . intval($_SESSION['id']) . "' ");
    $num = hesk_dbResult($res, 0, 0);

    return $num;
} // END hesk_checkNewMail()


function hesk_getCategoriesArray($kb = 0)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    $categories = array();
    if ($kb) {
        $result = hesk_dbQuery('SELECT `id`, `name` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_categories` ORDER BY `cat_order` ASC');
    } else {
        $result = hesk_dbQuery('SELECT `id`, `name` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'categories` ORDER BY `cat_order` ASC');
    }

    while ($row = hesk_dbFetchAssoc($result)) {
        $categories[$row['id']] = $row['name'];
    }

    return $categories;
} // END hesk_getCategoriesArray()


function hesk_getHTML($in)
{
    global $hesk_settings, $hesklang;

    $replace_from = array("\t", "<?", "?>", "$", "<%", "%>");
    $replace_to = array("", "&lt;?", "?&gt;", "\$", "&lt;%", "%&gt;");

    $in = trim($in);
    $in = str_replace($replace_from, $replace_to, $in);
    $in = preg_replace('/\<script(.*)\>(.*)\<\/script\>/Uis', "<script$1></script>", $in);
    $in = preg_replace('/\<\!\-\-(.*)\-\-\>/Uis', "<!-- comments have been removed -->", $in);

    if (HESK_SLASH === true) {
        $in = addslashes($in);
    }
    $in = str_replace('\"', '"', $in);

    return $in;
} // END hesk_getHTML()


function hesk_activeSessionValidate($username, $password_hash, $tag)
{
    // Salt and hash need to be separated by a |
    if (!strpos($tag, '|')) {
        return false;
    }

    // Get two parts of the tag
    list($salt, $hash) = explode('|', $tag, 2);

    // Make sure the hash matches existing username and password
    if ($hash == sha1($salt . strtolower($username) . $password_hash)) {
        return true;
    }

    return false;
} // hesk_activeSessionValidate


function hesk_activeSessionCreateTag($username, $password_hash)
{
    $salt = uniqid(mt_rand(), true);
    return $salt . '|' . sha1($salt . strtolower($username) . $password_hash);
} // END hesk_activeSessionCreateTag()


function hesk_autoLogin($noredirect = 0)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    if (!$hesk_settings['autologin']) {
        return false;
    }

    $user = hesk_htmlspecialchars(hesk_COOKIE('hesk_username'));
    $hash = hesk_htmlspecialchars(hesk_COOKIE('hesk_p'));
    define('HESK_USER', $user);

    if (empty($user) || empty($hash)) {
        return false;
    }

    /* Login cookies exist, now lets limit brute force attempts */
    hesk_limitBfAttempts();
	
	// Admin login URL
	$url = $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/index.php?a=login&notice=1';

    /* Check username */
    $result = hesk_dbQuery('SELECT * FROM `' . $hesk_settings['db_pfix'] . "users` WHERE `user` = '" . hesk_dbEscape($user) . "' LIMIT 1");
    if (hesk_dbNumRows($result) != 1) {
        hesk_setcookie('hesk_username', '');
        hesk_setcookie('hesk_p', '');
        header('Location: '.$url);
        exit();
    }

    $res = hesk_dbFetchAssoc($result);

    /* Check password */
    if ($hash != hesk_Pass2Hash($res['pass'] . strtolower($user) . $res['pass'])) {
        hesk_setcookie('hesk_username', '');
        hesk_setcookie('hesk_p', '');
        header('Location: '.$url);
        exit();
    }

    // Set user details
    foreach ($res as $k => $v) {
        $_SESSION[$k] = $v;
    }

    /* Check if default password */
    if ($_SESSION['pass'] == '499d74967b28a841c98bb4baaabaad699ff3c079') {
        hesk_process_messages($hesklang['chdp'], 'NOREDIRECT', 'NOTICE');
    }

    // Set a tag that will be used to expire sessions after username or password change
    $_SESSION['session_verify'] = hesk_activeSessionCreateTag($user, $_SESSION['pass']);

    // We don't need the password hash anymore
    unset($_SESSION['pass']);

    /* Login successful, clean brute force attempts */
    hesk_cleanBfAttempts();

    /* Regenerate session ID (security) */
    hesk_session_regenerate_id();

    /* Get allowed categories */
    if (empty($_SESSION['isadmin'])) {
        $_SESSION['categories'] = explode(',', $_SESSION['categories']);
    }

    /* Renew cookies */
    hesk_setcookie('hesk_username', "$user", strtotime('+1 year'));
    hesk_setcookie('hesk_p', "$hash", strtotime('+1 year'));

    /* Close any old tickets here so Cron jobs aren't necessary */
    if ($hesk_settings['autoclose']) {
        $revision = sprintf($hesklang['thist3'], hesk_date(), $hesklang['auto']);
        $dt = date('Y-m-d H:i:s', time() - $hesk_settings['autoclose'] * 86400);

        // Notify customer of closed ticket?
        if ($hesk_settings['notify_closed']) {
            // Get list of tickets
            $result = hesk_dbQuery("SELECT * FROM `" . $hesk_settings['db_pfix'] . "tickets` WHERE `status` = '2' AND `lastchange` <= '" . hesk_dbEscape($dt) . "' ");
            if (hesk_dbNumRows($result) > 0) {
                global $ticket;

                // Load required functions?
                if (!function_exists('hesk_notifyCustomer')) {
                    require(HESK_PATH . 'inc/email_functions.inc.php');
                }

                while ($ticket = hesk_dbFetchAssoc($result)) {
                    $ticket['dt'] = hesk_date($ticket['dt'], true);
                    $ticket['lastchange'] = hesk_date($ticket['lastchange'], true);
                    $ticket = hesk_ticketToPlain($ticket, 1, 0);
                    $modsForHesk_settings = mfh_getSettings();
                    hesk_notifyCustomer($modsForHesk_settings, 'ticket_closed');
                }
            }
        }

        // Update ticket statuses and history in database
        hesk_dbQuery("UPDATE `" . $hesk_settings['db_pfix'] . "tickets` SET `status`='3', `closedat`=NOW(), `closedby`='-1', `history`=CONCAT(`history`,'" . hesk_dbEscape($revision) . "') WHERE `status` = '2' AND `lastchange` <= '" . hesk_dbEscape($dt) . "' ");
    }

    /* If session expired while a HESK page is open just continue using it, don't redirect */
    if ($noredirect) {
        return true;
    }

    /* Redirect to the destination page */
    header('Location: ' . hesk_verifyGoto());
    exit();
} // END hesk_autoLogin()


function hesk_isLoggedIn()
{
    global $hesk_settings;

    $referer = hesk_input($_SERVER['REQUEST_URI']);
    $referer = str_replace('&amp;', '&', $referer);
	
	// Admin login URL
	$url = $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/index.php?a=login&notice=1&goto='.urlencode($referer);

    if (empty($_SESSION['id']) || empty($_SESSION['session_verify'])) {
        if ($hesk_settings['autologin'] && hesk_autoLogin(1)) {
            // Users online
            if ($hesk_settings['online']) {
                require(HESK_PATH . 'inc/users_online.inc.php');
                hesk_initOnline($_SESSION['id']);
            }

            return true;
        }

        hesk_session_stop();
        header('Location: ' . $url);
        exit();
    } else {
        hesk_session_regenerate_id();

        // Let's make sure access data is up-to-date
        $res = hesk_dbQuery("SELECT `user`, `pass`, `isadmin`, `categories`, `heskprivileges` FROM `" . $hesk_settings['db_pfix'] . "users` WHERE `id` = '" . intval($_SESSION['id']) . "' LIMIT 1");

        // Exit if user not found
        if (hesk_dbNumRows($res) != 1) {
            hesk_session_stop();
            header('Location: ' . $url);
            exit();
        }

        // Fetch results from database
        $me = hesk_dbFetchAssoc($res);

        // Verify this session is still valid
        if (!hesk_activeSessionValidate($me['user'], $me['pass'], $_SESSION['session_verify'])) {
            hesk_session_stop();
            header('Location: ' . $url);
            exit();
        }

        // Update session variables as needed
        if ($me['isadmin'] == 1) {
            $_SESSION['isadmin'] = 1;
        } else {
            $_SESSION['isadmin'] = 0;
            $_SESSION['categories'] = explode(',', $me['categories']);
            $_SESSION['heskprivileges'] = $me['heskprivileges'];
        }

        // Users online
        if ($hesk_settings['online']) {
            require(HESK_PATH . 'inc/users_online.inc.php');
            hesk_initOnline($_SESSION['id']);
        }

        return true;
    }

} // END hesk_isLoggedIn()


function hesk_verifyGoto()
{
    // Default redirect URL
    $url_default = 'admin_main.php';

    // If no "goto" parameter is set, redirect to the default page
    if (!hesk_isREQUEST('goto')) {
        return $url_default;
    }

    // Get the "goto" parameter
    $url = hesk_REQUEST('goto');

    // Fix encoded "&"
    $url = str_replace('&amp;', '&', $url);

    // Parse the URL for verification
    $url_parts = parse_url($url);

    // The "path" part is required
    if (!isset($url_parts['path'])) {
        return $url_default;
    }

    // Extract the file name from path
    $url = basename($url_parts['path']);

    // Allowed files for redirect
    $OK_urls = array(
        'admin_main.php' => '',
        'admin_settings.php' => '',
        'admin_settings_save.php' => 'admin_settings.php',
        'admin_ticket.php' => '',
        'archive.php' => '',
        'assign_owner.php' => '',
		'banned_emails.php' => '',
		'banned_ips.php' => '',
        'change_status.php' => '',
        'edit_post.php' => '',
		'email_templates.php' => '',
        'export.php' => '',
        'find_tickets.php' => '',
        'generate_spam_question.php' => '',
        'knowledgebase_private.php' => '',
        'lock.php' => '',
        'mail.php' => '',
        'manage_canned.php' => '',
        'manage_categories.php' => '',
        'manage_knowledgebase.php' => '',
		'manage_ticket_templates.php' => '',
        'manage_users.php' => '',
        'new_ticket.php' => '',
        'profile.php' => '',
        'reports.php' => '',
		'service_messages.php' => '',
        'show_tickets.php' => '',
    );

    // URL must match one of the allowed ones
    if (!isset($OK_urls[$url])) {
        return $url_default;
    }

    // Modify redirect?
    if (strlen($OK_urls[$url])) {
        $url = $OK_urls[$url];
    }

    // All OK, return the URL with query if set
    return isset($url_parts['query']) ? $url . '?' . $url_parts['query'] : $url;

} // END hesk_verifyGoto()


function hesk_Pass2Hash($plaintext)
{
    $majorsalt = '';
    $len = strlen($plaintext);
    for ($i = 0; $i < $len; $i++) {
        $majorsalt .= sha1(substr($plaintext, $i, 1));
    }
    $corehash = sha1($majorsalt);
    return $corehash;
} // END hesk_Pass2Hash()


function hesk_formatDate($dt, $from_database = true)
{
    $dt = hesk_date($dt, $from_database);
    $dt = str_replace(' ', '<br />', $dt);
    return $dt;
} // End hesk_formatDate()


function hesk_jsString($str)
{
    $str  = addslashes($str);
    $str  = str_replace('<br />' , '' , $str);
    $from = array("/\r\n|\n|\r/", '/\<a href="mailto\:([^"]*)"\>([^\<]*)\<\/a\>/i', '/\<a href="([^"]*)" target="_blank"\>([^\<]*)\<\/a\>/i');
    $to = array("\\r\\n' + \r\n'", "$1", "$1");
    return preg_replace($from, $to, $str);
} // END hesk_jsString()


function hesk_myCategories($what = 'category')
{
    if (!empty($_SESSION['isadmin'])) {
        return '1';
    } else {
        return " `" . hesk_dbEscape($what) . "` IN ('" . implode("','", array_map('intval', $_SESSION['categories'])) . "')";
    }
} // END hesk_myCategories()


function hesk_okCategory($cat, $error = 1, $user_isadmin = false, $user_cat = false)
{
    global $hesklang;

    /* Checking for current user or someone else? */
    if ($user_isadmin === false) {
        $user_isadmin = $_SESSION['isadmin'];
    }

    if ($user_cat === false) {
        $user_cat = $_SESSION['categories'];
    }

    /* Is admin? */
    if ($user_isadmin) {
        return true;
    } /* Staff with access? */
    elseif (in_array($cat, $user_cat)) {
        return true;
    } /* No access */
    else {
        if ($error) {
            hesk_error($hesklang['not_authorized_tickets']);
        } else {
            return false;
        }
    }

} // END hesk_okCategory()


function hesk_checkPermission($feature, $showerror = 1)
{
    global $hesklang;

    /* Admins have full access to all features */
    if (isset($_SESSION['isadmin']) && $_SESSION['isadmin']) {
        return true;
    }

    /* Check other staff for permissions */
    if (isset($_SESSION['heskprivileges']) && strpos($_SESSION['heskprivileges'], $feature) === false) {
        if ($showerror) {
            hesk_error($hesklang['no_permission'] . '<p>&nbsp;</p><p align="center"><a href="index.php">' . $hesklang['click_login'] . '</a>');
        } else {
            return false;
        }
    } else {
        return true;
    }

} // END hesk_checkPermission()

function hesk_purge_cache($type = '', $expire_after_seconds = 0)
{
    global $hesk_settings;

    $cache_dir = dirname(dirname(__FILE__)).'/'.$hesk_settings['cache_dir'].'/';

    if ( ! is_dir($cache_dir))
    {
        return false;
    }

    switch ($type)
    {
        case 'export':
            $files = glob($cache_dir.'hesk_export_*', GLOB_NOSORT);
            break;
        case 'status':
            $files = glob($cache_dir.'status_*', GLOB_NOSORT);
            break;
        case 'cf':
            $files = glob($cache_dir.'cf_*', GLOB_NOSORT);
            break;
        default:
            hesk_rrmdir(trim($cache_dir, '/'), true);
            return true;
    }

    if (is_array($files))
    {
        array_walk($files, 'hesk_unlink_callable', $expire_after_seconds);
    }

    return true;

} // END hesk_purge_cache()


function hesk_rrmdir($dir, $keep_top_level=false)
{
    $files = $keep_top_level ? array_diff(scandir($dir), array('.','..','index.htm')) : array_diff(scandir($dir), array('.','..'));

    foreach ($files as $file)
    {
        (is_dir("$dir/$file")) ? hesk_rrmdir("$dir/$file") : @unlink("$dir/$file");
    }

    return $keep_top_level ? true : @rmdir($dir);

} // END hesk_rrmdir()
