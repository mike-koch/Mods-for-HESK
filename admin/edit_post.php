<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.2 from 18th March 2015
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
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
hesk_checkPermission('can_view_tickets');
hesk_checkPermission('can_edit_tickets');

/* Ticket ID */
$trackingID = hesk_cleanID() or die($hesklang['int_error'].': '.$hesklang['no_trackID']);

$is_reply = 0;
$tmpvar = array();

/* Get ticket info */
$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid`='".hesk_dbEscape($trackingID)."' LIMIT 1");
if (hesk_dbNumRows($result) != 1)
{
	hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);

// Demo mode
if ( defined('HESK_DEMO') )
{
	$ticket['email']	= 'hidden@demo.com';
}

/* Is this user allowed to view tickets inside this category? */
hesk_okCategory($ticket['category']);

if ( hesk_isREQUEST('reply') )
{
	$tmpvar['id'] = intval( hesk_REQUEST('reply') ) or die($hesklang['id_not_valid']);

	$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` WHERE `id`='{$tmpvar['id']}' AND `replyto`='".intval($ticket['id'])."' LIMIT 1");
	if (hesk_dbNumRows($result) != 1)
    {
    	hesk_error($hesklang['id_not_valid']);
    }
    $reply = hesk_dbFetchAssoc($result);
    $ticket['message'] = $reply['message'];
    $is_reply = 1;
}

if (isset($_POST['save']))
{
	/* A security check */
	hesk_token_check('POST');

	$hesk_error_buffer = array();

    if ($is_reply)
    {
		$tmpvar['message'] = hesk_input( hesk_POST('message') ) or $hesk_error_buffer[]=$hesklang['enter_message'];

	    if (count($hesk_error_buffer))
	    {
	    	$myerror = '<ul>';
		    foreach ($hesk_error_buffer as $error)
		    {
		        $myerror .= "<li>$error</li>\n";
		    }
	        $myerror .= '</ul>';
	    	hesk_error($myerror);
	    }

		$tmpvar['message'] = hesk_makeURL($tmpvar['message']);
		$tmpvar['message'] = nl2br($tmpvar['message']);

    	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."replies` SET `message`='".hesk_dbEscape($tmpvar['message'])."' WHERE `id`='".intval($tmpvar['id'])."' AND `replyto`='".intval($ticket['id'])."' LIMIT 1");
    }
    else
    {
        $tmpvar['language'] = hesk_POST('customerLanguage');
		$tmpvar['name']    = hesk_input( hesk_POST('name') ) or $hesk_error_buffer[]=$hesklang['enter_your_name'];
		$tmpvar['email']   = hesk_validateEmail( hesk_POST('email'), 'ERR', 0);
		$tmpvar['subject'] = hesk_input( hesk_POST('subject') ) or $hesk_error_buffer[]=$hesklang['enter_ticket_subject'];
		$tmpvar['message'] = hesk_input( hesk_POST('message') ) or $hesk_error_buffer[]=$hesklang['enter_message'];

		// Demo mode
		if ( defined('HESK_DEMO') )
		{
			$tmpvar['email'] = 'hidden@demo.com';
		}

	    if (count($hesk_error_buffer))
	    {
	    	$myerror = '<ul>';
		    foreach ($hesk_error_buffer as $error)
		    {
		        $myerror .= "<li>$error</li>\n";
		    }
	        $myerror .= '</ul>';
	    	hesk_error($myerror);
	    }

		$tmpvar['message'] = hesk_makeURL($tmpvar['message']);
		$tmpvar['message'] = nl2br($tmpvar['message']);

		foreach ($hesk_settings['custom_fields'] as $k=>$v)
		{
			if ($v['use'] && isset($_POST[$k]))
		    {
                if( $v['type'] == 'date' && $_POST[$k] != '')
                {
                    $tmpvar[$k] = strtotime($_POST[$k]);
                } elseif (is_array($_POST[$k]))
	            {
					$tmpvar[$k]='';
					foreach ($_POST[$k] as $myCB)
					{
						$tmpvar[$k] .= ( is_array($myCB) ? '' : hesk_input($myCB) ) . '<br />';
					}
					$tmpvar[$k]=substr($tmpvar[$k],0,-6);
	            }
	            else
	            {
		    		$tmpvar[$k]=hesk_makeURL(nl2br(hesk_input($_POST[$k])));
	            }
			}
		    else
		    {
		    	$tmpvar[$k] = '';
		    }
		}

		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET
		`name`='".hesk_dbEscape($tmpvar['name'])."',
		`email`='".hesk_dbEscape($tmpvar['email'])."',
		`subject`='".hesk_dbEscape($tmpvar['subject'])."',
		`message`='".hesk_dbEscape($tmpvar['message'])."',
		`custom1`='".hesk_dbEscape($tmpvar['custom1'])."',
		`custom2`='".hesk_dbEscape($tmpvar['custom2'])."',
		`custom3`='".hesk_dbEscape($tmpvar['custom3'])."',
		`custom4`='".hesk_dbEscape($tmpvar['custom4'])."',
		`custom5`='".hesk_dbEscape($tmpvar['custom5'])."',
		`custom6`='".hesk_dbEscape($tmpvar['custom6'])."',
		`custom7`='".hesk_dbEscape($tmpvar['custom7'])."',
		`custom8`='".hesk_dbEscape($tmpvar['custom8'])."',
		`custom9`='".hesk_dbEscape($tmpvar['custom9'])."',
		`custom10`='".hesk_dbEscape($tmpvar['custom10'])."',
		`custom11`='".hesk_dbEscape($tmpvar['custom11'])."',
		`custom12`='".hesk_dbEscape($tmpvar['custom12'])."',
		`custom13`='".hesk_dbEscape($tmpvar['custom13'])."',
		`custom14`='".hesk_dbEscape($tmpvar['custom14'])."',
		`custom15`='".hesk_dbEscape($tmpvar['custom15'])."',
		`custom16`='".hesk_dbEscape($tmpvar['custom16'])."',
		`custom17`='".hesk_dbEscape($tmpvar['custom17'])."',
		`custom18`='".hesk_dbEscape($tmpvar['custom18'])."',
		`custom19`='".hesk_dbEscape($tmpvar['custom19'])."',
		`custom20`='".hesk_dbEscape($tmpvar['custom20'])."',
		`language`='".hesk_dbEscape($tmpvar['language'])."'
		WHERE `id`='".intval($ticket['id'])."' LIMIT 1");
    }

    unset($tmpvar);
    hesk_cleanSessionVars('tmpvar');

    hesk_process_messages($hesklang['edt2'],'admin_ticket.php?track='.$trackingID.'&Refresh='.mt_rand(10000,99999),'SUCCESS');
}

$ticket['message'] = hesk_msgToPlain($ticket['message'],0,0);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<ol class="breadcrumb">
  <li><a href="admin_ticket.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000,99999); ?>"><?php echo $hesklang['ticket'].' '.$trackingID; ?></a></li>
  <li class="active"><?php echo $hesklang['edtt']; ?></li>
</ol>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <h3><?php echo $hesklang['edtt']; ?></h3>
        <div class="footerWithBorder blankSpace"></div>

        <form role="form" class="form-horizontal" method="post" action="edit_post.php" name="form1">
            <?php
            /* If it's not a reply edit all the fields */
            if (!$is_reply)
            {
                if ($hesk_settings['can_sel_lang']) {
            ?>
                    <div class="form-group">
                        <label for="customerLanguage" class="col-sm-3 control-label"><?php echo $hesklang['chol']; ?>:</label>
                        <div class="col-sm-9">
                            <select name="customerLanguage" id="customerLanguage" class="form-control">
                                <?php hesk_listLanguages(); ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group">
                    <label for="subject" class="col-sm-3 control-label"><?php echo $hesklang['subject']; ?>:</label>
                    <div class="col-sm-9">
                         <input class="form-control" type="text" name="subject" size="40" maxlength="40" value="<?php echo $ticket['subject'];?>" placeholder="<?php echo htmlspecialchars($hesklang['subject']); ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['name']; ?>:</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="name" size="40" maxlength="30" value="<?php echo $ticket['name'];?>" placeholder="<?php echo htmlspecialchars($hesklang['name']); ?>" />
                    </div>     
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?>:</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="email" size="40" maxlength="1000" value="<?php echo $ticket['email'];?>" placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>" />
                    </div>
                </div>
                <?php
		        foreach ($hesk_settings['custom_fields'] as $k=>$v)
		        {
			        if ($v['use'])
		            {
                        if ($modsForHesk_settings['custom_field_setting'])
                        {
                            $v['name'] = $hesklang[$v['name']];
                        }

				        $k_value  = $ticket[$k];

				        if ($v['type'] == 'checkbox')
	                    {
	            	        $k_value = explode('<br />',$k_value);
	                    }

		                switch ($v['type'])
		                {
		        	        /* Radio box */
		        	        case 'radio':
						        echo '
						        <div class="form-group">
						            <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
		                        <div class="col-sm-9">';

		            	        $options = explode('#HESK#',$v['value']);

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

		                	        echo '<div class="radio"><label><input type="radio" name="'.$k.'" value="'.$option.'" '.$checked.' /> '.$option.'</label></div>';
		                        }

		                        echo '</div>
						        </div>
						        ';
		                    break;

		                    /* Select drop-down box */
		                    case 'select':
						        echo '
						        <div class="form-group">
						        <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
		                        <div class="col-sm-9"><select class="form-control" name="'.$k.'">';

                                // Show "Click to select"?
                                $v['value'] = str_replace('{HESK_SELECT}', '', $v['value'], $num);
                                if ($num)
                                {
                                    echo '<option value="">'.$hesklang['select'].'</option>';
                                }

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

		                        echo '</select></div>
						        </div>
						        ';
		                    break;

		                    /* Checkbox */
		        	        case 'checkbox':
						        echo '
						        <div class="form-group">
						            <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
		                            <div class="col-sm-9">';

		            	        $options = explode('#HESK#',$v['value']);

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

		                	        echo '<div class="checkbox"><label><input type="checkbox" name="'.$k.'[]" value="'.$option.'" '.$checked.' /> '.$option.'</label></div>';
		                        }

		                        echo '</div>
						        </div>
						        ';
		                    break;

		                    /* Large text box */
		                    case 'textarea':
		                        $size = explode('#',$v['value']);
	                            $size[0] = empty($size[0]) ? 5 : intval($size[0]);
	                            $size[1] = empty($size[1]) ? 30 : intval($size[1]);
                                $k_value = hesk_msgToPlain($k_value,0,0);

						        echo '
						        <div class="form-group">
						            <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
						            <div class="col-sm-9">
                                        <textarea class="form-control" name="'.$k.'" rows="'.$size[0].'" placeholder="'.htmlspecialchars($v['name']).'" cols="'.$size[1].'">'.$k_value.'</textarea>
						            </div>
		                        </div>';
		                    break;

                            case 'date':
                                if (strlen($k_value) != 0)
                                {
                                    $v['value'] = $k_value;
                                }
                                echo '
                                <div class="form-group">
                                    <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="datepicker form-control white-readonly" placeholder="'.htmlspecialchars($v['name']).'" id="'.$v['name'].'" name="'.$k.'" size="40"
                                            maxlength="'.$v['maxlen'].'" value="'.date('Y-m-d', $v['value']).'" readonly/>
                                    </div>
                                </div>';
                                break;
                            case 'multiselect':
                                echo '<div class="form-group"><label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
                                <div class="col-sm-9"><select class="form-control" id="'.$v['name'].'" name="'.$k.'" multiple>';
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
                        	        $k_value = hesk_msgToPlain($k_value,0,0);
	                    	        $v['value'] = $k_value;
	                            }
						        echo '
						        <div class="form-group">
						            <label for="'.$v['name'].'" class="col-sm-3 control-label">'.$v['name'].': </label>
						            <div class="col-sm-9">
                                        <input type="text" class="form-control" placeholder="'.htmlspecialchars($v['name']).'" name="'.$k.'" size="40" maxlength="'.$v['maxlen'].'" value="'.$v['value'].'" />
                                    </div>
						        </div>
						        ';
		                }
		            }
		        }
                ?>

            <?php } ?>
            <div class="form-group">
                <label for="message" class="col-sm-3 control-label"><?php echo $hesklang['message']; ?>:</label>
                <div class="col-sm-9">
                    <textarea class="form-control" name="message" rows="12" placeholder="<?php echo htmlspecialchars($hesklang['message']); ?>" cols="60"><?php echo $ticket['message']; ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <input type="hidden" name="save" value="1" /><input type="hidden" name="track" value="<?php echo $trackingID; ?>" />
                <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                <?php
	            if ($is_reply)
	            {
		            ?>
		            <input type="hidden" name="reply" value="<?php echo $tmpvar['id']; ?>" />
		            <?php
	            }
	            ?>
            </div>
            <div class="form-group" style="text-align: center">
                <input type="submit" value="<?php echo $hesklang['save_changes']; ?>" class="btn btn-default" />
                <a class="btn btn-default" href="javascript:history.go(-1)"><?php echo $hesklang['back']; ?></a>
            </div>
        </form>
    </div>     
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
