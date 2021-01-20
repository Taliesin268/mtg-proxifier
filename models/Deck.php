<?php

namespace Proxifier;

require_once './vendor/autoload.php';

use ArrayAccess;
use Iterator;
use JsonSerializable;

/**
 * Class CardList
 *
 * Represents a list of cards.
 *
 * Serves as a wrapper around an array of cards.
 */
class Deck implements ArrayAccess, Iterator, JsonSerializable
{
    /** @var array The cards in this deck. */
    private array $cards = [];
    /** @var int The pointer for use in iterations. */
    private int $pointer = 0;

    /**
     * CardList constructor.
     * @param array $deck An array of strings representing card names preceded by the quantity of that card.
     */
    public function __construct(array $deck)
    {
        foreach ($deck as $cardString) {
            // Find all the cards based on this line
            $foundCards = $this->interpretLine($cardString);

            // If there were no found cards, skip to the next row
            if (empty($foundCards) || empty($foundCards['cards'])) {
                continue;
            }

            // Add the first card found a number of times equal to its quantity in the deck.
            for($i = 0; $i < $foundCards['quantity'];$i++) {
                $card = $foundCards['cards'][0];
                $this->cards[] = $card;
            }
        }
    }

    /**
     * Converts a string into a collection of cards.
     *
     * Converts a string in the format '1 [MIR#71] Kukemssa Pirates' into a card, and quantity.
     *
     * @param string $line The string to be interpreted.
     * @return array|null
     */
    public function interpretLine(string $line): array|bool
    {
        // Remove everything after the first '//' (exclude comments)
        $line = explode('//', $line)[0];

        // Trim the spaces from the line
        $line = trim($line);

        // Get the quantity, set, number in set, and name from the input string.
        $match = preg_match('/^([0-9]* )?(\[([A-Z0-9]{1,})#?([A-Z0-9]{1,})\] )?(.*)$/', $line, $matches);

        // If there is no match, return false.
        if (!$match) {
            return false;
        }

        // If the quantity is set to be less than 1, increase it to 1.
        if($matches[1] < 1) {
            $matches[1] = 1;
        }

        // Return this card
        return [
            'rawString' => $matches[0],
            'quantity' => $matches[1] ?? 1,
            'set' => $matches[3],
            'number' => $matches[4],
            'name' => $matches[5],
            'cards' => $this->findCards($matches[5], $matches[3], $matches[4])
        ];
    }

    /**
     * Searches for a card.
     *
     * Searches for a card based on the name, set, and / or number provided.
     *
     * @param $name
     * @param $set
     * @param $number
     * @return array|bool Either returns an array of found cards, empty array if no cards were found, or false if no
     *  search terms were provided.
     */
    public function findCards($name, $set, $number): array|bool
    {
        $searchArray = [];

        // If there is a name, search by that.
        if (!empty($name)) {
            $searchArray['name'] = $name;
        }

        // If there is a set, search by that.
        if (!empty($set)) {
            $searchArray['set'] = $set;
        }

        // If there is a number, search by that.
        if (!empty($number)) {
            $searchArray['number'] = $number;
        }

        // If there is something to search for, return what we find, otherwise return false.
        if (!empty($searchArray)) {
            return Card::where($searchArray)->all();
        } else {
            return false;
        }
    }

    /**
     * Checks if an offset is a valid id of a card in this deck.
     *
     * @param mixed $offset The index to check.
     * @return bool Whether or not the provided offset is a valid index of a card in this deck.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->cards[$offset]);
    }

    /**
     * Gets a particular card.
     *
     * Gets a card at the specified offset (if a card exists, otherwise returns null)
     *
     * @param mixed $offset the index to look retrieve.
     * @return Card|null the card at this index, or null if not found.
     */
    public function offsetGet($offset): Card|null
    {
        return isset($this->cards[$offset]) ? $this->cards[$offset] : null;
    }

    /**
     * Sets a particular card.
     *
     * Sets the card at the specified offset.
     *
     * @param mixed $offset where to set the card.
     * @param mixed $value the card to put in this position.
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->cards[] = $value;
        } else {
            $this->cards[$offset] = $value;
        }
    }

    /**
     * Unsets a particular card.
     *
     * Unsets the card at the specified offset.
     *
     * @param mixed $offset where to unset the card.
     */
    public function offsetUnset($offset)
    {
        unset($this->cards[$offset]);
    }

    /**
     * Gets the current card.
     *
     * Gets the card at the index currently defined by the pointer.
     *
     * @return Card the card at the current index.
     */
    public function current(): Card
    {
        return $this->cards[$this->pointer];
    }

    /**
     * Move the pointer to the next card.
     */
    public function next(): void
    {
        $this->pointer++;
    }

    /**
     * Gets the current position of the pointer.
     *
     * @return int The current index.
     */
    public function key(): int
    {
        return $this->pointer;
    }

    /**
     * Checks whether the current pointer is in a valid position.
     *
     * @return bool True if a valid pointer is provided.
     */
    public function valid(): bool
    {
        return $this->pointer < count($this->cards);
    }

    /**
     * Sets the pointer back to the beginning.
     */
    public function rewind(): void
    {
        $this->pointer = 0;
    }

    /**
     * Gets the object to JSON Serialize.
     *
     * @return array The cards in this deck.
     */
    public function jsonSerialize():array
    {
        return $this->cards;
    }
}
