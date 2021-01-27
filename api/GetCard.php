<?php

require_once '../vendor/autoload.php';

use Proxifier\Scry;

if (!empty($_GET['name'])) {
    echo (json_encode(Scry::lookupCardByName($_GET['name'])));
}