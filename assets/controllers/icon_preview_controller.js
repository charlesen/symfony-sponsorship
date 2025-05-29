import { Controller } from '@hotwired/stimulus';

/**
 * Controller for handling icon previews in forms
 *
 * @property {HTMLElement} iconInput - The input field containing the icon class
 * @property {HTMLElement} iconPreview - The element that shows the icon preview
 */
export default class extends Controller {
    static targets = ['iconInput', 'iconPreview'];

    connect() {
        this.updatePreview();
        this.setupModalListener();
    }

    /**
     * Sets up event listener for icon selection from modal
     */
    setupModalListener() {
        // Listen for custom event when an icon is selected from the modal
        document.addEventListener('icon:selected', (event) => {
            if (event.detail && event.detail.iconClass) {
                this.iconInputTarget.value = event.detail.iconClass;
                this.updatePreview();
            }
        });
    }

    /**
     * Updates the icon preview based on the input value
     */
    updatePreview() {
        const iconClass = this.iconInputTarget.value.trim();
        const previewElement = this.iconPreviewTarget;
        
        // Clear previous content
        previewElement.innerHTML = '';
        previewElement.className = 'ms-2';
        
        if (iconClass) {
            // Create icon element
            const iconElement = document.createElement('i');
            iconElement.className = `${iconClass} fa-2x`;
            
            // Create text node with the icon class
            const textNode = document.createTextNode(` ${iconClass}`);
            
            // Add elements to preview
            previewElement.appendChild(iconElement);
            previewElement.appendChild(textNode);
        } else {
            // Show placeholder if no icon class is provided
            const placeholder = document.createElement('span');
            placeholder.className = 'text-muted';
            placeholder.textContent = this.element.getAttribute('data-no-icon-text') || 'No icon selected';
            previewElement.appendChild(placeholder);
        }
    }
}
