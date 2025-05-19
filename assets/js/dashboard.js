// Fonction utilitaire pour ajouter des classes en fonction de la route active
function setActiveMenuItems() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('nav a').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
}

// Initialisation des composants DaisyUI
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des menus déroulants
    const dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = toggle.getAttribute('data-dropdown-toggle');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });

    // Fermer les menus déroulants quand on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!e.target.matches('[data-dropdown-toggle]') && !e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // Initialisation des modales
    const modalToggles = document.querySelectorAll('[data-modal-toggle]');
    modalToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('data-modal-toggle');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.toggle('hidden');
            }
        });
    });

    // Fermer les modales avec le bouton de fermeture
    const modalCloses = document.querySelectorAll('[data-modal-hide]');
    modalCloses.forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            const targetId = closeBtn.getAttribute('data-modal-hide');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // Gestion des onglets
    const tabButtons = document.querySelectorAll('[data-tab]');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            const tabContent = document.getElementById(tabId);
            
            if (tabContent) {
                // Masquer tous les contenus d'onglets
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Désactiver tous les boutons d'onglets
                document.querySelectorAll('[data-tab]').forEach(btn => {
                    btn.classList.remove('tab-active');
                });
                
                // Afficher le contenu de l'onglet sélectionné
                tabContent.classList.remove('hidden');
                button.classList.add('tab-active');
            }
        });
    });

    // Activer le premier onglet par défaut
    const firstTab = document.querySelector('[data-tab]');
    if (firstTab) {
        firstTab.click();
    }

    // Initialisation des menus actifs
    setActiveMenuItems();
});

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} fixed top-4 right-4 max-w-md z-50`;
    notification.innerHTML = `
        <div class="flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 mx-2 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <label>${message}</label>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer la notification après 5 secondes
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 5000);
}

// Gestion des messages flash
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });
});
