<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('INTERNAL_API_PATH', '../../');
require_once(HESK_PATH . 'hesk_settings.inc.php');
require_once(HESK_PATH . 'inc/common.inc.php');
require_once(INTERNAL_API_PATH . 'core/output.php');

hesk_load_internal_api_database_functions();
hesk_dbConnect();

if (!empty($_FILES)) {

}

return http_response_code(400);