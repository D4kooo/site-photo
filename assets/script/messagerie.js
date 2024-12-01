let currentReceiverId = null;

const modal = document.getElementById("new-message-modal");
const newMessageBtn = document.getElementById("new-message-btn");
const closeBtn = document.getElementsByClassName("close")[0];
const recipientSearch = document.getElementById("recipient-search");
const recipientsList = document.getElementById("recipients-list");

newMessageBtn.onclick = () => modal.style.display = "block";
closeBtn.onclick = () => modal.style.display = "none";

window.onclick = (event) => {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

recipientSearch.addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    const userItems = document.querySelectorAll('.user-item');
    
    userItems.forEach(item => {
        const username = item.querySelector('span').textContent.toLowerCase();
        if (username.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Ajouter les écouteurs d'événements pour tous les utilisateurs
document.querySelectorAll('.user-item').forEach(item => {
    item.addEventListener('click', () => {
        const userId = item.dataset.userId;
        const username = item.dataset.username;
        startNewConversation(userId, username);
        modal.style.display = "none";
    });
});

// Ajouter la gestion des clics sur les conversations existantes
document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', () => {
        const userId = item.dataset.interlocuteurId;
        const username = item.dataset.interlocuteur;
        loadConversation(userId, username);
    });
});

async function loadConversation(userId, username) {
    // Mettre à jour l'ID du destinataire actuel
    currentReceiverId = userId;

    // Mettre à jour l'en-tête de la conversation
    const conversationHeader = document.querySelector('.conversation-header');
    conversationHeader.innerHTML = `
        <h3>${username}</h3>
    `;

    // Mettre à jour la zone de messages
    const messagesContainer = document.querySelector('.conversation-messages');
    messagesContainer.innerHTML = '<div class="loading">Chargement des messages...</div>';

    try {
        const response = await fetch(`../includes/get_messages.php?user_id=${userId}`);
        const messages = await response.json();
        
        messagesContainer.innerHTML = messages.map(message => {
            if (message.Type === 'photo') {
                return `
                    <div class="message ${message.is_sender ? 'sent' : 'received'}">
                        <div class="message-content">
                            <img src="${message.Text}" alt="Image envoyée">
                            <span class="message-time">${message.Date_message}</span>
                        </div>
                    </div>`;
            } else {
                return `
                    <div class="message ${message.is_sender ? 'sent' : 'received'}">
                        <div class="message-content">
                            <p>${message.Text}</p>
                            <span class="message-time">${message.Date_message}</span>
                        </div>
                    </div>`;
            }
        }).join('');
        
        // Scroll vers le bas
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Mettre à jour le bouton d'envoi
        const sendButton = document.getElementById('send-button');
        sendButton.onclick = () => sendMessage(userId);
    } catch (error) {
        console.error('Erreur lors du chargement des messages:', error);
        messagesContainer.innerHTML = '<div class="error">Erreur lors du chargement des messages</div>';
    }
}

// Remplacer la fonction sendMessage
async function sendMessage(recipientId) {
    if (!currentReceiverId) return;

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();

    // Si une image est sélectionnée
    if (selectedFile) {
        const formData = new FormData();
        formData.append('photo', selectedFile);
        formData.append('receiver_id', currentReceiverId);
        if (message) {
            formData.append('message', message);
        }

        try {
            const response = await fetch('../includes/send_photo.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                // Réinitialiser les inputs
                messageInput.value = '';
                document.querySelector('.image-preview-container').style.display = 'none';
                document.getElementById('photo-upload').value = '';
                selectedFile = null;

                // Ajouter l'image immédiatement dans la conversation
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message sent';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <img src="${data.photo_url}" alt="Photo envoyée">
                        <span class="message-time">${new Date().toLocaleTimeString()}</span>
                    </div>
                `;
                document.querySelector('.conversation-messages').appendChild(messageDiv);

                // Scroll vers le bas
                const messagesContainer = document.querySelector('.conversation-messages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi de l\'image');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert(error.message);
        }
    } else if (message) {
        // Envoi d'un message texte normal
        try {
            const response = await fetch('../includes/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: currentReceiverId,
                    message: message
                })
            });
            
            if (response.ok) {
                messageInput.value = '';
                // Recharger la conversation
                await loadConversation(currentReceiverId, document.querySelector('.conversation-header h3').textContent);
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
        }
    }
}

// Modifier startNewConversation pour utiliser directement loadConversation
function startNewConversation(userId, username) {
    loadConversation(userId, username);
}

// Modifier la gestion de l'upload de photos
let selectedFile = null;

document.getElementById('photo-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.querySelector('.image-preview-container').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

document.querySelector('.remove-preview').addEventListener('click', function() {
    document.querySelector('.image-preview-container').style.display = 'none';
    document.getElementById('photo-upload').value = '';
    selectedFile = null;
});

// Mettre à jour le gestionnaire de clic du bouton d'envoi
document.getElementById('send-button').onclick = () => sendMessage(currentReceiverId);

// ...existing code...