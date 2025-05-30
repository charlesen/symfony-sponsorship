import { Controller } from '@hotwired/stimulus';

/**
 * Controller for the icon picker modal
 * 
 * @property {HTMLElement} searchInput - The search input field
 * @property {HTMLElement} iconGrid - The container for the icon grid
 */
export default class extends Controller {
    static targets = ['searchInput', 'iconGrid'];
    static values = {
        icons: { type: Array, default: [
            // Common Font Awesome icons
            'fas fa-user', 'fas fa-users', 'fas fa-tasks', 'fas fa-check-circle',
            'fas fa-star', 'fas fa-heart', 'fas fa-home', 'fas fa-envelope',
            'fas fa-phone', 'fas fa-calendar', 'fas fa-clock', 'fas fa-bell',
            'fas fa-cog', 'fas fa-search', 'fas fa-plus', 'fas fa-minus',
            'fas fa-times', 'fas fa-arrow-right', 'fas fa-arrow-left', 'fas fa-arrow-up',
            'fas fa-arrow-down', 'fas fa-sync', 'fas fa-redo', 'fas fa-undo',
            'fas fa-trash', 'fas fa-edit', 'fas fa-save', 'fas fa-download',
            'fas fa-upload', 'fas fa-folder', 'fas fa-file', 'fas fa-image',
            'fas fa-camera', 'fas fa-video', 'fas fa-music', 'fas fa-headphones',
            'fas fa-gamepad', 'fas fa-tv', 'fas fa-laptop', 'fas fa-mobile',
            'fas fa-tablet', 'fas fa-desktop', 'fas fa-print', 'fas fa-keyboard',
            'fas fa-mouse', 'fas fa-hdd', 'fas fa-server', 'fas fa-database',
            'fas fa-network-wired', 'fas fa-wifi', 'fas fa-bluetooth', 'fas fa-battery-full',
            'fas fa-battery-three-quarters', 'fas fa-battery-half', 'fas fa-battery-quarter',
            'fas fa-battery-empty', 'fas fa-plug', 'fas fa-bolt', 'fas fa-fire',
            'fas fa-umbrella', 'fas fa-snowflake', 'fas fa-sun', 'fas fa-moon',
            'fas fa-cloud', 'fas fa-cloud-sun', 'fas fa-cloud-moon', 'fas fa-cloud-rain',
            'fas fa-cloud-showers-heavy', 'fas fa-cloud-sun-rain', 'fas fa-cloud-moon-rain',
            'fas fa-poo-storm', 'fas fa-smog', 'fas fa-wind', 'fas fa-fan',
            'fas fa-thermometer-half', 'fas fa-fire-extinguisher', 'fas fa-shield-alt',
            'fas fa-lock', 'fas fa-unlock', 'fas fa-key', 'fas fa-fingerprint',
            'fas fa-user-secret', 'fas fa-mask', 'fas fa-ghost', 'fas fa-robot',
            'fas fa-brain', 'fas fa-microchip', 'fas fa-memory', 'fas fa-game-board',
            'fas fa-chess', 'fas fa-dice', 'fas fa-dice-d20', 'fas fa-dice-d6',
            'fas fa-dice-five', 'fas fa-football-ball', 'fas fa-basketball-ball',
            'fas fa-baseball-ball', 'fas fa-volleyball-ball', 'fas fa-futbol',
            'fas fa-golf-ball', 'fas fa-table-tennis', 'fas fa-hockey-puck',
            'fas fa-bowling-ball', 'fas fa-chess-knight', 'fas fa-chess-king',
            'fas fa-chess-queen', 'fas fa-chess-rook', 'fas fa-chess-bishop',
            'fas fa-chess-pawn', 'fas fa-chess-board', 'fas fa-chess-knight-alt',
            'fas fa-chess-king-alt', 'fas fa-chess-queen-alt', 'fas fa-chess-rook-alt',
            'fas fa-chess-bishop-alt', 'fas fa-chess-pawn-alt', 'fas fa-chess-clock',
            'fas fa-chess-clock-alt', 'fas fa-chess-king-piece', 'fas fa-chess-queen-piece',
            'fas fa-chess-rook-piece', 'fas fa-chess-bishop-piece', 'fas fa-chess-knight-piece',
            'fas fa-chess-pawn-piece', 'fas fa-chess-king-alt-piece', 'fas fa-chess-queen-alt-piece',
            'fas fa-chess-rook-alt-piece', 'fas fa-chess-bishop-alt-piece', 'fas fa-chess-knight-alt-piece',
            'fas fa-chess-pawn-alt-piece', 'fas fa-chess-king-piece-alt', 'fas fa-chess-queen-piece-alt',
            'fas fa-chess-rook-piece-alt', 'fas fa-chess-bishop-piece-alt', 'fas fa-chess-knight-piece-alt',
            'fas fa-chess-pawn-piece-alt', 'fas fa-chess-king-alt-piece-alt', 'fas fa-chess-queen-alt-piece-alt',
            'fas fa-chess-rook-alt-piece-alt', 'fas fa-chess-bishop-alt-piece-alt', 'fas fa-chess-knight-alt-piece-alt',
            'fas fa-chess-pawn-alt-piece-alt'
        ]}
    };

    connect() {
        this.renderIcons();
        this.setupSearch();
    }

    /**
     * Renders the icon grid with all available icons
     */
    renderIcons(filter = '') {
        const filteredIcons = this.iconsValue.filter(icon => 
            icon.toLowerCase().includes(filter.toLowerCase())
        );

        this.iconGridTarget.innerHTML = '';

        if (filteredIcons.length === 0) {
            this.iconGridTarget.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-2x mb-3 text-muted"></i>
                    <p class="text-muted">No icons found</p>
                </div>
            `;
            return;
        }

        const iconsHtml = filteredIcons.map(icon => `
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                <button type="button" 
                        class="btn btn-outline-secondary w-100 h-100 py-3"
                        data-action="click->icon-picker#selectIcon"
                        data-icon="${icon}">
                    <i class="${icon} fa-2x mb-2"></i>
                    <div class="small text-truncate">${icon.replace('fas fa-', '')}</div>
                </button>
            </div>
        `).join('');

        this.iconGridTarget.innerHTML = `
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2">
                ${iconsHtml}
            </div>
        `;
    }

    /**
     * Sets up the search functionality
     */
    setupSearch() {
        let timeout;
        this.searchInputTarget.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.renderIcons(e.target.value);
            }, 300);
        });
    }

    /**
     * Handles icon selection
     * @param {Event} event - The click event
     */
    selectIcon(event) {
        const button = event.currentTarget;
        const iconClass = button.getAttribute('data-icon');
        
        // Dispatch event to update the form field
        const iconSelectedEvent = new CustomEvent('icon:selected', {
            detail: { iconClass },
            bubbles: true
        });
        
        this.element.dispatchEvent(iconSelectedEvent);
        
        // Update active state
        document.querySelectorAll('[data-icon]').forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-secondary');
        });
        
        button.classList.remove('btn-outline-secondary');
        button.classList.add('active', 'btn-primary');
        
        // Close the modal after a short delay for better UX
        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(this.element);
            if (modal) {
                modal.hide();
            }
        }, 300);
    }
}
