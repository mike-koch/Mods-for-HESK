<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$showFinished = 'none';
$showInstructions = 'block';
if ($_GET['update'] == 1)
{
    hesk_dbConnect();
    $somethingRS = hesk_dbQuery("SHOW TABLES LIKE '".$hesk_settings['db_pfix']."statuses'");
    $shouldBuildTable = ($somethingRS->num_rows == 0);
    $showInstructions = 'none';
    $showFinished = 'block';

    if ($shouldBuildTable)
    {
        $showInstructions = 'none';
        $showFinished = 'block';

        //-- Need to do this since we are no longer restricted on IDs and we want an INT for proper INNER JOINs
        hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT '0'");

        hesk_dbQuery("CREATE TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (
                      `ID` INT NOT NULL,
                      `ShortNameContentKey` TEXT NOT NULL,
                      `TicketViewContentKey` TEXT NOT NULL,
                      `TextColor` TEXT NOT NULL,
                      `IsNewTicketStatus` BIT NOT NULL DEFAULT 0,
                      `IsClosed` BIT NOT NULL DEFAULT 0,
                      `IsClosedByClient` BIT NOT NULL DEFAULT 0,
                      `IsCustomerReplyStatus` BIT NOT NULL DEFAULT 0,
                      `IsStaffClosedOption` BIT NOT NULL DEFAULT 0,
                      `IsStaffReopenedStatus` BIT NOT NULL DEFAULT 0,
                      `IsDefaultStaffReplyStatus` BIT NOT NULL DEFAULT 0,
                      `LockedTicketStatus` BIT NOT NULL DEFAULT 0,
                        PRIMARY KEY (`ID`))");
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (0, 'open', 'open', '#FF0000', 1, 0, 0, 0, 0, 0, 0, 0);");

        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (1, 'wait_reply', 'wait_staff_reply', '#FF9933', 0, 0, 0, 1, 0, 1, 0, 0);");
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (2, 'replied', 'wait_cust_reply', '#0000FF', 0, 0, 0, 0, 0, 0, 1, 0);");
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (3, 'closed', 'closed', '#008000', 0, 1, 1, 0, 1, 0, 0, 1);");
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (4, 'in_progress', 'in_progress', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
        hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."statuses` (ID, ShortNameContentKey, TicketViewContentKey, TextColor, IsNewTicketStatus, IsClosed, IsClosedByClient, IsCustomerReplyStatus,
		IsStaffClosedOption, IsStaffReopenedStatus, IsDefaultStaffReplyStatus, LockedTicketStatus)
	VALUES (5, 'on_hold', 'on_hold', '#000000', 0, 0, 0, 0, 0, 0, 0, 0);");
    }
}

?>
<html>
    <head>
        <title>NuHesk 1.2.0 Install / Upgrade</title>
    </head>
    <body>
        <div style="display: <?php echo $showInstructions; ?>">
        <h1>Install / Upgrade NuHesk to 1.2.0</h1>
        <h4><i>If you have not yet installed/updated HESK, please do so first before continuing; otherwise installation will <b>fail</b>!</i></h4>
        <br/>
        <p>Please verify the database information below.  Addtionally, ensure that the database user has CREATE permissions.</p>
        <p><b>Database Host: </b> <?php echo $hesk_settings['db_host']; ?></p>
        <p><b>Database Name: </b><?php echo $hesk_settings['db_name']; ?></p>
        <p><b>Database User: </b><?php echo $hesk_settings['db_user']; ?></p>
        <p><b>Database Password: </b><?php echo $hesk_settings['db_pass']; ?></p>
        <p><b>Database Prefix: </b><?php echo $hesk_settings['db_pfix']; ?></p>
        <a href="?update=1">Proceed with installation/upgrade</a>
        </div>
        <div style="display: <?php echo $showFinished; ?>">
            <h1>Installation / Upgrade Finished</h1>
            <p>The installation / upgrade of NuHesk has finished. You can now delete the <b>install</b> directory and access the <a href="<?php echo HESK_PATH . 'admin'; ?>">admin area</a></p>
        </div>
    </body>
</html>