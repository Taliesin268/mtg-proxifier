import Card from './Card.js'
/**
 * Represents a deck of MTG cards.
 */
export default class Deck {
    length: number = 0;
    private cards: Card[] = [];

    constructor(decklist: string) {
        let decklistArray: string[] = decklist.split('\n');
        const findQuantity: RegExp = /^([0-9]+ )?(.*)/
        decklistArray.forEach((value: string, index: number) => {
            const matches = findQuantity.exec(value);
            if (!matches) {
                throw new Error('No Text Provided for Deck')
            } else {
                const quantity: number = isNaN(Number(matches[1])) ? 1 : Number(matches[1]);
                for (let i = 0; i < quantity; i++) {
                    this.push(new Card(matches[2], this.length + 1));
                }
            }
        });
    }

    push(element: Card): number {
        length = this.cards.push();
        this.cards.push(element);
        this.length = length;
        return length;
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