<?php

namespace DataAccess\Files;


use BusinessLogic\Exceptions\ApiFriendlyException;

class FileDeleter {
    function deleteFile($name, $folder) {
        $path = __DIR__ . "/../../../{$folder}/{$name}";
        if (!file_exists($path)) {
            return;
        }

        @unlink($path);
    }
}