/**
 * animations.js - Script pour gérer les animations et la responsivité
 */

document.addEventListener('DOMContentLoaded', function() {
    // Détecter les appareils mobiles
    const isMobile = window.innerWidth < 992;
    
    // Initialiser la confetti animation si l'élément existe
    const confettiContainer = document.querySelector('.confetti-container');
    if (confettiContainer && !document.body.classList.contains('no-animations')) {
        initConfetti();
    }
    
    // Animations pour les éléments avec la classe .animate-on-scroll
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if (animatedElements.length > 0 && !document.body.classList.contains('no-animations')) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });
        
        animatedElements.forEach(el => observer.observe(el));
    }
    
    // Gestion responsive pour les tableaux larges
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('table-responsive');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Gestion des onglets responsives
    const tabLinks = document.querySelectorAll('[data-toggle="tab"]');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Retirer la classe active de tous les onglets
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Ajouter la classe active à l'onglet cliqué
            this.classList.add('active');
            
            // Désactiver tous les contenus d'onglets
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active', 'show');
            });
            
            // Activer le contenu d'onglet correspondant
            const targetId = this.getAttribute('href');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('active', 'show');
            }
        });
    });
    
    // Ajuster la hauteur des colonnes égales
    function equalizeHeight() {
        document.querySelectorAll('.equal-height').forEach(group => {
            const elements = group.querySelectorAll('.equal-height-item');
            let maxHeight = 0;
            
            // Réinitialiser les hauteurs
            elements.forEach(el => {
                el.style.height = 'auto';
                maxHeight = Math.max(maxHeight, el.offsetHeight);
            });
            
            // Appliquer la hauteur maximale
            elements.forEach(el => {
                el.style.height = maxHeight + 'px';
            });
        });
    }
    
    // Exécuter equalizeHeight après le chargement complet et lors du redimensionnement
    if (document.querySelectorAll('.equal-height').length > 0) {
        window.addEventListener('load', equalizeHeight);
        window.addEventListener('resize', debounce(equalizeHeight, 200));
    }
    
    // Fonction utilitaire pour limiter la fréquence des appels lors du redimensionnement
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
    
    // Fonction pour initialiser l'animation de confettis
    function initConfetti() {
        for (let i = 0; i < 10; i++) {
            createConfettiPiece(i);
        }
    }
    
    function createConfettiPiece(index) {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti-piece');
        confetti.style.backgroundColor = getRandomColor();
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDelay = Math.random() * 5 + 's';
        confetti.style.animationDuration = (Math.random() * 5 + 5) + 's';
        
        confettiContainer.appendChild(confetti);
    }
    
    function getRandomColor() {
        const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    // Adapter l'interface pour les appareils tactiles
    if ('ontouchstart' in window || navigator.maxTouchPoints) {
        document.body.classList.add('touch-device');
        
        // Améliorer les interactions tactiles pour les éléments draggables
        document.querySelectorAll('[draggable="true"]').forEach(el => {
            el.addEventListener('touchstart', function(e) {
                this.classList.add('touch-drag');
            });
            
            el.addEventListener('touchend', function(e) {
                this.classList.remove('touch-drag');
            });
        });
    }
}); 