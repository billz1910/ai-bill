<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Panel Pro - DeepSeek</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">🧠</span>
                <span class="logo-text">AI Panel</span>
            </div>
            <button class="btn-new-chat" id="newChatBtn">
                <span>+</span> New Chat
            </button>
        </div>

        <div class="sidebar-body">
            <div class="sidebar-section">
                <div class="section-title">Recent Chats</div>
                <div class="chat-list" id="chatList">
                    <!-- Akan di-load via JS -->
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">👤</div>
                <div class="user-details">
                    <div class="user-name">User</div>
                    <div class="user-status">● Online</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="main-header">
            <button class="btn-toggle-sidebar" id="toggleSidebar">☰</button>
            <div class="header-center">
                <div class="model-selector">
                    <button class="model-btn active" data-model="df/deepseek-v4-flash-vision-exp">
                        <span>🧠</span> DeepSeek V4
                    </button>
                    <button class="model-btn" data-model="deepseek-ai/DeepSeek-V3">
                        <span>🚀</span> DeepSeek V3
                    </button>
                    <button class="model-btn" data-model="deepseek-ai/DeepSeek-R1">
                        <span>⚡</span> DeepSeek R1
                    </button>
                </div>
            </div>
            <div class="header-right">
                <button class="btn-icon" id="settingsBtn" title="Settings">⚙️</button>
                <span class="status-badge" id="statusBadge">● Online</span>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area" id="chatArea">
            <div class="chat-messages" id="chatMessages">
                <div class="message assistant">
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
                </div>
            </div>

            <!-- Typing Indicator -->
            <div class="typing-indicator" id="typingIndicator" style="display:none;">
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
                <span class="typing-text">AI sedang mengetik...</span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-wrapper">
                <textarea id="userInput" rows="1" placeholder="Tulis pesan di sini..." enterkeyhint="send"></textarea>
                <button class="btn-send" id="sendBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                </button>
            </div>
            <div class="input-footer">
                <span class="input-hint">Press Enter to send, Shift+Enter for new line</span>
                <span class="token-info">⚡ <span id="tokenCount">0</span> tokens</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Settings -->
<div class="modal" id="settingsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>⚙️ Settings</h2>
            <button class="modal-close" id="closeSettings">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>API Key</label>
                <div class="input-with-button">
                    <input type="password" id="apiKeyInput" placeholder="Masukkan API Key" />
                    <button class="btn-show-key" id="toggleKeyVisibility">👁️</button>
                </div>
                <small>Default: sk-dca0173befe1a998-0us61o-c8855a33</small>
            </div>
            <div class="form-group">
                <label>Model</label>
                <select id="modelSelect">
                    <option value="df/deepseek-v4-flash-vision-exp">DeepSeek V4 Flash Vision</option>
                    <option value="deepseek-ai/DeepSeek-V3">DeepSeek V3</option>
                    <option value="deepseek-ai/DeepSeek-R1">DeepSeek R1</option>
                </select>
            </div>
            <div class="form-group">
                <label>Max Tokens</label>
                <input type="number" id="maxTokensInput" value="2048" min="100" max="8192" />
            </div>
            <div class="form-group">
                <label>Temperature</label>
                <input type="range" id="temperatureInput" min="0" max="2" step="0.1" value="0.7" />
                <span id="temperatureValue">0.7</span>
            </div>
            <button class="btn-save-settings" id="saveSettings">💾 Save Settings</button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
