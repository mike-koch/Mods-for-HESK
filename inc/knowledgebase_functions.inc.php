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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {die('Invalid attempt');} 

/*** FUNCTIONS ***/

function hesk_kbArticleContentPreview($txt)
{
	global $hesk_settings;

	// Strip HTML tags
	$txt = strip_tags($txt);

	// If text is larger than article preview length, shorten it
	if (strlen($txt) > $hesk_settings['kb_substrart'])
	{
		// The quick but not 100% accurate way (number of chars displayed may be lower than the limit)
		return substr($txt, 0, $hesk_settings['kb_substrart']) . '...';

		// If you want a more accurate, but also slower way, use this instead
		// return hesk_htmlentities( substr( hesk_html_entity_decode($txt), 0, $hesk_settings['kb_substrart'] ) ) . '...';
	}

	return $txt;
} // END hesk_kbArticleContentPreview()


function hesk_kbTopArticles($how_many, $index = 1)
{
	global $hesk_settings, $hesklang;

	// Index page or KB main page?
	if ($index)
	{
		// Disabled?
		if ( ! $hesk_settings['kb_index_popart'])
		{
			return true;
		}

		// Show title in italics
		$font_weight = 'i';
	}
	else
	{
		// Disabled?
		if ( ! $hesk_settings['kb_popart'])
		{
			return true;
		}

		// Show title in bold
		$font_weight = 'b';

		// Print a line for spacing
		echo '<hr />';
	}
	?>

    
	<h4 style="text-align: left"><?php echo $hesklang['popart']; ?></h4>
    <div class="footerWithBorder blankSpace"></div>

    <table border="0" width="100%">
	<tr>

	<?php
    /* Get list of articles from the database */
    $res = hesk_dbQuery("SELECT `t1`.* FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
			LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`sticky` DESC, `t1`.`views` DESC, `t1`.`art_order` ASC LIMIT ".intval($how_many));

    /* Show number of views? */
	if ($hesk_settings['kb_views'] && hesk_dbNumRows($res) != 0)
	{
		echo '<td style="text-align:right"><i>' . $hesklang['views'] . '</i></td>';
	}
	?>

	</tr>
	</table>

	<?php
    /* Get list of articles from the database */
    $res = hesk_dbQuery("SELECT `t1`.* FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
			LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`sticky` DESC, `t1`.`views` DESC, `t1`.`art_order` ASC LIMIT ".intval($how_many));

	/* If no results found end here */
	if (hesk_dbNumRows($res) == 0)
	{
		echo '<p style="text-align: left;"><i>'.$hesklang['noa'].'</i><br />&nbsp;</p>';
        return true;
	}

	/* We have some results, print them out */
	?>
    <div align="left">
    <table border="0" cellspacing="1" cellpadding="3" width="100%">
    <?php

	while ($article = hesk_dbFetchAssoc($res))
	{
		echo '
		<tr>
		<td>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<td width="1" valign="top"><span class="glyphicon glyphicon-file"></span></td>
		<td valign="top">&nbsp;<a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a></td>
		';

		if ($hesk_settings['kb_views'])
		{
			echo '<td valign="top" style="text-align:right" width="200">' . $article['views'] . '</td>';
		}

		echo '
		</tr>
		</table>
		</td>
		</tr>
		';
	}
	?>

    </table>
    </div>
    <br/>
    <?php
} // END hesk_kbTopArticles()


function hesk_kbLatestArticles($how_many, $index = 1)
{
	global $hesk_settings, $hesklang;

	// Index page or KB main page?
	if ($index)
	{
		// Disabled?
		if ( ! $hesk_settings['kb_index_latest'])
		{
			return true;
		}

		// Show title in italics
		$font_weight = 'i';
	}
	else
	{
		// Disabled?
		if ( ! $hesk_settings['kb_latest'])
		{
			return true;
		}

		// Show title in bold
		$font_weight = 'b';

		// Print a line for spacing if we don't show popular articles
		if (  ! $hesk_settings['kb_popart'])
		{
			echo '<hr />';
		}
	}
	?>

	<h4 style="text-align: left"><?php echo $hesklang['latart']; ?></h4>
    <div class="footerWithBorder blankSpace"></div>

    <table border="0" width="100%">
    <tr>
	<?php
    /* Get list of articles from the database */
    $res = hesk_dbQuery("SELECT `t1`.* FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
			LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`dt` DESC LIMIT ".intval($how_many));

    /* Show number of views? */
	if ($hesk_settings['kb_date'] && hesk_dbNumRows($res) != 0)
	{
		echo '<td style="text-align:right"><i>' . $hesklang['dta'] . '</i></td>';
	}
	?>

	</tr>
	</table>

	<?php
    /* Get list of articles from the database */
    $res = hesk_dbQuery("SELECT `t1`.* FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
			LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`dt` DESC LIMIT ".intval($how_many));

	/* If no results found end here */
	if (hesk_dbNumRows($res) == 0)
	{
		echo '<p style="text-align: left"><i>'.$hesklang['noa'].'</i><br />&nbsp;</p>';
        return true;
	}

	/* We have some results, print them out */
	?>
    <div align="center">
    <table border="0" cellspacing="1" cellpadding="3" width="100%">
    <?php

	while ($article = hesk_dbFetchAssoc($res))
	{
		echo '
		<tr>
		<td>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<td width="1" valign="top"><span class="glyphicon glyphicon-file"></span></td>
		<td valign="top">&nbsp;<a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a></td>
		';

		if ($hesk_settings['kb_date'])
		{
			echo '<td valign="top" style="text-align:right" width="200">' . hesk_date($article['dt'], true) . '</td>';
		}

		echo '
		</tr>
		</table>
		</td>
		</tr>
		';
	}
	?>

    </table>
    </div>

    &nbsp;

    <?php
} // END hesk_kbLatestArticles()


function hesk_kbSearchLarge($admin = '')
{
	global $hesk_settings, $hesklang;

	if ($hesk_settings['kb_search'] != 2)
	{
		return '';
	}

    $action = $admin ? 'knowledgebase_private.php' : 'knowledgebase.php';

	?>

	<div style="text-align:center">
		<form role="form" action="<?php echo $action; ?>" method="get" style="display: inline; margin: 0;" name="searchform">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?php echo $hesklang['search_the_knowledgebase']; ?>" name="search">
				<span class="input-group-btn">
					<button class="btn btn-default" type="submit" value="<?php echo $hesklang['search']; ?>" title="<?php echo $hesklang['search']; ?>"><?php echo $hesklang['search']; ?></button>
				</span>
			</div>
			<!--<span class="largebold"><?php echo $hesklang['ask']; ?></span>
			<input type="text" name="search" class="searchfield" />
			<input type="submit" value="<?php echo $hesklang['search']; ?>" title="<?php echo $hesklang['search']; ?>" class="searchbutton" /><br />
		--></form>
	</div>

	<br />

	<!-- START KNOWLEDGEBASE SUGGEST -->
		<div id="kb_suggestions" style="display:none">
			<img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0" style="vertical-align:text-bottom" /> <i><?php echo $hesklang['lkbs']; ?></i>
		</div>

		<script language="Javascript" type="text/javascript"><!--
		hesk_suggestKBsearch(<?php echo $admin; ?>);
		//-->
		</script>
	<!-- END KNOWLEDGEBASE SUGGEST -->

	<br />

	<?php
} // END hesk_kbSearchLarge()


function hesk_kbSearchSmall()
{
	global $hesk_settings, $hesklang;

	if ($hesk_settings['kb_search'] != 1)
	{
		return '';
	}
    ?>

	<td style="text-align:right" valign="top" width="300">
		<div style="display:inline;margin-left:auto;margin-right:auto">
			<form action="knowledgebase.php" method="get" class="form-inline" style="display: inline; margin: 0;">
			     <input type="text" name="search" class="form-control" />
			     <button class="btn btn-default" type="submit" value="<?php echo $hesklang['search_the_knowledgebase']; ?>" title="<?php echo $hesklang['search_the_knowledgebase']; ?>">
                     <?php echo $hesklang['search_the_knowledgebase']; ?>
                 </button>
			</form>
            <br><br>
		</div>
	</td>

	<?php
} // END hesk_kbSearchSmall()


function hesk_detect_bots()
{
	$botlist = array('googlebot', 'msnbot', 'slurp', 'alexa', 'teoma', 'froogle',
	'gigabot', 'inktomi', 'looksmart', 'firefly', 'nationaldirectory',
	'ask jeeves', 'tecnoseek', 'infoseek', 'webfindbot', 'girafabot',
	'crawl', 'www.galaxy.com', 'scooter', 'appie', 'fast', 'webbug', 'spade', 'zyborg', 'rabaz',
	'baiduspider', 'feedfetcher-google', 'technoratisnoop', 'rankivabot',
	'mediapartners-google', 'crawler', 'spider', 'robot', 'bot/', 'bot-','voila');

	if ( ! isset($_SERVER['HTTP_USER_AGENT']))
    {
    	return false;
    }

    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

	foreach ($botlist as $bot)
    {
    	if (strpos($ua,$bot) !== false)
        {
        	return true;
        }
    }

	return false;
} // END hesk_detect_bots()
