<?php

namespace Controllers\System;


class HeskVersionController extends \BaseClass {
    static function getHeskVersion() {
        global $hesk_settings;

        return self::getLatestVersion('__latest.txt', 'https://www.hesk.com/version', $hesk_settings);
    }

    static function getModsForHeskVersion() {
        global $hesk_settings;

        return self::getLatestVersion('__latest-mfh.txt', 'https://www.mods-for-hesk.com/latest-version', $hesk_settings);
    }

    private static function getLatestVersion($fileName, $url, $hesk_settings) {
        if (file_exists(__DIR__ . '/../../../' . $hesk_settings['cache_dir'] . '/' . $fileName) &&
            preg_match('/^(\d+)\|([\d.]+)+$/',
                @file_get_contents(__DIR__ . '/../../../' . $hesk_settings['cache_dir'] . '/' . $fileName), $matches) &&
            (time() - intval($matches[1])) < 3600) {
            return output(array('version' => $matches[2]));
        }

        // Try using cURL
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            $latest = curl_exec($ch);
            curl_close($ch);

            self::cacheLatestVersion($latest, $fileName, $hesk_settings);

            return output(array('version' => $latest));
        }

        // Try using a simple PHP function instead
        if ($latest = @file_get_contents($url)) {
            self::cacheLatestVersion($latest, $fileName, $hesk_settings);

            return output(array('version' => $latest));
        }

        // Can't check automatically, will need a manual check
        return http_response_code(408);
    }

    private static function cacheLatestVersion($latest, $fileName, $hesk_settings) {
        @file_put_contents(__DIR__ . '/../../../' . $hesk_settings['cache_dir'] . '/' . $fileName,
            time() . '|' . $latest);
    }
}