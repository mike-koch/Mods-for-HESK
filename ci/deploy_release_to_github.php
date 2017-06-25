<?php
$zip_name = "release.zip";

if (!extension_loaded("zip")) {
    die("Cannot zip file contents!");
}

$zip = new ZipArchive();
$res = $zip->open($zip_name, ZipArchive::CREATE);
if ($res !== true) {
    die("Failed to create zip!\n");
}

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("../"),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    echo "\nWorking with file " . $file->getFilename() . "\n";

    continue;

    if (substr($file->getFilename(), 0, strlen($file->getFilename())) === "." ||
        strtolower($file->getFilename()) === 'attachments' ||
        strtolower($file->getFilename()) === "contributing.md" ||
        strtolower($file->getFilename() === "ci") ||
        strtolower($file->getFilename() === "apidoc.json")) {
        //-- Don"t compress . files
        echo "Skipped file.\n";
        continue;
    }

    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen("../") + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    } else {
        echo "Skipped directory " . $file->getFilename() . "\n";
    }
}