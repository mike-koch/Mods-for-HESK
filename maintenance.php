<?php
define('IN_SCRIPT',1);
define('HESK_PATH','./');
define('ON_MAINTENANCE_PAGE', 1);

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require_once(HESK_PATH . 'inc/header.inc.php');
if (!$modsForHesk_settings['maintenance_mode']) {
    //-- The user refreshed the maintenance page, but maintenance mode is off. Redirect them back to the index page.
    header('Location: '.HESK_PATH);
}
?>
<div class="row">
    <div class="col-md-6 col-md-offset-3" style="padding-top: 30px; text-align: center;">
            <i class="fa fa-exclamation-triangle fa-5x" style="color: orange"></i><br>
            <p>The helpdesk is currently undergoing maintenance. Please come back later.</p>
    </div>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
?>