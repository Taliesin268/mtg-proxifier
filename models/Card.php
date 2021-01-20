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
    private function sanitize(): void
    {
        $this->manaCost = $this->manaCost ?? '';
        $this->text = $this->text ?? '';
    }

    /**
     * @return string
     */
    public function toHTML(): string
    {
        // Clean up missing fields before beginning.
        $this->sanitize();

        // Init the footer.
        $footerHtml = '';

        // If it's a creature, add power & toughness.
        if(strpos($this->type, 'Creature') !== false) {
            $footerHtml = "<div class='power-block'>$this->power / $this->toughness</div>";
        }

        // If it's a planeswalker, add loyalty.
        if(strpos($this->type, 'Planeswalker') !== false) {
            $footerHtml = "<div class='power-block'>$this->loyalty</div>";
        }

        // Write the rest of the card.
        $html = "
            <div class='card'>
                <div class='card-content'>
                    <div class='card-header'>
                        <div class='card-name'>$this->name</div>
                        <div class='mana-cost'>$this->manaCost</div>
                    </div>
                    <div class='card-image'></div>
                    <div class='card-type'>$this->type</div>
                    <div class='card-text'>$this->text</div>
                    <div class='card-footer'>
                        $footerHtml
                    </div>
                </div>
            </div>
        ";

        // Return the HTML with code converted to mana symbols.
        return self::convertManaSymbolsToHtml($html);
    }

    /**
     * @param string $inputString
     * @return string
     */
    public static function convertManaSymbolsToHtml(string $inputString): string
    {
        return preg_replace_callback("|\{([A-z0-9]*)\}|", function($matches) {
            $match = $matches[1] == 't' ? 'tap' : strtolower($matches[1]);
            return "<i class='mi mi-$match mi-mana'></i>";
        }, $inputString);
    }

    /**
     * Convert the card to json.
     *
     * @return array an associated array representing this card.
     */
    public function jsonSerialize(): array
    {
        // Clean up missing fields before beginning.
        $this->sanitize();

        $cardFields = [
            'id' => $this->id,
            'name' => $this->name,
            'manaCost' => $this->manaCost,
            'colors' => $this->colors,
            'type' => $this->type,
            'text' => $this->text,
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