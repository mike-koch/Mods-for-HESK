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
