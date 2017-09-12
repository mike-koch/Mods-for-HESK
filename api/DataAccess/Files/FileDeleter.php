<?php

namespace DataAccess\Files;


class FileDeleter extends \BaseClass {
    function deleteFile($name, $folder) {
        $path = __DIR__ . "/../../../{$folder}/{$name}";
        if (!file_exists($path)) {
            return;
        }

        @unlink($path);
    }
}