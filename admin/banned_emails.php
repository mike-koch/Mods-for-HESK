<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.0 beta 1 from 30th December 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2014 Klemen Stirn. All Rights Reserved.
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

/* Check permissions for this feature */
hesk_checkPermission('can_ban_emails');
$can_unban = hesk_checkPermission('can_unban_emails', 0);

// Define required constants
define('LOAD_TABS',1);

// What should we do?
if ( $action = hesk_REQUEST('a') )
{
	if ( defined('HESK_DEMO') ) {hesk_process_messages($hesklang['ddemo'], 'banned_emails.php', 'NOTICE');}
	elseif ($action == 'ban')   {ban_email();}
	elseif ($action == 'unban' && $can_unban) {unban_email();}
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row" style="padding: 20px">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#"><?php echo $hesklang['banemail']; ?> <i class="fa fa-question-circle settingsquestionmark" onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['banemail_intro']); ?>')"></i></a>
        </li>
        <?php
        // Show a link to banned_ips.php if user has permission to do so
        if ( hesk_checkPermission('can_ban_ips',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">'.$hesklang['banemail'].'</a>
            </li>';
        }
        // Show a link to status_message.php if user has permission to do so
        if ( hesk_checkPermission('can_service_msg',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
            </li>';
        }
        ?>
    </ul>
    <div class="tab-content summaryList tabPadding">
        <script language="javascript" type="text/javascript"><!--
            function confirm_delete()
            {
                if (confirm('<?php echo hesk_makeJsString($hesklang['delban_confirm']); ?>')) {return true;}
                else {return false;}
            }
            //-->
        </script>
        <div class="row">
            <div class="col-md-8">
                <br><br>
                <?php
                /* This will handle error, success and notice messages */
                hesk_handle_messages();
                ?>
                <form action="banned_emails.php" method="post" name="form1" role="form" class="form-horizontal">
                    <div class="form-group">
                        <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['bananemail']; ?></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="email" size="30" maxlength="255" placeholder="<?php echo $hesklang['email']; ?>">
                            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                            <input type="hidden" name="a" value="ban" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="submit" value="<?php echo $hesklang['savebanemail']; ?>" class="btn btn-default">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <h6 style="font-weight: bold"><?php echo $hesklang['banex']; ?></h6>
                <div class="footerWithBorder blankSpace"></div>
                <b>john@email.com</b><br />
                <b>@domain.com</b></td>
            </div>
        </div>
        <div class="row">
            <?php

            // Get banned emails from database
            $res = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'banned_emails` ORDER BY `email` ASC');
            $num = hesk_dbNumRows($res);

            echo '<h4>'.$hesklang['eperm'].'</h4>';
            if ($num < 1)
            {
                echo '<p>'.$hesklang['no_banemails'].'</p>';
            }
            else
            {
                // List of staff
                if ( ! isset($admins) )
                {
                    $admins = array();
                    $res2 = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users`");
                    while ($row=hesk_dbFetchAssoc($res2))
                    {
                        $admins[$row['id']]=$row['name'];
                    }
                }

                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php echo $hesklang['email']; ?></th>
                            <th><?php echo $hesklang['banby']; ?></th>
                            <th><?php echo $hesklang['date']; ?></th>
                            <?php
                            if ($can_unban)
                            {
                                ?>
                                <th><?php echo $hesklang['opt']; ?></th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        while ($ban=hesk_dbFetchAssoc($res))
                        {
                        $color = '';
                        if (isset($_SESSION['ban_email']['id']) && $ban['id'] == $_SESSION['ban_email']['id'])
                        {
                        $color = 'success';
                        unset($_SESSION['ban_email']['id']);
                        }

                        echo '
                        <tr>
                            <td class="'.$color.'" style="text-align:left">'.$ban['email'].'</td>
                            <td class="'.$color.'" style="text-align:left">'.(isset($admins[$ban['banned_by']]) ? $admins[$ban['banned_by']] : $hesklang['e_udel']).'</td>
                            <td class="'.$color.'" style="text-align:left">'.$ban['dt'].'</td>
                            ';

                            if ($can_unban)
                            {
                            echo '
                            <td class="'.$color.'" style="text-align:left;">
                                <a href="banned_emails.php?a=unban&amp;id='.$ban['id'].'&amp;token='.hesk_token_echo(0).'" onclick="return confirm_delete();"
                                    data-toggle="tooltip" data-placement="top" data-original-title="'.$hesklang['delban'].'">
                                    <i class="fa fa-times" style="color: red; font-size: 16px;"></i>
                                </a>
                            </td>
                            ';
                            }

                            echo '</tr>';
                        } // End while
                    ?>
                    </tbody>
                </table>
                <div align="center">
                    <table border="0" cellspacing="1" cellpadding="3" class="white" width="100%">
                        <?php



                        ?>
                    </table>
                </div>
            <?php
            }

            ?>
        </div>
    </div>
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function ban_email()
{
	global $hesk_settings, $hesklang;

	// A security check
	hesk_token_check();

	// Get the email
	$email = strtolower( hesk_input( hesk_REQUEST('email') ) );

	// Nothing entered?
	if ( ! strlen($email) )
	{
    	hesk_process_messages($hesklang['enterbanemail'],'banned_emails.php');
	}

	// Only allow one email to be entered
	$email = ($index = strpos($email, ',')) ? substr($email, 0,  $index) : $email;
	$email = ($index = strpos($email, ';')) ? substr($email, 0,  $index) : $email;

	// Validate email address
	$hesk_settings['multi_eml'] = 0;

	if ( ! hesk_validateEmail($email, '', 0) && ! verify_email_domain($email) )
	{
		hesk_process_messages($hesklang['validbanemail'],'banned_emails.php');
	}

	// Redirect either to banned emails or ticket page from now on
	$redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999) : 'banned_emails.php';

	// Prevent duplicate rows
	if ( $_SESSION['ban_email']['id'] = hesk_isBannedEmail($email) )
	{
    	hesk_process_messages( sprintf($hesklang['emailbanexists'], $email) ,$redirect_to,'NOTICE');
	}

	// Insert the email address into database
	hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_emails` (`email`,`banned_by`) VALUES ('".hesk_dbEscape($email)."','".intval($_SESSION['id'])."')");

	// Remember email that got banned
	$_SESSION['ban_email']['id'] = hesk_dbInsertID();

	// Show success
    hesk_process_messages( sprintf($hesklang['email_banned'], $email) ,$redirect_to,'SUCCESS');

} // End ban_email()


function unban_email()
{
	global $hesk_settings, $hesklang;

	// A security check
	hesk_token_check();

	// Delete from bans
	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_emails` WHERE `id`=" . intval( hesk_GET('id') ) . " LIMIT 1");

	// Redirect either to banned emails or ticket page from now on
	$redirect_to = ($trackingID = hesk_cleanID()) ? 'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999) : 'banned_emails.php';

	// Show success
    hesk_process_messages($hesklang['email_unbanned'],$redirect_to,'SUCCESS');

} // End unban_email()


function verify_email_domain($domain)
{
    // Does it start with an @?
	$atIndex = strrpos($domain, "@");
	if ($atIndex !== 0)
	{
		return false;
	}

	// Get the domain and domain length
	$domain = substr($domain, 1);
	$domainLen = strlen($domain);

    // Check domain part length
	if ($domainLen < 1 || $domainLen > 254)
	{
		return false;
	}

    // Check domain part characters
	if ( ! preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain) )
	{
		return false;
	}

	// Domain part mustn't have two consecutive dots
	if ( strpos($domain, '..') !== false )
	{
		return false;
	}

	// All OK
	return true;

} // END verify_email_domain()

?>
