/**
 * Script principal pour l'application Pharmacie de Garde
 * Contient toutes les fonctions JavaScript nécessaires
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    initTooltips();
    
    // Gestion des formulaires
    initFormValidations();
    
    // Fonctions spécifiques aux pages
    if (document.getElementById('map')) {
        initMap();
    }
    
    if (document.getElementById('calendar')) {
        initCalendar();
    }
    
    if (document.getElementById('zoneStatsChart')) {
        initZoneStatsChart();
    }
    
    // Gestion des messages flash automatiques
    autoDismissAlerts();
});

/**
 * Initialise les tooltips Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialise la validation des formulaires
 */
function initFormValidations() {
    // Validation des formulaires avec Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
    
    // Validation personnalisée pour les dates
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateDateRange(this);
        });
    });
}

/**
 * Valide que la date de fin est après la date de début
 * @param {HTMLElement} input L'élément input date modifié
 */
function validateDateRange(input) {
    const form = input.closest('form');
    if (!form) return;
    
    const startDateInput = form.querySelector('input[name="date_debut"]');
    const endDateInput = form.querySelector('input[name="date_fin"]');
    
    if (!startDateInput || !endDateInput) return;
    
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (startDate && endDate && endDate < startDate) {
        endDateInput.setCustomValidity('La date de fin doit être postérieure à la date de début');
        endDateInput.reportValidity();
    } else {
        endDateInput.setCustomValidity('');
    }
}

/**
 * Initialise la carte interactive (Leaflet)
 */
function initMap() {
    // Les marqueurs sont ajoutés directement dans le HTML
    // Cette fonction initialise juste la carte centrée sur une position par défaut
    const map = L.map('map').setView([34.0, -6.85], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Vous pouvez ajouter ici des fonctions supplémentaires pour la carte
}

/**
 * Initialise le calendrier (FullCalendar)
 */
function initCalendar() {
    // Le calendrier est initialisé directement dans le HTML
    // Cette fonction est un placeholder pour des extensions futures
}

/**
 * Initialise le graphique des statistiques par zone (Chart.js)
 */
function initZoneStatsChart() {
    fetch('ajax/get_zone_stats.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('zoneStatsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Nombre de pharmacies',
                        data: data.values,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statistiques:', error);
        });
}

/**
 * Ferme automatiquement les alertes après un délai
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
}

/**
 * Fonction pour rechercher des pharmacies en AJAX
 * @param {string} query Terme de recherche
 * @param {function} callback Fonction de rappel
 */
function searchPharmacies(query, callback) {
    fetch(`ajax/search_pharmacies.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
            callback([]);
        });
}

/**
 * Met à jour l'interface utilisateur en fonction des permissions
 */
function updateUIForPermissions() {
    // Cacher les éléments réservés aux admins si l'utilisateur n'est pas admin
    if (!document.body.classList.contains('admin')) {
        document.querySelectorAll('.admin-only').forEach(el => {
            el.style.display = 'none';
        });
    }
}

// Fonctions utilitaires supplémentaires

/**
 * Formate un numéro de téléphone
 * @param {string} phone Le numéro à formater
 * @return {string} Le numéro formaté
 */
function formatPhoneNumber(phone) {
    return phone.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');
}

/**
 * Convertit une date au format français
 * @param {string} dateStr La date au format YYYY-MM-DD
 * @return {string} La date au format DD/MM/YYYY
 */
function formatFrenchDate(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

/**
 * Affiche une modal de confirmation
 * @param {string} message Le message à afficher
 * @param {function} onConfirm Fonction à exécuter si confirmé
 */
function showConfirmationModal(message, onConfirm) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    document.getElementById('confirmationMessage').textContent = message;
    
    document.getElementById('confirmButton').onclick = function() {
        onConfirm();
        modal.hide();
    };
    
    modal.show();
}