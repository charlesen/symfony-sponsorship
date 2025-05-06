import { Controller } from '@hotwired/stimulus';

// Stimulus controller for DaisyUI theme switching with localStorage persistence
export default class extends Controller {
    static targets = ["checkbox"];

    connect() {
        // Find the checkbox (toggle)
        this.checkbox = this.element.querySelector('input[type="checkbox"].theme-controller');
        // Set initial state from localStorage
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.applyTheme(savedTheme);
        if (this.checkbox) {
            this.checkbox.checked = (savedTheme === 'synthwave');
        }
    }

    toggle() {
        if (this.checkbox) {
            const theme = this.checkbox.checked ? 'synthwave' : 'light';
            this.applyTheme(theme);
            localStorage.setItem('theme', theme);
        }
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }
}
