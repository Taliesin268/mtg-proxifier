import {calculateIntendedFontSize, resizeElement} from './FitText.js'

interface Set {
    name: string | undefined;
    code: string | undefined;
}

interface APICardResponse {
    id: string;
    manaCost: string;
    name: string;
    setNumber: string;
    text: string;
    type: string;

    power: string | undefined;
    toughness: string | undefined;
    loyalty: string | undefined;

    set: Set;
    colors: string[];
}

interface CardElement {
    card: HTMLDivElement;
    content: HTMLDivElement;
    header: HTMLDivElement;
    name: HTMLDivElement;
    manaCost: HTMLDivElement;
    imageWrapper: HTMLDivElement;
    type: HTMLDivElement;
    text: HTMLDivElement;
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
    type: string | undefined;
    colors: string[] | undefined;

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
    private initElements(){
        this.elements = {
            card: document.createElement('div'),
            content: document.createElement('div'),
            header: document.createElement('div'),
            name: document.createElement('div'),
            manaCost: document.createElement('div'),
            imageWrapper: document.createElement('div'),
            type: document.createElement('div'),
            text: document.createElement('div'),
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
        this.type = data.type;

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

        this.updateCard();
    }

    /**
     * Updates the card based on the current data
     *
     * @return string The HTML representation of this card.
     */
    updateCard(): void
    {
        // Fill in the regular text
        if (this.text != null) {
            this.elements.text.innerHTML = Card.convertManaSymbolsToHtml(this.text.replace('\n', '<br/><br/>'));
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

        this.elements.name.innerText = this.name;
        resizeElement(this.elements.name)
    }

    private static convertManaSymbolsToHtml(inputString: string): string
    {
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

    private static convertSingleManaSymbol(symbol:string, split = false, phyrexian = false): string
    {
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
}