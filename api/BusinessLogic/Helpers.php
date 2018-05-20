<?php

namespace BusinessLogic;


class Helpers extends \BaseClass {
    static function getHeader($key) {
        $headers = getallheaders();

        $uppercaseHeaders = array();
        foreach ($headers as $header => $value) {
            $uppercaseHeaders[strtoupper($header)] = $value;
        }

        return isset($uppercaseHeaders[$key])
            ? $uppercaseHeaders[$key]
            : NULL;
    }

    static function hashToken($token) {
        return hash('sha512', $token);
    }

    static function safeArrayGet($array, $key) {
        return $array !== null && array_key_exists($key, $array)
            ? $array[$key]
            : null;
    }

    static function boolval($val) {
        return $val == true;
    }

    static function heskHtmlSpecialCharsDecode($in) {
        return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $in);
    }

    static function heskMakeUrl($text, $class = '', $shortenLinks = true) {
        if (!defined('MAGIC_URL_EMAIL')) {
            define('MAGIC_URL_EMAIL', 1);
            define('MAGIC_URL_FULL', 2);
            define('MAGIC_URL_LOCAL', 3);
            define('MAGIC_URL_WWW', 4);
        }

        $class = ($class) ? ' class="' . $class . '"' : '';

        // matches a xxxx://aaaaa.bbb.cccc. ...
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])(' . "[a-z][a-z\d+]*:/{2}(?:(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?" . ')#iu',
            function($matches) use ($class, $shortenLinks) {
                return self::makeClickableCallback(MAGIC_URL_FULL, $matches[1], $matches[2], '', $class, $shortenLinks);
            },
            $text
        );

        // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
        $text = preg_replace_callback(
            '#(^|[\n\t (>])(' . "www\.(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?" . ')#iu',
            function($matches) use ($class, $shortenLinks) {
                return self::makeClickableCallback(MAGIC_URL_WWW, $matches[1], $matches[2], '', $class, $shortenLinks);
            },
            $text
        );

        // matches an email address
        $text = preg_replace_callback(
            '/(^|[\n\t (>])(' . '((?:[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&amp;)+)@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,63})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)' . ')/iu',
            function($matches) use ($class, $shortenLinks) {
                return self::makeClickableCallback(MAGIC_URL_EMAIL, $matches[1], $matches[2], '', $class, $shortenLinks);
            },
            $text
        );

        return $text;
    }

    static function makeClickableCallback($type, $whitespace, $url, $relative_url, $class, $shortenLinks)
    {
        global $hesk_settings;

        $orig_url = $url;
        $orig_relative = $relative_url;
        $append = '';
        $url = htmlspecialchars_decode($url);
        $relative_url = htmlspecialchars_decode($relative_url);

        // make sure no HTML entities were matched
        $chars = array('<', '>', '"');
        $split = false;

        foreach ($chars as $char) {
            $next_split = strpos($url, $char);
            if ($next_split !== false) {
                $split = ($split !== false) ? min($split, $next_split) : $next_split;
            }
        }

        if ($split !== false) {
            // an HTML entity was found, so the URL has to end before it
            $append = substr($url, $split) . $relative_url;
            $url = substr($url, 0, $split);
            $relative_url = '';
        } else if ($relative_url) {
            // same for $relative_url
            $split = false;
            foreach ($chars as $char) {
                $next_split = strpos($relative_url, $char);
                if ($next_split !== false) {
                    $split = ($split !== false) ? min($split, $next_split) : $next_split;
                }
            }

            if ($split !== false) {
                $append = substr($relative_url, $split);
                $relative_url = substr($relative_url, 0, $split);
            }
        }

        // if the last character of the url is a punctuation mark, exclude it from the url
        $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];

        switch ($last_char) {
            case '.':
            case '?':
            case '!':
            case ':':
            case ',':
                $append = $last_char;
                if ($relative_url) {
                    $relative_url = substr($relative_url, 0, -1);
                } else {
                    $url = substr($url, 0, -1);
                }
                break;

            // set last_char to empty here, so the variable can be used later to
            // check whether a character was removed
            default:
                $last_char = '';
                break;
        }

        $short_url = ($hesk_settings['short_link'] && strlen($url) > 70 && $shortenLinks) ? substr($url, 0, 54) . ' ... ' . substr($url, -10) : $url;

        switch ($type) {
            case MAGIC_URL_LOCAL:
                $tag = 'l';
                $relative_url = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
                $url = $url . '/' . $relative_url;
                $text = $relative_url;

                // this url goes to http://domain.tld/path/to/board/ which
                // would result in an empty link if treated as local so
                // don't touch it and let MAGIC_URL_FULL take care of it.
                if (!$relative_url) {
                    return $whitespace . $orig_url . '/' . $orig_relative; // slash is taken away by relative url pattern
                }
                break;

            case MAGIC_URL_FULL:
                $tag = 'm';
                $text = $short_url;
                break;

            case MAGIC_URL_WWW:
                $tag = 'w';
                $url = 'http://' . $url;
                $text = $short_url;
                break;

            case MAGIC_URL_EMAIL:
                $tag = 'e';
                $text = $short_url;
                $url = 'mailto:' . $url;
                break;
        }

        $url = htmlspecialchars($url);
        $text = htmlspecialchars($text);
        $append = htmlspecialchars($append);

        $html = "$whitespace<a href=\"$url\" target=\"blank\" $class>$text</a>$append";

        return $html;
    } // END make_clickable_callback()

    static function fullNameToFirstName($full_name) {
        $name_parts = explode(' ', $full_name);

        // Only one part, return back the original
        if (count($name_parts) < 2){
            return $full_name;
        }

        $first_name = self::heskMbStrToLower($name_parts[0]);

        // Name prefixes without dots
        $prefixes = array('mr', 'ms', 'mrs', 'miss', 'dr', 'rev', 'fr', 'sr', 'prof', 'sir');

        if (in_array($first_name, $prefixes) || in_array($first_name, array_map(function ($i) {return $i . '.';}, $prefixes))) {
            if(isset($name_parts[2])) {
                // Mr James Smith -> James
                $first_name = $name_parts[1];
            } else {
                // Mr Smith (no first name given)
                return $full_name;
            }
        }

        // Detect LastName, FirstName
        if (self::heskMbSubstr($first_name, -1, 1) == ',') {
            if (count($name_parts) == 2) {
                $first_name = $name_parts[1];
            } else {
                return $full_name;
            }
        }

        // If the first name doesn't have at least 3 chars, return the original
        if(self::heskMbStrlen($first_name) < 3) {
            return $full_name;
        }

        // Return the name with first character uppercase
        return self::heskUcfirst($first_name);
    }

    static function heskMbStrToLower($in) {
        return function_exists('mb_strtolower') ? mb_strtolower($in) : strtolower($in);
    }

    static function heskMbStrlen($in) {
        return function_exists('mb_strlen') ? mb_strlen($in, 'UTF-8') : strlen($in);
    }

    static function heskMbSubstr($in, $start, $length) {
        return function_exists('mb_substr') ? mb_substr($in, $start, $length, 'UTF-8') : substr($in, $start, $length);
    }

    static function heskUcfirst($in) {
        return function_exists('mb_convert_case') ? mb_convert_case($in, MB_CASE_TITLE, 'UTF-8') : ucfirst($in);
    }
}