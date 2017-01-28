<?php

namespace BusinessLogic\Security;


use DataAccess\Security\BanGateway;

class BanRetriever {
    /**
     * @param $email
     * @param $heskSettings
     * @return bool
     */
    static function isEmailBanned($email, $heskSettings) {
        require_once(__DIR__ . '/../../dao/security/BanGateway.php');

        $bannedEmails = BanGateway::getEmailBans($heskSettings);

        foreach ($bannedEmails as $bannedEmail) {
            if ($bannedEmail->email === $email) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $ip int the IP address, converted beforehand using ip2long()
     * @param $heskSettings
     * @return bool
     */
    static function isIpAddressBanned($ip, $heskSettings) {
        require_once(__DIR__ . '/../../dao/security/BanGateway.php');

        $bannedIps = BanGateway::getIpBans($heskSettings);

        foreach ($bannedIps as $bannedIp) {
            if ($bannedIp->ipFrom <= $ip && $bannedIp->ipTo >= $ip) {
                return true;
            }
        }

        return false;
    }
}