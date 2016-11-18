<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

define('IN_SCRIPT', 1);
define('HESK_PATH', './');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/knowledgebase_functions.inc.php');


// Load Knowledgebase-related functions
hesk_load_database_functions();

/* Connect to database */
hesk_dbConnect();

// Are we in maintenance mode?
hesk_check_maintenance();

define('PAGE_TITLE', 'CUSTOMER_KB');

/* Is Knowledgebase enabled? */
if (!$hesk_settings['kb_enable']) {
    hesk_error($hesklang['kbdis']);
}

/* Rating? */
if (isset($_GET['rating'])) {
    // Detect and block robots
    if (hesk_detect_bots()) {
        ?>
        <html>
        <head>
            <meta name="robots" content="noindex, nofollow">
        </head>
        <body>
        </body>
        </html>
        <?php
    }

    // Rating
    $rating = intval(hesk_GET('rating'));

    // Rating value may only be 1 or 5
    if ($rating != 1 && $rating != 5) {
        die($hesklang['attempt']);
    }

    // Article ID
    $artid = intval(hesk_GET('id', 0)) or die($hesklang['kb_art_id']);

    // Check cookies for already rated, rate and set cookie if not already
    $_COOKIE['hesk_kb_rate'] = hesk_COOKIE('hesk_kb_rate');

    if (strpos($_COOKIE['hesk_kb_rate'], 'a' . $artid . '%') === false) {
        // Update rating, make sure it's a public article in a public category
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
					LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON t1.`catid` = t2.`id`
					SET `rating`=((`rating`*`votes`)+{$rating})/(`votes`+1), t1.`votes`=t1.`votes`+1
					WHERE t1.`id`='{$artid}' AND t1.`type`='0' AND t2.`type`='0'
					");
    }

    hesk_setcookie('hesk_kb_rate', $_COOKIE['hesk_kb_rate'] . 'a' . $artid . '%', time() + 2592000);
    header('Location: knowledgebase.php?article=' . $artid . '&rated=1');
    exit();
}

/* Any category ID set? */
$catid = intval(hesk_GET('category', 1));
$artid = intval(hesk_GET('article', 0));

if (isset($_GET['search'])) {
    $query = hesk_input(hesk_GET('search'));
} else {
    $query = 0;
}

$hesk_settings['kb_link'] = ($artid || $catid != 1 || $query) ? '<a href="knowledgebase.php" class="smaller">' . $hesklang['kb_text'] . '</a>' : $hesklang['kb_text'];

if ($hesk_settings['kb_search'] && $query) {
    hesk_kb_search($query);
} elseif ($artid) {
    // Get article from DB, make sure that article and category are public
    $result = hesk_dbQuery("SELECT t1.*, t2.`name` AS `cat_name`
							FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
							LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
							WHERE `t1`.`id` = '{$artid}'
							AND `t1`.`type` = '0'
							AND `t2`.`type` = '0'
                            ");

    $article = hesk_dbFetchAssoc($result) or hesk_error($hesklang['kb_art_id']);
    hesk_show_kb_article($artid);
} else {
    hesk_show_kb_category($catid);
}
echo '</div>';
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/

function hesk_kb_header($kb_link) {
global $hesk_settings, $hesklang;
?>
<ol class="breadcrumb">
    <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
    <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
    <li class="active"><?php echo $hesklang['kb_text']; ?></li>
</ol>

<?php
$columnWidth = 'col-md-8';
$showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 4");
$show = hesk_dbFetchAssoc($showRs);
if (!$show['show']) {
    $columnWidth = 'col-md-10 col-md-offset-1';
}
?>
<div class="row">
    <?php if ($columnWidth == 'col-md-8'): ?>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo $hesklang['quick_help']; ?>
                </div>
                <div class="panel-body">
                    <p style="text-align: justify;"><?php echo $hesklang['kb_is']; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="<?php echo $columnWidth; ?>">
        <?php
        /* Print small search box */
        hesk_kbSearchSmall();

        /* Print large search box */
        hesk_kbSearchLarge();

        } // END hesk_kb_header()


        function hesk_kb_search($query)
        {
            global $hesk_settings, $hesklang;

            define('HESK_NO_ROBOTS', 1);

            /* Print header */
            $hesk_settings['tmp_title'] = $hesklang['sr'] . ': ' . substr(hesk_htmlspecialchars(stripslashes($query)), 0, 20);
            require_once(HESK_PATH . 'inc/header.inc.php');
            hesk_kb_header($hesk_settings['kb_link']);

            $res = hesk_dbQuery('SELECT t1.`id`, t1.`subject`, LEFT(`t1`.`content`, ' . max(200, $hesk_settings['kb_substrart'] * 2) . ') AS `content`, t1.`rating` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_articles` AS t1
    					LEFT JOIN `' . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS t2 ON t1.`catid` = t2.`id`
						WHERE t1.`type`='0' AND t2.`type`='0' AND  MATCH(`subject`,`content`,`keywords`) AGAINST ('" . hesk_dbEscape($query) . "') LIMIT " . intval($hesk_settings['kb_search_limit']));
            $num = hesk_dbNumRows($res);

            ?>
            <h4><?php echo $hesklang['sr']; ?> (<?php echo $num; ?>)</h4>
            <div class="footerWithBorder blankSpace"></div>

            <?php
            if ($num == 0) {
                echo '<p><i>' . $hesklang['nosr'] . '</i></p>
        <p>&nbsp;</p>
        ';
                hesk_show_kb_category(1, 1);
            } else {
                ?>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="roundcornersleft">&nbsp;</td>
                        <td>
                            <div align="center">
                                <table border="0" cellspacing="1" cellpadding="3" width="100%">
                                    <?php
                                    while ($article = hesk_dbFetchAssoc($res)) {
                                        $txt = hesk_kbArticleContentPreview($article['content']);

                                        if ($hesk_settings['kb_rating']) {
                                            $alt = $article['rating'] ? sprintf($hesklang['kb_rated'], sprintf("%01.1f", $article['rating'])) : $hesklang['kb_not_rated'];
                                            $rat = '<td width="1" valign="top"><img src="img/star_' . (hesk_round_to_half($article['rating']) * 10) . '.png" width="85" height="16" alt="' . $alt . '" border="0" style="vertical-align:text-bottom" /></td>';
                                        } else {
                                            $rat = '';
                                        }

                                        echo '
				<tr>
				<td>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><span class="glyphicon glyphicon-file"></span></td>
	                <td valign="top"><a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a></td>
	                ' . $rat . '
                    </tr>
	                </table>
	                <table border="0" width="100%" cellspacing="0" cellpadding="1">
	                <tr>
	                <td width="1" valign="top"><img src="img/blank.gif" width="16" height="10" style="vertical-align:middle" alt="" /></td>
	                <td><span class="article_list">' . $txt . '</span></td>
                    </tr>
	                </table>

	            </td>
				</tr>';
                                    }
                                    ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <p>&nbsp;<br/>&laquo; <a href="javascript:history.go(-1)"><?php echo $hesklang['back']; ?></a></p>
                <?php
            } // END else

        } // END hesk_kb_search()


        function hesk_show_kb_article($artid)
        {
            global $hesk_settings, $hesklang, $article;

            // Print header
            $hesk_settings['tmp_title'] = $article['subject'];
            require_once(HESK_PATH . 'inc/header.inc.php');
            hesk_kb_header($hesk_settings['kb_link']);

            // Update views by 1 - exclude known bots and reloads because of ratings
            if (!isset($_GET['rated']) && !hesk_detect_bots()) {
                hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` SET `views`=`views`+1 WHERE `id`={$artid}");
            }
            if (!isset($_GET['suggest'])) {
                $historyNumber = isset($_GET['rated']) ? '-2' : '-1';
                $goBackText = '<a href="javascript:history.go(' . $historyNumber . ')">
            <i class="fa fa-arrow-circle-left" data-toggle="tooltip" data-placement="top" title="' . $hesklang['back'] . '"></i></a>';
            } else {
                $goBackText = '';
            }

            echo '<h3 class="text-left">' . $goBackText . '&nbsp;' . $article['subject'] . '</h3>
    <div class="footerWithBorder blankSpace"></div>
    <h4 class="text-left">' . $hesklang['as'] . '</h4>
    <div class="kbContent">'
                . $article['content'] . '</div>';

            if (!empty($article['attachments'])) {
                echo '<p><b>' . $hesklang['attachments'] . ':</b><br />';
                $att = explode(',', substr($article['attachments'], 0, -1));
                foreach ($att as $myatt) {
                    list($att_id, $att_name) = explode('#', $myatt);
                    echo '<img src="img/clip.png" width="16" height="16" alt="' . $att_name . '" style="align:text-bottom" /> <a href="download_attachment.php?kb_att=' . $att_id . '" rel="nofollow">' . $att_name . '</a><br />';
                }
                echo '</p>';
            }

            // Article rating
            if ($hesk_settings['kb_rating'] && strpos(hesk_COOKIE('hesk_kb_rate'), 'a' . $artid . '%') === false) {
                echo '
	    <div id="rating" class="rate" align="right">&nbsp;<br />' . $hesklang['rart'] . '
			<a href="Javascript:void(0)" onclick="Javascript:window.location=\'knowledgebase.php?rating=5&amp;id=' . $article['id'] . '\'" rel="nofollow">' . strtolower($hesklang['yes']) . '</a> /
	        <a href="Javascript:void(0)" onclick="Javascript:window.location=\'knowledgebase.php?rating=1&amp;id=' . $article['id'] . '\'" rel="nofollow">' . strtolower($hesklang['no']) . '</a>
	    </div>
        ';
            }

            // Related articles
            if ($hesk_settings['kb_related']) {
                $showRelated = false;
                $column = 'col-md-12';
                require(HESK_PATH . 'inc/mail/email_parser.php');

                $query = hesk_dbEscape($article['subject'] . ' ' . convert_html_to_text($article['content']));

                // Get relevant articles from the database
                $res = hesk_dbQuery("SELECT t1.`id`, t1.`subject`, MATCH(`subject`,`content`,`keywords`) AGAINST ('{$query}') AS `score` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_articles` AS t1 LEFT JOIN `' . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS t2 ON t1.`catid` = t2.`id` WHERE t1.`type`='0' AND t2.`type`='0' AND MATCH(`subject`,`content`,`keywords`) AGAINST ('{$query}') LIMIT " . intval($hesk_settings['kb_related'] + 1));

                // Array with related articles
                $related_articles = array();

                while ($related = hesk_dbFetchAssoc($res)) {
                    // Get base match score from the first article
                    if (!isset($base_score)) {
                        $base_score = $related['score'];
                    }

                    // Ignore this article
                    if ($related['id'] == $artid) {
                        continue;
                    }

                    // Stop when articles reach less than 10% of base score
                    if ($related['score'] / $base_score < 0.10) {
                        break;
                    }

                    // This is a valid related article
                    $related_articles[$related['id']] = $related['subject'];
                }

                // Print related articles if we have any valid matches
                if (count($related_articles)) {
                    $column = 'col-md-6';
                    $showRelated = true;
                }
            }

            if ($article['catid'] == 1) {
                $link = 'knowledgebase.php';
            } else {
                $link = 'knowledgebase.php?category=' . $article['catid'];
            }
            ?>

            <div class="row">
                <div class="<?php echo $column; ?> col-sm-12">
                    <h4 class="text-left"><?php echo $hesklang['ad']; ?></h4>

                    <div class="text-left">
                        <p><?php echo $hesklang['aid']; ?>: <?php echo $article['id']; ?></p>

                        <p><?php echo $hesklang['category']; ?>: <a
                                href="<?php echo $link; ?>"><?php echo $article['cat_name']; ?></a></p>

                        <?php
                        if ($hesk_settings['kb_date']) {
                            ?>
                            <p><?php echo $hesklang['dta']; ?>: <?php echo hesk_date($article['dt'], true); ?></p>
                            <?php
                        }

                        if ($hesk_settings['kb_views']) {
                            ?>
                            <p><?php echo $hesklang['views']; ?>
                                : <?php echo(isset($_GET['rated']) ? $article['views'] : $article['views'] + 1); ?></p>
                            <?php
                        }

                        if ($hesk_settings['kb_rating']) {
                            $alt = $article['rating'] ? sprintf($hesklang['kb_rated'], sprintf("%01.1f", $article['rating'])) : $hesklang['kb_not_rated'];
                            echo '
            <p>' . $hesklang['rating'] . ' (' . $hesklang['votes'] . '): <img src="img/star_' . (hesk_round_to_half($article['rating']) * 10) . '.png" width="85" height="16" alt="' . $alt . '" title="' . $alt . '" border="0" style="vertical-align:text-bottom" /> (' . $article['votes'] . ')</p>
            ';
                        }
                        ?>
                    </div>
                </div>
                <?php if ($showRelated) { ?>
                    <div class="col-md-6 col-sm-12">
                        <h4 class="text-left"><?php echo $hesklang['relart']; ?></h4>

                        <div class="text-left">
                            <?php
                            foreach ($related_articles as $id => $subject) {
                                echo '<span class="glyphicon glyphicon-file icon-link"></span> <a href="knowledgebase.php?article=' . $id . '">' . $subject . '</a><br />';
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php
            hesk_kbFooter();
        } // END hesk_show_kb_article()


        function hesk_show_kb_category($catid, $is_search = 0)
        {
            global $hesk_settings, $hesklang;

            $res = hesk_dbQuery("SELECT `name`,`parent` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` WHERE `id`='{$catid}' AND `type`='0' LIMIT 1");
            $thiscat = hesk_dbFetchAssoc($res) or hesk_error($hesklang['kb_cat_inv']);

            if ($is_search == 0) {
                /* Print header */
                $hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . hesk_htmlspecialchars($thiscat['name']);
                require_once(HESK_PATH . 'inc/header.inc.php');
                hesk_kb_header($hesk_settings['kb_link']);
            }

            // If we are in "Knowledgebase only" mode show system messages
            if ($catid == 1 && hesk_check_kb_only(false)) {
                // Service messages
                $res = hesk_dbQuery('SELECT `title`, `message`, `style` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . "service_messages` WHERE `type`='0' ORDER BY `order` ASC");
                while ($sm = hesk_dbFetchAssoc($res)) {
                    hesk_service_message($sm);
                }
            }

            if ($thiscat['parent']) {
                $link = ($thiscat['parent'] == 1) ? 'knowledgebase.php' : 'knowledgebase.php?category=' . $thiscat['parent'];
                echo '<h3 class="text-left"><a href="javascript:history.go(-1)"><i class="fa fa-arrow-circle-left" data-toggle="tooltip" data-placement="top" title="' . $hesklang['back'] . '"></i></a>&nbsp;' . $hesklang['kb_cat'] . ': ' . $thiscat['name'] . ' </h3>
        <div class="footerWithBorder blankSpace"></div>
        <div class="blankSpace"></div>
		';
            }

            $result = hesk_dbQuery("SELECT `id`,`name`,`articles` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` WHERE `parent`='{$catid}' AND `type`='0' ORDER BY `cat_order` ASC");
            if (hesk_dbNumRows($result) > 0) {
                ?>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="text-left"><?php echo $hesklang['kb_cat_sub']; ?></h4>
                    </div>
                    <table class="table table-striped">

                        <?php
                        $per_col = $hesk_settings['kb_cols'];
                        $i = 1;

                        while ($cat = hesk_dbFetchAssoc($result)) {

                            if ($i == 1) {
                                echo '<tr>';
                            }

                            echo '
                    <td width="50%" valign="top">
                    <table border="0">
                    <tr><td><i class="fa fa-folder"></i>&nbsp;<a href="knowledgebase.php?category=' . $cat['id'] . '">' . $cat['name'] . '</a></td></tr>
                    ';

                            /* Print most popular/sticky articles */
                            if ($hesk_settings['kb_numshow'] && $cat['articles']) {
                                $res = hesk_dbQuery("SELECT `id`,`subject`, `sticky` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` WHERE `catid`='{$cat['id']}' AND `type`='0' ORDER BY `sticky` DESC, `views` DESC, `art_order` ASC LIMIT " . (intval($hesk_settings['kb_numshow']) + 1));
                                $num = 1;
                                while ($art = hesk_dbFetchAssoc($res)) {
                                    $icon = 'glyphicon glyphicon-file';
                                    $style = '';
                                    if ($art['sticky']) {
                                        $icon = 'glyphicon glyphicon-pushpin';
                                        $style = 'style="color: #FF0000"';
                                    }
                                    echo '
                            <tr>
                            <td ' . $style . '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="' . $icon . '"></span>
                            <a href="knowledgebase.php?article=' . $art['id'] . '" class="article">' . $art['subject'] . '</a></td>
                            </tr>';

                                    if ($num == $hesk_settings['kb_numshow']) {
                                        break;
                                    } else {
                                        $num++;
                                    }
                                }
                                if (hesk_dbNumRows($res) > $hesk_settings['kb_numshow']) {
                                    echo '<tr><td>&raquo; <a href="knowledgebase.php?category=' . $cat['id'] . '"><i>' . $hesklang['m'] . '</i></a></td></tr>';
                                }
                            }

                            echo '
			</table>
		    </td>
			';

                            if ($i == $per_col) {
                                echo '</tr>';
                                $i = 0;
                            }
                            $i++;
                        }
                        /* Finish the table if needed */
                        if ($i != 1) {
                            for ($j = 1; $j <= $per_col; $j++) {
                                echo '<td width="50%">&nbsp;</td>';
                                if ($i == $per_col) {
                                    echo '</tr>';
                                    break;
                                }
                                $i++;
                            }
                        }

                        ?>
                    </table>
                </div>

                <?php
            } // END if NumRows > 0
            ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="text-left"><?php echo $hesklang['ac_no_colon']; ?></h4>
                </div>
                <table class="table table-striped">
                    <tbody>
                    <?php
                    $res = hesk_dbQuery("SELECT `id`, `subject`, `sticky`, LEFT(`content`, " . max(200, $hesk_settings['kb_substrart'] * 2) . ") AS `content`, `rating` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` WHERE `catid`='{$catid}' AND `type`='0' ORDER BY `sticky` DESC, `art_order` ASC");
                    if (hesk_dbNumRows($res) == 0) {
                        echo '<tr><td><i>' . $hesklang['noac'] . '</i></td></tr>';
                    } else {
                        while ($article = hesk_dbFetchAssoc($res)) {
                            $icon = 'fa fa-file';
                            $color = '';
                            $style = '';

                            $txt = hesk_kbArticleContentPreview($article['content']);

                            if ($article['sticky']) {
                                $icon = 'glyphicon glyphicon-pushpin';
                                $style = 'style="color: #FF0000"';
                            }

                            if ($hesk_settings['kb_rating']) {
                                $alt = $article['rating'] ? sprintf($hesklang['kb_rated'], sprintf("%01.1f", $article['rating'])) : $hesklang['kb_not_rated'];
                                $rat = '<td><img src="img/star_' . (hesk_round_to_half($article['rating']) * 10) . '.png" width="85" height="16" alt="' . $alt . '" title="' . $alt . '" border="0" style="vertical-align:text-bottom" /></td>';
                            } else {
                                $rat = '';
                            }

                            echo '
                        <tr>
                            <td>
                                <i class="' . $icon . '" ' . $style . '></i>
                                <a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a>
                                <br>
                                <span class="indent-15">' . $txt . '</span>
                            </td>
                            ' . $rat . '
                        </tr>';
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <?php
            /* On the main KB page print out top and latest articles if needed */
            if ($catid == 1) {
                /* Get list of top articles */
                hesk_kbTopArticles($hesk_settings['kb_popart'], 0);

                /* Get list of latest articles */
                hesk_kbLatestArticles($hesk_settings['kb_latest'], 0);
            }
            hesk_kbFooter();
        } // END hesk_show_kb_category()

        function hesk_kbFooter()
        {
            global $hesk_settings;

            $showRs = hesk_dbQuery("SELECT `show` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "quick_help_sections` WHERE `id` = 4");
            $show = hesk_dbFetchAssoc($showRs);
            if (!$show['show']) {
                echo '<div class="col-md-1">&nbsp;</div></div>';
            }
        }

        ?>
