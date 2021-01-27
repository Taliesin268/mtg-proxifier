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

/**
 * Class representing a Magic the Gathering card.
 */
export default class Card {
    id: number;
    reg: RegExp = /^(\[([A-Z0-9]+)#?([A-Z0-9]+)] )?(.*)$/
    element: HTMLElement | undefined;

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
    }

    /**
     * Creates a card under the provided parent.
     *
     * @param parent What element to place this card under.
     */
    renderTemplate(parent: HTMLElement): void {
        // Create the main card div
        this.element = document.createElement('div');
        this.element.className = 'card';
        this.element.id = `card-${this.id}`;
        this.element.setAttribute('name', this.name.toLowerCase());

        // Render the card to the page
        parent.appendChild(this.element);

        // Create the content wrapper
        let cardContent = document.createElement('div');
        cardContent.className = 'card-content';
        this.element.appendChild(cardContent);

        // Create the header
        let cardHeader = document.createElement('div');
        cardHeader.className = 'card-header'
        cardContent.appendChild(cardHeader);

        // Create the cardname
        let cardName = document.createElement('div');
        cardName.className = 'card-name auto-resize';
        cardName.innerText = this.name;
        cardHeader.appendChild(cardName);

        // Create the manacost
        let manaCost = document.createElement('div');
        manaCost.className = 'mana-cost';
        cardHeader.appendChild(manaCost);

        // Create the image wrapper
        let imageWrapper = document.createElement('div');
        imageWrapper.className = 'card-image';
        cardContent.appendChild(imageWrapper);

        // Create the type box
        let cardType = document.createElement('div');
        cardType.className = 'card-type';
        cardContent.appendChild(cardType);

        // Create the text box
        let cardText = document.createElement('div');
        cardText.className = 'card-text';
        cardContent.appendChild(cardText);

        // Create a button to render content
        let renderButton = document.createElement('button');
        renderButton.className = 'render-button';
        renderButton.setAttribute('onClick', `deck.load('${this.id}')`);
        renderButton.innerText = 'Load';
        imageWrapper.appendChild(renderButton);
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
    }
}