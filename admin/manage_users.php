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

/* Check permissions for this feature */
hesk_checkPermission('can_man_users');

/* Possible user features */
$hesk_settings['features'] = array(
'can_view_tickets',		/* User can read tickets */
'can_reply_tickets',	/* User can reply to tickets */
'can_del_tickets',		/* User can delete tickets */
'can_edit_tickets',		/* User can edit tickets */
'can_merge_tickets',	/* User can merge tickets */
'can_del_notes',		/* User can delete ticket notes posted by other staff members */
'can_change_cat',		/* User can move ticke to a new category/department */
'can_man_kb',			/* User can manage knowledgebase articles and categories */
'can_man_users',		/* User can create and edit staff accounts */
'can_man_cat',			/* User can manage categories/departments */
'can_man_canned',		/* User can manage canned responses */
'can_man_settings',		/* User can manage help desk settings */
'can_add_archive',		/* User can mark tickets as "Tagged" */
'can_assign_self',		/* User can assign tickets to himself/herself */
'can_assign_others',	/* User can assign tickets to other staff members */
'can_view_unassigned',	/* User can view unassigned tickets */
'can_view_ass_others',	/* User can view tickets that are assigned to other staff */
'can_run_reports',		/* User can run reports and see statistics (only allowed categories and self) */
'can_run_reports_full', /* User can run reports and see statistics (unrestricted) */
'can_export',			/* User can export own tickets to Excel */
'can_view_online',		/* User can view what staff members are currently online */
);

/* Set default values */
$default_userdata = array(
	'name' => '',
	'email' => '',
	'user' => '',
	'signature' => '',
	'isadmin' => 1,
	'categories' => array('1'),
	'features' => array('can_view_tickets','can_reply_tickets','can_change_cat','can_assign_self','can_view_unassigned','can_view_online'),
	'signature' => '',
	'cleanpass' => '',
);

/* A list of all categories */
$hesk_settings['categories'] = array();
$res = hesk_dbQuery('SELECT `id`,`name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `cat_order` ASC');
while ($row=hesk_dbFetchAssoc($res))
{
	if ( hesk_okCategory($row['id'], 0) )
    {
		$hesk_settings['categories'][$row['id']] = $row['name'];
    }
}

/* Non-admin users may not create users with more permissions than they have */
if ( ! $_SESSION['isadmin'])
{
	/* Can't create admin users */
    $_POST['isadmin'] = 0;

    /* Can only add features he/she has access to */
	$hesk_settings['features'] = array_intersect( explode(',', $_SESSION['heskprivileges']) , $hesk_settings['features']);

	/* Can user modify auto-assign setting? */
    if ($hesk_settings['autoassign'] && ( ! hesk_checkPermission('can_assign_self', 0) || ! hesk_checkPermission('can_assign_others', 0) ) )
    {
    	$hesk_settings['autoassign'] = 0;
    }
}

/* Use any set values, default otherwise */
foreach ($default_userdata as $k => $v)
{
	if ( ! isset($_SESSION['userdata'][$k]) )
    {
    	$_SESSION['userdata'][$k] = $v;
    }
}

$_SESSION['userdata'] = hesk_stripArray($_SESSION['userdata']);

/* What should we do? */
if ( $action = hesk_REQUEST('a') )
{
	if ($action == 'reset_form')
	{
		$_SESSION['edit_userdata'] = TRUE;
		header('Location: ./manage_users.php');
	}
	elseif ($action == 'edit')       {edit_user();}
	elseif ( defined('HESK_DEMO') )  {hesk_process_messages($hesklang['ddemo'], 'manage_users.php', 'NOTICE');}
	elseif ($action == 'new')        {new_user();}
	elseif ($action == 'save')       {update_user();}
	elseif ($action == 'remove')     {remove();}
	elseif ($action == 'autoassign') {toggle_autoassign();}
    else 							 {hesk_error($hesklang['invalid_action']);}
}

else
{

/* If one came from the Edit page make sure we reset user values */

if (isset($_SESSION['save_userdata']))
{
	$_SESSION['userdata'] = $default_userdata;
    unset($_SESSION['save_userdata']);
}
if (isset($_SESSION['edit_userdata']))
{
	$_SESSION['userdata'] = $default_userdata;
    unset($_SESSION['edit_userdata']);
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="enclosingDashboard">
<div style="margin-top: 20px" class="row">
    <div align="left" class="col-md-4">
	    <div class="moreToLeft">
		    <ul class="nav nav-tabs">
			    <li class="active"><a href="#" onclick="return false;"><?php echo $hesklang['add_user']; ?></a></li>
		    </ul>
		    <div class="summaryList">
                <div class="viewTicketSidebar">
			        <h4><?php echo $hesklang['add_user']; ?></h4>
                    <h6><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></h6>
                    <div class="footerWithBorder blankSpace"></div>

                    <form class="form-horizontal" name="form1" action="manage_users.php" method="post">
                        <div class="form-group">
                            <label for="name" class="col-sm-5 control-label"><?php echo $hesklang['real_name']; ?>: <font class="important">*</font></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="name" size="40" maxlength="50" value="<?php echo $_SESSION['userdata']['name']; ?>" placeholder="<?php echo $hesklang['real_name']; ?>" />
                            </div>     
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-5 control-label"><?php echo $hesklang['email']; ?>: <font class="important">*</font></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="email" size="40" maxlength="255" placeholder="<?php echo $hesklang['email']; ?>" value="<?php echo $_SESSION['userdata']['email']; ?>" />    
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user" class="col-sm-5 control-label"><?php echo $hesklang['username']; ?>: <font class="important">*</font></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="user" size="40" maxlength="20" value="<?php echo $_SESSION['userdata']['user']; ?>" placeholder="<?php echo $hesklang['username']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pass" class="col-sm-5 control-label"><?php echo $hesklang['pass']; ?>: <font class="important">*</font></label>
                            <div class="col-sm-7">
                                <input type="password" class="form-control" name="newpass" autocomplete="off" size="40" placeholder="<?php echo $hesklang['pass']; ?>" value="<?php echo $_SESSION['userdata']['cleanpass']; ?>" onkeyup="javascript:hesk_checkPassword(this.value)" />     
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmPass" class="col-sm-5 control-label" style="font-size: .9em"><?php echo $hesklang['confirm_pass']; ?>: <font class="important">*</font></label>     
                            <div class="col-sm-7">
                                <input type="password" name="newpass2" class="form-control" autocomplete="off" placeholder="<?php echo $hesklang['confirm_pass']; ?>" size="40" value="<?php echo $_SESSION['userdata']['cleanpass']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pwStrength" class="col-sm-5 control-label" style="font-size: .9em"><?php echo $hesklang['pwdst']; ?>:</label>
                            <div class="col-sm-7">
                                <div style="border: 1px solid gray; width: 100px;">
                                    <div id="progressBar"
                                        style="font-size: 1px; height: 22px; width: 0px; border: 1px solid white;">
                                    </div>
                                </div> 
                            </div>     
                        </div>
                        <div class="form-group">
                            <label for="administrator" class="col-sm-5 control-label"><?php echo $hesklang['administrator']; ?>: <font class="important">*</font></label>
                            <div class="col-sm-7">
                                <?php
                                /* Only administrators can create new administrator accounts */
                                if ($_SESSION['isadmin'])
                                {
	                                ?>
                                    <div class="radio"><label><input type="radio" name="isadmin" value="1" onchange="Javascript:hesk_toggleLayerDisplay('options')" <?php if ($_SESSION['userdata']['isadmin']) echo 'checked="checked"'; ?> /> <?php echo $hesklang['yes'].' '.$hesklang['admin_can']; ?></label></div>
	                                <div class="radio"><label><input type="radio" name="isadmin" value="0" onchange="Javascript:hesk_toggleLayerDisplay('options')" <?php if (!$_SESSION['userdata']['isadmin']) echo 'checked="checked"'; ?> /> <?php echo $hesklang['no'].' '.$hesklang['staff_can']; ?></label></div>
                                    <?php
                                }
                                else
                                {
	                                echo $hesklang['no'].' '.$hesklang['staff_can'];
                                }
                                ?>
                            </div>
                        </div>
                        <div id="options" style="display: <?php echo ($_SESSION['isadmin'] && $_SESSION['userdata']['isadmin']) ? 'none' : 'block'; ?>">
                            <div class="form-group">
                                <label for="categories" class="col-sm-5 control-label"><?php echo $hesklang['allowed_cat']; ?>: <font class="important">*</font></label>
                                <div class="col-sm-7">
                                     <?php
                                        foreach ($hesk_settings['categories'] as $catid => $catname)
                                        {
        	                                echo '<div class="checkbox"><label><input type="checkbox" name="categories[]" value="' . $catid . '" ';
                                            if ( in_array($catid,$_SESSION['userdata']['categories']) )
                                            {
            	                                echo ' checked="checked" ';
                                            }
                                            echo ' />' . $catname . '</label></div> ';
                                        }
		                            ?>
                                </div>     
                            </div>
                            <div class="form-group">
                                <label for="permissions" class="col-sm-5 control-label"><?php echo $hesklang['allow_feat']; ?>: <font class="important">*</font></label>
                                <div class="col-sm-7">
                                     <?php
		                                foreach ($hesk_settings['features'] as $k)
                                        {
        	                                echo '<div class="checkbox"><label><input type="checkbox" name="features[]" value="' . $k . '" ';
                                            if (in_array($k,$_SESSION['userdata']['features']))
                                            {
            	                                echo ' checked="checked" ';
                                            }
                                            echo ' />' . $hesklang[$k] . '</label></div> ';
                                        }
                                    ?>    
                                </div>     
                            </div> 
                        </div>
                        <?php
                        if ($hesk_settings['autoassign'])
                        {
	                        ?>
                        <div class="form-group">
                            <label for="auto-assign" class="col-sm-5 control-label"><?php echo $hesklang['opt']; ?>:</label>
                            <div class="col-sm-7">
                                <div class="checkbox">
                                    <label><input type="checkbox" name="autoassign" value="Y" <?php if ( ! isset($_SESSION['userdata']['autoassign']) || $_SESSION['userdata']['autoassign'] == 1 ) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['user_aa']; ?></label>    
                                </div>
                            </div>
                        </div>
                        <?php  } ?>
                        <div class="form-group">
                            <label for="signature" class="col-sm-5 control-label"><?php echo $hesklang['signature_max']; ?>:</label>
                            
                            <div class="col-sm-7">
                                <textarea class="form-control" name="signature" rows="6" placeholder="<?php echo $hesklang['sig']; ?>" cols="40"><?php echo $_SESSION['userdata']['signature']; ?></textarea>
                                <?php echo $hesklang['sign_extra']; ?>
                            </div>     
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12" style="text-align: right">
                                <input type="hidden" name="a" value="new" />
                                <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                                <input type="submit" class="btn btn-default" value="<?php echo $hesklang['create_user']; ?>" />
                                <a class="btn btn-default" href="manage_users.php?a=reset_form"><?php echo $hesklang['refi']; ?></a>     
                            </div>     
                        </div>
                                  
                    </form>
                </div>				
		    </div>
	    </div>
	</div>
    <div class="col-md-7">
         <script language="Javascript" type="text/javascript"><!--
            function confirm_delete()
            {
            if (confirm('<?php echo addslashes($hesklang['sure_remove_user']); ?>')) {return true;}
            else {return false;}
            }
            //-->
        </script>

        <?php
        /* This will handle error, success and notice messages */
        hesk_handle_messages();
        ?>

        <h3 style="padding-bottom:5px"><?php echo $hesklang['manage_users']; ?> <a href="javascript:void(0)" onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['users_intro']); ?>')"><i class="fa fa-question-circle" style="color:black"></i></a></h3>
        <div class="footerWithBorder blankSpace"></div>

        <table class="table table-hover">
            <tr>
            <th><b><i><?php echo $hesklang['name']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['email']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['username']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['administrator']; ?></i></b></th>
                <?php
                /* Is user rating enabled? */
                if ($hesk_settings['rating'])
                {
	                ?>
	                <th><b><i><?php echo $hesklang['rating']; ?></i></b></th>
	                <?php
                }
                ?>
            <th><b><i>&nbsp;<?php echo $hesklang['opt']; ?>&nbsp;</i></b></th>
            </tr>
<!-- I can't get this block to tab over without breaking, so it will be awkwardly sticking out for now :( -->
<?php
$res = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` ORDER BY `id` ASC');

$i=1;
$cannot_manage = array();

while ($myuser = hesk_dbFetchAssoc($res))
{

	if ( ! compare_user_permissions($myuser['id'], $myuser['isadmin'], explode(',', $myuser['categories']) , explode(',', $myuser['heskprivileges'])) )
    {
    	$cannot_manage[$myuser['id']] = array('name' => $myuser['name'], 'user' => $myuser['user'], 'email' => $myuser['email']);
        continue;
    }

    if ( isset($_SESSION['seluser']) && $myuser['id'] == $_SESSION['seluser'])
    {
		$color = 'admin_green';
		unset($_SESSION['seluser']);
	}
    else
    {
		$color = $i ? 'admin_white' : 'admin_gray';
    }

	$tmp   = $i ? 'White' : 'Blue';
    $style = 'class="option'.$tmp.'OFF" onmouseover="this.className=\'option'.$tmp.'ON\'" onmouseout="this.className=\'option'.$tmp.'OFF\'"';
	$i	   = $i ? 0 : 1;

    /* User online? */
	if ($hesk_settings['online'])
	{
    	if (isset($hesk_settings['users_online'][$myuser['id']]))
        {
			$myuser['name'] = '<i style="color: green" class="fa fa-circle" data-toggle="tooltip" data-placement="top" title="'.$hesklang['online'].'"></i> ' . $myuser['name'];
        }
        else
        {
			$myuser['name'] = '<i style="color: gray" class="fa fa-circle" data-toggle="tooltip" data-placement="top" title="'.$hesklang['offline'].'"></i> ' . $myuser['name'];
        }
	}

	/* To edit yourself go to "Profile" page, not here. */
    if ($myuser['id'] == $_SESSION['id'])
    {
    	$edit_code = '<a href="profile.php"><i style="font-size: 16px" class="fa fa-pencil" data-toggle="tooltip" data-placement="top" title="'.$hesklang['edit'].'"></i></a>';
    }
    else
    {
    	$edit_code = '<a href="manage_users.php?a=edit&amp;id='.$myuser['id'].'" data-toggle="tooltip" data-placement="top" title="'.$hesklang['edit'].'"><i style="font-size: 16px" class="fa fa-pencil"></i></a>';
    }

    if ($myuser['isadmin'])
    {
    	$myuser['isadmin'] = '<font class="open">'.$hesklang['yes'].'</font>';
    }
    else
    {
    	$myuser['isadmin'] = '<font class="resolved">'.$hesklang['no'].'</font>';
    }

    /* Deleting user with ID 1 (default administrator) is not allowed */
    if ($myuser['id'] == 1)
    {
        $remove_code = ' <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
    }
    else
    {
        $remove_code = ' <a href="manage_users.php?a=remove&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'" onclick="return confirm_delete();" data-toggle="tooltip" data-placement="top" title="'.$hesklang['delete'].'"><i style="font-size: 16px; color: red" class="fa fa-times"></i></a>';
    }

	/* Is auto assign enabled? */
	if ($hesk_settings['autoassign'])
    {
    	if ($myuser['autoassign'])
        {
			$autoassign_code = '<a href="manage_users.php?a=autoassign&amp;s=0&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaon'].'"><i style="color: orange; font-size: 16px" class="fa fa-bolt"></i></a>';
        }
        else
        {
			$autoassign_code = '<a href="manage_users.php?a=autoassign&amp;s=1&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaoff'].'"><i style="color: gray; font-size: 16px" class="fa fa-bolt"></i></a>';
        }
    }
    else
    {
		$autoassign_code = '';
    }

echo <<<EOC
<tr>
<td>$myuser[name]</td>
<td><a href="mailto:$myuser[email]">$myuser[email]</a></td>
<td>$myuser[user]</td>
<td>$myuser[isadmin]</td>

EOC;

if ($hesk_settings['rating'])
{
	$alt = $myuser['rating'] ? sprintf($hesklang['rated'], sprintf("%01.1f", $myuser['rating']), ($myuser['ratingneg']+$myuser['ratingpos'])) : $hesklang['not_rated'];
	echo '<td><img src="../img/star_'.(hesk_round_to_half($myuser['rating'])*10).'.png" width="85" height="16" alt="'.$alt.'" data-toggle="tooltip" data-placement="top" title="'.$alt.'" border="0" style="vertical-align:text-bottom" />&nbsp;</td>';
}

echo <<<EOC
<td>$autoassign_code $edit_code $remove_code</td>
</tr>

EOC;
} // End while
?>
</table>
<?php if ($hesk_settings['online'])
{
    echo '&nbsp;&nbsp;&nbsp;<i style="color: green" class="fa fa-circle"></i> '.$hesklang['online'].' &nbsp;&nbsp;&nbsp; <i style="color: gray" class="fa fa-circle"></i> '.$hesklang['offline'];
}?>
    </div>     
</div>

<script language="Javascript" type="text/javascript"><!--
hesk_checkPassword(document.form1.newpass.value);
//-->
</script>

<p>&nbsp;</p>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

} // End else


/*** START FUNCTIONS ***/


function compare_user_permissions($compare_id, $compare_isadmin, $compare_categories, $compare_features)
{
	global $hesk_settings;

    /* Comparing myself? */
    if ($compare_id == $_SESSION['id'])
    {
    	return true;
    }

    /* Admins have full access, no need to compare */
	if ($_SESSION['isadmin'])
    {
    	return true;
    }
    elseif ($compare_isadmin)
    {
    	return false;
    }

	/* Compare categories */
    foreach ($compare_categories as $catid)
    {
    	if ( ! array_key_exists($catid, $hesk_settings['categories']) )
        {
        	return false;
        }
    }

	/* Compare features */
    foreach ($compare_features as $feature)
    {
    	if ( ! in_array($feature, $hesk_settings['features']) )
        {
        	return false;
        }
    }

    return true;

} // END compare_user_permissions()


function edit_user()
{
	global $hesk_settings, $hesklang, $default_userdata;

	$id = intval( hesk_GET('id') ) or hesk_error("$hesklang[int_error]: $hesklang[no_valid_id]");

	/* To edit self fore using "Profile" page */
    if ($id == $_SESSION['id'])
    {
    	hesk_process_messages($hesklang['eyou'],'profile.php','NOTICE');
    }

    $_SESSION['edit_userdata'] = TRUE;

    if ( ! isset($_SESSION['save_userdata']))
    {
		$res = hesk_dbQuery("SELECT `user`,`pass`,`isadmin`,`name`,`email`,`signature`,`categories`,`autoassign`,`heskprivileges` AS `features` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='".intval($id)."' LIMIT 1");
    	$_SESSION['userdata'] = hesk_dbFetchAssoc($res);

        /* Store original username for display until changes are saved successfully */
        $_SESSION['original_user'] = $_SESSION['userdata']['user'];

        /* A few variables need special attention... */
        if ($_SESSION['userdata']['isadmin'])
        {
	        $_SESSION['userdata']['features'] = $default_userdata['features'];
	        $_SESSION['userdata']['categories'] = $default_userdata['categories'];
        }
        else
        {
	        $_SESSION['userdata']['features'] = explode(',',$_SESSION['userdata']['features']);
	        $_SESSION['userdata']['categories'] = explode(',',$_SESSION['userdata']['categories']);
        }
        $_SESSION['userdata']['cleanpass'] = '';
    }

	/* Make sure we have permission to edit this user */
	if ( ! compare_user_permissions($id, $_SESSION['userdata']['isadmin'], $_SESSION['userdata']['categories'], $_SESSION['userdata']['features']) )
	{
		hesk_process_messages($hesklang['npea'],'manage_users.php');
	}

    /* Print header */
	require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

	/* Print main manage users page */
	require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
	?>

    <ol class="breadcrumb">
      <li><a href="manage_users.php"><?php echo $hesklang['manage_users']; ?></a></li>
      <li class="active"><?php echo $hesklang['editing_user'].' '.$_SESSION['original_user']; ?></li>
    </ol>
    
    <div class="row" style="padding-top: 20px">
        <div class="col-md-8 col-md-offset-2">
        	<?php
	        /* This will handle error, success and notice messages */
	        hesk_handle_messages();
	        ?>
            
            <h3><?php echo $hesklang['editing_user'].' '.$_SESSION['original_user']; ?></h3>
            <h6><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></h6>
            <div class="footerWithBorder blankSpace"></div>
            
            <form role="form" class="form-horizontal" name="form1" method="post" action="manage_users.php">
                <!-- Contact info -->
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label"><?php echo $hesklang['real_name']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="<?php echo $hesklang['real_name']; ?>" type="text" name="name" size="40" maxlength="50" value="<?php echo $_SESSION['userdata']['name']; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-3 control-label"><?php echo $hesklang['email']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="<?php echo $hesklang['email']; ?>"  type="text" name="email" size="40" maxlength="255" value="<?php echo $_SESSION['userdata']['email']; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="user" class="col-sm-3 control-label"><?php echo $hesklang['username']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <input class="form-control" placeholder="<?php echo $hesklang['username']; ?>" type="text" name="user" size="40" maxlength="20" value="<?php echo $_SESSION['userdata']['user']; ?>" />
                    </div>     
                </div>
                <div class="form-group">
                    <label for="newpass" class="col-sm-3 control-label"><?php echo $hesklang['pass']; ?>:</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" placeholder="<?php echo $hesklang['pass']; ?>"  name="newpass" autocomplete="off" size="40" value="<?php echo $_SESSION['userdata']['cleanpass']; ?>" onkeyup="javascript:hesk_checkPassword(this.value)" />         
                    </div>
                </div>
                <div class="form-group">
                    <label for="newpass2" class="col-sm-3 control-label"><?php echo $hesklang['confirm_pass']; ?>:</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" placeholder="<?php echo $hesklang['confirm_pass']; ?>"  name="newpass2" autocomplete="off" size="40" value="<?php echo $_SESSION['userdata']['cleanpass']; ?>" />
                    </div>     
                </div>
                <div class="form-group">
                    <label for="pwdst" class="col-sm-3 control-label"><?php echo $hesklang['pwdst']; ?>:</label>
                    <div class="col-sm-9">
                        <div style="border: 1px solid gray; width: 100px;">
	                        <div id="progressBar"
	                             style="font-size: 1px; height: 14px; width: 0px; border: 1px solid white;">
	                        </div>
                        </div>
                    </div>   
                </div>
                <div class="form-group">
                    <label for="isadmin" class="col-sm-3 control-label"><?php echo $hesklang['administrator']; ?>: <font class="important">*</font></label>
                    <div class="col-sm-9">
                        <?php
	                    /* Only administrators can create new administrator accounts */
	                    if ($_SESSION['isadmin'])
	                    {
		                    ?>
	                        <div class="radio"><label><input type="radio" name="isadmin" value="1" onchange="Javascript:hesk_toggleLayerDisplay('options')" <?php if ($_SESSION['userdata']['isadmin']) echo 'checked="checked"'; ?> /> <?php echo $hesklang['yes'].' '.$hesklang['admin_can']; ?></label></div>
		                    <div class="radio"><label><input type="radio" name="isadmin" value="0" onchange="Javascript:hesk_toggleLayerDisplay('options')" <?php if (!$_SESSION['userdata']['isadmin']) echo 'checked="checked"'; ?> /> <?php echo $hesklang['no'].' '.$hesklang['staff_can']; ?></label></div>
	                        <?php
	                    }
	                    else
	                    {
		                    echo $hesklang['no'].' '.$hesklang['staff_can'];
	                    }
	                    ?>        
                    </div>     
                </div>
                <div class="form-group" id="options" style="display: <?php echo ($_SESSION['isadmin'] && $_SESSION['userdata']['isadmin']) ? 'none' : ''; ?>">
                   <div class="row">
                        <label for="cats" class="control-label col-sm-3"><?php echo $hesklang['allowed_cat']; ?>: <font class="important">*</font></label>
                        <div class="col-sm-6">
                            <?php
	                            foreach ($hesk_settings['categories'] as $catid => $catname)
	                            {
	        	                    echo '<div class="checkbox"><label><input type="checkbox" name="categories[]" value="' . $catid . '" ';
	                                if ( in_array($catid,$_SESSION['userdata']['categories']) )
	                                {
	            	                    echo ' checked="checked" ';
	                                }
	                                echo ' />' . $catname . '</label></div> ';
	                            }
			                ?>         
                        </div>
                    </div>
                    <div class="row">
                        <label for="feats" class="control-label col-sm-3"><?php echo $hesklang['allow_feat']; ?>: <font class="important">*</font></label>
                        <div class="col-sm-6">
                            <?php
			                    foreach ($hesk_settings['features'] as $k)
	                            {
	        	                    echo '<div class="checkbox"><label><input type="checkbox" name="features[]" value="' . $k . '" ';
	                                if (in_array($k,$_SESSION['userdata']['features']))
	                                {
	            	                    echo ' checked="checked" ';
	                                }
	                                echo ' />' . $hesklang[$k] . '</label></div> ';
	                            }
	                        ?>    
                        </div>
                    </div>
                </div>
                <?php if ($hesk_settings['autoassign'])
                {    ?>                
                <div class="form-group">
                    <label for="autoassign" class="col-sm-3 control-label"><?php echo $hesklang['opt']; ?>:</label>
                    <div class="col-sm-9">
                        <div class="checkbox">
                            <label><input type="checkbox" name="autoassign" value="Y" <?php if ( isset($_SESSION['userdata']['autoassign']) && $_SESSION['userdata']['autoassign'] == 1 ) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['user_aa']; ?></label>
                        </div>   
                    </div>
                </div> 
                <?php } ?>
                <div class="form-group">
                    <label for="signature" class="col-sm-3 control-label"><?php echo $hesklang['signature_max']; ?>:</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" placeholder="<?php echo $hesklang['sig']; ?>" name="signature" rows="6" cols="40"><?php echo $_SESSION['userdata']['signature']; ?></textarea><br />
	                    <?php echo $hesklang['sign_extra']; ?>    
                    </div>    
                </div>

                <!-- Submit -->
                <div class="form-group" style="text-align: center">
                    <input type="hidden" name="a" value="save" />
                    <input type="hidden" name="userid" value="<?php echo $id; ?>" />
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
	                <input class="btn btn-default" type="submit" value="<?php echo $hesklang['save_changes']; ?>" />
                    <a class="btn btn-default" href="manage_users.php"><?php echo $hesklang['dich']; ?></a>         
                </div>
            </form>
            <script language="Javascript" type="text/javascript"><!--
	            hesk_checkPassword(document.form1.newpass.value);
	            //-->
	        </script>    
        </div>
    </div>

	<?php
	require_once(HESK_PATH . 'inc/footer.inc.php');
	exit();
} // End edit_user()


function new_user()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check('POST');

	$myuser = hesk_validateUserInfo();

	/* Can view unassigned tickets? */
	if ( in_array('can_view_unassigned', $myuser['features']) )
	{
		$sql_where = '';
		$sql_what = '';
	}
	else
	{
		$sql_where = ' , `notify_new_unassigned`, `notify_reply_unassigned` ';
		$sql_what = " , '0', '0' ";
	}

    /* Categories and Features will be stored as a string */
    $myuser['categories'] = implode(',',$myuser['categories']);
    $myuser['features'] = implode(',',$myuser['features']);

    /* Check for duplicate usernames */
	$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `user` = '".hesk_dbEscape($myuser['user'])."' LIMIT 1");
	if (hesk_dbNumRows($result) != 0)
	{
        hesk_process_messages($hesklang['duplicate_user'],'manage_users.php');
	}

    /* Admins will have access to all features and categories */
    if ($myuser['isadmin'])
    {
		$myuser['categories'] = '';
		$myuser['features'] = '';
    }

	hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (`user`,`pass`,`isadmin`,`name`,`email`,`signature`,`categories`,`autoassign`,`heskprivileges` $sql_where) VALUES (
	'".hesk_dbEscape($myuser['user'])."',
	'".hesk_dbEscape($myuser['pass'])."',
	'".intval($myuser['isadmin'])."',
	'".hesk_dbEscape($myuser['name'])."',
	'".hesk_dbEscape($myuser['email'])."',
	'".hesk_dbEscape($myuser['signature'])."',
	'".hesk_dbEscape($myuser['categories'])."',
	'".intval($myuser['autoassign'])."',
	'".hesk_dbEscape($myuser['features'])."'
	$sql_what )" );

    $_SESSION['seluser'] = hesk_dbInsertID();

    unset($_SESSION['userdata']);

    hesk_process_messages(sprintf($hesklang['user_added_success'],$myuser['user'],$myuser['cleanpass']),'./manage_users.php','SUCCESS');
} // End new_user()


function update_user()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check('POST');

    $_SESSION['save_userdata'] = TRUE;

	$tmp = intval( hesk_POST('userid') ) or hesk_error("$hesklang[int_error]: $hesklang[no_valid_id]");

	/* To edit self fore using "Profile" page */
    if ($tmp == $_SESSION['id'])
    {
    	hesk_process_messages($hesklang['eyou'],'profile.php','NOTICE');
    }

    $_SERVER['PHP_SELF'] = './manage_users.php?a=edit&id='.$tmp;
	$myuser = hesk_validateUserInfo(0,$_SERVER['PHP_SELF']);
    $myuser['id'] = $tmp;

	/* If can't view assigned changes this */
	if (in_array('can_view_unassigned',$myuser['features']))
	{
		$sql_where = "";
	}
	else
	{
		$sql_where = " , `notify_new_unassigned`='0', `notify_reply_unassigned`='0' ";
	}

    /* Check for duplicate usernames */
	$res = hesk_dbQuery("SELECT `id`,`isadmin`,`categories`,`heskprivileges` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `user` = '".hesk_dbEscape($myuser['user'])."' LIMIT 1");
	if (hesk_dbNumRows($res) == 1)
	{
    	$tmp = hesk_dbFetchAssoc($res);

        /* Duplicate? */
        if ($tmp['id'] != $myuser['id'])
        {
        	hesk_process_messages($hesklang['duplicate_user'],$_SERVER['PHP_SELF']);
        }

		/* Do we have permission to edit this user? */
		if ( ! compare_user_permissions($tmp['id'], $tmp['isadmin'], explode(',', $tmp['categories']) , explode(',', $tmp['heskprivileges'])) )
		{
			hesk_process_messages($hesklang['npea'],'manage_users.php');
		}
	}

    /* Admins will have access to all features and categories */
    if ($myuser['isadmin'])
    {
		$myuser['categories'] = '';
		$myuser['features'] = '';
    }
	/* Not admin */
	else
    {
		/* Categories and Features will be stored as a string */
	    $myuser['categories'] = implode(',',$myuser['categories']);
	    $myuser['features'] = implode(',',$myuser['features']);

    	/* Unassign tickets from categories that the user had access before but doesn't anymore */
        hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `owner`=0 WHERE `owner`='".intval($myuser['id'])."' AND `category` NOT IN (".$myuser['categories'].")");
    }

	hesk_dbQuery(
    "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET
    `user`='".hesk_dbEscape($myuser['user'])."',
    `name`='".hesk_dbEscape($myuser['name'])."',
    `email`='".hesk_dbEscape($myuser['email'])."',
    `signature`='".hesk_dbEscape($myuser['signature'])."'," . ( isset($myuser['pass']) ? "`pass`='".hesk_dbEscape($myuser['pass'])."'," : '' ) . "
    `categories`='".hesk_dbEscape($myuser['categories'])."',
    `isadmin`='".intval($myuser['isadmin'])."',
    `autoassign`='".intval($myuser['autoassign'])."',
    `heskprivileges`='".hesk_dbEscape($myuser['features'])."'
    $sql_where
    WHERE `id`='".intval($myuser['id'])."' LIMIT 1");

    unset($_SESSION['save_userdata']);
    unset($_SESSION['userdata']);

    hesk_process_messages( $hesklang['user_profile_updated_success'],$_SERVER['PHP_SELF'],'SUCCESS');
} // End update_profile()


function hesk_validateUserInfo($pass_required = 1, $redirect_to = './manage_users.php')
{
	global $hesk_settings, $hesklang;

    $hesk_error_buffer = '';

	$myuser['name']		  = hesk_input( hesk_POST('name') ) or $hesk_error_buffer .= '<li>' . $hesklang['enter_real_name'] . '</li>';
	$myuser['email']	  = hesk_validateEmail( hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer .= '<li>' . $hesklang['enter_valid_email'] . '</li>';
	$myuser['user']		  = hesk_input( hesk_POST('user') ) or $hesk_error_buffer .= '<li>' . $hesklang['enter_username'] . '</li>';
	$myuser['isadmin']	  = empty($_POST['isadmin']) ? 0 : 1;
	$myuser['signature']  = hesk_input( hesk_POST('signature') );
    $myuser['autoassign'] = hesk_POST('autoassign') == 'Y' ? 1 : 0;

    /* If it's not admin at least one category and fature is required */
    $myuser['categories']	= array();
    $myuser['features']		= array();

    if ($myuser['isadmin']==0)
    {
    	if (empty($_POST['categories']) || ! is_array($_POST['categories']) )
        {
			$hesk_error_buffer .= '<li>' . $hesklang['asign_one_cat'] . '</li>';
        }
        else
        {
			foreach ($_POST['categories'] as $tmp)
			{
            	if (is_array($tmp))
                {
                	continue;
                }

				if ($tmp = intval($tmp))
				{
					$myuser['categories'][] = $tmp;
				}
			}
        }

    	if (empty($_POST['features']) || ! is_array($_POST['features']) )
        {
			$hesk_error_buffer .= '<li>' . $hesklang['asign_one_feat'] . '</li>';
        }
        else
        {
			foreach ($_POST['features'] as $tmp)
			{
				if (in_array($tmp,$hesk_settings['features']))
				{
					$myuser['features'][] = $tmp;
				}
			}
        }
	}

	if (strlen($myuser['signature'])>255)
    {
    	$hesk_error_buffer .= '<li>' . $hesklang['signature_long'] . '</li>';
    }

    /* Password */
	$myuser['cleanpass'] = '';

	$newpass = hesk_input( hesk_POST('newpass') );
	$passlen = strlen($newpass);

	if ($pass_required || $passlen > 0)
	{
        /* At least 5 chars? */
        if ($passlen < 5)
        {
        	$hesk_error_buffer .= '<li>' . $hesklang['password_not_valid'] . '</li>';
        }
        /* Check password confirmation */
        else
        {
        	$newpass2 = hesk_input( hesk_POST('newpass2') );

			if ($newpass != $newpass2)
			{
				$hesk_error_buffer .= '<li>' . $hesklang['passwords_not_same'] . '</li>';
			}
            else
            {
                $myuser['pass'] = hesk_Pass2Hash($newpass);
                $myuser['cleanpass'] = $newpass;
            }
        }
	}

    /* Save entered info in session so we don't loose it in case of errors */
	$_SESSION['userdata'] = $myuser;

    /* Any errors */
    if (strlen($hesk_error_buffer))
    {
    	$hesk_error_buffer = $hesklang['rfm'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
    	hesk_process_messages($hesk_error_buffer,$redirect_to);
    }

	return $myuser;

} // End hesk_validateUserInfo()


function remove()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

	$myuser = intval( hesk_GET('id' ) ) or hesk_error($hesklang['no_valid_id']);

    /* You can't delete the default user */
	if ($myuser == 1)
    {
        hesk_process_messages($hesklang['cant_del_admin'],'./manage_users.php');
    }

    /* You can't delete your own account (the one you are logged in) */
	if ($myuser == $_SESSION['id'])
    {
        hesk_process_messages($hesklang['cant_del_own'],'./manage_users.php');
    }

    /* Un-assign all tickets for this user */
    $res = hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `owner`=0 WHERE `owner`='".intval($myuser)."'");

    /* Delete user info */
	$res = hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='".intval($myuser)."'");
	if (hesk_dbAffectedRows() != 1)
    {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['user_not_found'],'./manage_users.php');
    }

    hesk_process_messages($hesklang['sel_user_removed'],'./manage_users.php','SUCCESS');
} // End remove()


function toggle_autoassign()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

	$myuser = intval( hesk_GET('id' ) ) or hesk_error($hesklang['no_valid_id']);
    $_SESSION['seluser'] = $myuser;

    if ( intval( hesk_GET('s') ) )
    {
		$autoassign = 1;
        $tmp = $hesklang['uaaon'];
    }
    else
    {
        $autoassign = 0;
        $tmp = $hesklang['uaaoff'];
    }

	/* Update auto-assign settings */
	$res = hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET `autoassign`='{$autoassign}' WHERE `id`='".intval($myuser)."'");
	if (hesk_dbAffectedRows() != 1)
    {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['user_not_found'],'./manage_users.php');
    }

    hesk_process_messages($tmp,'./manage_users.php','SUCCESS');
} // End toggle_autoassign()
?>
