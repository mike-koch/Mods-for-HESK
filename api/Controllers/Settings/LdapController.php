<?php

namespace Controllers\Settings;


use BusinessLogic\Exceptions\ApiFriendlyException;
use Controllers\JsonRetriever;

class LdapController extends \BaseClass {
    function post() {
        $json = JsonRetriever::getJsonData();

        if (!extension_loaded('ldap')) {
            throw new ApiFriendlyException("The LDAP module is not installed / enabled!", "Error", 400);
        }

        $domain = $json['domain'];
        $port = 389;
        if (strpos($json['domain'], ':')) {
            $parts = explode(':', $json['domain']);
            $domain = $parts[0];
            $port = $parts[1];
        }
        $ldapConnection = ldap_connect("ldap://" . $domain, $port);
    }
}