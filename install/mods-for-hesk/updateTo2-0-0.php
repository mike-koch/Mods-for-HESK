<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

$banRS = hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`
                        UNION ALL SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");

$banRows = hesk_dbNumRows($banRS);
if (hesk_dbNumRows($banRS) > 0)
{
    $usersRS = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `active` = '1' ORDER BY `name`");
?>
<h2>Migrating IP / E-mail Bans</h2>
<p>Mods for HESK has detected that you have added IP address and/or email bans using Mods for HESK. As part of the upgrade process,
Mods for HESK will migrate these bans for you to HESK 2.6.0's IP/email ban feature. Select the user below that will be the "creator" of the bans,
then click "Submit".</p>
<form action="migrateBans.php" method="post" role="form">
    <select name="user" id="user">
        <?php
            while ($row = hesk_dbFetchAssoc($usersRS))
            { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
           <?php }
        ?>
    </select>
    <input type="submit">
</form>
<?php } else { ?>
    <h1>Installation / Update complete!</h1>
    <p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../../">Help Desk</a></p>
<?php } ?>