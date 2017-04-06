<?php

namespace DataAccess\Files;


class FileReader {
    /**
     * @param $name string - The file name (including extension)
     * @param $folder - The folder name (relative to the ROOT of the helpdesk)
     * @returns string - The contents of the file to write
     * @throws \Exception When the file fails to save
     */
    function readFromFile($name, $folder) {
        // __DIR__ === '/{ROOT}/api/DataAccess/Files
        $location = __DIR__ . "/../../../{$folder}/{$name}";
        $fileContents = file_get_contents($location);

        if ($fileContents === false) {
            throw new \Exception("Failed to read the file!");
        }

        return $fileContents;
    }
}