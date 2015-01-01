<?php
define('IN_SCRIPT',1);
define('HESK_PATH','./');

// Get all the required files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'modsForHesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
hesk_load_database_functions();
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
        $showForm = true;

        if (isset($_GET['key']) || isset($_POST['key']))
        {

            $key = isset($_GET['key'])
                ? $_GET['key']
                : $_POST['key'];

            $submittedTickets = array();
            $email = '';
            hesk_dbConnect();
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
                    // Notify the customer
                    hesk_notifyCustomer();

                    // Need to notify staff?
                    // --> From autoassign?
                    if ($tmpvar['owner'] && $autoassign_owner['notify_assigned'])
                    {
                        hesk_notifyAssignedStaff($autoassign_owner, 'ticket_assigned_to_you');
                    }
                    // --> No autoassign, find and notify appropriate staff
                    elseif ( ! $tmpvar['owner'] )
                    {
                        hesk_notifyStaff('new_ticket_staff', " `notify_new_unassigned` = '1' ");
                    }

                    array_push($submittedTickets, $innerResult['trackid']);
                    hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."stage_tickets`
                        WHERE `id` = ".$innerResult['id']);
                }
            }
            hesk_dbQuery("DELETE FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."pending_verification_emails`
                    WHERE `ActivationKey` = '".hesk_dbEscape($key)."'");

            //-- were there an email recored for the key?
            if (!empty($email))
            {
                $showForm = false;
                ?>
                <div class="alert alert-success">
                    <p><i class="fa fa-check"></i> <?php echo sprintf($hesklang['email_verified'], $email) ?></p>
                    <ul>
                        <?php
                            foreach ($submittedTickets as $ticket)
                            {
                                echo '<li><a href="'.$hesk_settings['hesk_url'].'/ticket.php?track='.$ticket['trackid'].'">'.$ticket.'</a></li>';
                            }
                            if (count($submittedTickets) == 0)
                            {
                                echo '<li>'.$hesklang['no_tickets_created'].'</li>';
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
        }
        if ($showForm) {
            ?>
            <form class="form-horizontal" action="verifyemail.php" method="post">
                <div class="form-group">
                    <label for="key" class="col-sm-3 control-label"><?php echo $hesklang['activation_key']; ?></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="key" name="key" placeholder="<?php echo $hesklang['activation_key']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <input type="submit" class="btn btn-default" value="<?php echo $hesklang['verify_email']; ?>">
                    </div>
                </div>
            </form>
            <?php
        }
        ?>
    </div>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
?>