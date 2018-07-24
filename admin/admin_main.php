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
define('HESK_PATH', '../');

/* Make sure the install folder is deleted */
if (is_dir(HESK_PATH . 'install')) {
    die('Please delete the <b>install</b> folder from your server for security reasons then refresh this page!');
}

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/status_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

define('CALENDAR', 1);
define('MAIN_PAGE', 1);
define('PAGE_TITLE', 'ADMIN_HOME');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('AUTO_RELOAD', 1);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');

/* Reset default settings? */
if (isset($_GET['reset']) && hesk_token_check()) {
    $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `default_list`='' WHERE `id` = '" . intval($_SESSION['id']) . "'");
    $_SESSION['default_list'] = '';
} /* Get default settings */
else {
    parse_str($_SESSION['default_list'], $defaults);
    $_GET = isset($_GET) && is_array($_GET) ? array_merge($_GET, $defaults) : $defaults;
}

?>
<div class="content-wrapper">
    <section class="content">
    <?php
    // Service messages
    $service_messages = mfh_get_service_messages('STAFF_HOME');
    foreach ($service_messages as $sm) {
        hesk_service_message($sm);
    }

    hesk_handle_messages();
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['tickets']; ?>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-6 text-left">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" onclick="toggleAutoRefresh(this);" id="reloadCB">
                            <?php echo $hesklang['arp']; ?>
                            <span id="timer"></span>
                        </label>
                    </div>
                    <script type="text/javascript">heskCheckReloading();</script>
                </div>
                <div class="col-xs-6 text-right">
                    <a href="new_ticket.php" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                        <?php echo $hesklang['nti']; ?>
                    </a>
                </div>
            </div>
            <?php
            /* Print tickets? */
            if (hesk_checkPermission('can_view_tickets', 0)) {
                /* Print the list of tickets */
                require(HESK_PATH . 'inc/print_tickets.inc.php');
                echo '<br>';
                /* Print forms for listing and searching tickets */
                require(HESK_PATH . 'inc/show_search_form.inc.php');
            } else {
                echo '<p><i>' . $hesklang['na_view_tickets'] . '</i></p>';
            }
            ?>
        </div>
    </div>
    <?php
    /*******************************************************************************
    The code below handles HESK licensing. Removing or modifying this code without
    purchasing a HESK license is strictly prohibited.

    To purchase a HESK license and support future HESK development please visit:
    https://www.hesk.com/buy.php
    *******************************************************************************/
    $x1a="\142a".chr(0163).chr(847249408>>23)."\66\x34".chr(796917760>>23)."\x65\156\143".chr(0157)."\x64\145";$hesk_settings['hesk_license']($x1a("\x3c\150r\x20\57\76".chr(503316480>>23)."\x74\141\142l\x65\40".chr(0142).chr(0157).chr(0162)."\144\145r\x3d\42\60".chr(285212672>>23)."\x20\x77\x69".chr(0144)."th".chr(511705088>>23)."\x22".chr(061)."\60\60\x25\42".chr(520093696>>23)."\x3c\164".chr(0162).">\74t\x64\x3e\x3c".chr(0142).chr(076).$hesklang[chr(956301312>>23)."\145\155\157\x76e".chr(796917760>>23)."\x73ta\164e\x6d".chr(847249408>>23)."\156\x74"].chr(503316480>>23)."\x2f\142\x3e".chr(074)."\57t\x64\76".chr(074)."td".chr(268435456>>23)."\x73ty\154\x65\x3d\x22te".chr(1006632960>>23)."t\x2d\141\x6c\x69".chr(0147).chr(922746880>>23)."\x3ar\151\x67ht\"\76".chr(503316480>>23)."\141 \x68\162\145\146\x3d\42".chr(0112).chr(813694976>>23)."v\141".chr(0163).chr(830472192>>23)."\162\x69".chr(0160).chr(0164)."\x3a".chr(989855744>>23)."\157\151d\50\x30".chr(343932928>>23).chr(042)."\40onc\154\151\143\153\x3d".chr(042)."\x61\x6c\145\x72t(\x27".$hesklang["\163".chr(981467136>>23)."\x70".chr(939524096>>23).chr(0157)."\162\164\137n".chr(931135488>>23)."\x74\151".chr(0143)."\x65"].chr(047)."\51\42\x3e".$hesklang["\x73\x68"]."\74".chr(394264576>>23)."\x61\x3e\74\57\164d\76\x3c/\x74\162\76".chr(503316480>>23).chr(057)."t\x61\x62\x6ce\x3e\x3c\x70\x3e".$hesklang[chr(0163)."\x75ppo\x72\x74\137".chr(956301312>>23).chr(847249408>>23)."\155".chr(931135488>>23)."v\x65"]."\x2e\x20\x3c".chr(813694976>>23)."\40\x68re\x66\x3d".chr(285212672>>23)."\150".chr(973078528>>23).chr(973078528>>23)."\160\x73".chr(486539264>>23)."\57\x2f".chr(998244352>>23)."\x77\167".chr(056)."\150".chr(847249408>>23)."s\153\56\x63\157".chr(0155)."/".chr(0142)."\165\171.".chr(0160)."h\x70".chr(285212672>>23)."\x20\x74\141".chr(0162)."g".chr(847249408>>23)."\164\x3d".chr(042)."\137b\x6c".chr(813694976>>23)."\x6ek\x22\76".$hesklang["\x63\154\151\143\153\x5f".chr(880803840>>23)."\x6e".chr(855638016>>23).chr(0157)]."\x3c/\141\x3e\x3c\x2fp".chr(076)."<\150\162\x20\x2f\x3e"),"");
    /*******************************************************************************
    END LICENSE CODE
    *******************************************************************************/

    /* Clean unneeded session variables */
    hesk_cleanSessionVars('hide');
    ?>
</section>
</div>

<?php


require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
