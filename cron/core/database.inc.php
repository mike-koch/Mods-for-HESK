<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.1 from 26th February 2015
*  Author: Klemen Stirn
*  Website: https://www.hesk.com
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
        echo $hesklang['cant_connect_db'] . ': ' . $message;
        die();
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

        echo $hesklang['cant_connect_db'] . ': ' . $message;
        die();
    }

    // Check MySQL/PHP version and set encoding to utf8
    hesk_dbSetNames();

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
    mfh_log_error('CRON', $hesklang['cant_sql'] . ': ' . $message, 'CRON');
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
