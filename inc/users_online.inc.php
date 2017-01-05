<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * http://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * http://www.hesk.com/eula.php
 *
 */

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}


function hesk_initOnline($user_id)
{
	global $hesk_settings, $hesklang;

    /* Set user to online */
	hesk_setOnline($user_id);

    /* Can this user view online staff? */
    if (hesk_checkPermission('can_view_online',0))
    {
    	$hesk_settings['users_online'] = hesk_listOnline();
        define('SHOW_ONLINE',1);
    }

    return true;
} // END hesk_initOnline()


function hesk_printOnline() {
	global $hesk_settings, $hesklang;
    ?>
    <li class="dropdown messages-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-users"></i>
            <span class="label label-danger"><?php echo count($hesk_settings['users_online']); ?></span>
        </a>
        <ul class="dropdown-menu">
            <li class="header"><?php echo $hesklang['onlinep']; ?></li>
            <li>
                <ul class="menu">
                    <?php foreach ($hesk_settings['users_online'] as $tmp): ?>
                        <li>
                            <?php $link = ($tmp['id'] == $_SESSION['id']) ? '' : ' href="mail.php?a=new&id='.$tmp['id'].'"'; ?>
                            <a<?php echo $link; ?>>
                                <div class="pull-left">
                                    <i class="fa fa-user fa-2x black"></i>
                                </div>
                                <h4><?php echo $tmp['name']; ?></h4>
                                <?php
                                if ($tmp['isadmin']): ?>
                                    <p>
                                        <span class="label label-default">
                                            <?php echo $hesklang['administrator']; ?>
                                        </span>
                                    </p>
                                <?php elseif ($tmp['id'] == $_SESSION['id']): ?>
                                    <!--<span class="badge badge-default">You</span>-->
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        </ul>
    </li>
<?php
} // END hesk_printOnline()


function hesk_listOnline($list_names=1)
{
	global $hesk_settings, $hesklang, $hesk_db_link;

    $users_online = array();

    /* Clean expired entries */
    hesk_cleanOnline();

    /* Get a list of online users */
    /* --> With names */
    if ($list_names)
    {
        $res = hesk_dbQuery("SELECT `t1`.`user_id` , `t2`.`name` , `t2`.`isadmin` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."online` AS `t1` INNER JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."users` AS `t2` ON `t1`.`user_id` = `t2`.`id`");
		while ($tmp = hesk_dbFetchAssoc($res))
        {
        	$users_online[$tmp['user_id']] = array(
            	'id'		=> $tmp['user_id'],
                'name'		=> $tmp['name'],
                'isadmin'	=> $tmp['isadmin']
            );
        }
    }
    /* --> Without names */
    else
    {
        $res = hesk_dbQuery("SELECT `user_id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."online`");
		while ($tmp = hesk_dbFetchAssoc($res))
        {
        	$users_online[] = $tmp['user_id'];
        }
    }

    return $users_online;

} // END hesk_listOnline()


function hesk_setOnline($user_id)
{
	global $hesk_settings, $hesklang, $hesk_db_link;

    /* If already online just update... */
    hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."online` SET `tmp` = `tmp` + 1 WHERE `user_id` = '".intval($user_id)."'");

	/* ... else insert a new entry */
    if ( ! hesk_dbAffectedRows() )
    {
	    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."online` (`user_id`) VALUES (".intval($user_id).") ");
    }

    return true;

} // END hesk_setOnline()


function hesk_setOffline($user_id)
{
	global $hesk_settings, $hesklang, $hesk_db_link;

    /* If already online just update... */
    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."online` WHERE `user_id` = '".intval($user_id)."'");

    return true;

} // END hesk_setOffline()


function hesk_cleanOnline()
{
	global $hesk_settings, $hesklang, $hesk_db_link;

    /* Delete old rows from the database */
    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."online` WHERE `dt` < ( NOW() - INTERVAL ".intval($hesk_settings['online_min'])." MINUTE) ");

	return true;
} // END hesk_cleanOnline()
