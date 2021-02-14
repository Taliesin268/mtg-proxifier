import Card from './Card.js'

/**
 * Represents a deck of MTG cards.
 */
export default class Deck {
    public length: number = 0;
    public cards: Card[] = [];

    /**
     * Creates a deck from a provided decklist string.
     *
     * @param decklist The string containing cards separated by newlines.
     */
    constructor(decklist: string) {
        // Split the provided decklist by newlines.
        let decklistArray: string[] = decklist.split('\n');
        // Remove any empty elements.
        decklistArray = decklistArray.filter(function(val){
            return val != '';
        });

        // Define the regex used for determining the quantity (if provided) and the card name.
        const findQuantity: RegExp = /^([0-9]+ )?(.*)/

        // For each line in the decklist...
        decklistArray.forEach((value: string) => {
            // Execute the regex to get the quantity (matches[1]), and the card name (matches[2]).
            const matches = findQuantity.exec(value);

            // If there are no matches, then there was no deck, so throw an error.
            if (!matches) {
                throw new Error('No Text Provided for Deck')
            }

            // Assume the quantity is 1
            let quantity = 1;

            // If the quantity specified is a number, and is greater than 0...
            if (!isNaN(Number(matches[1])) && Number(matches[1]) > 0) {
                // Use the provided quantity instead.
                quantity = Number(matches[1]);
            }

            // For a number of times equal to the card quantity...
            for (let i = 0; i < quantity; i++) {
                // Add the card to this deck.
                this.push(new Card(matches[2], this.length + 1));
            }
        });
    }

    /**
     * Adds a card to this deck.
     *
     * @param card The Card to add.
     */
    push(card: Card): number {
        this.length = this.cards.push(card);
        return this.length;
    }

    /**
     * Calls splice on the array of cards.
     *
     * Required for this item to appear as an array in Chrome.
     * @param start where to begin splicing.
     * @param deleteCount how many items to delete.
     * @param items any new items to add.
     */
    splice(start: number, deleteCount: number = 0, ...items: Card[]) {
        // Return splice on the card array
        this.cards.splice(start, deleteCount, ...items);
    }

    /**
     * Deconstructs this deck.
     */
    public deconstruct(): void {
        // Deconstruct each of the cards
        this.cards.forEach(function (card) {
            card.deconstruct();
        });
    }

    /**
     * Gets all the card details by name from the API.
     */
    public getAllCardDetails(): void {
        // Create a new HTTP request
        let xhttp = new XMLHttpRequest();

        // Save 'this' in a variable so we can access it inside the anonymous function later
        let deck = this;

        // Add a function for when this becomes more ready
        xhttp.onreadystatechange = function () {
            // If this XHR request is completely ready
            if (this.readyState == 4 && this.status == 200) {
                // Import the data from the endpoint into this deck
                deck.processMultiCardResponse(this.responseText);
            }
        };

        // Connect to the API
        xhttp.open("POST", encodeURI(`/api/GetCard.php`), true);

        // Get the list of card names (and set each card to 'loading')
        let names: string[] = [];
        this.cards.forEach(function (card) {
            names.push(card.name);
        });

        // and Send the request
        xhttp.send(JSON.stringify({names: names}));
    }

    /**
     * Handle the response from getAllCardDetails().
     *
     * @param response the raw JSON response from getAllCardDetails().
     */
    public processMultiCardResponse(response: string): void {
        // Parse the response
        const data = JSON.parse(response);

        // Render each card from the list
        this.cards.forEach(function (card){
            // Remove its 'loading' status
            card.setLoading(false);
            if (card.name in data.found){
                // If the card was found, then load its data
                card.importData(data.found[card.name]);
            } else {
                // If the card wasn't found, set it to error
                card.setError('Card not found');
            }
        });
    }

    /**
     * Initialises the deck.
     * @param printSection What element to print the deck under.
     */
    public deckInit(printSection: HTMLDivElement): void {
        // Render each card's template
        window.deck.cards.forEach((card: Card) => {
            card.renderTemplate(printSection);
            // Set the card to 'loading' (will be removed in processMultiCardResponse)
            card.setLoading(true);
        });

        // Load all this deck's card details from the API.
        this.getAllCardDetails();
    }
}