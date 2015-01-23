<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.0 beta 1 from 30th December 2014
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

// Auto-focus first empty or error field
define('AUTOFOCUS', true);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Pre-populate fields
// Customer name
if ( isset($_REQUEST['name']) )
{
    $_SESSION['as_name'] = $_REQUEST['name'];
}

// Customer email address
if ( isset($_REQUEST['email']) )
{
    $_SESSION['as_email']  = $_REQUEST['email'];
    $_SESSION['as_email2'] = $_REQUEST['email'];
}

// Category ID
if ( isset($_REQUEST['catid']) )
{
    $_SESSION['as_category'] = intval($_REQUEST['catid']);
}
if ( isset($_REQUEST['category']) )
{
    $_SESSION['as_category'] = intval($_REQUEST['category']);
}

// Priority
if ( isset($_REQUEST['priority']) )
{
    $_SESSION['as_priority'] = intval($_REQUEST['priority']);
}

// Subject
if ( isset($_REQUEST['subject']) )
{
    $_SESSION['as_subject'] = $_REQUEST['subject'];
}

// Message
if ( isset($_REQUEST['message']) )
{
    $_SESSION['as_message'] = $_REQUEST['message'];
}

// Custom fields
foreach ($hesk_settings['custom_fields'] as $k=>$v)
{
    if ($v['use'] && isset($_REQUEST[$k]) )
    {
        $_SESSION['as_'.$k] = $_REQUEST[$k];
    }
}

/* Varibles for coloring the fields in case of errors */
if (!isset($_SESSION['iserror']))
{
	$_SESSION['iserror'] = array();
}

if (!isset($_SESSION['isnotice']))
{
	$_SESSION['isnotice'] = array();
}

/* List of users */
$admins = array();
$result = hesk_dbQuery("SELECT `id`,`name`,`isadmin`,`categories`,`heskprivileges` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `active` = '1' ORDER BY `name` ASC");
while ($row=hesk_dbFetchAssoc($result))
{
	/* Is this an administrator? */
	if ($row['isadmin'])
    {
	    $admins[$row['id']]=$row['name'];
	    continue;
    }

	/* Not admin, is user allowed to view tickets? */
	if (strpos($row['heskprivileges'], 'can_view_tickets') !== false)
	{
		$admins[$row['id']]=$row['name'];
		continue;
	}
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

?>

<ol class="breadcrumb">
  <li><a href="admin_main.php"><?php echo $hesk_settings['hesk_title']; ?></a></li>
  <li class="active"><?php echo $hesklang['nti2']; ?></li>
</ol>
    <div class="row" style="margin-top: 20px">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['quick_help']; ?></div>
                <div class="panel-body">
                    <p><?php echo $hesklang['nti3']; ?></p>
                    <br/>
                    <p><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <?php
                /* This will handle error, success and notice messages */
                hesk_handle_messages();
            ?>
            <h3><?php echo $hesklang['nti2']; ?></h3>
            <div class="footerWithBorder blankSpace"></div>

            <!-- START FORM -->
            <form role="form" class="form-horizontal" method="post" action="admin_submit_ticket.php" name="form1" enctype="multipart/form-data">
                <!-- Contact info -->
                <?php if (in_array('name',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>
                    <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['name']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" size="40" maxlength="30"
                               value="<?php if (isset($_SESSION['as_name'])) {echo stripslashes(hesk_input($_SESSION['as_name']));}
                                   else if (isset($_GET['name'])) {echo hesk_GET('name');} ?>"
                               placeholder="<?php echo $hesklang['name']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?>: </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="email" size="40" maxlength="1000"
                               value="<?php if (isset($_SESSION['as_email'])) {echo stripslashes(hesk_input($_SESSION['as_email']));}
                                   else if (isset($_GET['email'])) {echo hesk_GET('email');} ?>" <?php if($hesk_settings['detect_typos']) { echo ' onblur="Javascript:hesk_suggestEmail(1)"'; } ?>
                               placeholder="<?php echo $hesklang['email']; ?>">
                    </div>
                
                </div>
                <div id="email_suggestions"></div>
                <hr/>
                <!-- Department and Priority -->
                <?php if (in_array('category',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} elseif (in_array('category',$_SESSION['isnotice'])) {echo '<div class="form-group has-warning">';} else {echo '<div class="form-group">';} ?>
                    <label for="category" class="col-sm-3 control-label"><?php echo $hesklang['category']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <select name="category" class="form-control">
                            <?php
                                // Show the "Click to select"?
                                if ($hesk_settings['select_cat'])
	                            {
                                    echo '<option value="">'.$hesklang['select'].'</option>';
	                            }

                                // List categories
	                            $result = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC');
	                            while ($row=hesk_dbFetchAssoc($result))
	                            {
	                                if (isset($_SESSION['as_category']) && $_SESSION['as_category'] == $row['id']) {$selected = ' selected="selected"';}
	                                else {$selected = '';}
	                                echo '<option value="'.$row['id'].'"'.$selected.'>'.$row['name'].'</option>';
	                            }
	                        ?>
                        </select>     
                    </div>
                </div>
                <?php if (in_array('priority',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>
                    <label for="priority" class="col-sm-3 control-label"><?php echo $hesklang['priority']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <select name="priority" class="form-control">
                            <?php
                            // Show the "Click to select"?
                            if ($hesk_settings['select_pri'])
                            {
                                echo '<option value="">'.$hesklang['select'].'</option>';
                            }
                            ?>
                            <option value="3" <?php
                                if((isset($_SESSION['as_priority']) && $_SESSION['as_priority']==3)
                                    || (isset($_GET['priority']) && $_GET['priority']==3)) {echo 'selected="selected"';} ?>><?php echo $hesklang['low']; ?></option>
	                        <option value="2" <?php
                                if((isset($_SESSION['as_priority']) && $_SESSION['as_priority']==2)
                                    || (isset($_GET['priority']) && $_GET['priority']==2)) {echo 'selected="selected"';} ?>><?php echo $hesklang['medium']; ?></option>
	                        <option value="1" <?php
                                if((isset($_SESSION['as_priority']) && $_SESSION['as_priority']==1)
                                    || (isset($_GET['priority']) && $_GET['priority']==1)) {echo 'selected="selected"';} ?>><?php echo $hesklang['high']; ?></option>
	                        <option value="0" <?php
                                if((isset($_SESSION['as_priority']) && $_SESSION['as_priority']==0)
                                    || (isset($_GET['priority']) && $_GET['priority']==0)) {echo 'selected="selected"';} ?>><?php echo $hesklang['critical']; ?></option>
                        </select>
                    </div>
                </div>
                <!-- Start Custom Before -->
                <?php

	            /* custom fields BEFORE comments */

	            foreach ($hesk_settings['custom_fields'] as $k=>$v)
	            {
		            if ($v['use'] && $v['place']==0)
	                {
                        if ($modsForHesk_settings['custom_field_setting'])
                        {
                            $v['name'] = $hesklang[$v['name']];
                        }

	    	            // $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
                        // Staff doesn't need to fill in required custom fields
                        $v['req'] = '';

			            if ($v['type'] == 'checkbox' && !isset($_GET["c_$k"]))
                        {
            	            $k_value = array();
                            if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"]))
                            {
	                            foreach ($_SESSION["c_$k"] as $myCB)
	                            {
	                	            $k_value[] = stripslashes(hesk_input($myCB));
	                            }
                            }
                        }
                        elseif (isset($_SESSION["c_$k"]))
                        {
            	            $k_value  = stripslashes(hesk_input($_SESSION["c_$k"]));
                        }
                        elseif (isset($_GET["c_$k"]))
                        {
                            if ($v['type'] == 'checkbox')
                            {
                                $k_value = explode('-CHECKBOX-', $_GET["c_$k"]);
                            } else
                            {
                                $k_value = stripslashes(hesk_GET("c_$k"));
                            }
                        }
                        else
                        {
            	            $k_value  = '';
                        }

	                    switch ($v['type'])
	                    {
	        	            /* Radio box */
	        	            case 'radio':
					            echo '<div class="form-group"><label class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label><div align="left" class="col-sm-9">';

	            	            $options = explode('#HESK#',$v['value']);
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

	                            foreach ($options as $option)
	                            {

		            	            if (strlen($k_value) == 0 || $k_value == $option)
		                            {
	                    	            $k_value = $option;
							            $checked = 'checked="checked"';
	                                }
	                                else
	                                {
	                    	            $checked = '';
	                                }

	                	            echo '<label style="font-weight: normal;"><input type="radio" id="'.$v['name'].'" name="'.$k.'" value="'.$option.'" '.$checked.' '.$cls.' /> '.$option.'</label><br />';
	                            }

                                echo '</div></div>';
	                        break;

	                        /* Select drop-down box */
	                        case 'select':

                	            $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group"><label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'" '.$cls.'>';

                                // Show "Click to select"?
                                $v['value'] = str_replace('{HESK_SELECT}', '', $v['value'], $num);
                                if ($num)
                                {
                                    echo '<option value="">'.$hesklang['select'].'</option>';
                                }

	            	            $options = explode('#HESK#',$v['value']);

	                            foreach ($options as $option)
	                            {

		            	            if ($k_value == $option)
		                            {
	                    	            $k_value = $option;
	                                    $selected = 'selected="selected"';
		                            }
	                                else
	                                {
	                    	            $selected = '';
	                                }

	                	            echo '<option '.$selected.'>'.$option.'</option>';
	                            }

	                            echo '</select></div></div>';
	                        break;

                            case 'multiselect':
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="'.$v['name'].'[]" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'[]" '.$cls.' multiple>';

                                $options = explode('#HESK#',$v['value']);

                                foreach ($options as $option)
                                {

                                    if (strlen($k_value == $option))
                                    {
                                        $k_value = $option;
                                        $selected = 'selected="selected"';
                                    }
                                    else
                                    {
                                        $selected = '';
                                    }

                                    echo '<option '.$selected.'>'.$option.'</option>';
                                }

                                echo '</select>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" onclick="selectAll(\''.$v['name'].'\')">Select All</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\''.$v['name'].'\')">Deselect All</button>
                                </div></div></div>';
                            break;

	                        /* Checkbox */
	        	            case 'checkbox':
					            echo '<div class="form-group"><label class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label><div align="left" class="col-sm-9">';
                                $options = explode('#HESK#',$v['value']);
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

	                            foreach ($options as $option)
	                            {

		            	            if (in_array($option,$k_value))
		                            {
							            $checked = 'checked="checked"';
	                                }
	                                else
	                                {
	                    	            $checked = '';
	                                }

	                	            echo '<label style="font-weight: normal;"><input id="'.$v['name'].'" type="checkbox" name="'.$k.'[]" value="'.$option.'" '.$checked.' '.$cls.' /> '.$option.'</label><br />';
	                            }
                                echo '</div></div>';
	                        break;

	                        /* Large text box */
	                        case 'textarea':
	                            $size = explode('#',$v['value']);
                                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><textarea class="form-control" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'" '.$cls.'>'.$k_value.'</textarea></div>
                                </div>';
	                        break;

                            case 'date':
                                if (strlen($k_value) != 0)
                                {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' isError ' : '';

                                echo '
                                <div class="form-group">
                                    <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="datepicker form-control white-readonly '.$cls.'" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" size="40"
                                            maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" readonly/>
                                        <span class="help-block">'.$hesklang['date_format'].'</span>
                                    </div>
                                </div>';
                                break;

                            case 'email':
                                if (strlen($k_value) != 0)
                                {
                                    $v['value'] = $k_value;
                                }

                                if ($v['value'] == 'cc' || $v['value'] == 'bcc')
                                {
                                    // (b)cc isn't a valid email but is the "value" used by settings. Just remove it.
                                    $v['value'] = '';
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" '.$cls.' /></div>
                                </div>';

                                break;

	                        /* Default text input */
	                        default:
                	            if (strlen($k_value) != 0)
                                {
                    	            $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" '.$cls.' /></div>
                                </div>';
	                    }
	                }
	            }
                ?>
                <!-- End custom before -->
                <!-- Ticket Info -->
                <?php
                // Lets handle ticket templates
                $can_options = '';

                // Get ticket templates from the database
                $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."ticket_templates` ORDER BY `tpl_order` ASC");

                // If we have any templates print them out
                if ( hesk_dbNumRows($res) )
                {
                    ?>
                    <script language="javascript" type="text/javascript"><!--
                        // -->
                        var myMsgTxt = new Array();
                        var mySubjectTxt = new Array();
                        myMsgTxt[0]='';
                        mySubjectTxt[0]='';

                        <?php
                        while ($mysaved = hesk_dbFetchRow($res))
                        {
                            $can_options .= '<option value="' . $mysaved[0] . '">' . $mysaved[1]. "</option>\n";
                            echo 'myMsgTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[2]))."';\n";
                            echo 'mySubjectTxt['.$mysaved[0].']=\''.str_replace("\r\n","\\r\\n' + \r\n'", addslashes($mysaved[1]))."';\n";
                        }

                        ?>

                        function setMessage(msgid)
                        {
                            var myMsg=myMsgTxt[msgid];
                            var mySubject=mySubjectTxt[msgid];

                            if (myMsg == '')
                            {
                                if (document.form1.mode[1].checked)
                                {
                                    document.getElementById('message').value = '';
                                    document.getElementById('subject').value = '';
                                }
                                return true;
                            }
                            if (document.getElementById)
                            {
                                if (document.getElementById('moderep').checked)
                                {
                                    document.getElementById('HeskMsg').innerHTML='<textarea name="message" id="message" rows="12" cols="60">'+myMsg+'</textarea>';
                                    document.getElementById('HeskSub').innerHTML='<input type="text" name="subject" id="subject" size="40" maxlength="40" value="'+mySubject+'" />';
                                }
                                else
                                {
                                    var oldMsg = document.getElementById('message').value;
                                    document.getElementById('HeskMsg').innerHTML='<textarea name="message" id="message" rows="12" cols="60">'+oldMsg+myMsg+'</textarea>';
                                    if (document.getElementById('subject').value == '')
                                    {
                                        document.getElementById('HeskSub').innerHTML='<input type="text" name="subject" id="subject" size="40" maxlength="40" value="'+mySubject+'" />';
                                    }
                                }
                            }
                            else
                            {
                                if (document.form1.mode[0].checked)
                                {
                                    document.form1.message.value=myMsg;
                                    document.form1.subject.value=mySubject;
                                }
                                else
                                {
                                    var oldMsg = document.form1.message.value;
                                    document.form1.message.value=oldMsg+myMsg;
                                    if (document.form1.subject.value == '')
                                    {
                                        document.form1.subject.value=mySubject;
                                    }
                                }
                            }

                        }
                        //-->
                    </script>
                <?php
                } // END fetchrows

                // Print templates
                if ( strlen($can_options) )
                {
                    ?>
                    <tr>
                        <td style="text-align:right" width="150" valign="top">&nbsp;</td>
                        <td width="80%">
                            <div align="center">
                                <table class="white" style="width:100%">
                                    <tr>
                                        <td class="admin_gray" colspan="2"><b>&raquo;<?php echo $hesklang['ticket_tpl']; ?></b> <?php echo hesk_checkPermission('can_man_ticket_tpl', 0) ? '(<a href="manage_ticket_templates.php">' . $hesklang['ticket_tpl_man'] . '</a>)' : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="admin_gray">
                                            <label><input type="radio" name="mode" id="modeadd" value="1" checked="checked" /> <?php echo $hesklang['madd']; ?></label><br />
                                            <label><input type="radio" name="mode" id="moderep" value="0" /> <?php echo $hesklang['mrep']; ?></label>
                                        </td>
                                        <td class="admin_gray">
                                            <?php echo $hesklang['select_ticket_tpl']; ?>:<br />
                                            <select name="saved_replies" onchange="setMessage(this.value)">
                                                <option value="0"> - <?php echo $hesklang['select_empty']; ?> - </option>
                                                <?php echo $can_options; ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php
                } // END printing templates
                elseif ( hesk_checkPermission('can_man_ticket_tpl', 0) )
                {
                    ?>
                    <tr>
                        <td style="text-align:right" width="150">&nbsp;</td>
                        <td width="80%"><a href="manage_ticket_templates.php"><?php echo $hesklang['ticket_tpl_man']; ?></a></td>
                    </tr>
                <?php
                }
                ?>

                <?php if (in_array('subject',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>
                    <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <span id="HeskSub"><input class="form-control" type="text" name="subject" id="subject" size="40" maxlength="40" value="<?php if (isset($_SESSION['as_subject']) || isset($_GET['subject'])) {echo stripslashes(hesk_input($_SESSION['as_subject']));} ?>" placeholder="<?php echo $hesklang['subject']; ?>" /></span>
                    </div>         
                </div>
                <?php if (in_array('message',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>
                    <div class="col-sm-12">
                        <span id="HeskSub">
                            <textarea class="form-control" name="message" id="message" rows="12" cols="60" placeholder="<?php echo $hesklang['message']; ?>" ><?php if (isset($_SESSION['as_message'])) {echo stripslashes(hesk_input($_SESSION['as_message']));} ?></textarea>
                        </span>
                    </div>
                </div>
                <hr/>
                <!-- Custom After -->
                <?php

	            /* custom fields BEFORE comments */

	            foreach ($hesk_settings['custom_fields'] as $k=>$v)
	            {
		            if ($v['use'] && $v['place'])
	                {
                        if ($modsForHesk_settings['custom_field_setting'])
                        {
                            $v['name'] = $hesklang[$v['name']];
                        }

	    	            // $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';
                        // Staff doesn't need to fill in required custom fields
                        $v['req'] = '';

			            if ($v['type'] == 'checkbox')
                        {
            	            $k_value = array();
                            if (isset($_SESSION["c_$k"]) && is_array($_SESSION["c_$k"]))
                            {
	                            foreach ($_SESSION["c_$k"] as $myCB)
	                            {
	                	            $k_value[] = stripslashes(hesk_input($myCB));
	                            }
                            }
                        }
                        elseif (isset($_SESSION["c_$k"]))
                        {
            	            $k_value  = stripslashes(hesk_input($_SESSION["c_$k"]));
                        }
                        else
                        {
            	            $k_value  = '';
                        }

	                    switch ($v['type'])
	                    {
	        	            /* Radio box */
	        	            case 'radio':
					            echo '<div class="form-group"><label class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label><div align="left" class="col-sm-9">';

	            	            $options = explode('#HESK#',$v['value']);
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

	                            foreach ($options as $option)
	                            {

		            	            if (strlen($k_value) == 0 || $k_value == $option)
		                            {
	                    	            $k_value = $option;
							            $checked = 'checked="checked"';
	                                }
	                                else
	                                {
	                    	            $checked = '';
	                                }

	                	            echo '<label style="font-weight: normal;"><input type="radio" id="'.$v['name'].'" name="'.$k.'" value="'.$option.'" '.$checked.' '.$cls.' /> '.$option.'</label><br />';
	                            }

                                echo '</div></div>';
	                        break;

	                        /* Select drop-down box */
	                        case 'select':

                	            $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group"><label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'" '.$cls.'>';

                                // Show "Click to select"?
                                $v['value'] = str_replace('{HESK_SELECT}', '', $v['value'], $num);
                                if ($num)
                                {
                                    echo '<option value="">'.$hesklang['select'].'</option>';
                                }

	            	            $options = explode('#HESK#',$v['value']);

	                            foreach ($options as $option)
	                            {

		            	            if ($k_value == $option)
		                            {
	                    	            $k_value = $option;
	                                    $selected = 'selected="selected"';
		                            }
	                                else
	                                {
	                    	            $selected = '';
	                                }

	                	            echo '<option '.$selected.'>'.$option.'</option>';
	                            }

	                            echo '</select></div></div>';
	                        break;

	                        /* Checkbox */
	        	            case 'checkbox':
					            echo '<div class="form-group"><label class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label><div align="left" class="col-sm-9">';

	            	            $options = explode('#HESK#',$v['value']);
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

	                            foreach ($options as $option)
	                            {

		            	            if (in_array($option,$k_value))
		                            {
							            $checked = 'checked="checked"';
	                                }
	                                else
	                                {
	                    	            $checked = '';
	                                }

	                	            echo '<label style="font-weight: normal;"><input id="'.$v['name'].'" type="checkbox" name="'.$k.'[]" value="'.$option.'" '.$checked.' '.$cls.' /> '.$option.'</label><br />';
	                            }
                                echo '</div></div>';
	                        break;

	                        /* Large text box */
	                        case 'textarea':
	                            $size = explode('#',$v['value']);
                                $size[0] = empty($size[0]) ? 5 : intval($size[0]);
                                $size[1] = empty($size[1]) ? 30 : intval($size[1]);

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><textarea class="form-control" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'" '.$cls.'>'.$k_value.'</textarea></div>
                                </div>';
	                        break;

                            case 'date':
                                if (strlen($k_value) != 0)
                                {
                                    $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' isError ' : '';

                                echo '
                                <div class="form-group">
                                    <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="datepicker form-control white-readonly '.$cls.'" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" size="40"
                                            maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" readonly/>
                                        <span class="help-block">'.$hesklang['date_format'].'</span>
                                    </div>
                                </div>';
                                break;

                            case 'multiselect':
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="'.$v['name'].'[]" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'[]" '.$cls.' multiple>';

                                $options = explode('#HESK#',$v['value']);

                                foreach ($options as $option)
                                {

                                    if ($k_value == $option)
                                    {
                                        $k_value = $option;
                                        $selected = 'selected="selected"';
                                    }
                                    else
                                    {
                                        $selected = '';
                                    }

                                    echo '<option '.$selected.'>'.$option.'</option>';
                                }

                                echo '</select>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" onclick="selectAll(\''.$v['name'].'\')">Select All</button>
                                    <button type="button" class="btn btn-default" onclick="deselectAll(\''.$v['name'].'\')">Deselect All</button>
                                </div></div></div>';
                                break;

	                        /* Default text input */
	                        default:
                	            if (strlen($k_value) != 0)
                                {
                    	            $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><input type="text" class="form-control" placeholder="'.$v['name'].'" id="'.$v['name'].'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" '.$cls.' /></div>
                                </div>';
	                    }
	                }
	            }
                /* end custom after */
	            /* attachments */
	            if ($hesk_settings['attachments']['use']) {

	            ?>
                <div class="form-group">
                    <label for="attachments" class="control-label col-sm-3"><?php echo $hesklang['attachments']; ?>:</label>
                    <div class="col-sm-9">
                            <?php
	                        for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
                            {
    	                        $cls = ($i == 1 && in_array('attachments',$_SESSION['iserror'])) ? ' class="isError" ' : '';
		                        echo '<input type="file" name="attachment['.$i.']" size="50" '.$cls.' /><br />';
	                        }
	                        ?>
	                        <a href="Javascript:void(0)" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;"><?php echo $hesklang['ful']; ?></a>
                    </div>
                </div>
                <hr />
	            <?php
	            }
	            ?> 
                <!-- Admin options -->
                <?php
                if ( ! isset($_SESSION['as_notify']) )
                {
                    $_SESSION['as_notify'] = $_SESSION['notify_customer_new'] ? 1 : 0;
                }
                ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo $hesklang['addop']; ?>:</label>
                    <div class="col-sm-9">
                        <label><input type="checkbox" name="notify" value="1" <?php echo empty($_SESSION['as_notify']) ? '' : 'checked="checked"'; ?> /> <?php echo $hesklang['seno']; ?></label><br />
                        <label><input type="checkbox" name="show" value="1" <?php echo (!isset($_SESSION['as_show']) || !empty($_SESSION['as_show'])) ? 'checked="checked"' : ''; ?> /> <?php echo $hesklang['otas']; ?></label><br />
                        <hr />     
                    </div>
                </div>
                <?php
	            if (hesk_checkPermission('can_assign_others',0))
	            {
                  if (in_array('owner',$_SESSION['iserror'])) {echo '<div class="form-group has-error">';} else {echo '<div class="form-group">';} ?>  
                    <label for="owner" class="col-sm-3 control-label"><?php echo $hesklang['asst2']; ?>:</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="owner" >
		                <option value="-1"> &gt; <?php echo $hesklang['unas']; ?> &lt; </option>
		                <?php

		                if ($hesk_settings['autoassign'])
		                {
			                echo '<option value="-2"> &gt; ' . $hesklang['aass'] . ' &lt; </option>';
		                }

                        $owner = isset($_SESSION['as_owner']) ? intval($_SESSION['as_owner']) : 0;

		                foreach ($admins as $k=>$v)
		                {
			                if ($k == $owner)
			                {
				                echo '<option value="'.$k.'" selected="selected">'.$v.'</option>';
			                }
                            else
			                {
				                echo '<option value="'.$k.'">'.$v.'</option>';
			                }

		                }
		                ?>
		                </select>
                    </div>
                </div>
                <?php
	            }
	            elseif (hesk_checkPermission('can_assign_self',0))
	            {
                $checked = (!isset($_SESSION['as_owner']) || !empty($_SESSION['as_owner'])) ? 'checked="checked"' : '';
	            ?> 
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <label><input type="checkbox" name="assing_to_self" value="1" <?php echo $checked; ?> /> <?php echo $hesklang['asss2']; ?></label>     
                    </div>
                </div> 
                <?php
	            }
	            ?>
                <!-- Submit -->
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" /><input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" class="btn btn-default" />
                    </div>   
                </div>       
            </form>

<?php

hesk_cleanSessionVars('iserror');
hesk_cleanSessionVars('isnotice');

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
