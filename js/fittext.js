function calculateIntendedFontSize(container) {
    // Get the current values
    let intendedHeight = window.getComputedStyle(container).getPropertyValue('height');
    let fontSize = window.getComputedStyle(container).getPropertyValue('font-size');
    fontSize = parseInt(fontSize);
    intendedHeight = parseInt(intendedHeight);

    console.log('The Intended Height is: ' + intendedHeight)

    // Make the height automatic
    container.style.height = 'auto';

    // As long as we have a valid fontsize...
    while(fontSize > 0) {
        let currentHeight = window.getComputedStyle(container).getPropertyValue('height');
        currentHeight = parseInt(currentHeight);
        console.log('-> New FontSize: ' + fontSize);
        console.log('-> New Height: ' + currentHeight);
        if(currentHeight <= intendedHeight) {
            break;
        } else {
            fontSize -= 0.5;
            container.style.fontSize = fontSize + 'px';
        }
    }
    console.log('=> Determined Size: ' + fontSize);

    container.style.height = intendedHeight + 'px';

    return fontSize;
}

function resizeElement(item) {
    let fontSize = calculateIntendedFontSize(item);
    item.style.fontSize = fontSize + 'px';
}

function resizeAllCardTexts() {
    let cardTexts = document.getElementsByClassName('auto-resize');
    let i;
    for (i = 0; i < cardTexts.length; i++) {
        resizeElement(cardTexts[i]);
    }
}

window.onload = function () {
    resizeAllCardTexts();
}