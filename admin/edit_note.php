<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
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

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_view_tickets');

// Ticket ID
$trackingID = hesk_cleanID() or die($hesklang['int_error'] . ': ' . $hesklang['no_trackID']);

// Note ID
$noteID = intval(hesk_REQUEST('note')) or die($hesklang['int_error'] . ': ' . $hesklang['mis_note']);

// Get ticket info
$result = hesk_dbQuery("SELECT `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE `trackid`='" . hesk_dbEscape($trackingID) . "' LIMIT 1");
if (hesk_dbNumRows($result) != 1) {
    hesk_error($hesklang['ticket_not_found']);
}
$ticket = hesk_dbFetchAssoc($result);

// Get note info
$result = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `id`={$noteID}");
if (hesk_dbNumRows($result) != 1) {
    hesk_error($hesklang['no_note']);
}
$note = hesk_dbFetchAssoc($result);

// Make sure the note matches the ticket and the user has permission to edit it
if ($note['ticket'] != $ticket['id'] || (!hesk_checkPermission('can_del_notes', 0) && $note['who'] != $_SESSION['id'])) {
    hesk_error($hesklang['perm_deny']);
}

// Save changes?
if (isset($_POST['save'])) {
    // A security check
    hesk_token_check('POST');

    // Get message
    $tmpvar['message'] = nl2br(hesk_makeURL(hesk_input(hesk_POST('message'))));

    // If we have message or attachments do the update
    if (strlen($tmpvar['message']) || strlen($note['attachments'])) {
        hesk_dbQuery("UPDATE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` SET `message`='" . hesk_dbEscape($tmpvar['message']) . "' WHERE `id`={$noteID}");
        hesk_process_messages($hesklang['ednote2'], 'admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999), 'SUCCESS');
    } // If not, delete the note
    else {
        hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "notes` WHERE `id`={$noteID}");
        header('Location: admin_ticket.php?track=' . $trackingID . '&Refresh=' . mt_rand(10000, 99999));
        exit();
    }
}

$note['message'] = hesk_msgToPlain($note['message'], 0, 0);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print admin navigation */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<ol class="breadcrumb">
    <li>
        <a href="admin_ticket.php?track=<?php echo $trackingID; ?>&amp;Refresh=<?php echo mt_rand(10000, 99999); ?>"><?php echo $hesklang['ticket'] . ' ' . $trackingID; ?></a>
    </li>
    <li class="active"><?php echo $hesklang['ednote']; ?></li>
</ol>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <h3><?php echo $hesklang['ednote']; ?></h3>

        <div class="footerWithBorder blankSpace"></div>

        <form method="post" action="edit_note.php" name="form1" class="form-horizontal" role="form">
            <div class="form-group">
                <label for="message" class="col-md-2 control-label"><?php echo $hesklang['message']; ?></label>

                <div class="col-md-10">
                    <textarea name="message" class="form-control" rows="12"
                              cols="60"><?php echo $note['message']; ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-10 col-md-offset-2">
                    <input type="hidden" name="save" value="1"/><input type="hidden" name="track"
                                                                       value="<?php echo $trackingID; ?>"/>
                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>
                    <input type="hidden" name="note" value="<?php echo $noteID; ?>"/>
                    <input type="submit" value="<?php echo $hesklang['save_changes']; ?>" class="btn btn-primary">
                    <a href="javascript:history.go(-1)" class="btn btn-default"><?php echo $hesklang['back']; ?></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();
?>
