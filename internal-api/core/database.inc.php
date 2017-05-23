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


function hesk_dbCollate()
{
    global $hesklang;

    // MySQL vesions prior to 5.6 don't support some collations
    if ( in_array($hesklang['_COLLATE'], array('utf8_croatian_ci', 'utf8_german2_ci', 'utf8_vietnamese_ci')) )
    {
        if ( version_compare( hesk_dbResult( hesk_dbQuery('SELECT VERSION() AS version') ), '5.6', '<') )
        {
            $hesklang['_COLLATE'] = 'utf8_general_ci';
        }
    }

    return hesk_dbEscape($hesklang['_COLLATE']);

} // END hesk_dbCollate()


function hesk_dbSetNames()
{
	global $hesk_settings, $hesk_db_link;

    if ($hesk_settings['db_vrsn'])
    {
		mysql_set_charset('utf8', $hesk_db_link);
    }
    else
    {
    	hesk_dbQuery("SET NAMES 'utf8'");
    }

} // END hesk_dbSetNames()


function hesk_dbFormatEmail($email, $field = 'email')
{
	global $hesk_settings;

	$email = hesk_dbLike($email);    

	if ($hesk_settings['multi_eml'])
	{
		return " (`".hesk_dbEscape($field)."` LIKE '".hesk_dbEscape($email)."' OR `".hesk_dbEscape($field)."` LIKE '%,".hesk_dbEscape($email)."' OR `".hesk_dbEscape($field)."` LIKE '".hesk_dbEscape($email).",%' OR `".hesk_dbEscape($field)."` LIKE '%,".hesk_dbEscape($email).",%') ";
	}
	else
	{
		return " `".hesk_dbEscape($field)."` LIKE '".hesk_dbEscape($email)."' ";
	}

} // END hesk_dbFormatEmail()


function hesk_dbTime()
{
	$res = hesk_dbQuery("SELECT NOW()");
	return strtotime(hesk_dbResult($res,0,0));
} // END hesk_dbTime()


function hesk_dbSetTimezone()
{
    global $hesk_settings;

    hesk_dbQuery('SET time_zone = "'.hesk_timeToHHMM(date('Z')).'"');

    return true;
} // END hesk_dbSetTimezone()


function hesk_dbEscape($in)
{
	global $hesk_db_link;

    $in = mysql_real_escape_string(stripslashes($in), $hesk_db_link);
    $in = str_replace('`','&#96;',$in);

    return $in;
} // END hesk_dbEscape()


function hesk_dbLike($in)
{
	return str_replace( array('_', '%'), array('\\\\_', '\\\\%'), $in);
} // END hesk_dbLike()


function hesk_dbConnect()
{
	global $hesk_settings;
	global $hesk_db_link;
    global $hesklang;

    // Is mysql supported?
    if ( ! function_exists('mysql_connect') )
    {
    	die($hesklang['emp']);
    }

    // Connect to the database
    $hesk_db_link = @mysql_connect($hesk_settings['db_host'], $hesk_settings['db_user'], $hesk_settings['db_pass']);

	// Errors?
    if ( ! $hesk_db_link)
    {
    	if ($hesk_settings['debug_mode'])
        {
            $message = $hesklang['mysql_said'] . ': ' . mysql_error();
        }
        else
        {
            $message = $hesklang['contact_webmaster'] . $hesk_settings['webmaster_email'];
        }
        header('Content-Type: application/json');
        print_error($hesklang['cant_connect_db'], $message);
        return http_response_code(500);
    }

    if ( ! @mysql_select_db($hesk_settings['db_name'], $hesk_db_link))
    {
    	if ($hesk_settings['debug_mode'])
        {
            $message = $hesklang['mysql_said'] . ': ' . mysql_error();
        }
        else
        {
            $message = $hesklang['contact_webmaster'] . $hesk_settings['webmaster_email'];
        }
        header('Content-Type: application/json');
        print_error($hesklang['cant_connect_db'], $message);
        die();
    }

    // Check MySQL/PHP version and set encoding to utf8
    hesk_dbSetNames();

    // Set the correct timezone
    hesk_dbSetTimezone();

    return $hesk_db_link;

} // END hesk_dbConnect()


function hesk_dbClose()
{
	global $hesk_db_link;

    return @mysql_close($hesk_db_link);

} // END hesk_dbClose()


function hesk_dbQuery($query)
{
    global $hesk_last_query;
    global $hesk_db_link;
    global $hesklang, $hesk_settings;

    if ( ! $hesk_db_link && ! hesk_dbConnect())
    {
        return false;
    }

    $hesk_last_query = $query;

    if ($res = @mysql_query($query, $hesk_db_link))
    {
    	return $res;
    }
    elseif ($hesk_settings['debug_mode'])
    {
        $message = $hesklang['mysql_said'] . mysql_error();
    }
    else
    {
        $message = $hesklang['contact_webmaster'] . $hesk_settings['webmaster_email'];
    }
    header('Content-Type: application/json');
    print_error($hesklang['cant_sql'], $message);
    die();

} // END hesk_dbQuery()


function hesk_dbFetchAssoc($res)
{

    return @mysql_fetch_assoc($res);

} // END hesk_FetchAssoc()


function hesk_dbFetchRow($res)
{

    return @mysql_fetch_row($res);

} // END hesk_FetchRow()


function hesk_dbResult($res, $row = 0, $column = 0)
{

    return @mysql_result($res, $row, $column);

} // END hesk_dbResult()


function hesk_dbInsertID()
{
	global $hesk_db_link;

    if ($lastid = @mysql_insert_id($hesk_db_link))
    {
        return $lastid;
    }

} // END hesk_dbInsertID()


function hesk_dbFreeResult($res)
{

    return mysql_free_result($res);

} // END hesk_dbFreeResult()


function hesk_dbNumRows($res)
{

    return @mysql_num_rows($res);

} // END hesk_dbNumRows()


function hesk_dbAffectedRows()
{
	global $hesk_db_link;

    return @mysql_affected_rows($hesk_db_link);

} // END hesk_dbAffectedRows()
