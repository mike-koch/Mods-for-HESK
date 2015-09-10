<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.5 from 28th August 2015
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
require(HESK_PATH . 'inc/profile_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_man_users');

/* Possible user features */
$hesk_settings['features'] = hesk_getFeatureArray();

/* Set default values */
$default_userdata = array(

    // Profile info
	'name' => '',
	'email' => '',
    'cleanpass' => '',
    'user' => '',
    'autoassign' => 'Y',

    // Signature
	'signature' => '',

    // Permissions
	'isadmin' => 1,
    'active' => 1,
	'categories' => array('1'),
	'features' => array('can_view_tickets','can_reply_tickets','can_change_cat','can_assign_self','can_view_unassigned','can_view_online'),

    // Preferences
    'afterreply' => 0,
    'autorefresh' => 0,

    // Defaults
    'autostart' => 1,
    'notify_customer_new' => 1,
    'notify_customer_reply' => 1,
    'show_suggested' => 1,

    // Notifications
    'notify_new_unassigned' => 1,
    'notify_new_my' => 1,
    'notify_reply_unassigned' => 1,
    'notify_reply_my' => 1,
    'notify_assigned' => 1,
    'notify_note' => 1,
    'notify_pm' => 1,
    'notify_note_unassigned' => 1,
);

$modsForHesk_settings = mfh_getSettings();
/* A list of all categories */
$orderBy = $modsForHesk_settings['category_order_column'];
$hesk_settings['categories'] = array();
$res = hesk_dbQuery('SELECT `id`,`name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` ORDER BY `'.$orderBy.'` ASC');
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
    if ( isset($_POST['isadmin']) )
    {
        unset($_POST['isadmin']);
    }

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
    elseif ($action == 'active')     {toggle_active();}
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

<div style="margin-top: 20px" class="row">
    <div class="col-md-10 col-md-offset-1">
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

        <h3 style="padding-bottom:5px"><?php echo $hesklang['manage_users']; ?> <a href="javascript:void(0)" onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['users_intro']); ?>')"><i class="fa fa-question-circle settingsquestionmark"></i></a></h3>
        <div class="footerWithBorder blankSpace"></div>

        <table class="table table-hover">
            <tr>
            <th><b><i><?php echo $hesklang['name']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['email']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['username']; ?></i></b></th>
            <th><b><i><?php echo $hesklang['permission_template']; ?></i></b></th>
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
$res = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` ORDER BY `name` ASC');

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
    } elseif ($myuser['id'] == 1)
    {
        $edit_code = ' <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
    } else
    {
    	$edit_code = '<a href="manage_users.php?a=edit&amp;id='.$myuser['id'].'"><i style="font-size: 16px" class="fa fa-pencil" data-toggle="tooltip" data-placement="top" title="'.$hesklang['edit'].'"></i></a>';
    }

    if ($myuser['isadmin'])
    {
    	$myuser['isadmin'] = '<font class="open">'.$hesklang['yes'].'</font>';
    }
    else
    {
    	$myuser['isadmin'] = '<font class="resolved">'.$hesklang['no'].'</font>';
    }

    /* Deleting user with ID 1 (default administrator) is not allowed. Also don't allow the logged in user to be deleted or inactivated */
    if ($myuser['id'] == 1 || $myuser['id'] == $_SESSION['id'])
    {
        $remove_code = ' <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
    } else
    {
        $remove_code = ' <a href="manage_users.php?a=remove&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'" onclick="return confirm_delete();"><i style="font-size: 16px; color: red" class="fa fa-times" data-toggle="tooltip" data-placement="top" title="'.$hesklang['delete'].'"></i></a>';
    }

	/* Is auto assign enabled? */
	if ($hesk_settings['autoassign'])
    {
    	if ($myuser['autoassign'])
        {
			$autoassign_code = '<a href="manage_users.php?a=autoassign&amp;s=0&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'"><i style="color: orange; font-size: 16px" class="fa fa-bolt" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaon'].'"></i></a>';
        }
        else
        {
			$autoassign_code = '<a href="manage_users.php?a=autoassign&amp;s=1&amp;id='.$myuser['id'].'&amp;token='.hesk_token_echo(0).'"><i style="color: gray; font-size: 16px" class="fa fa-bolt" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaoff'].'"></i></a>';
        }
    }
    else
    {
		$autoassign_code = '';
    }

    $activeMarkup = '';
    if ($myuser['id'] != $_SESSION['id'] && $myuser['id'] != 1) {
        /* Is the user active? */
        if ($myuser['active']) {
            $activeMarkup = '<a href="manage_users.php?a=active&amp;s=0&amp;id=' . $myuser['id'] . '&amp;token=' . hesk_token_echo(0) . '"><i style="color: green; font-size: 16px" class="fa fa-user" data-toggle="tooltip" data-placement="top" title="' . $hesklang['disable_user'] . '"></i></a>';
        } else {
            $activeMarkup = '<a href="manage_users.php?a=active&amp;s=1&amp;id=' . $myuser['id'] . '&amp;token=' . hesk_token_echo(0) . '"><i style="color: gray; font-size: 16px" class="fa fa-user" data-toggle="tooltip" data-placement="top" title="' . $hesklang['enable_user'] . '"></i></a>';
        }
    }

    $templateName = $hesklang['custom'];
    if ($myuser['permission_template'] != -1) {
        $result = hesk_dbQuery("SELECT `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."permission_templates` WHERE `id` = ".intval($myuser['permission_template']));
        $row = hesk_dbFetchAssoc($result);
        $templateName = $row['name'];
    }


echo <<<EOC
<tr>
<td>$myuser[name]</td>
<td><a href="mailto:$myuser[email]">$myuser[email]</a></td>
<td>$myuser[user]</td>
<td>$templateName</td>

EOC;

if ($hesk_settings['rating'])
{
	$alt = $myuser['rating'] ? sprintf($hesklang['rated'], sprintf("%01.1f", $myuser['rating']), ($myuser['ratingneg']+$myuser['ratingpos'])) : $hesklang['not_rated'];
	echo '<td><img src="../img/star_'.(hesk_round_to_half($myuser['rating'])*10).'.png" width="85" height="16" alt="'.$alt.'" data-toggle="tooltip" data-placement="top" title="'.$alt.'" border="0" style="vertical-align:text-bottom" />&nbsp;</td>';
}

echo <<<EOC
<td>$autoassign_code $edit_code $remove_code $activeMarkup</td>
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
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h3><?php echo $hesklang['add_user']; ?></h3>
        <h6><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></h6>
        <div class="footerWithBorder blankSpace"></div>

        <form name="form1" method="post" action="manage_users.php" class="form-horizontal" role="form">
        <?php hesk_profile_tab('userdata', false, 'create_user'); ?>
        </form>
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

    if ($id == 1)
    {
        hesk_process_messages($hesklang['cant_edit_admin'],'./manage_users.php');
    }

    $_SESSION['edit_userdata'] = TRUE;

    if ( ! isset($_SESSION['save_userdata']))
    {
		$res = hesk_dbQuery("SELECT *,`heskprivileges` AS `features`, `active`
                            FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id`='".intval($id)."' LIMIT 1");
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
    
    <div class="row pad-down-20">
        <div class="col-md-8 col-md-offset-2">
        	<?php
	        /* This will handle error, success and notice messages */
	        hesk_handle_messages();
	        ?>
            
            <h3><?php echo $hesklang['editing_user'].' '.$_SESSION['original_user']; ?></h3>
            <h6><?php echo $hesklang['req_marked_with']; ?> <font class="important">*</font></h6>
            <div class="footerWithBorder blankSpace"></div>
            
            <form role="form" class="form-horizontal" name="form1" method="post" action="manage_users.php">
                <?php hesk_profile_tab('userdata',false,'edit_user'); ?>
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

	hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (
	    `user`,
	    `pass`,
	    `isadmin`,
	    `name`,
	    `email`,
	    `signature`,
	    `categories`,
	    `autoassign`,
	    `heskprivileges`,
	    `afterreply`,
        `autostart`,
        `notify_customer_new`,
        `notify_customer_reply`,
        `show_suggested`,
        `notify_new_unassigned`,
        `notify_new_my`,
        `notify_reply_unassigned`,
        `notify_reply_my`,
        `notify_assigned`,
        `notify_pm`,
        `notify_note`,
        `notify_note_unassigned`,
        `autorefresh`,
        `permission_template`) VALUES (
	'".hesk_dbEscape($myuser['user'])."',
	'".hesk_dbEscape($myuser['pass'])."',
	'".intval($myuser['isadmin'])."',
	'".hesk_dbEscape($myuser['name'])."',
	'".hesk_dbEscape($myuser['email'])."',
	'".hesk_dbEscape($myuser['signature'])."',
	'".hesk_dbEscape($myuser['categories'])."',
	'".intval($myuser['autoassign'])."',
	'".hesk_dbEscape($myuser['features'])."',
	'".($myuser['afterreply'])."' ,
	'".($myuser['autostart'])."' ,
	'".($myuser['notify_customer_new'])."' ,
	'".($myuser['notify_customer_reply'])."' ,
	'".($myuser['show_suggested'])."' ,
	'".($myuser['notify_new_unassigned'])."' ,
	'".($myuser['notify_new_my'])."' ,
	'".($myuser['notify_reply_unassigned'])."' ,
	'".($myuser['notify_reply_my'])."' ,
	'".($myuser['notify_assigned'])."' ,
	'".($myuser['notify_pm'])."',
	'".($myuser['notify_note'])."',
	'".($myuser['notify_note_unassigned'])."',
	".intval($myuser['autorefresh']).",
	".intval($myuser['template']).")" );

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

    /* Only active users can be assigned tickets. Also turn off all notifications */
    if (!$myuser['active']) {
        $myuser['autoassign'] = 0;
        $myuser['notify_new_unassigned'] = 0;
        $myuser['notify_new_my'] = 0;
        $myuser['notify_reply_unassigned'] = 0;
        $myuser['notify_reply_my'] = 0;
        $myuser['notify_assigned'] = 0;
        $myuser['notify_pm'] = 0;
        $myuser['notify_note'] = 0;
        $myuser['notify_note_unassigned'] = 0;
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

    // Find the list of categories they are manager of. If they no longer have access to the category, revoke their manager permission.
    if ($myuser['isadmin']) {
        // Admins can't be managers
        hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` SET `manager` = 0 WHERE `manager` = '.intval($myuser['id']));
    } else {
        $currentCatRs = hesk_dbQuery("SELECT `categories` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `id` = '".intval($myuser['id'])."' LIMIT 1");
        $rowOfCategories = hesk_dbFetchAssoc($currentCatRs);
        $cats = $rowOfCategories['categories'];
        $currentCategories = explode(',', $cats);
        $newCategories = explode(',', $myuser['categories']);

        // If any any elements are in current but not in new, add them to the revoke array
        $revokeCats = array();
        foreach ($currentCategories as $currentCategory) {
            if (!in_array($currentCategory, $newCategories) && $currentCategory != '') {
                array_push($revokeCats, $currentCategory);
            }
        }

        if (count($revokeCats) > 0) {
            hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` SET `manager` = 0 WHERE `id` IN (" . implode(',', $revokeCats) . ")");
        }
    }


	hesk_dbQuery(
    "UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET
    `user`='".hesk_dbEscape($myuser['user'])."',
    `name`='".hesk_dbEscape($myuser['name'])."',
    `email`='".hesk_dbEscape($myuser['email'])."',
    `signature`='".hesk_dbEscape($myuser['signature'])."'," . ( isset($myuser['pass']) ? "`pass`='".hesk_dbEscape($myuser['pass'])."'," : '' ) . "
    `categories`='".hesk_dbEscape($myuser['categories'])."',
    `isadmin`='".intval($myuser['isadmin'])."',
    `active`='".intval($myuser['active'])."',
    `autoassign`='".intval($myuser['autoassign'])."',
    `heskprivileges`='".hesk_dbEscape($myuser['features'])."',
    `afterreply`='".($myuser['afterreply'])."' ,
	`autostart`='".($myuser['autostart'])."' ,
	`notify_customer_new`='".($myuser['notify_customer_new'])."' ,
	`notify_customer_reply`='".($myuser['notify_customer_reply'])."' ,
	`show_suggested`='".($myuser['show_suggested'])."' ,
	`notify_new_unassigned`='".($myuser['notify_new_unassigned'])."' ,
	`notify_new_my`='".($myuser['notify_new_my'])."' ,
	`notify_reply_unassigned`='".($myuser['notify_reply_unassigned'])."' ,
	`notify_reply_my`='".($myuser['notify_reply_my'])."' ,
	`notify_assigned`='".($myuser['notify_assigned'])."' ,
	`notify_pm`='".($myuser['notify_pm'])."',
	`notify_note`='".($myuser['notify_note'])."',
	`notify_note_unassigned`='".($myuser['notify_note_unassigned'])."',
	`autorefresh`=".intval($myuser['autorefresh']).",
	`permission_template`=".intval($myuser['template'])."
    WHERE `id`='".intval($myuser['id'])."' LIMIT 1");

    // If they are now inactive, remove any manager rights
    if (!$myuser['active']) {
        hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `manager` = 0 WHERE `manager` = ".intval($myuser['id']));
    }



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
	$myuser['isadmin']	  = hesk_POST('template') == '1' ? 1 : 0;
    $myuser['template']   = hesk_POST('template');
	$myuser['signature']  = hesk_input( hesk_POST('signature') );
    $myuser['autoassign'] = hesk_POST('autoassign') == 'Y' ? 1 : 0;
    $myuser['active'] = empty($_POST['active']) ? 0 : 1;
    $myuser['can_change_notification_settings'] = empty($_POST['can_change_notification_settings']) ? 0 : 1;

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

    /* After reply */
    $myuser['afterreply'] = intval( hesk_POST('afterreply') );
    if ($myuser['afterreply'] != 1 && $myuser['afterreply'] != 2)
    {
    	$myuser['afterreply'] = 0;
    }
    $myuser['autorefresh'] = intval(hesk_POST('autorefresh'));

    // Defaults
    $myuser['autostart']				= isset($_POST['autostart']) ? 1 : 0;
    $myuser['notify_customer_new']		= isset($_POST['notify_customer_new']) ? 1 : 0;
    $myuser['notify_customer_reply']	= isset($_POST['notify_customer_reply']) ? 1 : 0;
    $myuser['show_suggested']			= isset($_POST['show_suggested']) ? 1 : 0;

    /* Notifications */
    $myuser['notify_new_unassigned']	= empty($_POST['notify_new_unassigned']) ? 0 : 1;
    $myuser['notify_new_my'] 			= empty($_POST['notify_new_my']) ? 0 : 1;
    $myuser['notify_reply_unassigned']	= empty($_POST['notify_reply_unassigned']) ? 0 : 1;
    $myuser['notify_reply_my']			= empty($_POST['notify_reply_my']) ? 0 : 1;
    $myuser['notify_assigned']			= empty($_POST['notify_assigned']) ? 0 : 1;
    $myuser['notify_note']				= empty($_POST['notify_note']) ? 0 : 1;
    $myuser['notify_pm']				= empty($_POST['notify_pm']) ? 0 : 1;
    $myuser['notify_note_unassigned']   = empty($_POST['notify_note_unassigned']) ? 0 : 1;

    /* Save entered info in session so we don't loose it in case of errors */
	$_SESSION['userdata'] = $myuser;

    /* Any errors */
    if (strlen($hesk_error_buffer))
    {
        if ($myuser['isadmin'])
        {
            // Preserve default staff data for the form
            global $default_userdata;
            $_SESSION['userdata']['features'] = $default_userdata['features'];
            $_SESSION['userdata']['categories'] = $default_userdata['categories'];
        }

        $hesk_error_buffer = $hesklang['rfm'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
        hesk_process_messages($hesk_error_buffer,$redirect_to);
    }

    // "can_unban_emails" feature also enables "can_ban_emails"
    if ( in_array('can_unban_emails', $myuser['features']) && ! in_array('can_ban_emails', $myuser['features']) )
    {
        $myuser['features'][] = 'can_ban_emails';
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

    // Revoke manager rights
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `manager` = 0 WHERE `manager` = ".intval($myuser));

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

function toggle_active()
{
    global $hesk_settings, $hesklang;

    /* Security check */
    hesk_token_check();

    $myuser = intval(hesk_GET('id')) or hesk_error($hesklang['no_valid_id']);
    $_SESSION['seluser'] = $myuser;

    if (intval($myuser) == $_SESSION['id'])
    {
        //-- You can't deactivate yourself!
        hesk_process_messages($hesklang['self_deactivation'], './manage_users.php');
    }

    if (intval(hesk_GET('s')))
    {
        $active = 1;
        $tmp = $hesklang['user_activated'];
        $notificationSql = "";
    } else
    {
        $active = 0;
        $tmp = $hesklang['user_deactivated'];

        // Revoke any manager rights
        hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `manager` = 0 WHERE `manager` = ".intval($myuser));

        $notificationSql = ", `autoassign` = 0, `notify_new_unassigned` = 0, `notify_new_my` = 0, `notify_reply_unassigned` = 0,
        `notify_reply_my` = 0, `notify_assigned` = 0, `notify_pm` = 0, `notify_note` = 0, `notify_note_unassigned` = 0";
    }

    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` SET `active` = '".$active."'".$notificationSql." WHERE `id` = '".intval($myuser)."'");

    if (hesk_dbAffectedRows() != 1) {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['user_not_found'],'./manage_users.php');
    }

    hesk_process_messages($tmp,'./manage_users.php','SUCCESS');
}
?>
