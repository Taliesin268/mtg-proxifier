<?php

require_once '../vendor/autoload.php';

use Proxifier\Scry;

$json = json_decode(file_get_contents('php://input'));

if (!empty($json->names)) {
    echo (json_encode(Scry::lookupManyCardsByNames($json->names)));
}

if (!empty($_GET['name'])) {
    echo (json_encode(Scry::lookupCardByName($_GET['name'])));
}
