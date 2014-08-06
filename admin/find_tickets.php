<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2013 Klemen Stirn. All Rights Reserved.
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

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

define('CALENDAR',1);
$_SESSION['hide']['ticket_list'] = true;

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');

$_SERVER['PHP_SELF'] = './admin_main.php';

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

?>

</td>
</tr>
<tr>
<td>

<h3 align="center"><?php echo $hesklang['tickets_found']; ?></h3>

<?php

// This SQL code will be used to retrieve results
$sql_final = "SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE ";

// This code will be used to count number of results
$sql_count = "SELECT COUNT(*) FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE ";

// This is common SQL for both queries
$sql = "";

// Some default settings
$archive = array(1=>0,2=>0);
$s_my = array(1=>1,2=>1);
$s_ot = array(1=>1,2=>1);
$s_un = array(1=>1,2=>1);

// --> TICKET CATEGORY
$category = intval( hesk_GET('category', 0) );

// Make sure user has access to this category
if ($category && hesk_okCategory($category, 0) )
{
	$sql .= " `category`='{$category}' ";
}
// No category selected, show only allowed categories
else
{
	$sql .= hesk_myCategories();
}

// Show only tagged tickets?
if ( ! empty($_GET['archive']) )
{
	$archive[2]=1;
	$sql .= " AND `archive`='1' ";
}

// Ticket owner preferences
$fid = 2;
require(HESK_PATH . 'inc/assignment_search.inc.php');

$hesk_error_buffer = '';
$no_query = 0;

// Search query
$q = stripslashes( hesk_input( hesk_GET('q', '') ) );

// No query entered?
if ( ! strlen($q) )
{
	$hesk_error_buffer .= $hesklang['fsq'];
	$no_query = 1;
}

// What field are we searching in
$what = hesk_GET('what', '') or $hesk_error_buffer .= '<br />' . $hesklang['wsel'];

// Sequential ID supported?
if ($what == 'seqid' && ! $hesk_settings['sequential'])
{
	$what = 'trackid';
}

// Setup SQL based on searching preferences
if ( ! $no_query)
{
	$sql .= " AND ";

	switch ($what)
	{
		case 'trackid':
		    $sql  .= " ( `trackid` = '".hesk_dbEscape($q)."' OR `merged` LIKE '%#".hesk_dbEscape($q)."#%' ) ";
		    break;
		case 'name':
		    $sql  .= "`name` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "' ";
		    break;
		case 'email':
	         $sql  .= "`email` LIKE '%".hesk_dbEscape($q)."%' ";
			 break;
		case 'subject':
		    $sql  .= "`subject` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "' ";
		    break;
		case 'message':
		    $sql  .= " ( `message` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "'
            		OR
                    `id` IN (
            		SELECT DISTINCT `replyto`
                	FROM   `".hesk_dbEscape($hesk_settings['db_pfix'])."replies`
                	WHERE  `message` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "' )
                    )
                    ";
		    break;
		case 'seqid':
	         $sql  .= "`id` = '".intval($q)."' ";
			 break;
		case 'notes':
		    $sql  .= "`id` IN (
            		SELECT DISTINCT `ticket`
                	FROM   `".hesk_dbEscape($hesk_settings['db_pfix'])."notes`
                	WHERE  `message` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "' )
                	";
		    break;
		default:
	    	if (isset($hesk_settings['custom_fields'][$what]) && $hesk_settings['custom_fields'][$what]['use'])
	        {
	        	$sql .= "`".hesk_dbEscape($what)."` LIKE '%".hesk_dbEscape($q)."%' COLLATE '" . hesk_dbEscape($hesklang['_COLLATE']) . "' ";
	        }
	        else
	        {
	        	$hesk_error_buffer .= '<br />' . $hesklang['invalid_search'];
	        }
	}
}

/* Date */
/* -> Check for compatibility with old date format */
if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", hesk_GET('dt'), $m))
{
	$_GET['dt']=$m[2].$m[3].$m[1];
}

/* -> Now process the date value */
$dt = preg_replace('/[^0-9]/','', hesk_GET('dt') );
if (strlen($dt) == 8)
{
	$date = substr($dt,4,4) . '-' . substr($dt,0,2) . '-' . substr($dt,2,2);
	$date_input= substr($dt,0,2) . '/' . substr($dt,2,2) . '/' . substr($dt,4,4);

	/* This search is valid even if no query is entered */
	if ($no_query)
	{
		$hesk_error_buffer = str_replace($hesklang['fsq'],'',$hesk_error_buffer);
	}

	$sql .= " AND (`dt` LIKE '".hesk_dbEscape($date)."%' OR `lastchange` LIKE '".hesk_dbEscape($date)."%') ";
}
else
{
	$date = '';
    $date_input = '';
}

/* Any errors? */
if (strlen($hesk_error_buffer))
{
	hesk_process_messages($hesk_error_buffer,'NOREDIRECT');
}

/* This will handle error, success and notice messages */
$handle = hesk_handle_messages();

# echo "$sql<br/>";

// That's all the SQL we need for count
$sql_count .= $sql;
$sql = $sql_final . $sql;

/* Prepare variables used in search and forms */
require_once(HESK_PATH . 'inc/prepare_ticket_search.inc.php');

/* If there has been an error message skip searching for tickets */
if ($handle !== FALSE)
{
	$href = 'find_tickets.php';
	require_once(HESK_PATH . 'inc/ticket_list.inc.php');
}
?>

<hr />

<?php

/* Clean unneeded session variables */
hesk_cleanSessionVars('hide');

/* Show the search form */
require_once(HESK_PATH . 'inc/show_search_form.inc.php');

/* Print footer */
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

?>
