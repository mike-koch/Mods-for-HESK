<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../modsForHeskSql.php');

$version = $_POST['version'];
if ($version == 1) {
    executePre140Scripts();
} elseif ($version == 140) {
    execute140Scripts();
} elseif ($version == 141) {
    execute141Scripts();
} elseif ($version == 150) {
    execute150Scripts();
} elseif ($version == 160) {
    execute160Scripts();
} elseif ($version == 161) {
    execute161Scripts();
} elseif ($version == 170) {
    execute170Scripts();
    execute170FileUpdate();
} elseif ($version == 200) {
    execute200Scripts();
    execute200FileUpdate();
} elseif ($version == 201) {
    execute201Scripts();
} elseif ($version == 210) {
    execute210Scripts();
    execute210FileUpdate();
} else {
    $response = 'The version "'.$version.'" was not recognized. Check the value submitted and try again.';
    print $response;
    http_response_code(400);
}
return;