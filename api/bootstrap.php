<?php
spl_autoload_register(function ($class) {
    // Uncomment for debugging
    //echo 'Looking for class ' . $class . "\n";

    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require($file);
    }
});