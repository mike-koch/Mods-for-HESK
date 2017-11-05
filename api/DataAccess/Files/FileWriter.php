<?php

namespace DataAccess\Files;


class FileWriter extends \BaseClass {
    /**
     * @param $name string - The file name (including extension)
     * @param $folder - The folder name (relative to the ROOT of the helpdesk)
     * @param $contents string - The contents of the file to write
     * @return int The file size, in bytes
     * @throws \Exception When the file fails to save
     */
    function writeToFile($name, $folder, $contents) {
        // __DIR__ === '/{ROOT}/api/DataAccess/Files
        $location = __DIR__ . "/../../../{$folder}/{$name}";
        $fileSize = file_put_contents($location, $contents);

        if ($fileSize === false) {
            throw new \BaseException("Failed to save the file!");
        }

        return $fileSize;
    }
}