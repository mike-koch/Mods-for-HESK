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
define('HESK_PATH','./');

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// What should we do?
$action = hesk_REQUEST('a');

switch ($action)
{
	case 'add':
		hesk_session_start();
        print_add_ticket();
	    break;

	case 'forgot_tid':
		hesk_session_start();
        forgot_tid();
	    break;

	default:
		print_start();
}

// Print footer
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

/*** START FUNCTIONS ***/

function print_add_ticket()
{
	global $hesk_settings, $hesklang, $modsForHesk_settings;

	// Auto-focus first empty or error field
	define('AUTOFOCUS', true);

	// Varibles for coloring the fields in case of errors
	if ( ! isset($_SESSION['iserror']))
	{
		$_SESSION['iserror'] = array();
	}

	if ( ! isset($_SESSION['isnotice']))
	{
		$_SESSION['isnotice'] = array();
	}

    if ( ! isset($_SESSION['c_category']))
    {
    	$_SESSION['c_category'] = 0;
    }

	hesk_cleanSessionVars('already_submitted');

	// Print header
	$hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['submit_ticket'];
	require_once(HESK_PATH . 'inc/header.inc.php');
	?>

<ol class="breadcrumb">
  <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
  <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
  <li class="active"><?php echo $hesklang['sub_support']; ?></li>
</ol>	

<!-- START MAIN LAYOUT -->
    <div class="row">
        <div align="left" class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['quick_help']; ?></div>
                <div class="panel-body">
                    <p><?php echo $hesklang['quick_help_submit_ticket']; ?></p>
                </div>
            </div>
	    </div>
        <div class="col-md-8">
            <?php
                // This will handle error, success and notice messages
                hesk_handle_messages();
            ?>
            <!-- START FORM -->
            <div class="form">
	            <h2><?php hesk_showTopBar($hesklang['submit_ticket']); ?></h2>
	            <small><?php echo $hesklang['use_form_below']; ?></small>
                <div class="blankSpace"></div>
	
            <div align="left" class="h3"><?php echo $hesklang['add_ticket_general_information']; ?></div>
            <div class="footerWithBorder"></div>
            <div class="blankSpace"></div>
	            <form class="form-horizontal" role="form" method="post" action="submit_ticket.php?submit=1" name="form1" enctype="multipart/form-data">
		            <!-- Contact info -->
		            <div class="form-group">
			            <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['name']; ?>: <font class="important">*</font></label>
			            <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" size="40" maxlength="30" value="<?php if (isset($_SESSION['c_name'])) {echo stripslashes(hesk_input($_SESSION['c_name']));} ?>" <?php if (in_array('name',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> placeholder="<?php echo $hesklang['name']; ?>" />
		                </div>
                    </div>
		            <div class="form-group">
			            <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?>: <font class="important">*</font></label>
			            <div class="col-sm-9"> 
                            <input type="text" class="form-control" id="email" name="email" size="40" maxlength="255" value="<?php if (isset($_SESSION['c_email'])) {echo stripslashes(hesk_input($_SESSION['c_email']));} ?>" <?php if (in_array('email',$_SESSION['iserror'])) {echo ' class="isError" ';} elseif (in_array('email',$_SESSION['isnotice'])) {echo ' class="isNotice" ';} ?> <?php if($hesk_settings['detect_typos']) { echo ' onblur="Javascript:hesk_suggestEmail(0)"'; } ?> placeholder="<?php echo $hesklang['email']; ?>" />
		                </div>
                    </div>
                    <?php
                    if ($hesk_settings['confirm_email'])
                    {
                    ?>
		            <div class="form-group">
                        <label for="email2" class="col-sm-3 control-label"><?php echo $hesklang['confemail']; ?>: <font class="important">*</font></label>
                        <div class="col-sm-9">
                            <input type="text" id="email2" class="form-control" name="email2" size="40" maxlength="255" value="<?php if (isset($_SESSION['c_email2'])) {echo stripslashes(hesk_input($_SESSION['c_email2']));} ?>" <?php if (in_array('email2',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> placeholder="<?php echo $hesklang['confemail']; ?>" />
                        </div>
                    </div>
                    <?php
                    } ?>
                    <div id="email_suggestions"></div>
                    <!-- Department and priority -->
                    <?php
                        $is_table = 0;

	                    hesk_load_database_functions();

                        // Get categories
	                    hesk_dbConnect();
	                    $res = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `type`='0' ORDER BY `cat_order` ASC");

                        if (hesk_dbNumRows($res) == 1)
                        {
    	                    // Only 1 public category, no need for a select box
    	                    $row = hesk_dbFetchAssoc($res);
		                    echo '<input type="hidden" name="category" value="'.$row['id'].'" />';
                        }
                        elseif (hesk_dbNumRows($res) < 1)
                        {
    	                    // No public categories, set it to default one
		                    echo '<input type="hidden" name="category" value="1" />';
                        }
                        else
                        {
		                    // Is the category ID preselected?
		                    if ( ! empty($_GET['catid']) )
		                    {
			                    $_SESSION['c_category'] = intval( hesk_GET('catid') );
		                    }

		                    // List available categories
		                    $is_table = 1;
		            ?>
                    <div class="form-group">
                        <label for="category" class="col-sm-3 control-label"><?php echo $hesklang['category']; ?>: <font class="important">*</font></label>
                        <div class="col-sm-9">
                            <select name="category" id="category" class="form-control" <?php if (in_array('category',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> ><?php
		                        while ($row = hesk_dbFetchAssoc($res))
		                        {
			                        echo '<option value="' . $row['id'] . '"' . (($_SESSION['c_category'] == $row['id']) ? ' selected="selected"' : '') . '>' . $row['name'] . '</option>';
		                        } ?>
                            </select>
                        </div>
                    </div>
                    <?php
                    }

	                /* Can customer assign urgency? */
	                if ($hesk_settings['cust_urgency'])
	                {		       
		            ?>
                    <div class="form-group">
                        <label for="priority" class="col-sm-3 control-label"><?php echo $hesklang['priority']; ?>: <font class="important">*</font></label>
                        <div class="col-sm-9">   
                            <select id="priority" class="form-control" name="priority" <?php if (in_array('priority',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> >
		                        <option value="3" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==3) {echo 'selected="selected"';} ?>><?php echo $hesklang['low']; ?></option>
		                        <option value="2" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==2) {echo 'selected="selected"';} ?>><?php echo $hesklang['medium']; ?></option>
		                        <option value="1" <?php if(isset($_SESSION['c_priority']) && $_SESSION['c_priority']==1) {echo 'selected="selected"';} ?>><?php echo $hesklang['high']; ?></option>
		                    </select>
                        </div>
                    </div>
                    <?php
                    }
                    ?>

	            <!-- START CUSTOM BEFORE -->
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

	    	            $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';

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

	            	            $options = explode('#HESK#',$v['value']);

	                            foreach ($options as $option)
	                            {

		            	            if (strlen($k_value) == 0 || $k_value == $option)
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
					            <div class="col-sm-9"><textarea class="form-control" id="'.$v['name'].'" name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'" '.$cls.'>'.$k_value.'</textarea></div>
                                </div>';
	                        break;

                            case 'multiselect':
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="'.$v['name'].'[]" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'[]" '.$cls.' multiple>';

                                $options = explode('#HESK#',$v['value']);

                                foreach ($options as $option)
                                {

                                    if (strlen($k_value) == 0 || $k_value == $option)
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

	                        /* Default text input */
	                        default:
                	            if (strlen($k_value) != 0)
                                {
                    	            $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="'.$v['name'].'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" '.$cls.' /></div>
                                </div>';
	                    }
	                }
	            }
	
	            ?>
	            <!-- END CUSTOM BEFORE -->

	           <div class="blankSpace"></div>   
               <div align="left" class="h3"><?php echo $hesklang['add_ticket_your_message']; ?></div>
               <div class="footerWithBorder"></div>
               <div class="blankSpace"></div>    
                <!-- ticket info -->
	            <div class="form-group">
                    <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject']; ?>: <font class="important">*</font></label>
	                <div class="col-sm-9">
                        <input type="text" id="subject" class="form-control" name="subject" size="40" maxlength="40" value="<?php if (isset($_SESSION['c_subject'])) {echo stripslashes(hesk_input($_SESSION['c_subject']));} ?>" <?php if (in_array('subject',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> placeholder="<?php echo $hesklang['subject']; ?>"/>
	                </div>
                </div>
                <div class="form-group">
                    
	                <div class="col-sm-12">
                        <textarea placeholder="<?php echo $hesklang['message']; ?>" name="message" id="message" class="form-control" rows="12" cols="60" <?php if (in_array('message',$_SESSION['iserror'])) {echo ' class="isError" ';} ?> ><?php if (isset($_SESSION['c_message'])) {echo stripslashes(hesk_input($_SESSION['c_message']));} ?></textarea>
                    </div>
                </div>

		            <!-- START KNOWLEDGEBASE SUGGEST -->
		            <?php
		            if ($hesk_settings['kb_enable'] && $hesk_settings['kb_recommendanswers'])
		            {
			            ?>
			            <div id="kb_suggestions" style="display:none">
                        <br />&nbsp;<br />
			            <img src="img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo $hesklang['lkbs']; ?></i>
			            </div>

			            <script language="Javascript" type="text/javascript"><!--
			            hesk_suggestKB();
			            //-->
			            </script>
			            <?php
		            }
		            ?>
		            <!-- END KNOWLEDGEBASE SUGGEST -->

	            <!-- START CUSTOM AFTER -->
	            <?php

	            /* custom fields AFTER comments */

	            foreach ($hesk_settings['custom_fields'] as $k=>$v)
	            {

		            if ($v['use'] && $v['place'])
	                {
                        if ($modsForHesk_settings['custom_field_setting'])
                        {
                            $v['name'] = $hesklang[$v['name']];
                        }
                        
	    	            $v['req'] = $v['req'] ? '<font class="important">*</font>' : '';

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

	            	            $options = explode('#HESK#',$v['value']);

	                            foreach ($options as $option)
	                            {

		            	            if (strlen($k_value) == 0 || $k_value == $option)
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
					            <div class="col-sm-9"><textarea class="form-control" id="'.$v['name'].'" name="'.$k.'" rows="'.$size[0].'" cols="'.$size[1].'" '.$cls.'>'.$k_value.'</textarea></div>
                                </div>';
	                        break;

                            case 'multiselect':
                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

                                echo '<div class="form-group"><label for="'.$v['name'].'[]" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'[]" '.$cls.' multiple>';

                                $options = explode('#HESK#',$v['value']);

                                foreach ($options as $option)
                                {

                                    if (strlen($k_value) == 0 || $k_value == $option)
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

	                        /* Default text input */
	                        default:
                	            if (strlen($k_value) != 0)
                                {
                    	            $v['value'] = $k_value;
                                }

                                $cls = in_array($k,$_SESSION['iserror']) ? ' class="isError" ' : '';

					            echo '<div class="form-group">
                                <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': '.$v['req'].'</label>
					            <div class="col-sm-9"><input type="text" class="form-control" id="'.$v['name'].'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" '.$cls.' /></div>
                                </div>';
	                    }
	                }
	            }
	
	            ?>
	            <!-- END CUSTOM AFTER -->

	            <?php
	            /* attachments */
	            if ($hesk_settings['attachments']['use'])
                {
	            ?>
                <div class="form-group">
	                <label for="attachments" class="col-sm-3 control-label"><?php echo $hesklang['attachments']; ?>:</label>
	                <div align="left" class="col-sm-9">
                        <?php
	                    for ($i=1;$i<=$hesk_settings['attachments']['max_number'];$i++)
                        {
    	                    $cls = ($i == 1 && in_array('attachments',$_SESSION['iserror'])) ? ' class="isError" ' : '';
		                    echo '<input type="file" name="attachment['.$i.']" size="50" '.$cls.' /><br />';
	                       }
	                    ?>
	                    <a href="file_limits.php" target="_blank" onclick="Javascript:hesk_window('file_limits.php',250,500);return false;"><?php echo $hesklang['ful']; ?></a>
                    </div>
                </div>
	            <?php
	            }

	            if ($hesk_settings['question_use'] || $hesk_settings['secimg_use'])
                {
		            ?>

                    <!-- Security checks -->
		            <?php
		            if ($hesk_settings['question_use'])
	                {
			            ?>
			            <div class="form-group">
			            <label for="question"><?php echo $hesklang['verify_q']; ?> <font class="important">*</font></label>
			
                        <?php
        	            $value = '';
        	            if (isset($_SESSION['c_question']))
                        {
	        	            $value = stripslashes(hesk_input($_SESSION['c_question']));
                        }
                        $cls = in_array('question',$_SESSION['iserror']) ? ' class="isError" ' : '';
		                echo $hesk_settings['question_ask'].'<br /><input class="form-control" id="question" type="text" name="question" size="20" value="'.$value.'" '.$cls.' />';
                        ?>
                        </div>
                    <?php
		            }

		            if ($hesk_settings['secimg_use'])
	                {
			            ?>
			            <div class="form-group">
			                <label for="secimage" class="col-sm-3 control-label"><?php echo $hesklang['verify_i']; ?> <font class="important">*</font></label>
			                <?php
			                // SPAM prevention verified for this session
			                if (isset($_SESSION['img_verified']))
			                {
				                echo '<img src="'.HESK_PATH.'img/success.png" width="16" height="16" border="0" alt="" style="vertical-align:text-bottom" /> '.$hesklang['vrfy'];
			                }
			                // Not verified yet, should we use Recaptcha?
			                elseif ($hesk_settings['recaptcha_use'])
			                {
				                ?>
				                <script type="text/javascript">
				                var RecaptchaOptions = {
				                theme : '<?php echo ( isset($_SESSION['iserror']) && in_array('mysecnum',$_SESSION['iserror']) ) ? 'red' : 'white'; ?>',
				                custom_translations : {
					                visual_challenge : "<?php echo hesk_slashJS($hesklang['visual_challenge']); ?>",
					                audio_challenge : "<?php echo hesk_slashJS($hesklang['audio_challenge']); ?>",
					                refresh_btn : "<?php echo hesk_slashJS($hesklang['refresh_btn']); ?>",
					                instructions_visual : "<?php echo hesk_slashJS($hesklang['instructions_visual']); ?>",
					                instructions_context : "<?php echo hesk_slashJS($hesklang['instructions_context']); ?>",
					                instructions_audio : "<?php echo hesk_slashJS($hesklang['instructions_audio']); ?>",
					                help_btn : "<?php echo hesk_slashJS($hesklang['help_btn']); ?>",
					                play_again : "<?php echo hesk_slashJS($hesklang['play_again']); ?>",
					                cant_hear_this : "<?php echo hesk_slashJS($hesklang['cant_hear_this']); ?>",
					                incorrect_try_again : "<?php echo hesk_slashJS($hesklang['incorrect_try_again']); ?>",
					                image_alt_text : "<?php echo hesk_slashJS($hesklang['image_alt_text']); ?>"
				                     }
				                };
				                </script>
				                <?php
				                require(HESK_PATH . 'inc/recaptcha/recaptchalib.php');
				                echo recaptcha_get_html($hesk_settings['recaptcha_public_key'], null, $hesk_settings['recaptcha_ssl']);
			                }
			                // At least use some basic PHP generated image (better than nothing)
			                else
			                {
				                $cls = in_array('mysecnum',$_SESSION['iserror']) ? ' class="isError" ' : '';

				                echo '<div align="left" class="col-sm-9">';
                               
                                echo $hesklang['sec_enter'].'<br />&nbsp;<br /><img src="print_sec_img.php?'.rand(10000,99999).'" width="150" height="40" alt="'.$hesklang['sec_img'].'" title="'.$hesklang['sec_img'].'" border="1" name="secimg" style="vertical-align:text-bottom" /> '.
				                '<a href="javascript:void(0)" onclick="javascript:document.form1.secimg.src=\'print_sec_img.php?\'+ ( Math.floor((90000)*Math.random()) + 10000);"><img src="img/reload.png" height="24" width="24" alt="'.$hesklang['reload'].'" title="'.$hesklang['reload'].'" border="0" style="vertical-align:text-bottom" /></a>'.
				                '<br />&nbsp;<br /><input type="text" name="mysecnum" size="20" maxlength="5" '.$cls.' />';
			                }
		                echo '</div></div>';
                    }
		            ?>

                <?php
                }
	            ?>

	            <!-- Submit -->
                <?php
                if ($hesk_settings['submit_notice'])
                {
	                ?>

	                <b><?php echo $hesklang['before_submit']; ?></b>
	                <ul>
	                <li><?php echo $hesklang['all_info_in']; ?>.</li>
		            <li><?php echo $hesklang['all_error_free']; ?>.</li>
	                </ul>


		            <b><?php echo $hesklang['we_have']; ?>:</b>
	                <ul>
	                <li><?php echo hesk_htmlspecialchars($_SERVER['REMOTE_ADDR']).' '.$hesklang['recorded_ip']; ?></li>
		            <li><?php echo $hesklang['recorded_time']; ?></li>
		            </ul>

		            <p align="center"><input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
	                <input type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" class="orangebutton"  onmouseover="hesk_btn(this,'orangebuttonover');" onmouseout="hesk_btn(this,'orangebutton');" /></p>

	                <?php
                } // End IF submit_notice
                else
                {
	                ?>
                    &nbsp;<br />&nbsp;<br />
		            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
	                <input class="btn btn-default" type="submit" value="<?php echo $hesklang['sub_ticket']; ?>" /><br />
	                &nbsp;<br />&nbsp;

	                <?php
                } // End ELSE submit_notice
                ?>
                </form>
            </div>
                 <!-- END FORM -->


<?php

hesk_cleanSessionVars('iserror');
hesk_cleanSessionVars('isnotice');

} // End print_add_ticket()


function print_start()
{
	global $hesk_settings, $hesklang;

	if ($hesk_settings['kb_enable'])
	{
        require(HESK_PATH . 'inc/knowledgebase_functions.inc.php');
		hesk_load_database_functions();
	    hesk_dbConnect();
	}

	/* Print header */
	require_once(HESK_PATH . 'inc/header.inc.php');

	?>

<ol class="breadcrumb">
  <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
  <li class="active"><?php echo $hesk_settings['hesk_title']; ?></li>
</ol>
	<div class="row">
		<div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $hesklang['view_ticket']; ?></div>
                <div class="panel-body">
                    <form role="form" class="viewTicketSidebar" action="ticket.php" method="get" name="form2">
                        <div class="form-group">
                            <br/>
                            <label for="ticketID"><?php echo $hesklang['ticket_trackID']; ?>:</label>
                            <input type="text" class="form-control" name="track" id="ticketID" maxlength="20" size="35" value="" placeholder="<?php echo $hesklang['ticket_trackID']; ?>">
                        </div>
                        <?php
                        $tmp = '';
                        if ($hesk_settings['email_view_ticket'])
                        {
                            $tmp = 'document.form1.email.value=document.form2.e.value;';
                        ?>
                        <div class="form-group">
                            <label for="emailAddress"><?php echo $hesklang['email']; ?>:</label>
                            <input type="text" class="form-control" name="e" id="emailAddress" size="35" value="<?php echo $my_email; ?>" placeholder="<?php echo $hesklang['email']; ?>"/>
                        </div>
                        <div class="checkbox">
                            <input type="checkbox" name="r" value="Y" <?php echo $do_remember; ?> /> <?php echo $hesklang['rem_email']; ?></label>
                        </div>
                        <?php
                        }
                        ?>
                        <input type="submit" value="<?php echo $hesklang['view_ticket']; ?>" class="btn btn-default" /><input type="hidden" name="Refresh" value="<?php echo rand(10000,99999); ?>"><input type="hidden" name="f" value="1">
                    </form>
                </div>
            </div>
		</div>
		<div class="col-md-8">
				<?php
				// Print small search box
				if ($hesk_settings['kb_enable'])
				{
					hesk_kbSearchSmall();
					hesk_kbSearchLarge();
				}
				else
				{
					echo '&nbsp;';
				}
				?>
            <div class="blockRow">
                <a href="index.php?a=add">
                    <div class="block">
                        <div class="upper">
                            <img src="img/newTicket.png" alt="<?php echo $hesklang['sub_support']; ?>" />
                        </div>
                        <div class="lower">
                            <p><?php echo $hesklang['sub_support']; ?></p>
                        </div>
                    </div>
                </a>
                <a href="ticket.php">
                    <div class="block">
                        <div class="upper">
                            <img src="img/viewTicket.png" alt="<?php echo $hesklang['view_existing']; ?>" />
                        </div>
                        <div class="lower">
                            <p><?php echo $hesklang['view_existing']; ?></p>
                        </div>
                    </div>
                </a>
                <?php
                if ($hesk_settings['kb_enable'])
                {
                ?>
                <a href="knowledgebase.php">
                    <div class="block">
                        <div class="upper">
                            <img src="img/knowledgebase.png" alt="<?php echo $hesklang['kb_text']; ?>" />
                        </div>
                        <div class="lower">
                            <p><?php echo $hesklang['kb_text']; ?></p>
                        </div>
                    </div>
                </a>
                <?php
                }
                include('custom/custom-blocks.php');
                ?>
            </div>
		</div>
	</div>
	<div class="blankSpace"></div>
	<div class="footerWithBorder"></div>
	<div class="blankSpace"></div>
</div>

<?php
	// Show a link to admin panel?
	if ($hesk_settings['alink'])
	{
		?>
		<p style="text-align:center"><a href="<?php echo $hesk_settings['admin_dir']; ?>/" ><?php echo $hesklang['ap']; ?></a></p>
		<?php
	}

} // End print_start()


function forgot_tid()
{
	global $hesk_settings, $hesklang;

	require(HESK_PATH . 'inc/email_functions.inc.php');

	$email = hesk_validateEmail( hesk_POST('email'), 'ERR' ,0) or hesk_process_messages($hesklang['enter_valid_email'],'ticket.php?remind=1');

	/* Prepare ticket statuses */
	$my_status = array(
	    0 => $hesklang['open'],
	    1 => $hesklang['wait_staff_reply'],
	    2 => $hesklang['wait_cust_reply'],
	    3 => $hesklang['closed'],
	    4 => $hesklang['in_progress'],
	    5 => $hesklang['on_hold'],
	);

	/* Get ticket(s) from database */
	hesk_load_database_functions();
	hesk_dbConnect();

    // Get tickets from the database
	$res = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` FORCE KEY (`statuses`) WHERE ' . ($hesk_settings['open_only'] ? "`status` IN ('0','1','2','4','5') AND " : '') . ' ' . hesk_dbFormatEmail($email) . ' ORDER BY `status` ASC, `lastchange` DESC ');

	$num = hesk_dbNumRows($res);
	if ($num < 1)
	{
		if ($hesk_settings['open_only'])
        {
        	hesk_process_messages($hesklang['noopen'],'ticket.php?remind=1&e='.$email);
        }
        else
        {
        	hesk_process_messages($hesklang['tid_not_found'],'ticket.php?remind=1&e='.$email);
        }
	}

	$tid_list = '';
	$name = '';

    $email_param = $hesk_settings['email_view_ticket'] ? '&e='.rawurlencode($email) : '';

	while ($my_ticket=hesk_dbFetchAssoc($res))
	{
		$name = $name ? $name : hesk_msgToPlain($my_ticket['name'], 1, 0);
$tid_list .= "
$hesklang[trackID]: "	. $my_ticket['trackid'] . "
$hesklang[subject]: "	. hesk_msgToPlain($my_ticket['subject'], 1, 0) . "
$hesklang[status]: "	. $my_status[$my_ticket['status']] . "
$hesk_settings[hesk_url]/ticket.php?track={$my_ticket['trackid']}{$email_param}
";
	}

	/* Get e-mail message for customer */
	$msg = hesk_getEmailMessage('forgot_ticket_id','',0,0,1);
	$msg = str_replace('%%NAME%%',			$name,												$msg);
	$msg = str_replace('%%NUM%%',			$num,												$msg);
	$msg = str_replace('%%LIST_TICKETS%%',	$tid_list,											$msg);
	$msg = str_replace('%%SITE_TITLE%%',	hesk_msgToPlain($hesk_settings['site_title'], 1),	$msg);
	$msg = str_replace('%%SITE_URL%%',		$hesk_settings['site_url'],							$msg);

    $subject = hesk_getEmailSubject('forgot_ticket_id');

	/* Send e-mail */
	hesk_mail($email, $subject, $msg);

	/* Show success message */
	$tmp  = '<b>'.$hesklang['tid_sent'].'!</b>';
	$tmp .= '<br />&nbsp;<br />'.$hesklang['tid_sent2'].'.';
	$tmp .= '<br />&nbsp;<br />'.$hesklang['check_spambox'];
	hesk_process_messages($tmp,'ticket.php?e='.$email,'SUCCESS');
	exit();

	/* Print header */
	$hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['tid_sent'];
	require_once(HESK_PATH . 'inc/header.inc.php');
	?>
            
<ol class="breadcrumb">
  <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
  <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
  <li class="active"><?php echo $hesklang['tid_sent']; ?></li>
</ol>
<tr>
<td>

	<?php

    } // End forgot_tid()

?>
