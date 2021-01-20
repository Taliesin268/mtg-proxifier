<?php

use mtgsdk\Card;

require_once './vendor/autoload.php';

/**
 * Class CardList
 * Represents a list of cards.
 */
class CardList implements ArrayAccess, Iterator
{
    //private array $cards = [];
    private array $cards = [];
    private int $pointer = 0;

    /**
     * CardList constructor.
     * @param array $deck
     */
    public function __construct(array $deck)
    {
        foreach ($deck as $cardString) {
            $card = $this->interpretLine($cardString);
            if (!isset($card) || !isset($card['cards'])) continue;
            $newCard = $card['cards'];
            $this->cards[] = $newCard[0];
        }
    }

    public function interpretLine(string $line)
    {
        // Remove everything after the first '//'
        $line = explode('//', $line)[0];
        // Remove everything after the first '#'
        //$line = explode('#', $line)[0];
        // Trim the spaces from the line
        $line = trim($line);

        $match = preg_match('/^([0-9]* )?(\[([A-Z0-9]{1,3})#?([A-Z0-9]{1,4})\] )?(.*)$/', $line, $matches);
        if (!$match) {
            return null;
        }
        return [
            'rawString' => $matches[0],
            'quantity' => $matches[1],
            'set' => $matches[3],
            'number' => $matches[4],
            'name' => $matches[5],
            'cards' => $this->findCards($matches[5], $matches[3], $matches[4])
        ];
    }

    public function findCards($name, $set, $number)
    {
        $searchArray = [];
        if (!empty($name)) {
            $searchArray['name'] = $name;
        }
        if (!empty($set)) {
            $searchArray['set'] = $set;
        }
        if (!empty($number)) {
            $searchArray['number'] = $number;
        }
        if (!empty($searchArray)) {
            return Card::where($searchArray)->all();
        } else {
            return false;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->cards[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->cards[$offset]) ? $this->cards[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->cards[] = $value;
        } else {
            $this->cards[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->cards[$offset]);
    }

    public function current()
    {
        return $this->cards[$this->pointer];
    }

    public function next()
    {
        $this->pointer++;
    }

    public function key()
    {
        return $this->pointer;
    }

    public function valid()
    {
        return $this->pointer < count($this->cards);
    }

    public function rewind()
    {
        $this->pointer = 0;
    }
}


if (!array_key_exists('cardlist', $_POST) || empty($_POST['cardlist'])) header('Location: /index.html');

$cardlist = new CardList(json_decode($_POST['cardlist']));
foreach ($cardlist as $i => $card) {
    echo "<br><br>Card Number $i:<br>";
    echo "<ul>";
    echo "<li>Name: $card->name</li>";
    echo "<li>Set: $card->set</li>";
    echo "<li>Number: $card->number</li>";
    echo "<li>Colors: <ul>";
    foreach ($card->colors as $color) {
        echo "<li>$color</li>";
    }
    echo "</ul></li>";
    echo "<li>Cost: $card->manaCost</li>";
    echo "<li>Type: $card->type</li>";
    echo "</ul>";
}
