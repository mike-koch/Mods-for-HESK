<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 1/27/2017
 * Time: 9:05 PM
 */

namespace DataAccess\Security;


use BusinessLogic\Security\BannedEmail;
use BusinessLogic\Security\BannedIp;

class BanGateway {

    /**
     * @param $heskSettings
     * @return BannedEmail[]
     */
    static function getEmailBans($heskSettings) {
        require_once(__DIR__ . '/../../businesslogic/security/BannedEmail.php');

        $rs = hesk_dbQuery("SELECT `bans`.`id` AS `id`, `bans`.`email` AS `email`,
              `users`.`id` AS `banned_by`, `bans`.`dt` AS `dt`
            FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "_banned_emails` AS `bans`
            LEFT JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "_users` AS `users`
                ON `bans`.`banned_by` = `users`.`id`
                AND `users`.`active` = '1'");

        $bannedEmails = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $bannedEmail = new BannedEmail();
            $bannedEmail->id = intval($row['id']);
            $bannedEmail->email = $row['email'];
            $bannedEmail->bannedById = $row['banned_by'] === null ? null : intval($row['banned_by']);
            $bannedEmail->dateBanned = $row['dt'];

            $bannedEmails[$bannedEmail->id] = $bannedEmail;
        }

        return $bannedEmails;
    }

    /**
     * @param $heskSettings
     * @return BannedIp[]
     */
    static function getIpBans($heskSettings) {
        require_once(__DIR__ . '/../../businesslogic/security/BannedIp.php');

        $rs = hesk_dbQuery("SELECT `bans`.`id` AS `id`, `bans`.`ip_from` AS `ip_from`,
              `bans`.`ip_to` AS `ip_to`, `bans`.`ip_display` AS `ip_display`,
              `users`.`id` AS `banned_by`, `bans`.`dt` AS `dt`
            FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "_banned_ips` AS `bans`
            LEFT JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "_users` AS `users`
                ON `bans`.`banned_by` = `users`.`id`
                AND `users`.`active` = '1'");

        $bannedIps = array();

        while ($row = hesk_dbFetchAssoc($rs)) {
            $bannedIp = new BannedIp();
            $bannedIp->id = intval($row['id']);
            $bannedIp->ipFrom = intval($row['ip_from']);
            $bannedIp->ipTo = intval($row['ip_to']);
            $bannedIp->ipDisplay = $row['ip_display'];
            $bannedIp->bannedById = $row['banned_by'] === null ? null : intval($row['banned_by']);
            $bannedIp->dateBanned = $row['dt'];

            $bannedIps[$bannedIp->id] = $bannedIp;
        }

        return $bannedIps;
    }
}