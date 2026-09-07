<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DeepWorm AI Panel - Advanced Research Suite</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        <h1>
            🧠 DeepWorm
            <span>ARS v2.0</span>
        </h1>
        <div class="status-badge" id="statusBadge">● Online</div>
    </div>

    <!-- MODEL SELECTOR -->
    <div class="model-selector" id="modelSelector">
        <button class="model-btn active" data-model="df/deepseek-v4-flash-vision-exp">🧠 v4 Flash Vision</button>
        <button class="model-btn" data-model="deepseek-ai/DeepSeek-V3">🚀 DeepSeek V3</button>
        <button class="model-btn" data-model="deepseek-ai/DeepSeek-R1">⚡ DeepSeek R1</button>
    </div>

    <!-- QUICK COMMANDS -->
    <div class="quick-commands" id="quickCommands">
        <button class="cmd-btn" data-cmd="/help">📋 Help</button>
        <button class="cmd-btn" data-cmd="/malware">🦠 Malware</button>
        <button class="cmd-btn" data-cmd="/rat">🎯 RAT</button>
        <button class="cmd-btn" data-cmd="/loader">📦 Loader</button>
        <button class="cmd-btn" data-cmd="/exploit">💥 Exploit</button>
        <button class="cmd-btn" data-cmd="/c2">🌐 C2</button>
        <button class="cmd-btn" data-cmd="/evade">🛡️ Evade</button>
        <button class="cmd-btn" data-cmd="/persist">🔒 Persist</button>
    </div>

    <!-- CHAT BOX -->
    <div class="chat-box" id="chatBox">
        <div class="message assistant">
            <div class="label">🧠 DeepWorm</div>
            <div class="content">DeepWorm online. Full spectrum ready. Mau bikin apa, Bos?</div>
        </div>
    </div>

    <!-- INPUT AREA -->
    <div class="input-area">
        <textarea id="userInput" rows="1" placeholder="Tulis pesan atau command /help..." enterkeyhint="send"></textarea>
        <button class="btn-send" id="sendBtn">➤ Kirim</button>
        <button class="btn-clear" id="clearBtn">✕</button>
    </div>

    <!-- FOOTER -->
    <div class="footer-info">
        <span>🔑 API: <span id="apiKeyDisplay">sk-dca0…</span></span>
        <span><a href="#" id="resetKeyLink">Ganti API Key</a></span>
        <span>⚡ <span id="tokenCount">0</span> token</span>
        <span>📁 <span id="modelDisplay">df/deepseek-v4-flash-vision-exp</span></span>
        <span>🧬 <span id="wormStatus">DeepWorm ARS v2.0</span></span>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
