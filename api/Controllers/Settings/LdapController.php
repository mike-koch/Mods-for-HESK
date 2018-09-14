<?php

namespace Controllers\Settings;


use BusinessLogic\Exceptions\ApiFriendlyException;
use Controllers\JsonRetriever;

class LdapController extends \BaseClass {
    function post() {
        set_error_handler(function() {return true;});
        try {
            $json = JsonRetriever::getJsonData();

            if (!extension_loaded('ldap')) {
                throw new ApiFriendlyException("The LDAP module is not installed / enabled!", "Error", 400);
            }

            $domain = $json['domain'];
            $port = 389;
            $baseDn = $json['baseDn'];
            if (strpos($json['domain'], ':')) {
                $parts = explode(':', $json['domain']);
                $domain = $parts[0];
                $port = $parts[1];
            }
            $ldapConnection = ldap_connect("ldap://" . $domain, $port);

            $output = '';
            if ($ldapConnection) {
                $output .= " [INFO] Established LDAP resource. Attempting to bind.\n";

                $bind = ldap_bind($ldapConnection, $json['username'], $json['password']);

                if (!$bind) {
                    $err = ldap_error($ldapConnection);
                    //ldap_get_option($ldapConnection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);

                    if ($err !== null) {
                        $output .= "[ERROR] {$err}\n";
                    }
                } else {
                    $output .= " [INFO] Connection established!\n";
                    $output .= " [INFO] Searching for any users in the base DN...\n";

                    $res = ldap_list($ldapConnection, $baseDn, '(objectClass=user)', array('cn'));
                    $entries = ldap_get_entries($ldapConnection, $res);

                    if ($entries['count'] === 0) {
                        $output .= " [WARN] No results found. If you expect results, double-check your settings and try again.\n";
                    } else {
                        for ($i = 0; $i < $entries['count']; $i++) {
                            $output .= " [INFO] Found: {$entries[$i]['cn'][0]}\n";
                        }
                    }
                }
            }

            restore_error_handler();
            return output(array('message' => $output));
        } catch (\Exception $e) {
            restore_error_handler();
            return output(array('message' => $e), 500);
        }
    }
}