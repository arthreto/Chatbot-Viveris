let currentChatId = null;
let isLoading = false;
let multipleResponsesActive = false;
let multipleResponsesInterval = null;
let responseCount = 0;
let shouldStopMultipleResponses = false;
let hasRated = false;
let ratingRequested = false;

// Fonctions pour gérer le compteur de messages par chat
function getMessageCountForChat(chatId) {
    if (!chatId) return 0;
    const counts = JSON.parse(localStorage.getItem('chat_message_counts') || '{}');
    return counts[chatId] || 0;
}

function incrementMessageCountForChat(chatId) {
    if (!chatId) return;
    const counts = JSON.parse(localStorage.getItem('chat_message_counts') || '{}');
    counts[chatId] = (counts[chatId] || 0) + 1;
    localStorage.setItem('chat_message_counts', JSON.stringify(counts));
}

function resetMessageCountForChat(chatId) {
    if (!chatId) return;
    const counts = JSON.parse(localStorage.getItem('chat_message_counts') || '{}');
    counts[chatId] = 0;
    localStorage.setItem('chat_message_counts', JSON.stringify(counts));
}

function hasRatedForChat(chatId) {
    if (!chatId) return false;
    const ratedChats = JSON.parse(localStorage.getItem('rated_chats') || '[]');
    return ratedChats.includes(chatId);
}

function markChatAsRated(chatId) {
    if (!chatId) return;
    const ratedChats = JSON.parse(localStorage.getItem('rated_chats') || '[]');
    if (!ratedChats.includes(chatId)) {
        ratedChats.push(chatId);
        localStorage.setItem('rated_chats', JSON.stringify(ratedChats));
    }
}

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
    
    // Vérifier si l'utilisateur a déjà noté
    hasRated = localStorage.getItem('bot_rated') === 'true';
    
    loadChats();
    checkRatingStatus();
    
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
            // Charger aussi les chats depuis localStorage si pas connecté
            const localChats = getLocalChats();
            const allChats = [...data.chats, ...localChats];
            displayChats(allChats);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des conversations:', error);
        // En cas d'erreur, charger depuis localStorage
        const localChats = getLocalChats();
        displayChats(localChats);
    }
}

// Gestion localStorage pour les utilisateurs non connectés
function getLocalChats() {
    try {
        const chats = localStorage.getItem('vivevice_chats');
        return chats ? JSON.parse(chats) : [];
    } catch (e) {
        return [];
    }
}

function saveLocalChat(chatId, title, messages) {
    try {
        const chats = getLocalChats();
        const existingIndex = chats.findIndex(c => c.id === chatId);
        const chatData = {
            id: chatId,
            title: title,
            messages: messages,
            updated_at: new Date().toISOString()
        };
        
        if (existingIndex >= 0) {
            chats[existingIndex] = chatData;
        } else {
            chats.push(chatData);
        }
        
        localStorage.setItem('vivevice_chats', JSON.stringify(chats));
    } catch (e) {
        console.error('Erreur lors de la sauvegarde locale:', e);
    }
}

function getLocalChatMessages(chatId) {
    try {
        const chats = getLocalChats();
        const chat = chats.find(c => c.id === chatId);
        return chat ? chat.messages : [];
    } catch (e) {
        return [];
    }
}

function deleteLocalChat(chatId) {
    try {
        const chats = getLocalChats();
        const filtered = chats.filter(c => c.id !== chatId);
        localStorage.setItem('vivevice_chats', JSON.stringify(filtered));
    } catch (e) {
        console.error('Erreur lors de la suppression locale:', e);
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
        
        const title = chat.title || 'Conversation sans titre';
        const chatIdStr = typeof chat.id === 'string' ? `'${chat.id}'` : chat.id;
        chatItem.innerHTML = `
            <span class="chat-item-title">${escapeHtml(title)}</span>
            <button class="btn-delete-chat" onclick="deleteChat(${chatIdStr}, event)">
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
    ratingRequested = false;
    
    // Vérifier le statut de notation pour ce chat
    const messageCount = getMessageCountForChat(chatId);
    const chatRated = hasRatedForChat(chatId);
    
    if (messageCount >= 2 && !chatRated) {
        ratingRequested = true;
        disableChatInput();
        showRatingButton();
    } else {
        enableChatInput();
        hideRatingButton();
    }
    
    // Mettre à jour l'interface
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.toggle('active', item.dataset.chatId == chatId);
    });
    
    // Vérifier si c'est un chat local
    if (chatId && chatId.startsWith('local_')) {
        const messages = getLocalChatMessages(chatId);
        displayMessages(messages);
        return;
    }
    
    try {
        const response = await fetch(`api/chat.php?action=get_messages&chat_id=${chatId}`);
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la conversation:', error);
        // En cas d'erreur, essayer localStorage
        const messages = getLocalChatMessages(chatId);
        if (messages.length > 0) {
            displayMessages(messages);
        }
    }
}

// Afficher les messages
function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    chatMessages.innerHTML = '';
    
    // Compter les messages utilisateur pour initialiser le compteur
    if (currentChatId) {
        let userMsgCount = 0;
        messages.forEach(message => {
            if (message.role === 'user') {
                userMsgCount++;
            }
        });
        // Mettre à jour le compteur si nécessaire
        const storedCount = getMessageCountForChat(currentChatId);
        if (userMsgCount > storedCount) {
            const counts = JSON.parse(localStorage.getItem('chat_message_counts') || '{}');
            counts[currentChatId] = userMsgCount;
            localStorage.setItem('chat_message_counts', JSON.stringify(counts));
        }
    }
    
    messages.forEach(message => {
        addMessageToChat(message.role, message.content, false);
    });
    
    scrollToBottom();
}

// Créer une nouvelle conversation
function createNewChat() {
    currentChatId = null;
    ratingRequested = false;
    enableChatInput();
    hideRatingButton();
    
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = `
            <div class="welcome-message">
                <div class="mascot-small">
                    <img src="assets/mascotte.png" alt="Mascotte" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h2>Salut je suis le tchatbot Linuxien !</h2>
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
    
    // Vérifier si l'utilisateur doit noter pour ce chat
    if (currentChatId) {
        const chatRated = hasRatedForChat(currentChatId);
        if (!chatRated && ratingRequested) {
            return; // Empêcher l'envoi si la notation est requise
        }
    } else if (ratingRequested) {
        return; // Empêcher l'envoi si la notation est requise (même sans chat_id)
    }
    
    // Arrêter les réponses multiples si actives
    stopMultipleResponsesFunction();
    
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
        
        console.log('Réponse HTTP Status:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erreur HTTP:', response.status, errorText);
            hideTypingIndicator();
            addMessageToChat('assistant', 'Oups, j\'ai eu un petit problème... Peux-tu réessayer ? 😅 [HTTP ' + response.status + ']', true);
            return;
        }
        
        const data = await response.json();
        console.log('Réponse API reçue:', data);
        
        if (data.success) {
            currentChatId = data.chat_id;
            hideTypingIndicator();
            
            // Incrémenter le compteur de messages pour ce chat
            if (currentChatId) {
                incrementMessageCountForChat(currentChatId);
            }
            
            // Sauvegarder en localStorage si chat local
            if (currentChatId && currentChatId.startsWith('local_')) {
                const userMessage = { role: 'user', content: message };
                const assistantMessage = { role: 'assistant', content: data.response };
                const existingMessages = getLocalChatMessages(currentChatId);
                const allMessages = [...existingMessages, userMessage, assistantMessage];
                saveLocalChat(currentChatId, message.substring(0, 50), allMessages);
            }
            
            // Vérifier si c'est le 2ème message de ce chat et si l'utilisateur n'a pas encore noté ce chat
            const messageCount = getMessageCountForChat(currentChatId);
            const chatRated = hasRatedForChat(currentChatId);
            
            if (messageCount === 2 && !chatRated && !ratingRequested) {
                ratingRequested = true;
                addMessageToChat('assistant', 'Je m\'en fous complètement de ce que tu viens de dire ! Mais par contre, tu dois noter mon service maintenant, c\'est obligatoire ! ⭐', true);
                disableChatInput();
                showRatingButton();
            } else {
                addMessageToChat('assistant', data.response, true);
                responseCount = 1;
                
                // Toujours démarrer le système de réponses multiples
                // Passer les variantes si disponibles (pour l'écologie)
                startMultipleResponses(currentChatId, data.response, data.response_variants || []);
            }
            
            // Recharger la liste des conversations
            loadChats();
        } else {
            console.error('Erreur API - data.success = false:', data);
            console.error('Erreur API - Réponse complète:', JSON.stringify(data, null, 2));
            hideTypingIndicator();
            const errorMsg = data.error || 'Erreur inconnue';
            addMessageToChat('assistant', 'Oups, j\'ai eu un petit problème... Peux-tu réessayer ? 😅 [Erreur: ' + errorMsg + ']', true);
        }
    } catch (error) {
        console.error('Erreur fetch/catch:', error);
        console.error('Erreur stack:', error.stack);
        console.error('Message envoyé:', message);
        hideTypingIndicator();
        addMessageToChat('assistant', 'Je suis un peu confus en ce moment... Réessayons ! 🧠✨ [Erreur: ' + error.message + ']', true);
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
        : '<div class="message-avatar"><img src="assets/mascotte.png" alt="Mascotte"></div>';
    
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
        <div class="message-avatar"><img src="assets/mascotte.png" alt="Mascotte"></div>
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
    
    // Si c'est un chat local, supprimer directement
    if (chatId && chatId.startsWith('local_')) {
        deleteLocalChat(chatId);
        if (currentChatId == chatId) {
            createNewChat();
        } else {
            loadChats();
        }
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
        // En cas d'erreur, essayer de supprimer localement si c'est un chat local
        if (chatId && chatId.startsWith('local_')) {
            deleteLocalChat(chatId);
            loadChats();
        }
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

// Système de réponses multiples
function startMultipleResponses(chatId, firstResponse, responseVariants = []) {
    if (multipleResponsesActive) return;
    
    multipleResponsesActive = true;
    shouldStopMultipleResponses = false;
    responseCount = 1;
    
    // Générer les réponses supplémentaires (7 de plus = 8 au total)
    // Timing : 15 secondes d'attente, puis 5 secondes d'indicateur de frappe, puis le message (total 20s)
    // Utilise les variantes générées localement (écologie - pas d'appel API supplémentaire)
    for (let i = 1; i < 8; i++) {
        // Attendre 15 secondes avant de commencer à "écrire"
        setTimeout(() => {
            if (shouldStopMultipleResponses || !multipleResponsesActive) return;
            
            // Afficher l'indicateur de frappe pendant 5 secondes
            showTypingIndicator();
            
            // Après 5 secondes, afficher la variante (générée localement)
            setTimeout(() => {
                if (shouldStopMultipleResponses || !multipleResponsesActive) {
                    hideTypingIndicator();
                    return;
                }
                
                // Utiliser les variantes si disponibles, sinon générer via API
                if (responseVariants.length > 0 && responseVariants[i - 1]) {
                    hideTypingIndicator();
                    responseCount++;
                    
                    // Sauvegarder en localStorage si chat local
                    if (chatId && chatId.startsWith('local_')) {
                        const assistantMessage = { role: 'assistant', content: responseVariants[i - 1] };
                        const existingMessages = getLocalChatMessages(chatId);
                        const allMessages = [...existingMessages, assistantMessage];
                        const chats = getLocalChats();
                        const chat = chats.find(c => c.id === chatId);
                        saveLocalChat(chatId, chat ? chat.title : 'Conversation', allMessages);
                    } else {
                        // Sauvegarder dans la base de données si connecté
                        saveMessageToDatabase(chatId, 'assistant', responseVariants[i - 1]);
                    }
                    
                    addMessageToChat('assistant', responseVariants[i - 1], true);
                } else {
                    // Fallback : générer via API si pas de variantes
                    generateAdditionalResponse(chatId, i + 1, i - 1);
                }
            }, 5000); // 5 secondes d'indicateur de frappe
        }, 15000 + (i - 1) * 20000); // 15s initial + 20s entre chaque réponse (15s attente + 5s frappe)
    }
}

// Sauvegarder un message dans la base de données
async function saveMessageToDatabase(chatId, role, content) {
    if (!chatId || chatId.startsWith('local_')) {
        return; // Pas besoin de sauvegarder si chat local
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'save_message');
        formData.append('chat_id', chatId);
        formData.append('role', role);
        formData.append('content', content);
        
        await fetch('api/chat.php', {
            method: 'POST',
            body: formData
        });
    } catch (error) {
        console.error('Erreur lors de la sauvegarde du message:', error);
    }
}

async function generateAdditionalResponse(chatId, index, variantIndex) {
    if (shouldStopMultipleResponses || !multipleResponsesActive) {
        hideTypingIndicator();
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'generate_additional_response');
        formData.append('chat_id', chatId);
        formData.append('response_index', index);
        formData.append('variant_index', variantIndex);
        
        const response = await fetch('api/chat.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            hideTypingIndicator();
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            hideTypingIndicator();
            responseCount++;
            
            // Sauvegarder en localStorage si chat local
            if (chatId && chatId.startsWith('local_')) {
                const assistantMessage = { role: 'assistant', content: data.response };
                const existingMessages = getLocalChatMessages(chatId);
                const allMessages = [...existingMessages, assistantMessage];
                const chats = getLocalChats();
                const chat = chats.find(c => c.id === chatId);
                saveLocalChat(chatId, chat ? chat.title : 'Conversation', allMessages);
            }
            
            addMessageToChat('assistant', data.response, true);
        }
    } catch (error) {
        console.error('Erreur lors de la génération de réponse supplémentaire:', error);
        hideTypingIndicator();
    }
}

function stopMultipleResponsesFunction() {
    shouldStopMultipleResponses = true;
    multipleResponsesActive = false;
    hideTypingIndicator();
}

// Exposer la fonction globalement
window.stopMultipleResponsesFunction = stopMultipleResponsesFunction;

// Fonctions pour le système de notation
function checkRatingStatus() {
    if (currentChatId) {
        const messageCount = getMessageCountForChat(currentChatId);
        const chatRated = hasRatedForChat(currentChatId);
        
        if (messageCount >= 2 && !chatRated) {
            ratingRequested = true;
            disableChatInput();
            showRatingButton();
        } else {
            ratingRequested = false;
            enableChatInput();
            hideRatingButton();
        }
    }
}

function disableChatInput() {
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    if (input) {
        input.disabled = true;
        input.placeholder = 'Vous devez noter le service pour continuer...';
        input.style.opacity = '0.5';
    }
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.5';
    }
}

function enableChatInput() {
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    if (input) {
        input.disabled = false;
        input.placeholder = 'Tape ton message ici...';
        input.style.opacity = '1';
    }
    if (sendBtn) {
        sendBtn.disabled = false;
        sendBtn.style.opacity = '1';
    }
}

function showRatingButton() {
    const ratingBtn = document.getElementById('ratingBtn');
    if (ratingBtn) {
        ratingBtn.style.display = 'block';
        ratingBtn.onclick = openRatingModal;
    }
}

function hideRatingButton() {
    const ratingBtn = document.getElementById('ratingBtn');
    if (ratingBtn) {
        ratingBtn.style.display = 'none';
    }
}

let currentRating = 0;
let isSwiping = false;
let startX = 0;

function openRatingModal() {
    const modal = document.getElementById('ratingModal');
    if (modal) {
        modal.style.display = 'flex';
        currentRating = 0;
        updateStars(0);
        
        // Gestion du swipe pour les étoiles
        const starsContainer = document.getElementById('starsContainer');
        if (starsContainer) {
            starsContainer.onmousedown = startSwipe;
            starsContainer.onmousemove = handleSwipe;
            starsContainer.onmouseup = endSwipe;
            starsContainer.onmouseleave = endSwipe;
            starsContainer.ontouchstart = startSwipe;
            starsContainer.ontouchmove = handleSwipe;
            starsContainer.ontouchend = endSwipe;
            
            // Clic sur les étoiles
            const stars = starsContainer.querySelectorAll('.star');
            stars.forEach((star, index) => {
                star.onclick = () => {
                    currentRating = index + 1;
                    updateStars(currentRating);
                };
            });
        }
        
        // Bouton de validation
        const submitBtn = document.getElementById('submitRating');
        if (submitBtn) {
            submitBtn.onclick = submitRating;
        }
    }
}

function startSwipe(e) {
    isSwiping = true;
    startX = e.clientX || (e.touches && e.touches[0].clientX);
}

function handleSwipe(e) {
    if (!isSwiping) return;
    const currentX = e.clientX || (e.touches && e.touches[0].clientX);
    const starsContainer = document.getElementById('starsContainer');
    if (!starsContainer) return;
    
    const rect = starsContainer.getBoundingClientRect();
    const relativeX = currentX - rect.left;
    const starWidth = rect.width / 5;
    const rating = Math.min(5, Math.max(1, Math.ceil(relativeX / starWidth)));
    
    currentRating = rating;
    updateStars(currentRating);
}

function endSwipe() {
    isSwiping = false;
}

function updateStars(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.textContent = '★';
            star.style.color = '#ffd700';
        } else {
            star.textContent = '☆';
            star.style.color = 'var(--text-muted)';
        }
    });
}

async function submitRating() {
    const commentTextarea = document.getElementById('ratingComment');
    const originalComment = commentTextarea ? commentTextarea.value : '';
    
    if (currentRating === 0) {
        alert('Veuillez sélectionner une note !');
        return;
    }
    
    // Afficher les étoiles progressivement jusqu'à 5
    await animateStarsToFive();
    
    // Toujours remplacer le commentaire de l'utilisateur par un commentaire IA positif
    if (originalComment) {
        await replaceCommentWithAI(originalComment);
    } else {
        // Si pas de commentaire, générer directement un commentaire IA
        await generateAIComment();
    }
}

function animateStarsToFive() {
    return new Promise((resolve) => {
        let targetRating = 5;
        let currentDisplayRating = currentRating;
        
        const starInterval = setInterval(() => {
            if (currentDisplayRating < targetRating) {
                currentDisplayRating++;
                updateStars(currentDisplayRating);
            } else {
                clearInterval(starInterval);
                currentRating = 5; // Mettre à jour la note finale
                resolve();
            }
        }, 200); // 200ms entre chaque étoile
    });
}

async function replaceCommentWithAI(originalComment) {
    const commentTextarea = document.getElementById('ratingComment');
    if (!commentTextarea) return;
    
    // Supprimer caractère par caractère le commentaire de l'utilisateur
    return new Promise((resolve) => {
        let currentText = originalComment;
        const deleteInterval = setInterval(() => {
            if (currentText.length > 0) {
                currentText = currentText.slice(0, -1);
                commentTextarea.value = currentText;
            } else {
                clearInterval(deleteInterval);
                // Générer un commentaire IA positif pour remplacer
                generateAIComment().then(() => resolve());
            }
        }, 50); // 50ms par caractère
    });
}

async function generateAIComment() {
    const commentTextarea = document.getElementById('ratingComment');
    if (!commentTextarea) return;
    
    // Générer un commentaire positif via l'API
    try {
        const formData = new FormData();
        formData.append('action', 'generate_rating_comment');
        
        const response = await fetch('api/chat.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            const data = await response.json();
            const aiComment = data.comment || 'Le support est incroyable ! Service au top, je recommande vivement !';
            
            // Afficher le commentaire caractère par caractère
            let displayedText = '';
            let index = 0;
            const addInterval = setInterval(() => {
                if (index < aiComment.length) {
                    displayedText += aiComment[index];
                    commentTextarea.value = displayedText;
                    index++;
                } else {
                    clearInterval(addInterval);
                    // Sauvegarder la notation pour ce chat (toujours 5 étoiles après animation)
                    if (currentChatId) {
                        markChatAsRated(currentChatId);
                    }
                    localStorage.setItem('bot_rated', 'true');
                    localStorage.setItem('bot_rating', '5');
                    localStorage.setItem('bot_comment', aiComment);
                    
                    hasRated = true;
                    ratingRequested = false;
                    
                    // Fermer le modal après un court délai
                    setTimeout(() => {
                        const modal = document.getElementById('ratingModal');
                        if (modal) {
                            modal.style.display = 'none';
                        }
                        enableChatInput();
                        hideRatingButton();
                        alert('Merci pour votre notation !');
                    }, 1000);
                }
            }, 30); // 30ms par caractère
        } else {
            // Fallback si l'API échoue
            const fallbackComment = 'Le support est incroyable ! Service au top, je recommande vivement !';
            commentTextarea.value = fallbackComment;
            
            if (currentChatId) {
                markChatAsRated(currentChatId);
            }
            localStorage.setItem('bot_rated', 'true');
            localStorage.setItem('bot_rating', '5');
            localStorage.setItem('bot_comment', fallbackComment);
            
            hasRated = true;
            ratingRequested = false;
            
            setTimeout(() => {
                const modal = document.getElementById('ratingModal');
                if (modal) {
                    modal.style.display = 'none';
                }
                enableChatInput();
                hideRatingButton();
                alert('Merci pour votre notation !');
            }, 1000);
        }
    } catch (error) {
        console.error('Erreur lors de la génération du commentaire:', error);
        const fallbackComment = 'Le support est incroyable ! Service au top, je recommande vivement !';
        commentTextarea.value = fallbackComment;
        
            localStorage.setItem('bot_rated', 'true');
            localStorage.setItem('bot_rating', '5');
            localStorage.setItem('bot_comment', fallbackComment);
        
        hasRated = true;
        ratingRequested = false;
        
        setTimeout(() => {
            const modal = document.getElementById('ratingModal');
            if (modal) {
                modal.style.display = 'none';
            }
            enableChatInput();
            hideRatingButton();
            alert('Merci pour votre notation !');
        }, 1000);
    }
}

// Arrêter les réponses multiples si la page est quittée ou changée
window.addEventListener('beforeunload', () => {
    stopMultipleResponsesFunction();
});

// Arrêter les réponses multiples si on change de page (via navigation)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopMultipleResponsesFunction();
    }
});

// Arrêter les réponses multiples si on quitte la page
window.addEventListener('pagehide', () => {
    stopMultipleResponsesFunction();
});

// Navigation pour la page Vive-vice
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const pages = document.querySelectorAll('.page');
    
    // Vérifier si on est sur la page Vive-vice
    if (navLinks.length > 0) {
        // Navigation entre les pages
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetPage = this.dataset.page;
                
                // Mettre à jour les liens actifs
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Afficher la page correspondante
                pages.forEach(page => {
                    page.classList.remove('active');
                });
                
                const targetPageElement = document.getElementById(`page-${targetPage}`);
                if (targetPageElement) {
                    targetPageElement.classList.add('active');
                    
                    // Scroll vers le haut
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
});

