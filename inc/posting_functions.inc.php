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

/*** FUNCTIONS ***/

function hesk_newTicket($ticket, $isVerified = true)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    // Generate a subject if necessary
    if (strlen($ticket['subject']) < 1)
    {
        $ticket['subject'] = sprintf($hesklang['default_subject'], $ticket['name']);
    }

    // If language is not set or default, set it to NULL.
    if (!isset($ticket['language']) || empty($ticket['language'])) {
        $language = (!$hesk_settings['can_sel_lang']) ? HESK_DEFAULT_LANGUAGE : hesk_dbEscape($hesklang['LANGUAGE']);
    } else {
        $language = $ticket['language'];
    }

    // Get the default ticket status for new tickets and set it accordingly
    $defaultNewTicketRs = hesk_dbQuery("SELECT `ID` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "statuses` WHERE `IsNewTicketStatus` = 1");
    $defaultNewTicket = hesk_dbFetchAssoc($defaultNewTicketRs);
    $ticket['status'] = $defaultNewTicket['ID'];
    $tableName = $isVerified ? 'tickets' : 'stage_tickets';

    $ticket['message'] = htmLawed($ticket['message'], array('safe' => 1, 'deny_attribute' => 'style'));

    $due_date = 'NULL';
    if ($ticket['due_date'] != '') {
        $due_date = "'" . hesk_dbEscape($ticket['due_date']) . "'";
    }

    // Prepare SQL for custom fields
    $custom_where = '';
    $custom_what  = '';

    for ($i=1; $i<=50; $i++)
    {
        $custom_where .= ", `custom{$i}`";
        $custom_what  .= ", '" . (isset($ticket['custom'.$i]) ? hesk_dbEscape($ticket['custom'.$i]) : '') . "'";
    }

    // Insert ticket into database
    hesk_dbQuery("
	INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . $tableName . "`
	(
		`trackid`,
		`name`,
		`email`,
		`category`,
		`priority`,
		`subject`,
		`message`,
		`dt`,
		`lastchange`,
		`articles`,
		`ip`,
		`language`,
		`openedby`,
		`owner`,
		`attachments`,
		`merged`,
		`status`,
		`latitude`,
		`longitude`,
		`html`,
		`user_agent`,
		`screen_resolution_height`,
		`screen_resolution_width`,
		`due_date`,
		`history`
		{$custom_where}
	)
	VALUES
	(
		'" . hesk_dbEscape($ticket['trackid']) . "',
		'" . hesk_dbEscape($ticket['name']) . "',
		'" . hesk_dbEscape($ticket['email']) . "',
		'" . intval($ticket['category']) . "',
		'" . intval($ticket['priority']) . "',
		'" . hesk_dbEscape($ticket['subject']) . "',
		'" . hesk_dbEscape($ticket['message']) . "',
		NOW(),
		NOW(),
		" . (isset($ticket['articles']) ? "'{$ticket['articles']}'" : 'NULL') . ",
		'" . hesk_dbEscape($_SERVER['REMOTE_ADDR']) . "',
		'" . hesk_dbEscape($language) . "',
		'" . (isset($ticket['openedby']) ? intval($ticket['openedby']) : 0) . "',
		'" . intval($ticket['owner']) . "',
		'" . hesk_dbEscape($ticket['attachments']) . "',
		'',
		'" . intval($ticket['status']) . "',
		'" . hesk_dbEscape($ticket['latitude']) . "',
		'" . hesk_dbEscape($ticket['longitude']) . "',
		'" . hesk_dbEscape($ticket['html']) . "',
		'" . hesk_dbEscape($ticket['user_agent']) . "',
		" . hesk_dbEscape($ticket['screen_resolution_height']) . ",
		" . hesk_dbEscape($ticket['screen_resolution_width']) . ",
		{$due_date},
		'" . hesk_dbEscape($ticket['history']) . "'
		{$custom_what}
	)
	");

    // Generate the array with ticket info that can be used in emails
    $info = array(
        'email' => $ticket['email'],
        'category' => $ticket['category'],
        'priority' => $ticket['priority'],
        'owner' => $ticket['owner'],
        'trackid' => $ticket['trackid'],
        'status' => $ticket['status'],
        'name' => $ticket['name'],
        'lastreplier' => $ticket['name'],
        'subject' => $ticket['subject'],
        'message' => $ticket['message'],
        'attachments' => $ticket['attachments'],
        'dt' => hesk_date(),
        'lastchange' => hesk_date(),
        'id' => hesk_dbInsertID(),
        'language' => $language,
        'html' => $ticket['html'],
        'due_date' => $ticket['due_date']
    );

    // Add custom fields to the array
    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        $info[$k] = $v['use'] ? $ticket[$k] : '';
    }

    return hesk_ticketToPlain($info, 1);

} // END hesk_newTicket()


function hesk_cleanFileName($filename)
{
    $parts = pathinfo($filename);

    if (isset($parts['filename'])) {
        $filename = $parts['filename'];
    } // PHP < 5.2 needs special care
    elseif (version_compare(PHP_VERSION, '5.2', '<')) {
        $filename = rtrim(str_ireplace($parts['extension'], '', $filename), '.');
    } else {
        $filename = '';
    }

    $filename = str_replace(array('%20', '+'), '-', $filename);
    $filename = preg_replace('/[\s-]+/', '-', $filename);
    $filename = remove_accents($filename);
    $filename = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $filename);
    $filename = trim($filename, '-_');

    if (strlen($filename) < 1 || strpos($filename, '.') === 0) {
        $filename = mt_rand(10000, 99999) . $filename;
    }

    return $filename . '.' . $parts['extension'];
} // END hesk_cleanFileName()


function hesk_getCategoryPriority($id)
{
    global $hesk_settings, $hesklang, $hesk_db_link;

    $priority = 3;

    // Does the category have a different default priority?
    $res = hesk_dbQuery("SELECT `priority` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`=" . intval($id) . " LIMIT 1");
    if (hesk_dbNumRows($res) == 1) {
        $priority = hesk_dbResult($res);
    }

    return $priority;

} // END hesk_getCategoryPriority()


function hesk_verifyCategory($any_type = 0)
{
    global $hesk_settings, $hesklang, $hesk_db_link, $hesk_error_buffer, $tmpvar;

    // Verify just by public or any category type?
    $type = $any_type ? " 1 " : " `type`='0' ";

    // Does the category exist?
    $res = hesk_dbQuery("SELECT `name`, `autoassign` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `id`='" . $tmpvar['category'] . "' AND {$type} LIMIT 1");
    if (hesk_dbNumRows($res) == 1) {
        $hesk_settings['category_data'][$tmpvar['category']] = hesk_dbFetchAssoc($res);
        return true;
    }

    // OK, something wrong with the category. Get a list of categories to check few things
    $res = hesk_dbQuery("SELECT `id`, `name`, `autoassign` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE {$type} ORDER BY `id` ASC");
    $num = hesk_dbNumRows($res);

    // If more than 1 choice is available, let the user choose
    if ($num > 1) {
        $hesk_error_buffer['category'] = $hesklang['sel_app_cat'];
        return false;
    } // Exactly one category is available, use it
    elseif ($num == 1) {
        $tmp = hesk_dbFetchAssoc($res);
        $tmpvar['category'] = $tmp['id'];
        $hesk_settings['category_data'][$tmpvar['category']] = $tmp;
        return true;
    } // No category is available, use the first one we find (should be ID 1)
    else {
        $res = hesk_dbQuery("SELECT `id`, `name`, `autoassign` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` ORDER BY `id` ASC LIMIT 1");

        if (hesk_dbNumRows($res) == 1) {
            $tmp = hesk_dbFetchAssoc($res);
            $tmpvar['category'] = $tmp['id'];
            $hesk_settings['category_data'][$tmpvar['category']] = $tmp;
        } else {
            // What the ...? No categories exist??? You know what, just error out...
            hesk_error($hesklang['int_error'] . ': ' . $hesklang['cat_not_found']);
        }
    }

} // END hesk_verifyCategory()


// The following code has been borrowed from Wordpress
// Credits: http://wordpress.org
function remove_accents($string)
{
    if (!preg_match('/[\x80-\xff]/', $string))
        return $string;

    if (seems_utf8($string)) {
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(194) . chr(170) => 'a', chr(194) . chr(186) => 'o',
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(134) => 'AE', chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E', chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E', chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I', chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I', chr(195) . chr(143) => 'I',
            chr(195) . chr(144) => 'D', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(158) => 'TH', chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a', chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a', chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a', chr(195) . chr(165) => 'a',
            chr(195) . chr(166) => 'ae', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(176) => 'd', chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o', chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o', chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(184) => 'o',
            chr(195) . chr(185) => 'u', chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u', chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y', chr(195) . chr(190) => 'th',
            chr(195) . chr(191) => 'y', chr(195) . chr(152) => 'O',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
            // Decompositions for Latin Extended-B
            chr(200) . chr(152) => 'S', chr(200) . chr(153) => 's',
            chr(200) . chr(154) => 'T', chr(200) . chr(155) => 't',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            chr(198) . chr(160) => 'O', chr(198) . chr(161) => 'o',
            chr(198) . chr(175) => 'U', chr(198) . chr(176) => 'u',
            // grave accent
            chr(225) . chr(186) . chr(166) => 'A', chr(225) . chr(186) . chr(167) => 'a',
            chr(225) . chr(186) . chr(176) => 'A', chr(225) . chr(186) . chr(177) => 'a',
            chr(225) . chr(187) . chr(128) => 'E', chr(225) . chr(187) . chr(129) => 'e',
            chr(225) . chr(187) . chr(146) => 'O', chr(225) . chr(187) . chr(147) => 'o',
            chr(225) . chr(187) . chr(156) => 'O', chr(225) . chr(187) . chr(157) => 'o',
            chr(225) . chr(187) . chr(170) => 'U', chr(225) . chr(187) . chr(171) => 'u',
            chr(225) . chr(187) . chr(178) => 'Y', chr(225) . chr(187) . chr(179) => 'y',
            // hook
            chr(225) . chr(186) . chr(162) => 'A', chr(225) . chr(186) . chr(163) => 'a',
            chr(225) . chr(186) . chr(168) => 'A', chr(225) . chr(186) . chr(169) => 'a',
            chr(225) . chr(186) . chr(178) => 'A', chr(225) . chr(186) . chr(179) => 'a',
            chr(225) . chr(186) . chr(186) => 'E', chr(225) . chr(186) . chr(187) => 'e',
            chr(225) . chr(187) . chr(130) => 'E', chr(225) . chr(187) . chr(131) => 'e',
            chr(225) . chr(187) . chr(136) => 'I', chr(225) . chr(187) . chr(137) => 'i',
            chr(225) . chr(187) . chr(142) => 'O', chr(225) . chr(187) . chr(143) => 'o',
            chr(225) . chr(187) . chr(148) => 'O', chr(225) . chr(187) . chr(149) => 'o',
            chr(225) . chr(187) . chr(158) => 'O', chr(225) . chr(187) . chr(159) => 'o',
            chr(225) . chr(187) . chr(166) => 'U', chr(225) . chr(187) . chr(167) => 'u',
            chr(225) . chr(187) . chr(172) => 'U', chr(225) . chr(187) . chr(173) => 'u',
            chr(225) . chr(187) . chr(182) => 'Y', chr(225) . chr(187) . chr(183) => 'y',
            // tilde
            chr(225) . chr(186) . chr(170) => 'A', chr(225) . chr(186) . chr(171) => 'a',
            chr(225) . chr(186) . chr(180) => 'A', chr(225) . chr(186) . chr(181) => 'a',
            chr(225) . chr(186) . chr(188) => 'E', chr(225) . chr(186) . chr(189) => 'e',
            chr(225) . chr(187) . chr(132) => 'E', chr(225) . chr(187) . chr(133) => 'e',
            chr(225) . chr(187) . chr(150) => 'O', chr(225) . chr(187) . chr(151) => 'o',
            chr(225) . chr(187) . chr(160) => 'O', chr(225) . chr(187) . chr(161) => 'o',
            chr(225) . chr(187) . chr(174) => 'U', chr(225) . chr(187) . chr(175) => 'u',
            chr(225) . chr(187) . chr(184) => 'Y', chr(225) . chr(187) . chr(185) => 'y',
            // acute accent
            chr(225) . chr(186) . chr(164) => 'A', chr(225) . chr(186) . chr(165) => 'a',
            chr(225) . chr(186) . chr(174) => 'A', chr(225) . chr(186) . chr(175) => 'a',
            chr(225) . chr(186) . chr(190) => 'E', chr(225) . chr(186) . chr(191) => 'e',
            chr(225) . chr(187) . chr(144) => 'O', chr(225) . chr(187) . chr(145) => 'o',
            chr(225) . chr(187) . chr(154) => 'O', chr(225) . chr(187) . chr(155) => 'o',
            chr(225) . chr(187) . chr(168) => 'U', chr(225) . chr(187) . chr(169) => 'u',
            // dot below
            chr(225) . chr(186) . chr(160) => 'A', chr(225) . chr(186) . chr(161) => 'a',
            chr(225) . chr(186) . chr(172) => 'A', chr(225) . chr(186) . chr(173) => 'a',
            chr(225) . chr(186) . chr(182) => 'A', chr(225) . chr(186) . chr(183) => 'a',
            chr(225) . chr(186) . chr(184) => 'E', chr(225) . chr(186) . chr(185) => 'e',
            chr(225) . chr(187) . chr(134) => 'E', chr(225) . chr(187) . chr(135) => 'e',
            chr(225) . chr(187) . chr(138) => 'I', chr(225) . chr(187) . chr(139) => 'i',
            chr(225) . chr(187) . chr(140) => 'O', chr(225) . chr(187) . chr(141) => 'o',
            chr(225) . chr(187) . chr(152) => 'O', chr(225) . chr(187) . chr(153) => 'o',
            chr(225) . chr(187) . chr(162) => 'O', chr(225) . chr(187) . chr(163) => 'o',
            chr(225) . chr(187) . chr(164) => 'U', chr(225) . chr(187) . chr(165) => 'u',
            chr(225) . chr(187) . chr(176) => 'U', chr(225) . chr(187) . chr(177) => 'u',
            chr(225) . chr(187) . chr(180) => 'Y', chr(225) . chr(187) . chr(181) => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            chr(201) . chr(145) => 'a',
            // macron
            chr(199) . chr(149) => 'U', chr(199) . chr(150) => 'u',
            // acute accent
            chr(199) . chr(151) => 'U', chr(199) . chr(152) => 'u',
            // caron
            chr(199) . chr(141) => 'A', chr(199) . chr(142) => 'a',
            chr(199) . chr(143) => 'I', chr(199) . chr(144) => 'i',
            chr(199) . chr(145) => 'O', chr(199) . chr(146) => 'o',
            chr(199) . chr(147) => 'U', chr(199) . chr(148) => 'u',
            chr(199) . chr(153) => 'U', chr(199) . chr(154) => 'u',
            // grave accent
            chr(199) . chr(155) => 'U', chr(199) . chr(156) => 'u',
        );

        $string = strtr($string, $chars);
    } else {
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
            . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
            . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
            . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
            . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
            . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
            . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
            . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
            . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
            . chr(252) . chr(253) . chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}


function seems_utf8($str)
{
    $length = strlen($str);
    for ($i = 0; $i < $length; $i++) {
        $c = ord($str[$i]);
        if ($c < 0x80) $n = 0; # 0bbbbbbb
        elseif (($c & 0xE0) == 0xC0) $n = 1; # 110bbbbb
        elseif (($c & 0xF0) == 0xE0) $n = 2; # 1110bbbb
        elseif (($c & 0xF8) == 0xF0) $n = 3; # 11110bbb
        elseif (($c & 0xFC) == 0xF8) $n = 4; # 111110bb
        elseif (($c & 0xFE) == 0xFC) $n = 5; # 1111110b
        else return false; # Does not match any model
        for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
            if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                return false;
        }
    }
    return true;
}


function utf8_uri_encode($utf8_string, $length = 0)
{
    $unicode = '';
    $values = array();
    $num_octets = 1;
    $unicode_length = 0;

    $string_length = strlen($utf8_string);
    for ($i = 0; $i < $string_length; $i++) {

        $value = ord($utf8_string[$i]);

        if ($value < 128) {
            if ($length && ($unicode_length >= $length))
                break;
            $unicode .= chr($value);
            $unicode_length++;
        } else {
            if (count($values) == 0) $num_octets = ($value < 224) ? 2 : 3;

            $values[] = $value;

            if ($length && ($unicode_length + ($num_octets * 3)) > $length)
                break;
            if (count($values) == $num_octets) {
                if ($num_octets == 3) {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                    $unicode_length += 9;
                } else {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                    $unicode_length += 6;
                }

                $values = array();
                $num_octets = 1;
            }
        }
    }

    return $unicode;
}

