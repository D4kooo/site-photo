document.addEventListener('DOMContentLoaded', function() {
    const conversationItems = document.querySelectorAll('.conversation-item');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    let currentInterlocuteurId = null;
    
    // Fonction pour charger les messages d'une conversation
    async function loadMessages(interlocuteurId) {
        try {
            const response = await fetch(`../ajax/get_messages.php?interlocuteur_id=${interlocuteurId}`);
            const messages = await response.json();
            
            const messagesContainer = document.querySelector('.conversation-messages');
            messagesContainer.innerHTML = '';
            
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${msg.is_sender ? 'sent' : 'received'}`;
                messageDiv.innerHTML = `
                    <p>${msg.text}</p>
                    ${msg.media ? `<img src="${msg.media}" class="message-media" alt="media">` : ''}
                    <span class="message-time">${msg.date}</span>
                `;
                messagesContainer.appendChild(messageDiv);
            });
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } catch (error) {
            console.error('Erreur lors du chargement des messages:', error);
        }
    }

    // Fonction pour envoyer un message
    async function sendMessage(text, interlocuteurId) {
        try {
            const formData = new FormData();
            formData.append('text', text);
            formData.append('interlocuteur_id', interlocuteurId);

            const response = await fetch('../ajax/send_message.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                messageInput.value = '';
                loadMessages(interlocuteurId);
                // Rafraîchir la liste des conversations
                updateConversationsList();
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
        }
    }

    // Gestionnaire d'événements pour les conversations
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Retirer la classe active des autres conversations
            conversationItems.forEach(i => i.classList.remove('active'));
            // Ajouter la classe active à la conversation sélectionnée
            this.classList.add('active');
            
            // Mettre à jour l'en-tête de la conversation
            const interlocuteur = this.dataset.interlocuteur;
            const interlocuteurId = this.dataset.interlocuteurId;
            currentInterlocuteurId = interlocuteurId;
            
            const header = document.querySelector('.conversation-header');
            header.innerHTML = `
                <img src="${this.querySelector('.conversation-user-img').src}" 
                     alt="${interlocuteur}" 
                     class="conversation-user-img">
                <h2>${interlocuteur}</h2>
            `;
            
            // Charger les messages
            loadMessages(interlocuteurId);
        });
    });

    // Gestionnaire d'événements pour l'envoi de messages
    sendButton.addEventListener('click', () => {
        const text = messageInput.value.trim();
        if (text && currentInterlocuteurId) {
            sendMessage(text, currentInterlocuteurId);
        }
    });

    // Envoi avec la touche Entrée
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (text && currentInterlocuteurId) {
                sendMessage(text, currentInterlocuteurId);
            }
        }
    });

    // Recherche de conversations
    const searchBar = document.getElementById('search-bar');
    searchBar.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        conversationItems.forEach(item => {
            const interlocuteur = item.dataset.interlocuteur.toLowerCase();
            item.style.display = interlocuteur.includes(searchTerm) ? 'flex' : 'none';
        });
    });
});