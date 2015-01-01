<?php
define('IN_SCRIPT',1);
define('HESK_PATH','./');
define('ON_MAINTENANCE_PAGE', 1);

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/posting_functions.inc.php');
require_once(HESK_PATH . 'inc/header.inc.php');
?>
<ol class="breadcrumb">
    <li><a href="<?php echo $hesk_settings['site_url']; ?>"><?php echo $hesk_settings['site_title']; ?></a></li>
    <li><a href="<?php echo $hesk_settings['hesk_url']; ?>"><?php echo $hesk_settings['hesk_title']; ?></a></li>
    <li class="active"><?php echo $hesklang['verify_email']; ?></li>
</ol>
<div class="row">
    <div class="col-md-8 col-md-offset-2 col-sm-12">
        <h3><?php echo $hesklang['verify_email']; ?></h3>
        <div class="footerWithBorder blankSpace"></div>

        <?php
        if (isset($_GET['key']) || isset($_POST['key']))
        {
            $key = isset($_GET['key'])
                ? $_GET['key']
                : $_POST['key'];

            $submittedTickets = array();
            $email = '';
            $getRs = hesk_dbQuery("SELECT `Email` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails`
                WHERE `ActivationKey` = '".hesk_dbEscape($key)."'");
            while ($result = $getRs->fetch_assoc())
            {
                $email = $result['Email'];
                $ticketRs = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets`
                    WHERE `email` = '".hesk_dbEscape($result['Email'])."'");
                while ($innerResult = $ticketRs->fetch_assoc())
                {
                    hesk_newTicket($innerResult);
                    array_push($submittedTickets, $innerResult['trackid']);
                    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets`
                        WHERE `id` = ".$innerResult['id']);
                }
            }
            hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails`
                    WHERE `ActivationKey` = '".hesk_dbEscape($key)."'");

            //-- were any tickets activated?
            if (count($submittedTickets) > 0)
            {
                ?>
                <div class="alert alert-success">
                    <p><i class="fa fa-check"></i> <?php echo sprintf($hesklang['email_verified'], $email) ?></p>
                    <ul>
                        <?php
                            foreach ($submittedTickets as $ticket)
                            {
                                echo '<li><a href="'.$hesk_settings['hesk_url'].'/ticket.php?track='.$ticket['trackid'].'">'.$ticket.'</a></li>';
                            }
                        ?>
                    </ul>
                </div>
                <?php
            } else
            {
                //-- no tickets were activated. invalid key, or was email already activated??
                ?>
                <div class="alert alert-warning">
                    <p><i class="fa fa-exclamation-triangle"></i> <?php echo $hesklang['verify_no_records']; ?></p>
                </div>
                <?php
            }
        } else
        {
            //-- The user accessed this page with no key. Output a form to enter their key.
            //TODO Do this
        }
        ?>
    </div>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
?>