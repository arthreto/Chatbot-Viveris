let currentChatId = null;
let isLoading = false;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const newChatBtn = document.getElementById('newChatBtn');
    
    if (chatForm) {
        chatForm.addEventListener('submit', handleSendMessage);
    }
    
    if (newChatBtn) {
        newChatBtn.addEventListener('click', createNewChat);
    }
    
    loadChats();
    
    // Animation de la mascotte
    animateMascot();
});

// Animation de la mascotte
function animateMascot() {
    const mascot = document.getElementById('mascot');
    if (!mascot) return;
    
    setInterval(() => {
        mascot.style.animation = 'none';
        setTimeout(() => {
            mascot.style.animation = 'float 3s ease-in-out infinite';
        }, 10);
    }, 5000);
}

// Charger les conversations
async function loadChats() {
    try {
        const response = await fetch('api/chat.php?action=get_chats');
        const data = await response.json();
        
        if (data.success) {
            displayChats(data.chats);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des conversations:', error);
    }
}

// Afficher les conversations
function displayChats(chats) {
    const chatsList = document.getElementById('chatsList');
    if (!chatsList) return;
    
    chatsList.innerHTML = '';
    
    chats.forEach(chat => {
        const chatItem = document.createElement('div');
        chatItem.className = 'chat-item';
        chatItem.dataset.chatId = chat.id;
        
        if (chat.id == currentChatId) {
            chatItem.classList.add('active');
        }
        
        chatItem.innerHTML = `
            <span class="chat-item-title">${escapeHtml(chat.title)}</span>
            <button class="btn-delete-chat" onclick="deleteChat(${chat.id}, event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        `;
        
        chatItem.addEventListener('click', (e) => {
            if (!e.target.closest('.btn-delete-chat')) {
                loadChat(chat.id);
            }
        });
        
        chatsList.appendChild(chatItem);
    });
}

// Charger une conversation
async function loadChat(chatId) {
    currentChatId = chatId;
    
    // Mettre à jour l'interface
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.toggle('active', item.dataset.chatId == chatId);
    });
    
    try {
        const response = await fetch(`api/chat.php?action=get_messages&chat_id=${chatId}`);
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la conversation:', error);
    }
}

// Afficher les messages
function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    chatMessages.innerHTML = '';
    
    messages.forEach(message => {
        addMessageToChat(message.role, message.content, false);
    });
    
    scrollToBottom();
}

// Créer une nouvelle conversation
function createNewChat() {
    currentChatId = null;
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = `
            <div class="welcome-message">
                <div class="mascot-small">
                    <div class="mascot-face">
                        <div class="eye left-eye"></div>
                        <div class="eye right-eye"></div>
                        <div class="mouth"></div>
                    </div>
                </div>
                <h2>Salut ! Je suis ton chatbot philosophique ! 🧠✨</h2>
                <p>Pose-moi n'importe quelle question, je vais répondre de manière complètement décalée mais géniale !</p>
            </div>
        `;
    }
    
    // Mettre à jour la liste
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    
    loadChats();
}

// Envoyer un message
async function handleSendMessage(e) {
    e.preventDefault();
    
    if (isLoading) return;
    
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Afficher le message de l'utilisateur
    addMessageToChat('user', message, true);
    input.value = '';
    
    // Afficher l'indicateur de frappe
    showTypingIndicator();
    
    isLoading = true;
    const sendBtn = document.getElementById('sendBtn');
    if (sendBtn) sendBtn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('message', message);
        if (currentChatId) {
            formData.append('chat_id', currentChatId);
        }
        
        const response = await fetch('api/chat.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentChatId = data.chat_id;
            hideTypingIndicator();
            addMessageToChat('assistant', data.response, true);
            
            // Recharger la liste des conversations
            loadChats();
        } else {
            hideTypingIndicator();
            addMessageToChat('assistant', 'Oups, j\'ai eu un petit problème... Peux-tu réessayer ? 😅', true);
        }
    } catch (error) {
        console.error('Erreur:', error);
        hideTypingIndicator();
        addMessageToChat('assistant', 'Je suis un peu confus en ce moment... Réessayons ! 🧠✨', true);
    } finally {
        isLoading = false;
        if (sendBtn) sendBtn.disabled = false;
    }
}

// Ajouter un message au chat
function addMessageToChat(role, content, animate = false) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    // Supprimer le message de bienvenue s'il existe
    const welcomeMessage = chatMessages.querySelector('.welcome-message');
    if (welcomeMessage) {
        welcomeMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role}`;
    
    if (animate) {
        messageDiv.style.animation = 'fadeIn 0.3s ease-out';
    }
    
    const avatar = role === 'user' 
        ? '<div class="message-avatar">👤</div>'
        : '<div class="message-avatar"><div class="mascot-face" style="width: 100%; height: 100%;"><div class="eye left-eye"></div><div class="eye right-eye"></div><div class="mouth"></div></div></div>';
    
    messageDiv.innerHTML = `
        ${avatar}
        <div class="message-content">${formatMessage(content)}</div>
    `;
    
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

// Formater le message (support markdown basique)
function formatMessage(text) {
    // Échapper le HTML
    text = escapeHtml(text);
    
    // Convertir les sauts de ligne
    text = text.replace(/\n/g, '<br>');
    
    // Emojis et formatage basique
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    return text;
}

// Afficher l'indicateur de frappe
function showTypingIndicator() {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    const typingDiv = document.createElement('div');
    typingDiv.className = 'message assistant';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <div class="message-avatar"><div class="mascot-face" style="width: 100%; height: 100%;"><div class="eye left-eye"></div><div class="eye right-eye"></div><div class="mouth"></div></div></div>
        <div class="typing-indicator">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
    `;
    
    chatMessages.appendChild(typingDiv);
    scrollToBottom();
}

// Masquer l'indicateur de frappe
function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Supprimer une conversation
async function deleteChat(chatId, event) {
    event.stopPropagation();
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette conversation ?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_chat');
        formData.append('chat_id', chatId);
        
        const response = await fetch('api/chat.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (currentChatId == chatId) {
                createNewChat();
            } else {
                loadChats();
            }
        }
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
    }
}

// Faire défiler vers le bas
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Échapper le HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

