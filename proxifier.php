<?php

require_once './vendor/autoload.php';

use Proxifier\Deck;

if (!array_key_exists('cardlist', $_POST) || empty($_POST['cardlist'])) header('Location: /index.html');

$deck = new Deck(json_decode($_POST['cardlist']));
echo "<script>console.log(".json_encode($deck, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE).");</script>";
