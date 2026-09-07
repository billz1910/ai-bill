// ============================================================
// STATE
// ============================================================
let state = {
    currentChatId: null,
    chats: [],
    messages: [],
    isProcessing: false,
    apiKey: '',
    model: '',
    maxTokens: 2048,
    temperature: 0.7
};

// ============================================================
// DOM REFS
// ============================================================
const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
const sendBtn = document.getElementById('sendBtn');
const chatList = document.getElementById('chatList');
const statusBadge = document.getElementById('statusBadge');
const tokenCount = document.getElementById('tokenCount');
const typingIndicator = document.getElementById('typingIndicator');
const settingsModal = document.getElementById('settingsModal');

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadChats();
    setupEventListeners();
});

// ============================================================
// LOAD CHATS
// ============================================================
async function loadChats() {
    try {
        const response = await fetch('/api/chats');
        const data = await response.json();
        state.chats = data;
        renderChatList();
        
        if (state.chats.length > 0) {
            // Load most recent chat
            const latest = state.chats[0];
            loadChat(latest.id);
        } else {
            // Create new chat
            await createNewChat();
        }
    } catch (error) {
        console.error('Error loading chats:', error);
    }
}

async function loadChat(chatId) {
    try {
        const response = await fetch(`/api/chat?id=${chatId}`);
        const data = await response.json();
        state.messages = data;
        state.currentChatId = chatId;
        renderMessages();
        updateChatListActive(chatId);
        
        // Update token count
        updateTokenCount();
    } catch (error) {
        console.error('Error loading chat:', error);
    }
}

async function createNewChat() {
    try {
        const response = await fetch('/api/new', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: 'New Chat' })
        });
        const data = await response.json();
        state.currentChatId = data.id;
        state.messages = [];
        renderMessages();
        await loadChats();
    } catch (error) {
        console.error('Error creating chat:', error);
    }
}

async function deleteChat(chatId, event) {
    event.stopPropagation();
    if (!confirm('Hapus chat ini?')) return;
    
    try {
        await fetch(`/api/delete?id=${chatId}`, { method: 'DELETE' });
        await loadChats();
        if (state.chats.length > 0) {
            loadChat(state.chats[0].id);
        } else {
            await createNewChat();
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
    }
}

// ============================================================
// RENDER
// ============================================================
function renderChatList() {
    chatList.innerHTML = '';
    state.chats.forEach(chat => {
        const div = document.createElement('div');
        div.className = `chat-item${chat.id === state.currentChatId ? ' active' : ''}`;
        div.dataset.id = chat.id;
        div.innerHTML = `
            <span class="chat-icon">💬</span>
            <span class="chat-title">${chat.title}</span>
            <button class="chat-delete" data-id="${chat.id}">✕</button>
        `;
        div.addEventListener('click', () => loadChat(chat.id));
        div.querySelector('.chat-delete').addEventListener('click', (e) => deleteChat(chat.id, e));
        chatList.appendChild(div);
    });
}

function updateChatListActive(chatId) {
    document.querySelectorAll('.chat-item').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.id) === chatId);
    });
}

function renderMessages() {
    chatMessages.innerHTML = '';
    
    if (state.messages.length === 0) {
        // Welcome message
        const div = document.createElement('div');
        div.className = 'message assistant';
        div.innerHTML = `
            <div class="message-avatar">🤖</div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-name">AI Assistant</span>
                    <span class="message-time">Just now</span>
                </div>
                <div class="message-body">
                    Halo! Saya siap membantu Anda. Silakan tanyakan apa saja. 😊
                </div>
            </div>
        `;
        chatMessages.appendChild(div);
        return;
    }
    
    state.messages.forEach(msg => {
        const div = document.createElement('div');
        div.className = `message ${msg.role}`;
        const avatar = msg.role === 'user' ? '👤' : '🤖';
        const name = msg.role === 'user' ? 'You' : 'AI Assistant';
        
        div.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-name">${name}</span>
                    <span class="message-time">${formatTime(msg.created_at)}</span>
                </div>
                <div class="message-body">${formatMessage(msg.content)}</div>
            </div>
        `;
        chatMessages.appendChild(div);
    });
    
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function formatMessage(text) {
    let formatted = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    formatted = formatted.replace(/\n/g, '<br>');
    formatted = formatted.replace(/`([^`]+)`/g, '<code>$1</code>');
    return formatted;
}

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000 / 60);
    
    if (diff < 1) return 'Just now';
    if (diff < 60) return `${diff}m ago`;
    if (diff < 1440) return `${Math.floor(diff / 60)}h ago`;
    return date.toLocaleDateString();
}

function updateTokenCount() {
    // Count tokens roughly
    let total = 0;
    state.messages.forEach(msg => {
        total += Math.ceil(msg.content.length / 4);
    });
    tokenCount.textContent = total;
}

// ============================================================
// SEND MESSAGE
// ============================================================
async function sendMessage() {
    const text = userInput.value.trim();
    if (!text || state.isProcessing) return;
    
    // Add message to UI
    const tempMsg = {
        role: 'user',
        content: text,
        created_at: new Date().toISOString()
    };
    state.messages.push(tempMsg);
    renderMessages();
    userInput.value = '';
    userInput.style.height = 'auto';
    
    state.isProcessing = true;
    sendBtn.disabled = true;
    showTyping(true);
    setStatus(true);
    
    try {
        const response = await fetch('/api/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: state.currentChatId,
                message: text,
                api_key: state.apiKey,
                model: state.model
            })
        });
        
        const data = await response.json();
        showTyping(false);
        
        if (data.success) {
            state.messages.push({
                role: 'assistant',
                content: data.content,
                created_at: new Date().toISOString()
            });
            renderMessages();
            updateTokenCount();
            await loadChats(); // Refresh list
        } else {
            throw new Error(data.error || 'Unknown error');
        }
        
        setStatus(true);
    } catch (error) {
        showTyping(false);
        setStatus(false);
        state.messages.push({
            role: 'assistant',
            content: `⚠️ Error: ${error.message}`,
            created_at: new Date().toISOString()
        });
        renderMessages();
    } finally {
        state.isProcessing = false;
        sendBtn.disabled = false;
        userInput.focus();
    }
}

// ============================================================
// TYPING INDICATOR
// ============================================================
function showTyping(show) {
    typingIndicator.style.display = show ? 'flex' : 'none';
    if (show) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// ============================================================
// STATUS
// ============================================================
function setStatus(online) {
    statusBadge.textContent = online ? '● Online' : '● Offline';
    statusBadge.className = `status-badge${online ? '' : ' offline'}`;
}

// ============================================================
// SETTINGS
// ============================================================
async function loadSettings() {
    try {
        const response = await fetch('/api/settings');
        const data = await response.json();
        state.apiKey = data.api_key || '';
        state.model = data.model || 'df/deepseek-v4-flash-vision-exp';
        state.maxTokens = data.max_tokens || 2048;
        state.temperature = data.temperature || 0.7;
        
        // Update UI
        document.getElementById('apiKeyInput').value = state.apiKey;
        document.getElementById('modelSelect').value = state.model;
        document.getElementById('maxTokensInput').value = state.maxTokens;
        document.getElementById('temperatureInput').value = state.temperature;
        document.getElementById('temperatureValue').textContent = state.temperature;
        
        // Update model buttons
        document.querySelectorAll('.model-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.model === state.model);
        });
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

async function saveSettings() {
    const settings = {
        api_key: document.getElementById('apiKeyInput').value,
        model: document.getElementById('modelSelect').value,
        max_tokens: parseInt(document.getElementById('maxTokensInput').value),
        temperature: parseFloat(document.getElementById('temperatureInput').value)
    };
    
    try {
        await fetch('/api/settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(settings)
        });
        
        state.apiKey = settings.api_key;
        state.model = settings.model;
        state.maxTokens = settings.max_tokens;
        state.temperature = settings.temperature;
        
        // Update model buttons
        document.querySelectorAll('.model-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.model === settings.model);
        });
        
        closeSettings();
        alert('Settings saved!');
    } catch (error) {
        alert('Error saving settings: ' + error.message);
    }
}

// ============================================================
// MODAL
// ============================================================
function openSettings() {
    settingsModal.classList.add('active');
    document.getElementById('apiKeyInput').value = state.apiKey;
    document.getElementById('modelSelect').value = state.model;
    document.getElementById('maxTokensInput').value = state.maxTokens;
    document.getElementById('temperatureInput').value = state.temperature;
    document.getElementById('temperatureValue').textContent = state.temperature;
}

function closeSettings() {
    settingsModal.classList.remove('active');
}

// ============================================================
// EVENT LISTENERS
// ============================================================
function setupEventListeners() {
    // Send
    sendBtn.addEventListener('click', sendMessage);
    userInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    userInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // New chat
    document.getElementById('newChatBtn').addEventListener('click', createNewChat);
    
    // Toggle sidebar (mobile)
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('open');
    });
    
    // Settings
    document.getElementById('settingsBtn').addEventListener('click', openSettings);
    document.getElementById('closeSettings').addEventListener('click', closeSettings);
    document.getElementById('saveSettings').addEventListener('click', saveSettings);
    document.getElementById('temperatureInput').addEventListener('input', function() {
        document.getElementById('temperatureValue').textContent = this.value;
    });
    
    // Toggle API key visibility
    document.getElementById('toggleKeyVisibility').addEventListener('click', function() {
        const input = document.getElementById('apiKeyInput');
        input.type = input.type === 'password' ? 'text' : 'password';
        this.textContent = input.type === 'password' ? '👁️' : '🔒';
    });
    
    // Model buttons
    document.querySelectorAll('.model-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const model = this.dataset.model;
            document.getElementById('modelSelect').value = model;
            saveSettings();
        });
    });
    
    // Close modal on overlay click
    settingsModal.addEventListener('click', function(e) {
        if (e.target === this) closeSettings();
    });
    
    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('toggleSidebar');
        if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
}
