import Card from './Card.js'
/**
 * Represents a deck of MTG cards.
 */
export default class Deck {
    public length: number = 0;
    private cards: Card[] = [];

    // noinspection JSUnusedGlobalSymbols
    /**
     * Creates a deck from a provided decklist string.
     *
     * @param decklist The string containing cards separated by newlines.
     */
    constructor(decklist: string) {
        // Split the provided decklist by newlines.
        let decklistArray: string[] = decklist.split('\n');

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
            if(!isNaN(Number(matches[1])) && Number(matches[1]) > 0) {
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
}