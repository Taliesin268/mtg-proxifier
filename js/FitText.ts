export function calculateIntendedFontSize(container: HTMLElement) {
    // Get the current values
    const intendedHeight: number = parseFloat(window.getComputedStyle(container).getPropertyValue('height'));
    let fontSize = parseFloat(window.getComputedStyle(container).getPropertyValue('font-size'));

    // Make the height automatic
    container.style.height = 'auto';

    // As long as we have a valid fontsize...
    while(fontSize > 0) {
        let currentHeight = parseFloat(window.getComputedStyle(container).getPropertyValue('height'));
        if(currentHeight <= intendedHeight) {
            break;
        } else {
            fontSize -= 0.2;
            container.style.fontSize = fontSize + 'px';
        }
    }

    container.style.removeProperty('height');

    return fontSize;
}

export function resizeElement(item: HTMLElement) {
    item.style.removeProperty('fontSize');
    let fontSize = calculateIntendedFontSize(item);
    item.style.fontSize = fontSize + 'px';
}
