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
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_man_cat');

// Possible priorities
$priorities = array(
    3 => array('value' => 3, 'text' => $hesklang['low'],		'formatted' => $hesklang['low']),
    2 => array('value' => 2, 'text' => $hesklang['medium'],		'formatted' => '<font class="medium">'.$hesklang['medium'].'</font>'),
    1 => array('value' => 1, 'text' => $hesklang['high'],		'formatted' => '<font class="important">'.$hesklang['high'].'</font>'),
    0 => array('value' => 0, 'text' => $hesklang['critical'],	'formatted' => '<font class="critical">'.$hesklang['critical'].'</font>'),
);

/* What should we do? */
if ( $action = hesk_REQUEST('a') )
{
	if ($action == 'linkcode')       {generate_link_code();}
	elseif ( defined('HESK_DEMO') )  {hesk_process_messages($hesklang['ddemo'], 'manage_categories.php', 'NOTICE');}
	elseif ($action == 'new')        {new_cat();}
	elseif ($action == 'rename')     {rename_cat();}
	elseif ($action == 'remove')     {remove();}
	elseif ($action == 'order')      {order_cat();}
	elseif ($action == 'autoassign') {toggle_autoassign();}
	elseif ($action == 'type')       {toggle_type();}
    elseif ($action == 'priority')   {change_priority();}
    elseif ($action == 'manager')    {change_manager();}
}

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<script language="Javascript" type="text/javascript"><!--
function confirm_delete()
{
    if (confirm('<?php echo hesk_makeJsString($hesklang['confirm_del_cat']); ?>')) {return true;}
else {return false;}
}
//-->
</script>

<?php
    $orderBy = $modsForHesk_settings['category_order_column'];
    $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `".$orderBy."` ASC");
    $options='';
    while ($mycat=hesk_dbFetchAssoc($res))
    {
        $options .= '<option value="'.$mycat['id'].'" ';
        $options .= (isset($_SESSION['selcat']) && $mycat['id'] == $_SESSION['selcat']) ? ' selected="selected" ' : '';
        $options .= '>'.$mycat['name'].'</option>';
    }
?>
<div class="row" style="margin-top: 20px">
    <div align="left" class="col-md-4">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#addCat" data-toggle="tab"><?php echo $hesklang['add_cat']; ?></a></li>
            <li><a href="#renameCat" data-toggle="tab"><?php echo $hesklang['ren_cat']; ?></a></li>
            <li><a href="#changePriority" data-toggle="tab"><?php echo $hesklang['ch_cat_pri']; ?></a></li>
        </ul>
        <div class="tab-content summaryList tabPadding">
            <div class="tab-pane fade in active" id="addCat">
                <!-- CONTENT -->
                <form action="manage_categories.php" method="post" role="form" class="form-horizontal">
                    <h4><?php echo $hesklang['add_cat']; ?></h4>
                    <div class="footerWithBorder blankSpace"></div>
                    <div class="form-group">
                        <p class="col-sm-4 control-label" style="font-size: .87em"><b><?php echo $hesklang['cat_name']; ?>:</b> (<?php echo $hesklang['max_chars']; ?>)</p>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['cat_name']); ?>" type="text" name="name" size="40" maxlength="40"
                                <?php
                                    if (isset($_SESSION['catname']))
                                    {
                                        echo ' value="'.hesk_input($_SESSION['catname']).'" ';
                                    }
                                ?>
                            />    
                        </div>   
                    </div>
                    <div class="form-group">
                        <label for="priority" class="col-sm-4 control-label" style="font-size: .87em"><?php echo $hesklang['def_pri']; ?> <a href="#" onclick="alert('<?php echo hesk_makeJsString($hesklang['cat_pri']); ?>')"><i class="fa fa-question-circle settingsquestionmark"></i> </a> </label>
                        <div class="col-sm-8">
                                <select name="priority" class="form-control">
                                    <?php
                                    // Default priority: low
                                    if ( ! isset($_SESSION['cat_priority']) )
                                    {
                                        $_SESSION['cat_priority'] = 3;
                                    }

                                    // List possible priorities
                                    foreach ($priorities as $value => $info)
                                    {
                                        echo '<option value="'.$value.'"'.($_SESSION['cat_priority'] == $value ? ' selected="selected"' : '').'>'.$info['text'].'</option>';
                                    }
                                    ?>
                                </select></p>

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="options" class="col-sm-4 control-label"><?php echo $hesklang['opt']; ?>:</label>
                        <div class="col-sm-8">
                            <?php
                            if ($hesk_settings['autoassign'])
                            {
                                ?>
                            <div class="checkbox">
                                <label><input type="checkbox" name="autoassign" value="Y" <?php if ( ! isset($_SESSION['cat_autoassign']) || $_SESSION['cat_autoassign'] == 1 ) {echo 'checked="checked"';} ?>  /> <?php echo $hesklang['cat_aa']; ?></label><br />
                            </div>	                               
                                <?php
                            }
                            ?>
                            <div class="checkbox">
                                <label><input type="checkbox" name="type" value="Y" <?php if ( isset($_SESSION['cat_type']) && $_SESSION['cat_type'] == 1 ) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['cat_type']; ?></label>     
                            </div>     
                        </div>     
                    </div>
                    <div class="form-group" style="text-align: center">
                        <input type="hidden" name="a" value="new" />
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                        <input type="submit" value="<?php echo $hesklang['create_cat']; ?>" class="btn btn-default" />     
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="renameCat">
                <form action="manage_categories.php" method="post" role="form" class="form-horizontal">
                    <h4><?php echo $hesklang['ren_cat']; ?></h4>
                    <div class="footerWithBorder blankSpace"></div>
                    <div class="form-group">
                        <label for="catid" class="col-sm-4 control-label"><?php echo $hesklang['oln']; ?></label>
                        <div class="col-sm-8">
                            <select class="form-control" name="catid"><?php echo $options; ?></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label"><?php echo $hesklang['nen']; ?></label>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['cat_name']); ?>" type="text" name="name" size="40" maxlength="40" <?php if (isset($_SESSION['catname2'])) {echo ' value="'.hesk_input($_SESSION['catname2']).'" ';} ?> />
                        </div>
                    </div>
                    <div class="form-group" style="text-align: center">
                        <input type="hidden" name="a" value="rename" />
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                        <input type="submit" value="<?php echo $hesklang['ren_cat']; ?>" class="btn btn-default" />     
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="changePriority">
                <form action="manage_categories.php" method="post" role="form" class="form-horizontal">
                    <h4><?php echo $hesklang['ch_cat_pri']; ?></h4>
                    <div class="footerWithBorder blankSpace"></div>
                    <div class="form-group">
                        <label for="catid" class="col-sm-4 control-label"><?php echo $hesklang['category']; ?></label>
                        <div class="col-sm-8">
                            <select name="catid" class="form-control"><?php echo $options; ?></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="priority" class="col-sm-4 control-label"><?php echo $hesklang['priority']; ?></label>
                        <div class="col-sm-8">
                            <select name="priority" class="form-control">
                                <?php
                                // Default priority: low
                                if ( ! isset($_SESSION['cat_ch_priority']) )
                                {
                                    $_SESSION['cat_ch_priority'] = 3;
                                }

                                // List possible priorities
                                foreach ($priorities as $value => $info)
                                {
                                    echo '<option value="'.$value.'"'.($_SESSION['cat_ch_priority'] == $value ? ' selected="selected"' : '').'>'.$info['text'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4">
                            <input type="hidden" name="a" value="priority" />
                            <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                            <input type="submit" value="<?php echo $hesklang['ch_cat_pri']; ?>" class="btn btn-default">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
          $(function () {
            $('#addCat a:last').tab('show')
          })
        </script>
    </div>
    <div class="col-md-8">
        <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();

            if ($hesk_settings['cust_urgency'])
            {
                hesk_show_notice($hesklang['cat_pri_info'] . ' ' . $hesklang['cpri']);
            }
        ?>
        <h3><?php echo $hesklang['manage_cat']; ?> <a href="javascript:void(0)" onclick="javascript:alert('<?php echo hesk_makeJsString($hesklang['cat_intro']); ?>')"><i class="fa fa-question-circle settingsquestionmark"></i></a></h3>
        <div class="footerWithBorder blankSpace"></div>
        <table class="table table-hover">
            <tr>
                <th><?php echo $hesklang['id']; ?></th>
                <th><?php echo $hesklang['cat_name']; ?></th>
                <th><?php echo $hesklang['priority']; ?></th>
                <th><?php echo $hesklang['not']; ?></th>
                <th><?php echo $hesklang['graph']; ?></th>
                <th><?php echo $hesklang['manager'] ?></th>
                <th><?php echo $hesklang['opt']; ?></th>
            </tr>

            <?php
            /* Get number of tickets per category */
            $tickets_all   = array();
            $tickets_total = 0;

            $res = hesk_dbQuery('SELECT COUNT(*) AS `cnt`, `category` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'tickets` GROUP BY `category`');
            while ($tmp = hesk_dbFetchAssoc($res))
            {
                $tickets_all[$tmp['category']] = $tmp['cnt'];
                $tickets_total += $tmp['cnt'];
            }

            /* Get list of categories */
            $res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `".$orderBy."` ASC");
            $usersRes = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `isadmin` = '0' ORDER BY `name` ASC");
            $users = array();
            while ($userRow = hesk_dbFetchAssoc($usersRes)) {
                array_push($users, $userRow);
            }

            $i=1;
            $j=0;
            $num = hesk_dbNumRows($res);

            while ($mycat=hesk_dbFetchAssoc($res))
            {
                $j++;

                if (isset($_SESSION['selcat2']) && $mycat['id'] == $_SESSION['selcat2'])
                {
                    $color = 'admin_green';
                    unset($_SESSION['selcat2']);
                }
                else
                {
                    $color = $i ? 'admin_white' : 'admin_gray';
                }

                $tmp   = $i ? 'White' : 'Blue';
                $style = 'class="option'.$tmp.'OFF" onmouseover="this.className=\'option'.$tmp.'ON\'" onmouseout="this.className=\'option'.$tmp.'OFF\'"';
                $i     = $i ? 0 : 1;

                /* Number of tickets and graph width */
                $all = isset($tickets_all[$mycat['id']]) ? $tickets_all[$mycat['id']] : 0;
                $width_all = 0;
                if ($tickets_total && $all)
                {
                    $width_all  = round(($all / $tickets_total) * 100);
                }

                /* Deleting category with ID 1 (default category) is not allowed */
                if ($mycat['id'] == 1)
                {
                    $remove_code=' <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
                }
                else
                {
                    $remove_code=' <a href="manage_categories.php?a=remove&amp;catid='.$mycat['id'].'&amp;token='.hesk_token_echo(0).'" onclick="return confirm_delete();"><i class="fa fa-times" style="color: #FF0000; font-size: 14px;" data-toggle="tooltip" data-placement="top" title="'.$hesklang['delete'].'"></i></a>';
                }

                /* Is category private or public? */
                if ($mycat['type'])
                {
                    $type_code = '<a href="manage_categories.php?a=type&amp;s=0&amp;catid='.$mycat['id'].'&amp;token='.hesk_token_echo(0).'"><span class="glyphicon glyphicon-user" style="color: gray" data-toggle="tooltip" data-placement="top" title="'.$hesklang['cat_private'].'"></span></a>';
                }
                else
                {
                    $type_code = '<a href="manage_categories.php?a=type&amp;s=1&amp;catid='.$mycat['id'].'&amp;token='.hesk_token_echo(0).'"><span class="glyphicon glyphicon-user" style="color: #0000FF" data-toggle="tooltip" data-placement="top" title="'.$hesklang['cat_public'].'"></span></a>';
                }

                /* Is auto assign enabled? */
                if ($hesk_settings['autoassign'])
                {
                    if ($mycat['autoassign'])
                    {
                        $autoassign_code = '<a href="manage_categories.php?a=autoassign&amp;s=0&amp;catid='.$mycat['id'].'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-bolt" style="font-size: 14px; color: orange" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaon'].'"></i></a>';
                    }
                    else
                    {
                        $autoassign_code = '<a href="manage_categories.php?a=autoassign&amp;s=1&amp;catid='.$mycat['id'].'&amp;token='.hesk_token_echo(0).'"><i class="fa fa-bolt" style="font-size: 14px; color: gray" data-toggle="tooltip" data-placement="top" title="'.$hesklang['aaoff'].'"></i></a>';
                    }
                }
                else
                {
                    $autoassign_code = '';
                }

                echo '
                <tr>
                <td>'.$mycat['id'].'</td>
                <td>'.$mycat['name'].'</td>
                <td width="1" style="white-space: nowrap;">'.$priorities[$mycat['priority']]['formatted'].'</td>
                <td><a href="show_tickets.php?category='.$mycat['id'].'&amp;s_all=1&amp;s_my=1&amp;s_ot=1&amp;s_un=1" alt="'.$hesklang['list_tickets_cat'].'" title="'.$hesklang['list_tickets_cat'].'">'.$all.'</a></td>
                <td>
                <div class="progress" style="width: 160px; margin-bottom: 0" title="'.sprintf($hesklang['perat'],$width_all.'%').'">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: '.$width_all.'%">
                        <span class="sr-only">40% Complete (success)</span>
                    </div>
                </div>
                </td>
                <td>'.output_user_dropdown($mycat['id'], $mycat['manager'], $users).'</td>
                <td>
                <a href="Javascript:void(0)" onclick="Javascript:hesk_window(\'manage_categories.php?a=linkcode&amp;catid='.$mycat['id'].'&amp;p='.$mycat['type'].'\',\'200\',\'500\')" id="tooltip"><i class="fa fa-code" style="font-size: 14px; color: '. ($mycat['type'] ? 'gray' : 'green') . '" data-toggle="tooltip" data-placement="top" title="'.$hesklang['geco'].'"></i></a>
                ' . $autoassign_code . '
                ' . $type_code . ' ';

                if ($orderBy != 'name' && $num > 1)
                {
                    if ($j == 1)
                    {
                        echo'<img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" /> <a href="manage_categories.php?a=order&amp;catid='.$mycat['id'].'&amp;move=15&amp;token='.hesk_token_echo(0).'"><i class="fa fa-arrow-down" style="font-size: 14px; color: green" data-toggle="tooltip" data-placement="top" title="'.$hesklang['move_dn'].'"></i></a>';
                    }
                    elseif ($j == $num)
                    {
                        echo'<a href="manage_categories.php?a=order&amp;catid='.$mycat['id'].'&amp;move=-15&amp;token='.hesk_token_echo(0).'"><i class="fa fa-arrow-up" style="font-size: 14px; color: green" data-toggle="tooltip" data-placement="top" title="'.$hesklang['move_up'].'"></i></a> <img src="../img/blank.gif" width="16" height="16" alt="" style="padding:3px;border:none;" />';
                    }
                    else
                    {
                        echo'
                        <a href="manage_categories.php?a=order&amp;catid='.$mycat['id'].'&amp;move=-15&amp;token='.hesk_token_echo(0).'"><i class="fa fa-arrow-up" style="font-size: 14px; color: green" data-toggle="tooltip" data-placement="top" title="'.$hesklang['move_up'].'"></i></a>
                        <a href="manage_categories.php?a=order&amp;catid='.$mycat['id'].'&amp;move=15&amp;token='.hesk_token_echo(0).'"><i class="fa fa-arrow-down" style="font-size: 14px; color: green" data-toggle="tooltip" data-placement="top" title="'.$hesklang['move_dn'].'"></i></a>&nbsp;
                        ';
                    }
                }

                echo $remove_code.'</td>
                </tr>
                ';

            } // End while

            ?>
        </table>
    </div>    
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function change_priority()
{
    global $hesk_settings, $hesklang, $priorities;

    /* A security check */
    hesk_token_check('POST');

    $_SERVER['PHP_SELF'] = 'manage_categories.php?catid='.intval( hesk_POST('catid') );

    $catid = hesk_isNumber( hesk_POST('catid'), $hesklang['choose_cat_ren'], $_SERVER['PHP_SELF']);
    $_SESSION['selcat'] = $catid;
    $_SESSION['selcat2'] = $catid;

    $priority = intval( hesk_POST('priority', 3));
    if ( ! array_key_exists($priority, $priorities) )
    {
        $priority = 3;
    }

    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `priority`='{$priority}' WHERE `id`='".intval($catid)."' LIMIT 1");

    hesk_cleanSessionVars('cat_ch_priority');

    hesk_process_messages($hesklang['cat_pri_ch'].' '.$priorities[$priority]['formatted'],$_SERVER['PHP_SELF'],'SUCCESS');
} // END change_priority()


function generate_link_code() {
	global $hesk_settings, $hesklang;
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML; 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php echo $hesklang['genl']; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>" />
<style type="text/css">
body
{
        margin:5px 5px;
        padding:0;
        background:#fff;
        color: black;
        font : 68.8%/1.5 Verdana, Geneva, Arial, Helvetica, sans-serif;
}

p
{
        color : black;
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size: 1.0em;
}
h3
{
        color : #AF0000;
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-weight: bold;
        font-size: 1.0em;
}
</style>
</head>
<body>

<div style="text-align:center">

<h3><?php echo $hesklang['genl']; ?></h3>

<?php
if ( ! empty($_GET['p']) )
{
	echo '<p>&nbsp;<br />' . $hesklang['cpric'] . '<br />&nbsp;</p>';
}
else
{
	?>
	<p><i><?php echo $hesklang['genl2']; ?></i></p>

	<textarea rows="3" cols="50" onfocus="this.select()"><?php echo $hesk_settings['hesk_url'].'/index.php?a=add&amp;catid='.intval( hesk_GET('catid') ); ?></textarea>
	<?php
}
?>

<p align="center"><a href="#" onclick="Javascript:window.close()"><?php echo $hesklang['cwin']; ?></a></p>

</div>

</body>

</html>
	<?php
    exit();
}


function new_cat()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check('POST');

    /* Options */
    $_SESSION['cat_autoassign'] = hesk_POST('autoassign') == 'Y' ? 1 : 0;
    $_SESSION['cat_type'] = hesk_POST('type') == 'Y' ? 1 : 0;

    // Default priority
    $_SESSION['cat_priority'] = intval( hesk_POST('priority', 3) );
    if ($_SESSION['cat_priority'] < 0 || $_SESSION['cat_priority'] > 3)
    {
        $_SESSION['cat_priority'] = 3;
    }

    /* Category name */
	$catname = hesk_input( hesk_POST('name') , $hesklang['enter_cat_name'], 'manage_categories.php');

    /* Do we already have a category with this name? */
	$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `name` LIKE '".hesk_dbEscape( hesk_dbLike($catname) )."' LIMIT 1");
    if (hesk_dbNumRows($res) != 0)
    {
		$_SESSION['catname'] = $catname;
		hesk_process_messages($hesklang['cndupl'],'manage_categories.php');
    }

	/* Get the latest cat_order */
	$res = hesk_dbQuery("SELECT `cat_order` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `cat_order` DESC LIMIT 1");
	$row = hesk_dbFetchRow($res);
	$my_order = $row[0]+10;

    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` (`name`,`cat_order`,`autoassign`,`type`, `priority`) VALUES ('".hesk_dbEscape($catname)."','".intval($my_order)."','".intval($_SESSION['cat_autoassign'])."','".intval($_SESSION['cat_type'])."','{$_SESSION['cat_priority']}')");

    hesk_cleanSessionVars('catname');
    hesk_cleanSessionVars('cat_autoassign');
    hesk_cleanSessionVars('cat_type');
    hesk_cleanSessionVars('cat_priority');

    $_SESSION['selcat2'] = hesk_dbInsertID();

	hesk_process_messages(sprintf($hesklang['cat_name_added'],'<i>'.stripslashes($catname).'</i>'),'manage_categories.php','SUCCESS');
} // End new_cat()


function rename_cat()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check('POST');

    $_SERVER['PHP_SELF'] = 'manage_categories.php?catid='.intval( hesk_POST('catid') );

	$catid = hesk_isNumber( hesk_POST('catid'), $hesklang['choose_cat_ren'], $_SERVER['PHP_SELF']);
	$_SESSION['selcat'] = $catid;
    $_SESSION['selcat2'] = $catid;

	$catname = hesk_input( hesk_POST('name'), $hesklang['cat_ren_name'], $_SERVER['PHP_SELF']);
    $_SESSION['catname2'] = $catname;

	$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `name` LIKE '".hesk_dbEscape( hesk_dbLike($catname) )."' LIMIT 1");
    if (hesk_dbNumRows($res) != 0)
    {
    	$old = hesk_dbFetchAssoc($res);
        if ($old['id'] == $catid)
        {
        	hesk_process_messages($hesklang['noch'],$_SERVER['PHP_SELF'],'NOTICE');
        }
        else
        {
    		hesk_process_messages($hesklang['cndupl'],$_SERVER['PHP_SELF']);
        }
    }

	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `name`='".hesk_dbEscape($catname)."' WHERE `id`='".intval($catid)."' LIMIT 1");

    unset($_SESSION['selcat']);
    unset($_SESSION['catname2']);

    hesk_process_messages($hesklang['cat_renamed_to'].' <i>'.stripslashes($catname).'</i>',$_SERVER['PHP_SELF'],'SUCCESS');
} // End rename_cat()


function remove()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

    $_SERVER['PHP_SELF'] = 'manage_categories.php';

	$mycat = intval( hesk_GET('catid') ) or hesk_error($hesklang['no_cat_id']);
	if ($mycat == 1)
    {
    	hesk_process_messages($hesklang['cant_del_default_cat'],$_SERVER['PHP_SELF']);
    }

	hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE `id`='".intval($mycat)."' LIMIT 1");
	if (hesk_dbAffectedRows() != 1)
    {
    	hesk_error("$hesklang[int_error]: $hesklang[cat_not_found].");
    }

	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `category`=1 WHERE `category`='".intval($mycat)."'");

    hesk_process_messages($hesklang['cat_removed_db'],$_SERVER['PHP_SELF'],'SUCCESS');
} // End remove()


function order_cat()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

	$catid = intval( hesk_GET('catid') ) or hesk_error($hesklang['cat_move_id']);
	$_SESSION['selcat2'] = $catid;

	$cat_move=intval( hesk_GET('move') );

	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `cat_order`=`cat_order`+".intval($cat_move)." WHERE `id`='".intval($catid)."' LIMIT 1");
	if (hesk_dbAffectedRows() != 1)
    {
    	hesk_error("$hesklang[int_error]: $hesklang[cat_not_found].");
    }

	/* Update all category fields with new order */
	$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `cat_order` ASC");

	$i = 10;
	while ($mycat=hesk_dbFetchAssoc($res))
	{
	    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `cat_order`=".intval($i)." WHERE `id`='".intval($mycat['id'])."' LIMIT 1");
	    $i += 10;
	}

    header('Location: manage_categories.php');
    exit();
} // End order_cat()


function toggle_autoassign()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

	$catid = intval( hesk_GET('catid') ) or hesk_error($hesklang['cat_move_id']);
	$_SESSION['selcat2'] = $catid;

    if ( intval( hesk_GET('s') ) )
    {
		$autoassign = 1;
        $tmp = $hesklang['caaon'];
    }
    else
    {
        $autoassign = 0;
        $tmp = $hesklang['caaoff'];
    }

	/* Update auto-assign settings */
	$res = hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `autoassign`='".intval($autoassign)."' WHERE `id`='".intval($catid)."' LIMIT 1");
	if (hesk_dbAffectedRows() != 1)
    {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['cat_not_found'],'./manage_categories.php');
    }

    hesk_process_messages($tmp,'./manage_categories.php','SUCCESS');

} // End toggle_autoassign()


function toggle_type()
{
	global $hesk_settings, $hesklang;

	/* A security check */
	hesk_token_check();

	$catid = intval( hesk_GET('catid') ) or hesk_error($hesklang['cat_move_id']);
	$_SESSION['selcat2'] = $catid;

    if ( intval( hesk_GET('s') ) )
    {
		$type = 1;
        $tmp = $hesklang['cpriv'];
    }
    else
    {
        $type = 0;
        $tmp = $hesklang['cpub'];
    }

	/* Update auto-assign settings */
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` SET `type`='{$type}' WHERE `id`='".intval($catid)."' LIMIT 1");
	if (hesk_dbAffectedRows() != 1)
    {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['cat_not_found'],'./manage_categories.php');
    }

    hesk_process_messages($tmp,'./manage_categories.php','SUCCESS');

} // End toggle_type()

function output_user_dropdown($catId, $selectId, $userArray) {
    global $hesklang;

    if (!hesk_checkPermission('can_set_manager', 0)) {
        foreach ($userArray as $user) {
            if ($user['id'] == $selectId) {
                return '<p>'.$user['name'].'</p>';
            }
        }
        return '<p>'.$hesklang['no_manager'].'</p>';
    } else {
        $dropdownMarkup = '<select class="form-control input-sm" name="managerid">
                <option value="0">'.$hesklang['no_manager'].'</option>';
        foreach ($userArray as $user) {
            $select = $selectId == $user['id'] ? 'selected' : '';
            $dropdownMarkup .= '<option value="'.$user['id'].'" '.$select.'>'.$user['name'].'</option>';
        }
        $dropdownMarkup .= '</select>';


        return '<form role="form" id="manager_form_'.$catId.'" action="manage_categories.php" method="post" class="form-inline" onchange="document.getElementById(\'manager_form_'.$catId.'\').submit();">
                <input type="hidden" name="a" value="manager">
                <input type="hidden" name="catid" value="'.$catId.'">
                '.$dropdownMarkup.'
            </form>';
    }
}

function change_manager() {
    global $hesklang, $hesk_settings;

    $catid = hesk_POST('catid');
    $newManagerId = hesk_POST('managerid');

    hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` SET `manager` = '.intval($newManagerId).' WHERE `id` = '.intval($catid));
    if (hesk_dbAffectedRows() != 1)
    {
        hesk_process_messages($hesklang['int_error'].': '.$hesklang['cat_not_found'],'./manage_categories.php');
    }
    if ($newManagerId == 0) {
        // There is no new manager.
        return;
    }
    // Add the category to the user's categories list, if not already present
    $currentCatRs = hesk_dbQuery('SELECT `categories` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` WHERE `id` = '.intval($newManagerId));
    $currentCategories = hesk_dbFetchAssoc($currentCatRs);
    $categories = explode(',', $currentCategories['categories']);
    if (!in_array($catid, $categories)) {
        hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` SET `categories` = \''.$currentCategories['categories'].','.$catid.'\' WHERE `id` = '.intval($newManagerId));
    }

    hesk_process_messages($hesklang['manager_updated'],'./manage_categories.php','SUCCESS');
}
?>
