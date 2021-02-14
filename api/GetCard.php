<?php

require_once '../vendor/autoload.php';

use Proxifier\Scry;

// Get the JSON input (if it exists)
$json = json_decode(file_get_contents('php://input'));

// If 'names' have been provided...
if (!empty($json->names)) {
    // Lookup those names and return them.
    header('Content-Type: application/json');
    die (json_encode(Scry::lookupManyCardsByNames($json->names)));
}

// If 'name' has been provided...
if (!empty($_GET['name'])) {
    // Lookup that one card name and return it.
    header('Content-Type: application/json');
    die (json_encode(Scry::lookupCardByName($_GET['name'])));
}

?>