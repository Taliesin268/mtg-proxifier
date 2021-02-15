<?php

namespace Proxifier;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * Class Set
 * @package Proxifier
 *
 * @property-read string $name This set's full text name.
 * @property-read string $code This set's code name.
 */
class Set {
    public string $name;
    public string $code;

    /**
     * Set constructor.
     * @param string $setName This set's full text name.
     * @param string $setCode This set's code name.
     */
    public function __construct(string $setName, string $setCode) {
        $this->name = $setName;
        $this->code = $setCode;
    }
}

/**
 * Class Card
 * @package Proxifier
 *
 * @property-read string $rawQuery The raw Scryfall DB result for this card.
 * @property-read string $id A unique ID for this card in Scryfall’s database.
 * @property-read string $name The name of this card. If this card has multiple faces, this field will contain both
 * names separated by ␣//␣.
 * @property-read string $manaCost The mana cost for this card. This value will be any empty string "" if the cost is
 * absent.
 * @property-read string $type The type line of this card.
 * @property-read string $text The Oracle text for this card, if any.
 * @property-read string|null $power This card’s power, if any. Note that some cards have powers that are not numeric, such
 * as *.
 * @property-read string|null $toughness This card’s toughness, if any. Note that some cards have toughnesses that are not
 * numeric, such as *.
 * @property-read string|null $loyalty This loyalty if any. Note that some cards have loyalties that are not numeric, such as
 * X.
 * @property-read array $setNumber This card’s collector number. Note that collector numbers can contain non-numeric
 * characters, such as letters or ★.
 * @property-read Set $set This card’s set code and set name.
 * @property-read array $colors This card’s colors.
 */
class Card implements JsonSerializable
{
    const SUPERTYPES = ['Basic', 'Legendary', 'Snow', 'World'];
    const CARD_TYPES = ['Artifact', 'Creature', 'Enchantment', 'Instant', 'Land', 'Planeswalker', 'Sorcery'];

    public string $id;
    public string $name;
    public string $manaCost;
    public string $type;
    public string $text;
    public string|null $power;
    public string|null $toughness;
    public string|null $loyalty;
    public string $setNumber;

    public Set $set;
    public array $colors;

    /**
     * Card constructor.
     *
     * @param stdClass|string $cardInfo The raw Scryfall DB response for this card.
     * @throws Exception If the card could not be created.
     */
    public function __construct(string | stdClass $cardInfo)
    {
        // Decode the data
        if (gettype($cardInfo) == 'string') {
            $card = json_decode($cardInfo);
        } else {
            $card = $cardInfo;
        }

        if (empty($card->id)) throw new Exception('Could not create Card. Info provided does not have an ID.');

        // Save each of the fields to this card
        $this->id = $card->id;
        $this->name = $card->name;
        $this->manaCost = $card->mana_cost ?? '';
        $this->type = $card->type_line;
        $this->text = $card->oracle_text ?? '';
        $this->power = $card->power ?? null;
        $this->toughness = $card->toughness ?? null;
        $this->loyalty = $card->loyalty ?? null;
        $this->setNumber = $card->collector_number;
        $this->set = new Set(
            $card->set_name,
            $card->set
        );
        $this->colors = $card->colors ?? [];
    }

    public function getSuperTypes(): string|array
    {
        $types = explode(' ', $this->type);
        $types = array_map('trim', $types);
        $supertypes = array_intersect($types, self::SUPERTYPES);
        $supertypes = array_values($supertypes);
        if (count($supertypes) == 1) {
            return $supertypes[0];
        } else {
            return array_values($supertypes);
        }
    }

    public function getCardTypes(): string|array
    {
        $types = explode(' ', $this->type);
        $types = array_map('trim', $types);
        $cardtypes = array_intersect($types, self::CARD_TYPES);
        $cardtypes = array_values($cardtypes);
        if (count($cardtypes) == 1) {
            return $cardtypes[0];
        } else {
            return array_values($cardtypes);
        }
    }

    public function getSubTypes(): string|array
    {
        $types = explode('—', $this->type);
        if (count($types) <= 1) {
            return [];
        }
        $subtypes = explode(' ', $types[1]);
        $subtypes = array_map('trim', $subtypes);
        $subtypes = array_filter($subtypes);
        $subtypes = array_values($subtypes);
        if (count($subtypes) == 1) {
            return $subtypes[0];
        } else {
            return $subtypes;
        }
    }

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
            'manaCost' => $this->manaCost,
            'colors' => $this->colors,
            'type' => $this->type,
            'text' => $this->text,
            'set' => $this->set,
            'setNumber' => $this->setNumber,

            // Cards
            'superTypes' => $this->getSuperTypes(),
            'cardTypes' => $this->getCardTypes(),
            'subTypes' => $this->getSubTypes()
        ];

        // If it's a creature, add power and toughness.
        if (strpos($this->type, 'Creature') !== false) {
            $cardFields['power'] = $this->power;
            $cardFields['toughness'] = $this->toughness;
        }

        // If it's a planeswalker, add loyalty.
        if (strpos($this->type, 'Planeswalker') !== false) {
            $cardFields['loyalty'] = $this->loyalty;
        }

        return $cardFields;
    }
}