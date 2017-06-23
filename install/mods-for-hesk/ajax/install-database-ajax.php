<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
require('../sql/installSql.php');

$version = $_POST['version'];
if ($version == 2) {
    executePre140Scripts();
} elseif ($version == 3) {
    execute140Scripts();
} elseif ($version == 4) {
    execute141Scripts();
} elseif ($version == 5) {
    execute150Scripts();
} elseif ($version == 6) {
    execute160Scripts();
} elseif ($version == 7) {
    execute161Scripts();
} elseif ($version == 8) {
    execute170Scripts();
} elseif ($version == 9) {
    execute200Scripts();
} elseif ($version == 10) {
    execute201Scripts();
} elseif ($version == 11) {
    execute210Scripts();
} elseif ($version == 12) {
    execute211Scripts();
} elseif ($version == 13) {
    execute220Scripts();
} elseif ($version == 14) {
    execute221Scripts();
} elseif ($version == 15) {
    execute230Scripts();
} elseif ($version == 16) {
    execute231Scripts();
} elseif ($version == 17) {
    execute232Scripts();
} elseif ($version == 18) {
    execute240Scripts();
} elseif ($version == 19) {
    execute241Scripts();
} elseif ($version == 20) {
    execute242Scripts();
} elseif ($version == 21) {
    migrateSettings();
    execute250Scripts();
} elseif ($version == 22) {
    execute251Scripts();
} elseif ($version == 23) {
    execute252Scripts();
} elseif ($version == 24) {
    execute253Scripts();
} elseif ($version == 25) {
    execute254Scripts();
} elseif ($version == 26) {
    execute255Scripts();
} elseif ($version == 27) {
    execute260Scripts();
} elseif ($version == 28) {
    execute261Scripts();
} elseif ($version == 29) {
    execute262Scripts();
} elseif ($version == 30) {
    execute263Scripts();
} elseif ($version == 31) {
    execute264Scripts();
} elseif ($version == 32) {
    execute300Beta1Scripts();
} elseif ($version == 33) {
    execute300RC1Scripts();
} elseif ($version == 34) {
    execute300Scripts();
} elseif ($version == 35) {
    execute301Scripts();
} elseif ($version == 36) {
    execute302Scripts();
} elseif ($version == 37) {
    execute303Scripts();
} elseif ($version == 38) {
    execute304Scripts();
} elseif ($version == 39) {
    execute305Scripts();
} elseif ($version == 40) {
    execute306Scripts();
} elseif ($version == 41) {
    execute307Scripts();
} elseif ($version == 42) {
    execute310Scripts();
} else {
    $response = 'The version "' . $version . '" was not recognized. Check the value submitted and try again.';
    print $response;
    http_response_code(400);
}
return;
