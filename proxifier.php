<!DOCTYPE html>
<html lang="en">

<head>
    <title>MTG Proxifier</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link href="css/magic-font.css" rel="stylesheet">
    <link href="css/card.css" rel="stylesheet">
</head>
<body>
<?php

require_once './vendor/autoload.php';

use Proxifier\Deck;

if (!array_key_exists('cardlist', $_POST) || empty($_POST['cardlist'])) header('Location: /index.html');

$deck = new Deck(json_decode($_POST['cardlist']));

// Log the deck to the console
echo "<script>console.log(".json_encode($deck, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE).");</script>";

foreach($deck as $card) {
    echo $card->toHTML();
}
?>
</body>
