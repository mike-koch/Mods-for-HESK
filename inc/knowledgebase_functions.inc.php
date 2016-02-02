<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.6 from 2nd February 2016
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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}

/*** FUNCTIONS ***/

function hesk_kbArticleContentPreview($txt)
{
    global $hesk_settings;

    // Strip HTML tags
    $txt = strip_tags($txt);

    // If text is larger than article preview length, shorten it
    if (strlen($txt) > $hesk_settings['kb_substrart']) {
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
    if ($index) {
        // Disabled?
        if (!$hesk_settings['kb_index_popart']) {
            return true;
        }

        // Show title in italics
        $font_weight = 'i';
    } else {
        // Disabled?
        if (!$hesk_settings['kb_popart']) {
            return true;
        }

        // Show title in bold
        $font_weight = 'b';

        // Print a line for spacing
        echo '<hr />';
    }
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="text-left"><?php echo $hesklang['popart_no_colon']; ?></h4>
        </div>
        <table border="0" width="100%" class="table table-striped table-fixed">
            <thead>
            <tr>
                <?php
                /* Get list of articles from the database */
                $res = hesk_dbQuery("SELECT `t1`.`id`,`t1`.`subject`,`t1`.`views`,`t1`.`sticky` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
			LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`sticky` DESC, `t1`.`views` DESC, `t1`.`art_order` ASC LIMIT " . intval($how_many));

                /* Show number of views? */
                if ($hesk_settings['kb_views'] && hesk_dbNumRows($res) != 0) {
                    echo '<th class="col-xs-8 col-sm-9">&nbsp;</th>';
                    echo '<th class="col-xs-4 col-sm-3"><i>' . $hesklang['views'] . '</i></th>';
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            /* Get list of articles from the database */
            $res = hesk_dbQuery("SELECT `t1`.`id`,`t1`.`subject`,`t1`.`dt`, `t1`.`views`,`t1`.`sticky` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
            LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
            WHERE `t1`.`type`='0' AND `t2`.`type`='0'
            ORDER BY `t1`.`sticky` DESC, `t1`.`views` DESC, `t1`.`art_order` ASC LIMIT " . intval($how_many));

            /* If no results found end here */
            if (hesk_dbNumRows($res) == 0) {
                $colspan = '';
                if (!$hesk_settings['kb_views']) {
                    $colspan = 'colspan="2"';
                }
                echo '<tr><td ' . $colspan . '><i>' . $hesklang['noa'] . '</i></td></tr>';
            }

            /* We have some results, print them out */
            $colspan = '';
            if (!$hesk_settings['kb_views']) {
                $colspan = 'colspan="2"';
            }
            while ($article = hesk_dbFetchAssoc($res)) {
                $icon = 'fa fa-file';
                $style = '';

                if ($article['sticky']) {
                    $icon = 'glyphicon glyphicon-pushpin';
                    $style = 'style="color: #FF0000"';
                }

                echo '
                <tr>
                    <td class="col-xs-8 col-sm-9" ' . $colspan . '>
                        <i class="' . $icon . '" ' . $style . '></i> <a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a>
                    </td>
                    ';
                if ($hesk_settings['kb_views']) {
                    echo '<td class="col-xs-4 col-sm-3">' . $article['views'] . '</td>';
                }
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php

    return true;
} // END hesk_kbTopArticles()


function hesk_kbLatestArticles($how_many, $index = 1)
{
    global $hesk_settings, $hesklang;

    // Index page or KB main page?
    if ($index) {
        // Disabled?
        if (!$hesk_settings['kb_index_latest']) {
            return true;
        }

        // Show title in italics
        $font_weight = 'i';
    } else {
        // Disabled?
        if (!$hesk_settings['kb_latest']) {
            return true;
        }

        // Show title in bold
        $font_weight = 'b';

        // Print a line for spacing if we don't show popular articles
        if (!$hesk_settings['kb_popart']) {
            echo '<hr />';
        }
    }
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="text-left"><?php echo $hesklang['latart_no_colon']; ?></h4>
        </div>
        <table class="table table-striped table-fixed">
            <thead>
            <tr>
                <?php
                $colspan = '';
                if (!$hesk_settings['kb_date']) {
                    $colspan = 'colspan="2"';
                }
                /* Get list of articles from the database */
                $res = hesk_dbQuery("SELECT `t1`.* FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
                LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
                WHERE `t1`.`type`='0' AND `t2`.`type`='0'
                ORDER BY `t1`.`dt` DESC LIMIT " . intval($how_many));

                /* Show number of views? */
                if (hesk_dbNumRows($res) != 0) {
                    echo '<th class="col-xs-9" ' . $colspan . '>&nbsp;</th>';
                    if ($hesk_settings['kb_date']) {
                        echo '<th class="col-xs-3"><i>' . $hesklang['dta'] . '</i></th>';
                    }
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            /* Get list of articles from the database */
            $res = hesk_dbQuery("SELECT `t1`.* FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_articles` AS `t1`
			LEFT JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS `t2` ON `t1`.`catid` = `t2`.`id`
			WHERE `t1`.`type`='0' AND `t2`.`type`='0'
			ORDER BY `t1`.`dt` DESC LIMIT " . intval($how_many));

            /* If no results found end here */
            if (hesk_dbNumRows($res) == 0) {
                $colspan = '';
                if ($hesk_settings['kb_date']) {
                    $colspan = 'colspan="2"';
                }
                echo '<td ' . $colspan . '><i>' . $hesklang['noa'] . '</i></td>';
            }

            /* We have some results, print them out */
            $colspan = $hesk_settings['kb_date'] ? '' : 'colspan="2"';
            while ($article = hesk_dbFetchAssoc($res)) {
                $icon = 'fa fa-file';
                $style = '';

                if ($article['sticky']) {
                    $icon = 'glyphicon glyphicon-pushpin';
                    $style = 'style="color: #FF0000"';
                }

                echo '
                <tr>
                    <td class="col-xs-9" ' . $colspan . '>
                        <i class="' . $icon . '" ' . $style . '></i> <a href="knowledgebase.php?article=' . $article['id'] . '">' . $article['subject'] . '</a>
                    </td>';
                if ($hesk_settings['kb_date']) {
                    echo '<td class="col-xs-3">' . hesk_date($article['dt'], true) . '</td>';
                }
                echo '</tr>';
            } ?>
            </tbody>
        </table>
    </div>

    <?php

    return true;
} // END hesk_kbLatestArticles()


function hesk_kbSearchLarge($admin = '')
{
    global $hesk_settings, $hesklang;

    $action = 'knowledgebase.php';
    if ($admin) {
        if (!$hesk_settings['kb_search']) {
            return '';
        }
        $action = 'knowledgebase_private.php';
    } elseif ($hesk_settings['kb_search'] != 2) {
        return '';
    }

    ?>

    <div style="text-align:center">
        <form role="form" action="<?php echo $action; ?>" method="get" style="display: inline; margin: 0;"
              name="searchform">
            <div class="input-group">
                <input type="text" class="form-control"
                       placeholder="<?php echo htmlspecialchars($hesklang['search_the_knowledgebase']); ?>"
                       name="search">
				<span class="input-group-btn">
					<button class="btn btn-default" type="submit" value="<?php echo $hesklang['search']; ?>"
                            title="<?php echo $hesklang['search']; ?>"><?php echo $hesklang['search']; ?></button>
				</span>
            </div>
        </form>
    </div>

    <br/>

    <!-- START KNOWLEDGEBASE SUGGEST -->
    <div id="kb_suggestions" style="display:none">
        <img src="<?php echo HESK_PATH; ?>img/loading.gif" width="24" height="24" alt="" border="0"
             style="vertical-align:text-bottom"/> <i><?php echo $hesklang['lkbs']; ?></i>
    </div>

    <script language="Javascript" type="text/javascript"><!--
        hesk_suggestKBsearch(<?php echo $admin; ?>);
        //-->
    </script>
    <!-- END KNOWLEDGEBASE SUGGEST -->

    <br/>

    <?php
} // END hesk_kbSearchLarge()


function hesk_kbSearchSmall()
{
    global $hesk_settings, $hesklang;

    if ($hesk_settings['kb_search'] != 1) {
        return '';
    }
    ?>

    <td class="text-right" valign="top" width="300">
        <div style="display:inline;margin-left:auto;margin-right:auto">
            <form action="knowledgebase.php" method="get" class="form-inline" style="display: inline; margin: 0;">
                <div class="input-group" style="margin: 0 15px">
                    <input type="text" name="search" class="form-control">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"
                                value="<?php echo $hesklang['search_the_knowledgebase']; ?>"
                                title="<?php echo $hesklang['search_the_knowledgebase']; ?>">
                            <?php echo $hesklang['search_the_knowledgebase']; ?>
                        </button>
                    </span>
                </div>
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
        'mediapartners-google', 'crawler', 'spider', 'robot', 'bot/', 'bot-', 'voila');

    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }

    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

    foreach ($botlist as $bot) {
        if (strpos($ua, $bot) !== false) {
            return true;
        }
    }

    return false;
} // END hesk_detect_bots()
