import Card from './Card.js';
import Deck from './Deck.js';

declare global {
    interface Window {
        loadDeck(): void;

        deck: Deck;
    }
}

window.loadDeck = function loadDeck() {
    // Get the card list, and create a deck from it.
    const cardList: HTMLTextAreaElement = <HTMLTextAreaElement>document.getElementById('cardList');
    window.deck = new Deck(cardList.value);

    // Log the deck to the console.
    console.log(window.deck);

    // Render each card in the deck to the print section.
    let printSection = <HTMLDivElement>document.getElementById('print-section');
    window.deck.cards.forEach((card: Card) => {
        card.renderTemplate(printSection);
    });
}
