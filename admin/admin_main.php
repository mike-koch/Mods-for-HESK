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
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

define('CALENDAR', 1);
define('MAIN_PAGE', 1);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row">
    <div class="col-md-12 pad-down-20">
        <?php
        /* This will handle error, success and notice messages */
        hesk_handle_messages();

        /* Print tickets? */
        if (hesk_checkPermission('can_view_tickets', 0)) {
            if (!isset($_SESSION['hide']['ticket_list']))  //Number of tickets (table header. NOT ACTUAL TABLE)
            {
                echo '
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>' . $hesklang['open_tickets'] . ' <span class="nu-floatRight panel-button"><a href="new_ticket.php" class="btn btn-success">' . $hesklang['nti'] . '</a></span></h4>
                    </div>'; // The rest of the panel will be printed by print_tickets.inc.php
            }

            /* Reset default settings? */
            if (isset($_GET['reset']) && hesk_token_check()) {
                $res = hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` SET `default_list`='' WHERE `id` = '" . intval($_SESSION['id']) . "' LIMIT 1");
                $_SESSION['default_list'] = '';
            } /* Get default settings */
            else {
                parse_str($_SESSION['default_list'], $defaults);
                $_GET = isset($_GET) && is_array($_GET) ? array_merge($_GET, $defaults) : $defaults;
            }

            /* Print the list of tickets */
            require(HESK_PATH . 'inc/print_tickets.inc.php');

            echo "&nbsp;<br />";

            /* Print forms for listing and searching tickets */
            require(HESK_PATH . 'inc/show_search_form.inc.php');
        } else {
            echo '<p><i>' . $hesklang['na_view_tickets'] . '</i></p>';
        }

        $hesk_settings['hesk_license']('HMgPSAxOw0KaWYgKGZpbGVfZXhpc3RzKEhFU0tfUEFUSCAuI
        CdoZXNrX2xpY2Vuc2UucGhwJykpDQp7DQokaCA9ICghZW1wdHkoJF9TRVJWRVJbJ0hUVFBfSE9TVCddK
        SkgPyAkX1NFUlZFUlsnSFRUUF9IT1NUJ10gOiAoKCFlbXB0eSgkX1NFUlZFUlsnU0VSVkVSX05BTUUnX
        SkpID8gJF9TRVJWRVJbJ1NFUlZFUl9OQU1FJ10gOiBnZXRlbnYoJ1NFUlZFUl9OQU1FJykpOw0KJGggP
        SBzdHJfcmVwbGFjZSgnd3d3LicsJycsc3RydG9sb3dlcigkaCkpOw0KaW5jbHVkZShIRVNLX1BBVEggL
        iAnaGVza19saWNlbnNlLnBocCcpOw0KaWYgKGlzc2V0KCRoZXNrX3NldHRpbmdzWydsaWNlbnNlJ10pI
        CYmIHN0cnBvcygkaGVza19zZXR0aW5nc1snbGljZW5zZSddLHNoYTEoJGguJ2gzJkZwMiNMYUEmNTkhd
        yg4LlpjXSordVI1MTInKSkgIT09IGZhbHNlKQ0Kew0KJHMgPSAwOw0KfQ0KZWxzZQ0Kew0KZWNobyAnP
        HAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyO2NvbG9yOnJlZDsiPklOVkFMSUQgTElDRU5TRSAoTk9UI
        FJFR0lTVEVSRUQgRk9SICcuJGguJykhPC9wPic7DQp9DQp9DQppZiAoJHMpDQp7DQplY2hvICc8aHIgL
        z48dGFibGUgYm9yZGVyPSIwIiB3aWR0aD0iMTAwJSI+PHRyPjx0ZD48Yj4nLiRoZXNrbGFuZ1sncmVtb
        3ZlX3N0YXRlbWVudCddLic8L2I+PC90ZD48dGQgc3R5bGU9InRleHQtYWxpZ246cmlnaHQiPjxhIGhyZ
        WY9IkphdmFzY3JpcHQ6dm9pZCgwKSIgb25jbGljaz0iYWxlcnQoXCcnLiRoZXNrbGFuZ1snc3VwcG9yd
        F9ub3RpY2UnXS4nXCcpIj4nLiRoZXNrbGFuZ1snc2gnXS4nPC9hPjwvdGQ+PC90cj48L3RhYmxlPjxwP
        icuJGhlc2tsYW5nWydzdXBwb3J0X3JlbW92ZSddLicuIDxhIGhyZWY9Imh0dHBzOi8vd3d3Lmhlc2suY
        29tL2J1eS5waHAiIHRhcmdldD0iX2JsYW5rIj4nLiRoZXNrbGFuZ1snY2xpY2tfaW5mbyddLic8L2E+P
        C9wPic7DQp9DQo=', "\112");

        echo '<hr />&nbsp;<br />';

        /* Clean unneeded session variables */
        hesk_cleanSessionVars('hide');
        ?>
    </div>
</div>

<?php


require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
