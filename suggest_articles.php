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

define('IN_SCRIPT', 1);
define('HESK_PATH', './');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();

/* Print XML header */
header('Content-Type: text/html; charset=' . $hesklang['ENCODING']);

/* Get the search query composed of the subject and message */
$query = hesk_REQUEST('q') or die('');

hesk_dbConnect();

/* Get relevant articles from the database */
$res = hesk_dbQuery("SELECT t1.`id`, t1.`subject`, LEFT(t1.`content`, " . max(200, $hesk_settings['kb_substrart'] * 2) . ") AS `content`, MATCH(`subject`,`content`,`keywords`) AGAINST ('" . hesk_dbEscape($query) . "') AS `score`
					FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . 'kb_articles` AS t1
					LEFT JOIN `' . hesk_dbEscape($hesk_settings['db_pfix']) . "kb_categories` AS t2 ON t1.`catid` = t2.`id`
					WHERE t1.`type`='0' AND t2.`type`='0' AND MATCH(`subject`,`content`,`keywords`) AGAINST ('" . hesk_dbEscape($query) . "')
					LIMIT " . intval($hesk_settings['kb_search_limit']));
$num = hesk_dbNumRows($res);

/* Solve some spacing issues */
if (hesk_isREQUEST('p')) {
    echo '&nbsp;<br />';
}

/* Return found articles */
?>
<div class="alert alert-info">
    <span style="font-size:12px;font-weight:bold"><?php echo $hesklang['sc']; ?>:</span><br/>&nbsp;<br/>
    <?php
    if (!$num) {
        echo '<i>' . $hesklang['nsfo'] . '</i>';
    } else {
        $max_score = 0;
        while ($article = hesk_dbFetchAssoc($res)) {
            if ($article['score'] > $max_score) {
                $max_score = $article['score'];
            }

            if ($max_score && ($article['score'] / $max_score) < 0.25) {
                break;
            }

            $txt = strip_tags($article['content']);
            if (strlen($txt) > $hesk_settings['kb_substrart']) {
                $txt = substr($txt, 0, $hesk_settings['kb_substrart']) . '...';
            }

            echo '
			<a href="knowledgebase.php?article=' . $article['id'] . '&amp;suggest=1" target="_blank">' . $article['subject'] . '</a>
			<input type="hidden" name="suggested[]" value="' . $article['id'] . '|' . stripslashes(hesk_input($article['subject'])) . '">
		    <br />' . $txt . '<br /><br />';
        }
    }
    ?>
</div>
