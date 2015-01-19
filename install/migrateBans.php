<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$updateSuccess = true;

hesk_dbConnect();

// Get the ID of the creator
$creator = $_POST['user'];

// Insert the email bans
$emailBanRS = hesk_dbQuery("SELECT `Email` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`");
while ($row = hesk_dbFetchAssoc($emailBanRS)) {
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_emails` (`email`, `banned_by`, `dt`)
                VALUES ('".hesk_dbEscape($row['Email'])."', ".$creator.", NOW())");
}

// Insert the IP bans
$ipBanRS = hesk_dbQuery("SELECT `RangeStart`, `RangeEnd` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");
while ($row = hesk_dbFetchAssoc($ipBanRS)) {
    $ipFrom = long2ip($row['RangeStart']);
    $ipTo = long2ip($row['RangeEnd']);
    $ipDisplay = $ipFrom == $ipTo ? $ipFrom : $ipFrom . ' - ' . $ipTo;
    hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."banned_ips` (`ip_from`, `ip_to`, `ip_display`, `banned_by`, `dt`)
                VALUES (".$row['RangeStart'].", ".$row['RangeEnd'].", '".$ipDisplay."', ".$creator.", NOW())");
}
// Migration Complete. Drop Tables.
hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");
hesk_dbQuery("DROP TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`");

if ($updateSuccess) {
?>

<h1>Installation / Update complete!</h1>
<p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>

<?php } ?>