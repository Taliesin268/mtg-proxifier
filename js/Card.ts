import {resizeElement} from './FitText.js'

/**
 * Represents a set in Magic the Gathering.
 */
interface Set {
    name: string | undefined;
    code: string | undefined;
}

/**
 * Represents the data returned for a single card from the API.
 *
 * Note this doesn't represent ALL of the data provided.
 */
interface APICardResponse {
    id: string;
    manaCost: string;
    name: string;
    setNumber: string;
    text: string;

    // Types
    type: string;
    superTypes: string[] | string;
    cardTypes: string[] | string;
    subTypes: string[] | string;

    // Powers
    power: string | undefined;
    toughness: string | undefined;
    loyalty: string | undefined;

    // Non-strings
    set: Set;
    colors: string[];
}

/**
 * All the HTML components of this card.
 */
interface CardElement {
    card: HTMLDivElement;
    content: HTMLDivElement;
    header: HTMLDivElement;
    name: HTMLDivElement;
    manaCost: HTMLDivElement;
    imageWrapper: HTMLDivElement;
    type: HTMLDivElement;
    text: HTMLDivElement;
    footer: HTMLDivElement | undefined;
    power: HTMLDivElement | undefined;
}

/**
 * Class representing a Magic the Gathering card.
 */
export default class Card {
    id: number;
    reg: RegExp = /^(\[([A-Z0-9]+)#?([A-Z0-9]+)] )?(.*)$/
    elements!: CardElement;

    // Fields derived from the provided string
    name: string;
    set: Set;
    number: string;

    // Fields set by the API
    manaCost: string | undefined;
    text: string | undefined;
    colors: string[] | undefined;

    // Types
    type: string | undefined;
    superTypes: string[] | string | undefined;
    cardTypes: string[] | string | undefined;
    subTypes: string[] | string | undefined;

    // Optional fields from the API
    toughness: string | undefined;
    loyalty: string | undefined;
    power: string | undefined;


    /**
     * Creates a blank template card.
     *
     * @param inputText a string in the format '# [SETNAME|SETNUMBER] CARDNAME'.
     * @param id a unique id for this card
     */
    constructor(inputText: string, id: number) {
        const matches = this.reg.exec(inputText);

        if (matches) {
            this.name = matches[4];
            this.set = {
                name: undefined,
                code: matches[3]
            }
            this.number = matches[2];
        } else {
            throw new Error('No Text Provided for Card');
        }

        this.id = id;

        this.initElements();
    }

    /**
     * Creates all the elements of the Card, but does not render them to the screen.
     *
     * @private
     */
    private initElements() {
        this.elements = {
            card: document.createElement('div'),
            content: document.createElement('div'),
            header: document.createElement('div'),
            name: document.createElement('div'),
            manaCost: document.createElement('div'),
            imageWrapper: document.createElement('div'),
            type: document.createElement('div'),
            text: document.createElement('div'),
            footer: undefined,
            power: undefined
        }

        // Create the main card div
        this.elements.card.className = 'card';
        this.elements.card.id = `card-${this.id}`;
        this.elements.card.setAttribute('name', this.name.toLowerCase());

        // Create the content wrapper
        this.elements.content.className = 'card-content';
        this.elements.card.appendChild(this.elements.content);

        // Create the header
        this.elements.header.className = 'card-header'
        this.elements.content.appendChild(this.elements.header);

        // Create the cardname
        this.elements.name.className = 'card-name auto-resize';
        this.elements.name.innerText = this.name;
        this.elements.header.appendChild(this.elements.name);

        // Create the manacost
        this.elements.manaCost.className = 'mana-cost';
        this.elements.header.appendChild(this.elements.manaCost);

        // Create the image wrapper
        this.elements.imageWrapper.className = 'card-image';
        this.elements.content.appendChild(this.elements.imageWrapper);

        // Create the type box
        this.elements.type.className = 'card-type';
        this.elements.content.appendChild(this.elements.type);

        // Create the text box
        this.elements.text.className = 'card-text';
        this.elements.content.appendChild(this.elements.text);
    }

    private constructFooter(power: string): void {
        if (this.elements.power == undefined) {
            this.elements.footer = document.createElement('div');
            this.elements.footer.className = 'card-footer';
            this.elements.text.className += ' has-footer';
            this.elements.power = document.createElement('div');
            this.elements.power.className = 'power-block';
            this.elements.footer.appendChild(this.elements.power);
            this.elements.content.appendChild(this.elements.footer);
        }

        this.elements.power.innerText = power;
    }

    /**
     * Renders this card under the provided parent.
     *
     * @param parent What element to place this card under.
     */
    renderTemplate(parent: HTMLElement): void {
        // Create a button to render content
        let renderButton = document.createElement('button');
        renderButton.className = 'render-button';
        renderButton.setAttribute('onClick', `deck.load('${this.id}')`);
        renderButton.innerText = 'Load';
        this.elements.imageWrapper.appendChild(renderButton);

        // Render the card to the page
        parent.appendChild(this.elements.card);
    }

    /**
     * Loads a card's data from the API.
     */
    load(): void {
        // Create a new HTTP request
        let xhttp = new XMLHttpRequest();

        // Save 'this' in a variable so we can access it inside the anonymous function later
        let card = this;

        // Add a function for when this becomes more ready
        xhttp.onreadystatechange = function () {
            // If this XHR request is completely ready
            if (this.readyState == 4 && this.status == 200) {
                // Import the data from the endpoint into this card
                card.importData(this.responseText);
            }
        };
        // Connect to the API
        xhttp.open("GET", encodeURI(`/api/GetCard.php?name=${this.name}`), true);
        // and Send the request
        xhttp.send();
    }

    /**
     * Loads data from the API into this card.
     *
     * Also refreshes the look of the card
     * @see updateCard
     *
     * @param dataString The card object from the API.
     */
    importData(dataString: string): void {
        // Get the APICardResponse object
        let data: APICardResponse = JSON.parse(dataString);

        // Update the fields on this class using the fields in the data.
        this.name = data.name;
        this.manaCost = data.manaCost;
        this.set = data.set;
        this.number = data.setNumber
        this.text = data.text;
        this.colors = data.colors;

        // Types
        this.type = data.type;
        this.superTypes = data.superTypes;
        this.cardTypes = data.cardTypes;
        this.subTypes = data.subTypes;

        // Check each of the optional fields first
        if (data.power) {
            this.power = data.power
        }
        if (data.toughness) {
            this.toughness = data.toughness
        }
        if (data.loyalty) {
            this.loyalty = data.loyalty
        }

        console.log(this);

        // Change how this card is rendered.
        this.updateCard();
    }

    /**
     * Updates the card based on the current data
     */
    updateCard(): void {
        // Update the card's color
        this.elements.card.className = `card ${this.getColorString()}`

        // Update the power & toughness or loyalty
        if (this.power && this.toughness) {
            this.constructFooter(`${this.power} / ${this.toughness}`)
        } else if (this.loyalty) {
            this.constructFooter(`${this.loyalty}`)
        }

        // Fill in the regular text (and resize it to fit)
        if (this.text != null) {
            this.elements.text.innerHTML = Card.convertManaSymbolsToHtml(this.text.replace(/\n/g, '<br/><br/>'));
            resizeElement(this.elements.text)
        }

        // Fill in the mana cost
        if (this.manaCost != null) {
            this.elements.manaCost.innerHTML = Card.convertManaSymbolsToHtml(this.manaCost);
        }

        // Fill in the type (and resize it to fit)
        if (this.type != null) {
            this.elements.type.innerText = this.type;
            resizeElement(this.elements.type)
        }

        // Update the name (and resize it to fit)
        this.elements.name.innerText = this.name;
        resizeElement(this.elements.name)

        // Set the image
        this.elements.imageWrapper.innerHTML = '';
        this.elements.imageWrapper.appendChild(Card.getSingleImageIcon(this.cardTypes));
    }

    /**
     * Converts all mana symbols in a block of text into HTML.
     * @param inputString the text to convert elements from.
     * @private
     *
     * @return returns the original string with the symbols replaced.
     */
    private static convertManaSymbolsToHtml(inputString: string): string {
        return inputString.replace(/{(([A-z0-9]+)(\/([A-z0-9]+))?)}/g, function (
            match: string,
            fullSymbol,
            symbol1,
            slashSymbol,
            symbol2
        ): string {
            if (symbol2 == null) {
                return Card.convertSingleManaSymbol(symbol1);
            } else if (symbol2 == 'P') {
                return Card.convertSingleManaSymbol(symbol2, true, true);
            } else if (symbol1 == 'P') {
                return Card.convertSingleManaSymbol(symbol1, true, true);
            } else {
                return '<div class="mi-split">'
                    + Card.convertSingleManaSymbol(symbol1, true)
                    + Card.convertSingleManaSymbol(symbol2, true)
                    + '</div>';
            }
        });

    }

    /**
     * Converts an individual mana symbol.
     *
     * @param symbol The single letter code for this mana symbol.
     * @param split Whether this symbol is for a split mana cost. Default false.
     * @param phyrexian Whether this symbol is phyrexian. Default false. Split should be true if this is.
     * @private
     *
     * @return The HTML version of the provided symbol.
     */
    private static convertSingleManaSymbol(symbol: string, split = false, phyrexian = false): string {
        // Minimise the mana symbol (for use in html).
        symbol = symbol.toLowerCase();

        // Convert 'q' into 'untap'.
        symbol = symbol == 'q' ? 'untap' : symbol;

        // If it's for a split mana symbol, add 'mi-mana'.
        if (!split) {
            symbol += ' mi-mana';
        } else if (phyrexian) {
            // If it's phyrexian mana, make it 'mi-p mi-mana-manaSymbol'.
            symbol = 'p mi-mana-' + symbol;
        }

        // return the HTML tag for this symbol.
        return `<i class='mi mi-${symbol}'></i>`;

    }

    /**
     * Gets the name of the color of this card.
     *
     * @return string The name of the color of this card.
     */
    public getColorString(): string {
        // If it has no colors, it's colorless.
        if (this.colors == null) {
            return 'colorless';
        }

        // If it has more than 2 colors, it's multicolored.
        if (this.colors.length > 2) {
            return 'multicolored';
        }

        // If it's exactly 2 colors, return both colors hyphenated.
        if (this.colors.length == 2) {
            return this.getTwoColorHouseName();
        }

        // If it's only 1 color, return that color.
        if (this.colors.length == 1) {
            return this.colors[0].toLowerCase();
        }

        return 'colorless';
    }

    /**
     * Get's the guild name of this card.
     *
     * @return string the house name, or '' if invalid colors provided.
     */
    private getTwoColorHouseName(): string {
        if (this.colors?.length != 2) {
            return '';
        }

        // Go through the Black colors.
        if (this.colors.includes('B')) {
            if (this.colors.includes('U')) {
                return 'dimir';
            } else if (this.colors.includes('G')) {
                return 'golgari';
            } else if (this.colors.includes('R')) {
                return 'rakdos';
            } else if (this.colors.includes('W')) {
                return 'orzhov';
            }
        }

        // Go through the Blue Colors
        if (this.colors.includes('U')) {
            if (this.colors.includes('G')) {
                return 'simic';
            } else if (this.colors.includes('R')) {
                return 'izzet';
            } else if (this.colors.includes('W')) {
                return 'azorius';
            }
        }

        // Go through the Green Colors
        if (this.colors.includes('G')) {
            if (this.colors.includes('R')) {
                return 'gruul';
            } else if (this.colors.includes('W')) {
                return 'selesnya';
            }
        }

        if (this.colors.includes('Red') && this.colors.includes('W')) {
            return 'boros';
        }

        return '';
    }

    /**
     * Gets the icon for a particular type.
     *
     * @return string the HTML for this icon.
     * @param types
     */
    private static getSingleImageIcon(types: string | string[] | undefined): HTMLDivElement {
        let imageDiv = document.createElement('div');
        imageDiv.className = `image-icon`;
        if (typeof (types) == 'undefined') {
            return imageDiv;
        } else if (typeof (types) != 'string') {
            imageDiv.className += ' split';
        } else {
            types = [types];
        }

        let type = "";

        for (type in types) {
            switch (types[type]) {
                case 'Artifact':
                case 'Creature':
                case 'Enchantment':
                case 'Instant':
                case 'Land':
                case 'Planeswalker':
                case 'Sorcery':
                    let image = document.createElement('IMG');
                    image.setAttribute('src', `./svg/${types[type].toLowerCase()}.svg`);
                    imageDiv.appendChild(image);
            }
        }

        return imageDiv;
    }
}