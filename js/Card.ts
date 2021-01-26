/**
 * Class representing a Magic the Gathering card.
 */
export default class Card {
    id: number;
    reg: RegExp = /^(\[([A-Z0-9]+)#?([A-Z0-9]+)] )?(.*)$/
    name: string;
    set: string;
    number: string;
    element: HTMLElement | undefined;

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
            this.set = matches[3];
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
        renderButton.setAttribute('onClick', `deck.render('${this.name.toLowerCase()}')`);
        renderButton.innerText = 'Load';
        imageWrapper.appendChild(renderButton);
    }
}