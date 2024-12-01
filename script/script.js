'use strict';

// Gestion des onglets dans les modales
function openTab(showTabId, hideTabId, showButtonId, hideButtonId) {
    document.getElementById(hideTabId).classList.remove('active');
    document.getElementById(hideButtonId).classList.remove('active');

    // Afficher l'onglet sélectionné
    document.getElementById(showTabId).classList.add('active');
    document.getElementById(showButtonId).classList.add('active');
}

// Fonction d'initialisation des messages de pop-up (success)
function initMessagePopup() {
    const progressBar = document.getElementById('progress-bar');
    const popup = document.getElementById('message-popup');

    if (progressBar && popup) {
        // Démarrer l'animation de la barre de progression
        setTimeout(() => {
            progressBar.style.transition = 'width 5s linear';
            progressBar.style.width = '100%';
        }, 10); // Petit délai pour permettre le rendu initial

        // Fermer la popup après 5 secondes
        setTimeout(closeMessagePopup, 5000);
    }
}

// Fonction pour fermer la popup
function closeMessagePopup() {
    const popup = document.getElementById('message-popup');
    if (popup) {
        popup.style.display = 'none';
    }
}

// Ouvrir une modale
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Fermer une modale
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Gestion de la fermeture de la modale en cliquant à l'extérieur
window.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal'); // Sélectionne toutes les modales
    modals.forEach((modal) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});


function showThemeOptions() {
    const typeEvent = document.getElementById('type').value;
    const themeSection = document.getElementById('theme-section');
    
    // Afficher le champ "Thème" si le type est "Cours" ou "Sortie"
    if (typeEvent === 'Cours' || typeEvent === 'Sortie') {
        themeSection.style.display = 'block';
    } else {
        themeSection.style.display = 'none';
    }
}

// Commentaires
function toggleComments(postId) {
    var commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
        commentsSection.style.display = 'block';
    } else {
        commentsSection.style.display = 'none';
    }
}

// Affichage sous menu (Post et Comment)
function toggleMenu(button) {
    var dropdown = button.nextElementSibling;
    dropdown.classList.toggle('show');
}

document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu profil
    const profileButton = document.getElementById('profile-button');
    const profileMenuContent = document.getElementById('profile-menu-content');
    
    if (profileButton) {
        profileButton.addEventListener('click', function() {
            profileMenuContent.classList.toggle('active');
        });
    }

    document.addEventListener('click', function(event) {
        const profileMenuContent = document.getElementById('profile-menu-content');
        const profileContainer = document.getElementById('profile-container');
    
        if (profileMenuContent && profileContainer && !profileContainer.contains(event.target)) {
            profileMenuContent.classList.remove('active');
        }
    });

    // Gestion du bouton hamburger pour le menu mobile
    const menuIcon = document.getElementById('menu-icon');
    const mobileMenu = document.querySelector('.nav-list-mobile');

    if (menuIcon && mobileMenu) {
        menuIcon.addEventListener('click', function() {
            menuIcon.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });
    }

    // Gestion de la fermeture des sous-menues en cliquant à l'extérieur
    window.onclick = function(event) {
        const dropdowns = document.querySelectorAll('.menu-dropdown');

        // Fermer tous les menus drop-down si le clic est en dehors
        if (!isMenuButton && !isInsideDropdown) {
            dropdowns.forEach((dropdown) => {
                dropdown.classList.remove('show');
            });
        }
    };


    
    // Initialisation du popup
    initMessagePopup();


});


//TODO séparer le code dans gallery.js


// Ouverture modal d'un post
document.addEventListener('DOMContentLoaded', function() {
    var galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(function(item) {
        item.addEventListener('click', function() {
            var postId = item.getAttribute('data-post-id');
            openPostModal(postId);
        });
    });
});

function openPostModal(postId) {
    const modalId = 'post-modal';
    openModal(modalId);

    const postContent = document.getElementById('post-content');
    if (postContent) {
        // Charger le contenu du post via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '../controllers/controller.php?controller=post&action=loadPost&id=' + postId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Insérer uniquement le contenu dans le conteneur dédié
                postContent.innerHTML = xhr.responseText;
            } else {
                console.error('Erreur lors du chargement du post.');
            }
        };
        xhr.send();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('close-post-modal').addEventListener('click', function () {
        document.getElementById('post-modal').style.display = 'none';
    });
});
