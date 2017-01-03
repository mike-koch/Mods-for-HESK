<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.6.8 from 10th August 2016
*  Author: Klemen Stirn
*  Website: https://www.hesk.com
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
define('PAGE_TITLE', 'ADMIN_KB');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/knowledgebase_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();


hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();
hesk_kb_preheader();


/* Is Knowledgebase enabled? */
if ( ! $hesk_settings['kb_enable'])
{
	hesk_error($hesklang['kbdis']);
}

/* Can this user manage Knowledgebase or just view it? */
$can_man_kb = hesk_checkPermission('can_man_kb',0);

/* Any category ID set? */
$catid = intval( hesk_GET('category', 1) );
$artid = intval( hesk_GET('article', 0) );


if (isset($_GET['search']))
{
	$query = hesk_input( hesk_GET('search') );
}
else
{
	$query = 0;
}

$hesk_settings['kb_link'] = ($artid || $catid != 1 || $query) ? '<a href="knowledgebase_private.php">'.$hesklang['gopr'].'</a>' : ($can_man_kb ? $hesklang['gopr'] : '');

if ($hesk_settings['kb_search'] && $query)
{
    if (hesk_kb_search($query)) {
		hesk_show_kb_category(1,1);
	}
}
elseif ($artid)
{
	// Show drafts only to staff who can manage knowledgebase
	if ($can_man_kb)
	{
		$result = hesk_dbQuery("SELECT t1.*, t2.`name` AS `cat_name`
		FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
		LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
		WHERE `t1`.`id` = '{$artid}'
		");
	}
	else
	{
		$result = hesk_dbQuery("SELECT t1.*, t2.`name` AS `cat_name`
		FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` AS `t1`
		LEFT JOIN `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
		WHERE `t1`.`id` = '{$artid}' AND `t1`.`type` IN ('0', '1')
		");
	}

    $article = hesk_dbFetchAssoc($result) or hesk_error($hesklang['kb_art_id']);
    hesk_show_kb_article($artid);
}
else
{
	hesk_show_kb_category($catid);
}

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/
function hesk_kb_preheader() {
	global $hesk_settings, $hesklang, $can_man_kb;

	/* Print admin navigation */
	require_once(HESK_PATH . 'inc/headerAdmin.inc.php');
	require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
}

function hesk_kb_header($kb_link, $catid=1)
{
	global $hesk_settings, $hesklang, $can_man_kb;
	?>

	<ol class="breadcrumb">
    <?php
    if ($can_man_kb)
    {
	    ?>
	    <li><a href="manage_knowledgebase.php"><?php echo $hesklang['kb']; ?></a></li>
	    <?php
    }
    ?>
	<li class="active"><?php echo $kb_link; ?></li>
    </ol>
	<?php
		show_subnav('view', $catid);
		hesk_kbSearchLarge(1);
} // END hesk_kb_header()


function hesk_kb_search($query)
{
	global $hesk_settings, $hesklang;

    define('HESK_NO_ROBOTS',1);

    $res = hesk_dbQuery('SELECT t1.`id`, t1.`subject`, LEFT(`t1`.`content`, '.max(200, $hesk_settings['kb_substrart'] * 2).') AS `content`, t1.`rating` FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_articles` AS t1 LEFT JOIN `'.hesk_dbEscape($hesk_settings['db_pfix']).'kb_categories` AS t2 ON t1.`catid` = t2.`id` '." WHERE t1.`type` IN ('0','1') AND MATCH(`subject`,`content`,`keywords`) AGAINST ('".hesk_dbEscape($query)."') LIMIT ".intval($hesk_settings['kb_search_limit']));
    $num = hesk_dbNumRows($res);
	$show_default_category = false;
    ?>
	<div class="content-wrapper">
		<?php hesk_kb_header($hesk_settings['kb_link']); ?>
		<section style="padding: 15px">
			<div class="box">
				<div class="box-header with-border">
					<h1 class="box-title">
						<?php echo $hesklang['sr']; ?> (<?php echo $num; ?>)
					</h1>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
					<?php
					if ($num == 0) {
						echo '<i>'.$hesklang['nosr'].'</i>';
						$show_default_category = true;
					} else {
						?>
						<table class="table table-striped">
							<?php
							while ($article = hesk_dbFetchAssoc($res))
							{
								$txt = hesk_kbArticleContentPreview($article['content']);

								if ($hesk_settings['kb_rating'])
								{
									$rat = '<td width="1" valign="top">' . mfh_get_stars($article['rating']) . '</td>';
								}
								else
								{
									$rat = '';
								}

								echo '
					<tr>
					<td>
						<table border="0" width="100%" cellspacing="0" cellpadding="1">
						<tr>
						<td width="1" valign="top"><span class="glyphicon glyphicon-file"></span></td>
						<td valign="top"><a href="knowledgebase_private.php?article='.$article['id'].'">'.$article['subject'].'</a></td>
						'.$rat.'
						</tr>
						</table>
						<table border="0" width="100%" cellspacing="0" cellpadding="1">
						<tr>
						<td width="1" valign="top"><img src="../img/blank.gif" width="16" height="10" style="vertical-align:middle" alt="" /></td>
						<td><span class="article_list">'.$txt.'</span></td>
						</tr>
						</table>

					</td>
					</tr>';
							}
							?>
						</table>
						<a href="javascript:history.go(-1)"><span class="glyphicon glyphicon-circle-arrow-left"></span>&nbsp;<?php echo $hesklang['back']; ?></a>
					<?php } ?>
				</div>
			</div>
		</section>
	</div>
    <?php
	return $show_default_category;
} // END hesk_kb_search()


function hesk_show_kb_article($artid)
{
	global $hesk_settings, $hesklang, $article;

	// Print header
    $hesk_settings['tmp_title'] = $article['subject'];

    // Update views by 1
    hesk_dbQuery('UPDATE `'.hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` SET `views`=`views`+1 WHERE `id`={$artid}");

?>
<div class="content-wrapper">
	<?php hesk_kb_header($hesk_settings['kb_link'], $article['catid']); ?>
	<section class="content">
		<div class="box">
			<div class="box-header with-border">
				<h1 class="box-title">
					<?php echo $article['subject']; ?>
				</h1>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse">
						<i class="fa fa-minus"></i>
					</button>
				</div>
			</div>
			<div class="box-body">
				<?php echo $article['content']; ?>
			</div>
			<?php if (!empty($article['attachments'])): ?>
			<div class="box-footer">
				<p><b><?php echo $hesklang['attachments']; ?></b></p>
				<?php
				$att=explode(',',substr($article['attachments'], 0, -1));
				foreach ($att as $myatt)
				{
					list($att_id, $att_name) = explode('#', $myatt);
					echo '<i class="fa fa-paperclip"></i> <a href="../download_attachment.php?kb_att='.$att_id.'" rel="nofollow">'.$att_name.'</a><br />';
				}
				?>
			</div>
			<?php endif; ?>
		</div>
		<?php

		if ($article['catid']==1)
		{
			$link = 'knowledgebase_private.php';
		}
		else
		{
			$link = 'knowledgebase_private.php?category='.$article['catid'];
		}
		?>
		<br><br>
		<div class="row">
			<?php
			$showRelated = false;
			$column = 'col-md-12';
			require(HESK_PATH . 'inc/mail/email_parser.php');

			$query = hesk_dbEscape( $article['subject'] . ' ' . convert_html_to_text($article['content']) );

			// Get relevant articles from the database
			$res = hesk_dbQuery("SELECT `id`, `subject`, MATCH(`subject`,`content`,`keywords`) AGAINST ('{$query}') AS `score` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` WHERE `type` IN ('0','1') AND MATCH(`subject`,`content`,`keywords`) AGAINST ('{$query}') LIMIT ".intval($hesk_settings['kb_related']+1));

			// Array with related articles
			$related_articles = array();

			while ($related = hesk_dbFetchAssoc($res))
			{
				// Get base match score from the first (this) article
				if ( ! isset($base_score) )
				{
					$base_score = $related['score'];
				}

				// Ignore this article
				if ($related['id'] == $artid)
				{
					continue;
				}

				// Stop when articles reach less than 10% of base score
				if ($related['score'] / $base_score < 0.10)
				{
					break;
				}

				// This is a valid related article
				$related_articles[$related['id']] = $related['subject'];
			}

			// Print related articles if we have any valid matches
			if ( count($related_articles) ) {
				$column = 'col-md-6';
				$showRelated = true;
			}
			?>
			<div class="<?php echo $column; ?> col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h1 class="box-title">
							<?php echo $hesklang['ad']; ?>
						</h1>
						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-widget="collapse">
								<i class="fa fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="box-body">
						<table border="0">
							<tr>
								<td><?php echo $hesklang['aid']; ?>: </td>
								<td>
									<?php
									echo $article['id'];
									if ($article['type'] == 0)
									{
										echo ' [<a href="' . $hesk_settings['hesk_url'] . '/knowledgebase.php?article=' . $article['id'] . '">' . $hesklang['public_link'] . '</a>]';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><?php echo $hesklang['category']; ?>: </td>
								<td><a href="<?php echo $link; ?>"><?php echo $article['cat_name']; ?></a></td>
							</tr>
							<tr>
								<td><?php echo $hesklang['dta']; ?>: </td>
								<td><?php echo hesk_date($article['dt'], true); ?></td>
							</tr>
							<tr>
								<td><?php echo $hesklang['views']; ?>: </td>
								<td><?php echo (isset($_GET['rated']) ? $article['views'] : $article['views']+1); ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<?php if ($showRelated) { ?>
				<div class="col-md-6 col-sm-12">
					<div class="box">
						<div class="box-header with-border">
							<h1 class="box-title">
								<?php echo $hesklang['relart']; ?>
							</h1>
							<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse">
									<i class="fa fa-minus"></i>
								</button>
							</div>
						</div>
						<div class="box-body">
							<?php
							// Related articles
							foreach ($related_articles as $id => $subject)
							{
								echo '<span class="glyphicon glyphicon-file" style="font-size: 16px;"></span> <a href="knowledgebase_private.php?article='.$id.'">'.$subject.'</a><br />';
							}
							?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php
		if (!isset($_GET['back']))
		{
			?>
			<p><br /><a href="javascript:history.go(-1)"><span class="glyphicon glyphicon-circle-arrow-left"></span>&nbsp;<?php echo $hesklang['back']; ?></a></p>
			<?php
		}
		?>
	</section>
</div>
<?php

} // END hesk_show_kb_article()


function hesk_show_kb_category($catid, $is_search = 0) {
	global $hesk_settings, $hesklang;

    $res = hesk_dbQuery("SELECT `name`,`parent` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` WHERE `id`='".intval($catid)."' LIMIT 1");
    $thiscat = hesk_dbFetchAssoc($res) or hesk_error($hesklang['kb_cat_inv']);

?>
<div class="content-wrapper">
	<?php
	if ($is_search == 0)
	{
		/* Print header */
		hesk_kb_header($hesk_settings['kb_link'], $catid);
	} ?>
	<section class="content">
		<?php if ($thiscat['parent']): ?>
			<h3><?php echo $hesklang['kb_cat'].': '.$thiscat['name']; ?></h3>
        	<p align="left"><a href="javascript:history.go(-1)">
					<span class="glyphicon glyphicon-circle-arrow-left"></span>
					<?php echo $hesklang['back']; ?>
			</a></p>
		<?php
		endif;

		$result = hesk_dbQuery("SELECT `id`,`name`,`articles`,`type` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_categories` WHERE `parent`='".intval($catid)."' ORDER BY `parent` ASC, `cat_order` ASC");

		if (hesk_dbNumRows($result) > 0) {
		?>
		<div class="box">
			<div class="box-header with-border">
				<h1 class="box-title">
					<?php echo $hesklang['kb_cat_sub']; ?>
				</h1>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse">
						<i class="fa fa-minus"></i>
					</button>
				</div>
			</div>
			<div class="box-body">
				<table class="table table-striped">
					<?php
					$per_col = $hesk_settings['kb_cols'];
					$i = 1;

					while ($cat = hesk_dbFetchAssoc($result))
					{

						if ($i == 1)
						{
							echo '<tr>';
						}

						$private = ($cat['type'] == 1) ? ' *' : '';

						echo '
		    <td width="50%" valign="top">
			<table border="0">
			<tr><td><span class="glyphicon glyphicon-folder-close"></span>&nbsp;<a href="knowledgebase_private.php?category='.$cat['id'].'">'.$cat['name'].'</a>'.$private.'</td></tr>
			';

						/* Print most popular/sticky articles */
						if ($hesk_settings['kb_numshow'] && $cat['articles'])
						{
							$res = hesk_dbQuery("SELECT `id`,`subject`,`type` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` WHERE `catid`='".intval($cat['id'])."' AND `type` IN ('0','1') ORDER BY `sticky` DESC, `views` DESC, `art_order` ASC LIMIT " . (intval($hesk_settings['kb_numshow']) + 1) );
							$num = 1;
							while ($art = hesk_dbFetchAssoc($res))
							{
								$private = ($art['type'] == 1) ? ' *' : '';
								echo '
		            <tr>
		            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-file"></span>
		            <a href="knowledgebase_private.php?article='.$art['id'].'" class="article">'.$art['subject'].'</a>'.$private.'</td>
		            </tr>';

								if ($num == $hesk_settings['kb_numshow'])
								{
									break;
								}
								else
								{
									$num++;
								}
							}
							if (hesk_dbNumRows($res) > $hesk_settings['kb_numshow'])
							{
								echo '<tr><td>&raquo; <a href="knowledgebase_private.php?category='.$cat['id'].'"><i>'.$hesklang['m'].'</i></a></td></tr>';
							}
						}

						echo '
			</table>
		    </td>
			';

						if ($i == $per_col)
						{
							echo '</tr>';
							$i = 0;
						}
						$i++;
					}
					/* Finish the table if needed */
					if ($i != 1)
					{
						for ($j=1;$j<=$per_col;$j++)
						{
							echo '<td width="50%">&nbsp;</td>';
							if ($i == $per_col)
							{
								echo '</tr>';
								break;
							}
							$i++;
						}
					}

					?>
				</table>
			</div>
			<div class="box-footer">
				<?php echo $hesklang['private_category_star']; ?>
			</div>
		</div>
		<?php } ?>
		<div class="box">
			<div class="box-header with-border">
				<h1 class="box-title">
					<?php echo $hesklang['ac']; ?>
				</h1>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse">
						<i class="fa fa-minus"></i>
					</button>
				</div>
			</div>
			<div class="box-body">
				<?php
				$res = hesk_dbQuery("SELECT `id`, `subject`, LEFT(`content`, ".max(200, $hesk_settings['kb_substrart'] * 2).") AS `content`, `rating`, `type` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."kb_articles` WHERE `catid`='".intval($catid)."' AND `type` IN ('0','1') ORDER BY `sticky` DESC, `art_order` ASC");
				if (hesk_dbNumRows($res) == 0)
				{
					echo '<i>'.$hesklang['noac'].'</i>';
				}
				else
				{
					echo '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
					while ($article = hesk_dbFetchAssoc($res))
					{
						$private = ($article['type'] == 1) ? ' *' : '';

						$txt = hesk_kbArticleContentPreview($article['content']);

						echo '
				<tr>
				<td>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><span class="glyphicon glyphicon-file"></span></td>
	                <td valign="top"><a href="knowledgebase_private.php?article='.$article['id'].'">'.$article['subject'].'</a>'.$private.'</td>
                    </tr>
	                </table>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><img src="../img/blank.gif" width="16" height="10" style="vertical-align:middle" alt="" /></td>
	                <td><span class="article_list">'.$txt.'</span></td>
                    </tr>
	                </table>
	            </td>
				</tr>';
					}
					echo '</table>';
				}
				?>
			</div>
			<div class="box-footer">
				<?php echo $hesklang['private_article_star']; ?>
			</div>
		</div>
	</section>
</div>
<?php
} // END hesk_show_kb_category()


function show_subnav($hide='', $catid=1)
{
	global $hesk_settings, $hesklang, $can_man_kb, $artid;

    if ( ! $can_man_kb)
    {
    	echo '';
        return true;
    }

    $catid = intval($catid);

    echo '<div style="margin-left:40px;margin-right:40px">';

    $link['view'] = '<a href="knowledgebase_private.php"><i class="fa fa-search" style="font-size:16px"></i></a> <a href="knowledgebase_private.php">'.$hesklang['gopr'].'</a> | ';
    $link['newa'] = '<a href="manage_knowledgebase.php?a=add_article&amp;catid='.$catid.'"><i class="fa fa-plus" style="color: green;font-size:16px"></i></a> <a href="manage_knowledgebase.php?a=add_article&amp;catid='.$catid.'">'.$hesklang['kb_i_art'].'</a> | ';
    $link['newc'] = '<a href="manage_knowledgebase.php?a=add_category&amp;parent='.$catid.'"><i class="fa fa-caret-right" style="font-size:18px; color:blue"></i></a> <a href="manage_knowledgebase.php?a=add_category&amp;parent='.$catid.'">'.$hesklang['kb_i_cat'].'</a> | ';

    if ($hide && isset($link[$hide]))
    {
    	$link[$hide] = preg_replace('/<a([^<]*)>/', '', $link[$hide]);
        $link[$hide] = str_replace('</a>','',$link[$hide]);
    }

	?>
	<form style="margin:0px;padding:0px;" method="get" action="manage_knowledgebase.php">
    <?php
    echo $link['view'];
    echo $link['newa'];
    echo $link['newc'];
    ?>
	<i class="fa fa-pencil" style="color:orange;font-size:16px"></i> <input type="hidden" name="a" value="edit_article" /><?php echo $hesklang['aid']; ?>: <input type="text" name="id" size="3" <?php if ($artid) echo 'value="' . $artid . '"'; ?> /> <input type="submit" value="<?php echo $hesklang['edit']; ?>" class="btn btn-default btn-xs" />
	</form>
    </div>

	<?php
} // End show_subnav()
?>