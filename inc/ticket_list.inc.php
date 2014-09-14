<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

/* List of staff */
if (!isset($admins))
{
	$admins = array();
	$res2 = hesk_dbQuery("SELECT `id`,`name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` ORDER BY `id` ASC");
	while ($row=hesk_dbFetchAssoc($res2))
	{
		$admins[$row['id']]=$row['name'];
	}
}

/* List of categories */
$hesk_settings['categories'] = array();
$res2 = hesk_dbQuery('SELECT `id`, `name` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'categories` WHERE ' . hesk_myCategories('id') . ' ORDER BY `cat_order` ASC');
while ($row=hesk_dbFetchAssoc($res2))
{
	$hesk_settings['categories'][$row['id']] = $row['name'];
}

/* Current MySQL time */
$mysql_time = hesk_dbTime();

/* Get number of tickets and page number */
$result = hesk_dbQuery($sql_count);
$total  = hesk_dbResult($result);

//-- Precondition: The panel has already been created, and there is NO open <div class="panel-body"> tag yet.
echo '<div class="panel-body">';
if ($total > 0)
{

	/* This query string will be used to browse pages */
	if ($href == 'show_tickets.php')
	{
		#$query  = 'status='.$status;

        $query = '';
        $query .= 's' . implode('=1&amp;s',array_keys($status)) . '=1';
        $query .= '&amp;p' . implode('=1&amp;p',array_keys($priority)) . '=1';

		$query .= '&amp;category='.$category;
		$query .= '&amp;sort='.$sort;
		$query .= '&amp;asc='.$asc;
		$query .= '&amp;limit='.$maxresults;
		$query .= '&amp;archive='.$archive[1];
		$query .= '&amp;s_my='.$s_my[1];
		$query .= '&amp;s_ot='.$s_ot[1];
		$query .= '&amp;s_un='.$s_un[1];

		$query .= '&amp;cot='.$cot;
		$query .= '&amp;g='.$group;

		$query .= '&amp;page=';
	}
	else
	{
		$query  = 'q='.$q;
	    $query .= '&amp;what='.$what;
		$query .= '&amp;category='.$category;
		$query .= '&amp;dt='.urlencode($date_input);
		$query .= '&amp;sort='.$sort;
		$query .= '&amp;asc='.$asc;
		$query .= '&amp;limit='.$maxresults;
		$query .= '&amp;archive='.$archive[2];
		$query .= '&amp;s_my='.$s_my[2];
		$query .= '&amp;s_ot='.$s_ot[2];
		$query .= '&amp;s_un='.$s_un[2];
		$query .= '&amp;page=';
	}

	$pages = ceil($total/$maxresults) or $pages = 1;
	if ($page > $pages)
	{
		$page = $pages;
	}
	$limit_down = ($page * $maxresults) - $maxresults;

	$prev_page = ($page - 1 <= 0) ? 0 : $page - 1;
	$next_page = ($page + 1 > $pages) ? 0 : $page + 1;
    $autorefreshInSeconds = $_SESSION['autorefresh']/1000;
    $autorefresh = '';
    if ($autorefreshInSeconds > 999) {
        $autorefresh = ' | '.$hesklang['autorefresh'].' '.$autorefreshInSeconds.' '.$hesklang['abbr']['second'];
    }
    echo sprintf($hesklang['tickets_on_pages'],$total,$pages).$autorefresh.' <br />';

    if ($pages > 1)
	{
                
		/* List pages */
        echo '<div class="row">
                <div class="col-md-6 col-sm-12 text-right nu-rtlFloatLeft">
                    <ul class="pagination" style="margin: 0">';
		if ($pages > 7)
		{
			if ($page > 2)
			{
				echo '<li><a href="'.$href.'?'.$query.'1">&laquo;</a></li>'; // <<
			}

			if ($prev_page)
			{
				echo '<li><a href="'.$href.'?'.$query.$prev_page.'">&lsaquo;</a></li>'; // <
			}
		}

		for ($i=1; $i<=$pages; $i++)
		{
			if ($i <= ($page+5) && $i >= ($page-5))
			{
				if ($i == $page)
				{
					echo '<li class="active"><a href="#">'.$i.'</a></li> ';
				}
				else
				{
					echo '<li><a href="'.$href.'?'.$query.$i.'">'.$i.'</a></li>';
				}
			}
		}

		if ($pages > 7)
		{
			if ($next_page)
			{
				echo '<li><a href="'.$href.'?'.$query.$next_page.'">&rsaquo;</a></li>'; // >
			}

			if ($page < ($pages - 1))
			{
				echo '<li><a href="'.$href.'?'.$query.$pages.'">&raquo;</a></li>'; // >>
			}
		}
        echo ' </ul>
               </div>
               <div class="col-md-6 col-sm-12 text-left">
                    <div class="form-inline">'.$hesklang['jump_page'].'
                    <select class="form-control" name="myHpage" id="myHpage" onchange="javascript:window.location=\''.$href.'?'.$query.'\'+document.getElementById(\'myHpage\').value">';
                for ($i=1;$i<=$pages;$i++)
                {
                    $tmp = ($page == $i) ? ' selected="selected"' : '';
                    echo '<option value="'.$i.'"'.$tmp.'>'.$i.'</option>';
                }
                echo'</select>
                </div>
             </div>
         </div>';

	}

	/* We have the full SQL query now, get tickets */
	$sql .= " LIMIT ".hesk_dbEscape($limit_down)." , ".hesk_dbEscape($maxresults)." ";
	$result = hesk_dbQuery($sql);

    /* Uncomment for debugging */
    # echo "SQL: $sql\n<br>";

	/* This query string will be used to order and reverse display */
	if ($href == 'show_tickets.php')
	{
		#$query  = 'status='.$status;

        $query = '';
        $query .= 's' . implode('=1&amp;s',array_keys($status)) . '=1';
        $query .= '&amp;p' . implode('=1&amp;p',array_keys($priority)) . '=1';

		$query .= '&amp;category='.$category;
		#$query .= '&amp;asc='.(isset($is_default) ? 1 : $asc_rev);
		$query .= '&amp;limit='.$maxresults;
		$query .= '&amp;archive='.$archive[1];
		$query .= '&amp;s_my='.$s_my[1];
		$query .= '&amp;s_ot='.$s_ot[1];
		$query .= '&amp;s_un='.$s_un[1];
		$query .= '&amp;page=1';
		#$query .= '&amp;sort=';

		$query .= '&amp;cot='.$cot;
		$query .= '&amp;g='.$group;

	}
	else
	{
		$query  = 'q='.$q;
	    $query .= '&amp;what='.$what;
		$query .= '&amp;category='.$category;
		$query .= '&amp;dt='.urlencode($date_input);
		#$query .= '&amp;asc='.$asc;
		$query .= '&amp;limit='.$maxresults;
		$query .= '&amp;archive='.$archive[2];
		$query .= '&amp;s_my='.$s_my[2];
		$query .= '&amp;s_ot='.$s_ot[2];
		$query .= '&amp;s_un='.$s_un[2];
		$query .= '&amp;page=1';
		#$query .= '&amp;sort=';
	}

    $query .= '&amp;asc=';

	/* Print the table with tickets */
	$random=rand(10000,99999);
	?>

	<form role="form" class="form-inline" name="form1" action="delete_tickets.php" method="post" onsubmit="return hesk_confirmExecute('<?php echo hesk_makeJsString($hesklang['confirm_execute']); ?>')">

    <?php
    if (empty($group))
    {
		hesk_print_list_head();
    }

	$i = 0;

    $group_tmp = '';
	$is_table = 0;
	$space = 0;

	while ($ticket=hesk_dbFetchAssoc($result))
	{

		if ($group)
        {
			require(HESK_PATH . 'inc/print_group.inc.php');
        }  // END if $group

        $color = '';

		$owner = '';
        $first_line = '(' . $hesklang['unas'] . ')'." \n\n";
		if ($ticket['owner'] == $_SESSION['id'])
		{
			$owner = '<span class="assignedyou" title="'.$hesklang['tasy2'].'"><span class="glyphicon glyphicon-user" data-toggle="tooltip" data-placement="top" title="'.$hesklang['tasy2'].'"></span></span> ';
            $first_line = $hesklang['tasy2'] . " \n\n";
		}
		elseif ($ticket['owner'])
		{
        	if (!isset($admins[$ticket['owner']]))
            {
            	$admins[$ticket['owner']] = $hesklang['e_udel'];
            }
			$owner = '<span class="assignedother" title="'.$hesklang['taso3'] . ' ' . $admins[$ticket['owner']] .'"><span class="glyphicon glyphicon-user" data-toggle="tooltip" data-placement="top" title="'.$hesklang['taso3'].' '.$admins[$ticket['owner']].'"></span></span> ';
            $first_line = $hesklang['taso3'] . ' ' . $admins[$ticket['owner']] . " \n\n";
		}

        $tagged = '';
        if ($ticket['archive'])
        {
			$tagged = '<i class="fa fa-tag" data-toggle="tooltip" data-placement="top" title="'.$hesklang['archived2'].'"></i> ';
        }

        $statusName = hesk_dbFetchAssoc(hesk_dbQuery("SELECT `ShortNameContentKey`, `TextColor` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` WHERE ID = ".$ticket['status']));
        $ticket['status']='<span style="color: '.$statusName['TextColor'].'">'.$hesklang[$statusName['ShortNameContentKey']].'</span>';

		switch ($ticket['priority'])
		{
			case 0:
				$ticket['priority']='<span style="color: red; font-size:1.3em" class="glyphicon glyphicon-flag" data-toggle="tooltip" data-placement="top" title="'.$hesklang['critical'].'"></span>';
                $color = 'danger';
				break;
			case 1:
				$ticket['priority']='<span style="color: orange; font-size:1.3em" class="glyphicon glyphicon-flag" data-toggle="tooltip" data-placement="top" title="'.$hesklang['high'].'"></span>';
				$color = 'warning';
                break;
			case 2:
				$ticket['priority']='<span style="color: green; font-size:1.3em" class="glyphicon glyphicon-flag" data-toggle="tooltip" data-placement="top" title="'.$hesklang['medium'].'"></span>';
				break;
			default:
				$ticket['priority']='<span style="color: blue; font-size:1.3em" class="glyphicon glyphicon-flag" data-toggle="tooltip" data-placement="top" title="'.$hesklang['low'].'"></span>';
		}

        $ticket['lastchange']=hesk_time_since(strtotime($ticket['lastchange']));

		if ($ticket['lastreplier'])
		{
			$ticket['repliername'] = isset($admins[$ticket['replierid']]) ? $admins[$ticket['replierid']] : $hesklang['staff'];
		}
		else
		{
			$ticket['repliername'] = $ticket['name'];
		}

		$ticket['archive'] = !($ticket['archive']) ? $hesklang['no'] : $hesklang['yes'];

		$ticket['message'] = $first_line . substr(strip_tags($ticket['message']),0,200).'...';
        $ownerColumn = $ticket['owner'] != 0 ? $admins[$ticket['owner']] : '('.$hesklang['unas'].')';
        
        $customFieldsHtml = '';
        for ($i = 1; $i <= 20; $i++) {
            if ($hesk_settings['custom_fields']['custom'.$i]['use']) {
                $display = 'display: none';
                if ((isset($_GET['sort']) && $_GET['sort'] == 'custom'.$i) || (isset($_GET['what']) && $_GET['what'] == 'custom'.$i)) {
                    $display = '';
                }
                $customFieldsHtml .= '<td style="'.$display.'" class="column_columnCustom'.$i.'">'.$ticket['custom'.$i].'</td>';
            }
        }
        

		echo <<<EOC
		<tr class="$color" id="$ticket[id]" title="$ticket[message]">
		<td><input type="checkbox" id="check$ticket[id]" name="id[]" value="$ticket[id]" />&nbsp;</td>
		<td class="column_trackID"><a href="admin_ticket.php?track=$ticket[trackid]&amp;Refresh=$random">$ticket[trackid]</a></td>
		<td class="column_last_update">$ticket[lastchange]</td>
		<td class="column_name">$ticket[name]</td>
		<td class="column_subject">$tagged$owner<a href="admin_ticket.php?track=$ticket[trackid]&amp;Refresh=$random">$ticket[subject]</a></td>
		<td class="column_status">$ticket[status]&nbsp;</td>
		<td class="column_lastreplier">$ticket[repliername]</td>
		<td class="column_priority">$ticket[priority]</td>
        <td class="column_owner" style="display: none">$ownerColumn</td>
        $customFieldsHtml
		</tr>

EOC;
	} // End while
	?>
	</table>
	</div>

    &nbsp;<br />
    <?php 
    $columnOneCheckboxes = array();
    $columnTwoCheckboxes = array();
    $columnThreeCheckboxes = array();
    $currentColumn = 3;
    
    for ($i = 1; $i <= 20; $i++) {
        if ($hesk_settings['custom_fields']['custom'.$i]['use']) {
            if ($currentColumn == 1) {
                array_push($columnOneCheckboxes, $i);
                $currentColumn = 2;
            } elseif ($currentColumn == 2) {
                array_push($columnTwoCheckboxes, $i);
                $currentColumn = 3;
            } else {
                array_push($columnThreeCheckboxes, $i);
                $currentColumn = 1;
            }
        }
    }
    ?>
    <table border="0" width="100%">
    <tr>
    <td width="50%" style="vertical-align:top">
        <h6 id="showFiltersText" style="font-weight: bold"><a href="javascript:void(0)" onclick="toggleFilterCheckboxes(true)"><?php echo $hesklang['show_filters']; ?></a></h6>
        <h6 id="hideFiltersText" style="font-weight: bold; display: none"><a href="javascript:void(0)" onclick="toggleFilterCheckboxes(false)"><?php echo $hesklang['hide_filters']; ?></a></h6>
        <div id="filterCheckboxes" style="display: none" class="row">
            <div class="col-md-4 col-sm-12">
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_trackID')" checked> <?php echo $hesklang['trackID']; ?>
                </div><br>
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_subject')" checked> <?php echo $hesklang['subject']; ?>
                </div><br>
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_priority')" checked> <?php echo $hesklang['priority']; ?>
                </div>
                <?php 
                    foreach ($columnOneCheckboxes as $i) {
                        $checked = '';
                        if ((isset($_GET['sort']) && $_GET['sort'] == 'custom'.$i) || (isset($_GET['what']) && $_GET['what'] == 'custom'.$i)) {
                            $checked = 'checked';
                        }
                        echo '<br><div class="checkbox">
                            <input type="checkbox" onclick="toggleColumn(\'column_columnCustom'.$i.'\')" '.$checked.'>
                                '.$hesk_settings['custom_fields']['custom'.$i]['name'].'</div>';
                    }
                ?>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_last_update')" checked> <?php echo $hesklang['last_update']; ?>
                </div><br>
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_status')" checked> <?php echo $hesklang['status']; ?>
                </div><br>
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_owner')"> <?php echo $hesklang['owner']; ?>
                </div>
                <?php 
                    foreach ($columnTwoCheckboxes as $i) {
                        $checked = '';
                        if ((isset($_GET['sort']) && $_GET['sort'] == 'custom'.$i) || (isset($_GET['what']) && $_GET['what'] == 'custom'.$i)) {
                            $checked = 'checked';
                        }
                        echo '<br><div class="checkbox">
                            <input type="checkbox" onclick="toggleColumn(\'column_columnCustom'.$i.'\')" '.$checked.'>
                                '.$hesk_settings['custom_fields']['custom'.$i]['name'].'</div>';
                    }
                ?>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_name')" checked> <?php echo $hesklang['name']; ?>
                </div><br>
                <div class="checkbox">
                    <input type="checkbox" onclick="toggleColumn('column_lastreplier')" checked> <?php echo $hesklang['last_replier']; ?>
                </div>
                <?php 
                    foreach ($columnThreeCheckboxes as $i) {
                        $checked = '';
                        if ((isset($_GET['sort']) && $_GET['sort'] == 'custom'.$i) || (isset($_GET['what']) && $_GET['what'] == 'custom'.$i)) {
                            $checked = 'checked';
                        }
                        echo '<br><div class="checkbox">
                            <input type="checkbox" onclick="toggleColumn(\'column_columnCustom'.$i.'\')" '.$checked.'>
                                '.$hesk_settings['custom_fields']['custom'.$i]['name'].'</div>';
                    }
                ?>
            </div>
        </div>
    </td>
    <td width="50%" class="text-right" style="vertical-align:top">
		<select class="form-control" name="a">
		<option value="close" selected="selected"><?php echo $hesklang['close_selected']; ?></option>
		<?php
		if ( hesk_checkPermission('can_add_archive', 0) )
		{
			?>
			<option value="tag"><?php echo $hesklang['add_archive_quick']; ?></option>
			<option value="untag"><?php echo $hesklang['remove_archive_quick']; ?></option>
			<?php
		}

		if ( ! defined('HESK_DEMO') )
		{

			if ( hesk_checkPermission('can_merge_tickets', 0) )
			{
				?>
				<option value="merge"><?php echo $hesklang['mer_selected']; ?></option>
				<?php
			}
			if ( hesk_checkPermission('can_del_tickets', 0) )
			{
				?>
				<option value="delete"><?php echo $hesklang['del_selected']; ?></option>
				<?php
			}

		} // End demo
		?>
		</select>
		<input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
		<input class="btn btn-default" type="submit" value="<?php echo $hesklang['execute']; ?>" />
    </td>
    </tr>
    </table>

	</form>
	<?php

} // END ticket list if total > 0
else
{
    echo '<div class="row"><div class="col-sm-12">';
    $autorefreshInSeconds = $_SESSION['autorefresh']/1000;
    
    if ($autorefreshInSeconds > 999) {
        echo $hesklang['autorefresh'].' '.$autorefreshInSeconds.' '.$hesklang['abbr']['second'];
    }
    
    if (isset($is_search) || $href == 'find_tickets.php')
    {
        hesk_show_notice($hesklang['no_tickets_crit'].'<span style="float: right"><a href="new_ticket.php">'.$hesklang['nti'].'</a></span>');
    }
    else
    {
        hesk_show_notice($hesklang['no_tickets_open'].'<span style="float: right"><a href="new_ticket.php">'.$hesklang['nti'].'</a></span>');
    }
    
    echo '</div></div>';
}
echo '</div>
    </div>';


function hesk_print_list_head()
{
	global $href, $query, $sort_possible, $hesklang, $hesk_settings;
	?>
	<div align="center">
	<table id="ticket-table" class="table table-hover">
        <thead>
	        <tr>
	        <th><input type="checkbox" id="checkall" name="checkall" value="2" onclick="hesk_changeAll(this)" /></th>
	        <th class="column_trackID"><a href="<?php echo $href . '?' . $query . $sort_possible['trackid'] . '&amp;sort='; ?>trackid"><?php echo $hesklang['trackID']; ?></a></th>
	        <th class="column_last_update"><a href="<?php echo $href . '?' . $query . $sort_possible['lastchange'] . '&amp;sort='; ?>lastchange"><?php echo $hesklang['last_update']; ?></a></th>
	        <th class="column_name"><a href="<?php echo $href . '?' . $query . $sort_possible['name'] . '&amp;sort='; ?>name"><?php echo $hesklang['name']; ?></a></th>
	        <th class="column_subject"><a href="<?php echo $href . '?' . $query . $sort_possible['subject'] . '&amp;sort='; ?>subject"><?php echo $hesklang['subject']; ?></a></th>
	        <th class="column_status"><a href="<?php echo $href . '?' . $query . $sort_possible['status'] . '&amp;sort='; ?>status"><?php echo $hesklang['status']; ?></a></th>
	        <th class="column_lastreplier"><a href="<?php echo $href . '?' . $query . $sort_possible['lastreplier'] . '&amp;sort='; ?>lastreplier"><?php echo $hesklang['last_replier']; ?></a></th>
	        <th class="column_priority"><a href="<?php echo $href . '?' . $query . $sort_possible['priority'] . '&amp;sort='; ?>priority"><i class="fa fa-sort-<?php echo (($sort_possible['priority']) ? 'asc' : 'desc'); ?>"></i></a></th>
            <!-- All other fields, hidden by default. -->
            <th class="column_owner" style="display: none"><a href="<?php echo $href . '?' . $query . $sort_possible['priority'] . '&amp;sort='; ?>owner"><?php echo $hesklang['owner']; ?></a></th>
            <?php
            for ($i = 1; $i <= 20; $i++) {
                if ($hesk_settings['custom_fields']['custom'.$i]['use']) {
                    $display = 'display: none';
                    if ((isset($_GET['sort']) && $_GET['sort'] == 'custom'.$i) || (isset($_GET['what']) && $_GET['what'] == 'custom'.$i)) {
                        $display = '';
                    }
                    echo '<th style="'.$display.'" class="column_columnCustom'.$i.'"><a href="'.$href . '?' . $query . $sort_possible['priority'] . '&amp;sort=custom'.$i.'">'.$hesk_settings['custom_fields']['custom'.$i]['name'].'</a></th>';
                }
            }
            ?>
	        </tr>
        </thead>
	<?php
} // END hesk_print_list_head()


function hesk_time_since($original)
{
	global $hesk_settings, $hesklang, $mysql_time;

    /* array of time period chunks */
    $chunks = array(
        array(60 * 60 * 24 * 365 , $hesklang['abbr']['year']),
        array(60 * 60 * 24 * 30 , $hesklang['abbr']['month']),
        array(60 * 60 * 24 * 7, $hesklang['abbr']['week']),
        array(60 * 60 * 24 , $hesklang['abbr']['day']),
        array(60 * 60 , $hesklang['abbr']['hour']),
        array(60 , $hesklang['abbr']['minute']),
        array(1 , $hesklang['abbr']['second']),
    );

	/* Invalid time */
    if ($mysql_time < $original)
    {
    	// DEBUG return "T: $mysql_time (".date('Y-m-d H:i:s',$mysql_time).")<br>O: $original (".date('Y-m-d H:i:s',$original).")";
        return "0".$hesklang['abbr']['second'];
    }

    $since = $mysql_time - $original;

    // $j saves performing the count function each time around the loop
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {

        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];

        // finding the biggest chunk (if the chunk fits, break)
        if (($count = floor($since / $seconds)) != 0) {
            // DEBUG print "<!-- It's $name -->\n";
            break;
        }
    }

    $print = "$count{$name}";

    if ($i + 1 < $j) {
        // now getting the second item
        $seconds2 = $chunks[$i + 1][0];
        $name2 = $chunks[$i + 1][1];

        // add second item if it's greater than 0
        if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
            $print .= "$count2{$name2}";
        }
    }
    return $print;
} // END hesk_time_since()
