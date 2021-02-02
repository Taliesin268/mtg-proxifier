/**
 * Calculates and sets the correct font size for the provided element.
 * @param item The item to calculate the font size for.
 */

export function resizeElement(item: HTMLElement) {
    // Remove the already set font size
    item.style.removeProperty('fontSize');

    // Get the current values for height and font size
    const intendedHeight: number = parseFloat(window.getComputedStyle(item).getPropertyValue('height'));
    let fontSize = parseFloat(window.getComputedStyle(item).getPropertyValue('font-size'));

    // Make the height automatic
    item.style.height = 'auto';

    // As long as we have a valid fontsize...
    while(fontSize > 0) {
        let currentHeight = parseFloat(window.getComputedStyle(item).getPropertyValue('height'));
        if(currentHeight <= intendedHeight) {
            break;
        } else {
            fontSize -= 0.2;
            item.style.fontSize = fontSize + 'px';
        }
    }

    item.style.removeProperty('height');
    item.style.fontSize = fontSize + 'px';
}
