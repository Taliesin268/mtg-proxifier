<?php

namespace Proxifier;

use JsonSerializable;

require_once './vendor/autoload.php';

/**
 * Class Card
 * @package Proxifier
 *
 * A wrapper around the mtgsdk Card that adds additional functionality.
 */
class Card extends \mtgsdk\Card implements JsonSerializable
{
    /**
     * Convert the card to json.
     *
     * @return array an associated array representing this card.
     */
    public function jsonSerialize(): array
    {
        $cardFields = [
            'id' => $this->id,
            'name' => $this->name,
            'manaCost' => $this->manaCost ?? '',
            'colors' => $this->colors,
            'type' => $this->type,
            'text' => $this->text ?? '',
            'set' => [
                'name' => $this->setName,
                'code' => $this->set,
                'number' => $this->number
            ]
        ];

        // If it's a creature, add power and toughness.
        if(strpos($this->type, 'Creature') !== false) {
            $cardFields['power'] = $this->power;
            $cardFields['toughness'] = $this->toughness;
        }

        // If it's a planeswalker, add loyalty.
        if(strpos($this->type, 'Planeswalker') !== false) {
            $cardFields['loyalty'] = $this->loyalty;
        }

        return $cardFields;
    }

    /**
     * Converts this card into a string.
     *
     * @return string the name of the card.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}