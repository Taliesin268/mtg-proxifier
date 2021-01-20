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
     * Gets the HTML representation of this card.
     *
     * @return string The HTML representation of this card.
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
     * Converts text to mana symbols.
     *
     * Converts any text from {#} to it's html tag.
     *
     * @param string $inputString the string to replace mana symbols in.
     * @return string The string now containing html mana symbols.
     */
    public static function convertManaSymbolsToHtml(string $inputString): string
    {
        return preg_replace_callback("|\{([A-z0-9]*)\}|", function($matches) {
            // If it's 't', make it 'tap', and lowercase the result either way.
            $match = $matches[1] == 't' ? 'tap' : strtolower($matches[1]);
            // return the HTML tag for this symbol.
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