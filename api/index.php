<?php
// Router: handles all REST requests to go to their proper place. Common dependency loading also happens here
require_once(__DIR__ . '/core/common.php');

// Returns the URI (or everything after the domain
$uri = $_SERVER["REQUEST_URI"];

// 