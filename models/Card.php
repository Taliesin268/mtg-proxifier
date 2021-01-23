<?php

namespace Proxifier;

use JsonSerializable;

require_once './vendor/autoload.php';

/**
 * Class Card
 * @package Proxifier
 *
 * A wrapper around the mtgsdk Card that adds additional functionality.
 *
 * @property-read string $color The name of this card's color.
 * @property-read string $htmlText The text of this card with newlines replaced with '<br /> <br />'.
 */
class Card extends \mtgsdk\Card implements JsonSerializable
{
    /**
     * Magic Get Methods.
     * @param $name string The parameter name to get.
     * @return mixed|string The parameter value.
     *
     * @noinspection PhpMissingParamTypeInspection Needs to be compatible with parent class.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'manaCost':
                return parent::__get('manaCost') ?? '';
            case 'text':
                return parent::__get('text') ?? '';
            case 'color':
                return $this->getColorString();
            case 'htmlText':
                return str_replace('<br />', '<br /><br />', nl2br($this->text));
        }

        return parent::__get($name);
    }

    /**
     * Gets the HTML representation of this card.
     *
     * @return string The HTML representation of this card.
     */
    public function toHTML(): string
    {
        // Init the footer.
        $footerHtml = '';
        $extraTextClasses = '';

        // If it's a creature, add power & toughness.
        if (strpos($this->type, 'Creature') !== false) {
            $footerHtml = "<div class='card-footer'><div class='power-block'>$this->power / $this->toughness</div></div>";
            $extraTextClasses = 'has-footer';
        }

        // If it's a planeswalker, add loyalty.
        if (strpos($this->type, 'Planeswalker') !== false) {
            $footerHtml = "<div class='card-footer'><div class='power-block'>$this->loyalty</div></div>";
            $extraTextClasses = 'has-footer';
        }

        // Write the rest of the card.
        $html = "
            <div class='card $this->color'>
                <div class='card-content'>
                    <div class='card-header'>
                        <div class='card-name auto-resize'>$this->name</div>
                        <div class='mana-cost'>$this->manaCost</div>
                    </div>
                    <div class='card-image'></div>
                    <div class='card-type auto-resize'>$this->type</div>
                    <div class='card-text auto-resize $extraTextClasses'>$this->htmlText</div>
                    $footerHtml
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
        return preg_replace_callback("|\{(([A-z0-9]+)(\/([A-z0-9]+))?)\}|", function ($matches) {
            if (empty($matches[4])) {
                return self::convertIndividualManaSymbolToHtml($matches[2]);
            } else if ($matches[4] == 'P') {
                return self::convertIndividualManaSymbolToHtml($matches[2],true,true);
            } else if ($matches[2] == 'P') {
                return self::convertIndividualManaSymbolToHtml($matches[4],true,true);
            } else {
                return '<div class="mi-split">'
                    . self::convertIndividualManaSymbolToHtml($matches[2], true)
                    . self::convertIndividualManaSymbolToHtml($matches[4], true)
                    . '</div>';
            }
        }, $inputString);
    }

    public static function convertIndividualManaSymbolToHtml(string $manaSymbol, bool $split = false, bool $phyrexian = false): string
    {
        // Minimise the mana symbol (for use in html).
        $manaSymbol = strtolower($manaSymbol);

        // Convert 'q' into 'untap'.
        $manaSymbol = $manaSymbol == 'q' ? 'untap' :$manaSymbol;

        // If it's for a split mana symbol, add 'mi-mana'.
        if (!$split) {
            $manaSymbol .= ' mi-mana';
        } else if ($phyrexian) {
            // If it's phyrexian mana, make it 'mi-p mi-mana-manaSymbol'.
            $manaSymbol = 'p mi-mana-' . $manaSymbol;
        }

        // return the HTML tag for this symbol.
        return "<i class='mi mi-$manaSymbol'></i>";

    }

    /**
     * Gets the name of the color of this card.
     *
     * @return string The name of the color of this card.
     */
    public function getColorString(): string
    {
        // If it has no colors, it's colorless.
        if (empty($this->colors)) {
            return 'colorless';
        }

        // If it has more than 2 colors, it's multicolored.
        if (count($this->colors) > 2) {
            return 'multicolored';
        }

        // If it's exactly 2 colors, return both colors hyphenated.
        if (count($this->colors) == 2) {
            return $this->getTwoHouseColorName($this->colors);
        }

        // If it's only 1 color, return that color.
        if (count($this->colors) == 1) {
            return strtolower($this->colors[0]);
        }
    }

    /**
     * @param $colorArray array an array of 2 colors.
     * @return string the house name, or '' if invalid colors provided.
     */
    private function getTwoHouseColorName($colorArray): string
    {
        // Go through the Black colors.
        if (in_array('Black', $this->colors)) {
            if (in_array('Blue', $this->colors)) {
                return 'dimir';
            } else if (in_array('Green', $this->colors)) {
                return 'golgari';
            } else if (in_array('Red', $this->colors)) {
                return 'rakdos';
            } else if (in_array('White', $this->colors)) {
                return 'orzhov';
            }
        }

        // Go through the Blue Colors
        if (in_array('Blue', $this->colors)) {
            if (in_array('Green', $this->colors)) {
                return 'simic';
            } else if (in_array('Red', $this->colors)) {
                return 'izzet';
            } else if (in_array('White', $this->colors)) {
                return 'azorius';
            }
        }

        // Go through the Green Colors
        if (in_array('Green', $this->colors)) {
            if (in_array('Red', $this->colors)) {
                return 'gruul';
            } else if (in_array('White', $this->colors)) {
                return 'selesnya';
            }
        }

        if (in_array('Red', $this->colors) && in_array('White', $this->colors)) {
            return 'boros';
        }

        return '';
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
            'set' => [
                'name' => $this->setName,
                'code' => $this->set,
                'number' => $this->number
            ]
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