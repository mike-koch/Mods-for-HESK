<?php

namespace BusinessLogic;


class Validators extends \BaseClass {
    /**
     * @param string $address - the email address
     * @param array $multiple_emails - true if HESK (or custom field) supports multiple emails
     * @param bool $return_emails (def. true): return the email address(es). Otherwise a boolean is returned
     *
     * @return mixed|null|string - array if multiple valid emails, null if no email and not required, string if valid email
     */
    static function validateEmail($address, $multiple_emails, $return_emails = true) {
        /* Allow multiple emails to be used? */
        if ($multiple_emails) {
            /* Make sure the format is correct */
            $address = preg_replace('/\s/', '', $address);
            $address = str_replace(';', ',', $address);

            /* Check if addresses are valid */
            $all = explode(',', $address);
            foreach ($all as $k => $v) {
                if (!self::isValidEmail($v)) {
                    unset($all[$k]);
                }
            }

            /* If at least one is found return the value */
            if (count($all)) {
                if ($return_emails) {
                    return implode(',', $all);
                }

                return true;
            } elseif (!$return_emails) {
                return false;
            }
        } else {
            /* Make sure people don't try to enter multiple addresses */
            $address = str_replace(strstr($address, ','), '', $address);
            $address = str_replace(strstr($address, ';'), '', $address);
            $address = trim($address);

            /* Valid address? */
            if (self::isValidEmail($address)) {
                if ($return_emails) {
                    return $address;
                }

                return true;
            } else {
                return false;
            }
        }

        //-- We shouldn't get here
        return false;
    } // END hesk_validateEmail()

    /**
     * @param $email
     * @return bool
     */
    static function isValidEmail($email) {
        /* Check for header injection attempts */
        if (preg_match("/\r|\n|%0a|%0d/i", $email)) {
            return false;
        }

        /* Does it contain an @? */
        $atIndex = strrpos($email, "@");
        if ($atIndex === false) {
            return false;
        }

        /* Get local and domain parts */
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);

        /* Check local part length */
        if ($localLen < 1 || $localLen > 64) {
            return false;
        }

        /* Check domain part length */
        if ($domainLen < 1 || $domainLen > 254) {
            return false;
        }

        /* Local part mustn't start or end with a dot */
        if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            return false;
        }

        /* Local part mustn't have two consecutive dots*/
        if (strpos($local, '..') !== false) {
            return false;
        }

        /* Check domain part characters */
        if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            return false;
        }

        /* Domain part mustn't have two consecutive dots */
        if (strpos($domain, '..') !== false) {
            return false;
        }

        /* Character not valid in local part unless local part is quoted */
        if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) /* " */ {
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) /* " */ {
                return false;
            }
        }

        /* All tests passed, email seems to be OK */
        return true;
    } // END hesk_isValidEmail()
}