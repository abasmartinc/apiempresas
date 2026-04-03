<!-- AI CHAT BUBBLE -->
<div id="ai-chat-container" class="ai-chat-minimized">
    <!-- Chat Window -->
    <div id="ai-chat-window" class="glass-morph">
        <div class="chat-header">
            <div class="header-info">
                <div class="ai-avatar">🤖</div>
                <div>
                    <span class="ai-name">Asistente IA</span>
                    <span class="ai-status">En línea</span>
                </div>
            </div>
            <button id="close-chat" class="icon-btn">✕</button>
        </div>
        
        <div id="chat-messages" class="chat-body">
            <div class="msg ai-msg bounce-in">
                ¡Hola! Soy el asistente de APIEmpresas. ¿En qué puedo ayudarte hoy?
            </div>
        </div>

        <!-- Typing Indicator (Hidden by default) -->
        <div id="ai-typing" class="typing-indicator" style="display: none;">
            <span></span><span></span><span></span>
            <small id="ai-status-text">Consultando...</small>
        </div>

        <div class="chat-footer">
            <div class="input-wrapper">
                <input type="text" id="chat-input" placeholder="Pregunta algo..." autocomplete="off">
                <button id="send-btn" class="send-icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path>
                    </svg>
                </button>
            </div>
            <div class="footer-meta">Powered by APIEmpresas AI</div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button id="chat-toggle" class="chat-btn-pulse">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
    </button>
</div>

<style>
/* --- CHAT BUBBLE STYLES --- */
#ai-chat-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    font-family: 'Inter', system-ui, sans-serif;
}

#ai-chat-window {
    width: 350px;
    height: 500px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 24px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform-origin: bottom right;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    margin-bottom: 20px;
}

.ai-chat-minimized #ai-chat-window {
    transform: scale(0) translateY(100px);
    opacity: 0;
    pointer-events: none;
}

/* Header */
.chat-header {
    background: linear-gradient(135deg, #133A82 0%, #1e4ea3 100%);
    padding: 20px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.ai-name {
    display: block;
    font-weight: 600;
    font-size: 0.95rem;
}

.ai-status {
    display: block;
    font-size: 0.75rem;
    opacity: 0.8;
}

#close-chat {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 1.1rem;
    opacity: 0.7;
    transition: 0.2s;
}

#close-chat:hover { opacity: 1; }

/* Body */
.chat-body {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.msg {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 0.9rem;
    line-height: 1.4;
}

.ai-msg {
    align-self: flex-start;
    background: #f1f3f9;
    color: #333;
    border-bottom-left-radius: 4px;
}

.user-msg {
    align-self: flex-end;
    background: #12B48A;
    color: white;
    border-bottom-right-radius: 4px;
}

/* Footer */
.chat-footer {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
}

.input-wrapper {
    display: flex;
    background: #f8f9fa;
    border-radius: 30px;
    padding: 8px 15px;
    border: 1px solid #ddd;
    transition: 0.2s;
}

.input-wrapper:focus-within {
    border-color: #133A82;
    box-shadow: 0 0 0 3px rgba(19, 58, 130, 0.1);
}

#chat-input {
    flex: 1;
    border: none;
    background: none;
    padding: 5px;
    font-size: 0.9rem;
    outline: none;
}

.send-icon {
    background: none;
    border: none;
    color: #133A82;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
}

.footer-meta {
    text-align: center;
    font-size: 0.65rem;
    color: #aaa;
    margin-top: 8px;
}

/* Toggle */
#chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #133A82;
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(19, 58, 130, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
}

#chat-toggle:hover {
    transform: scale(1.1);
}

.chat-btn-pulse {
    animation: chat-pulse 3s infinite;
}

@keyframes chat-pulse {
    0% { box-shadow: 0 0 0 0 rgba(19, 58, 130, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(19, 58, 130, 0); }
    100% { box-shadow: 0 0 0 0 rgba(19, 58, 130, 0); }
}

.bounce-in {
    animation: bounce 0.4s ease-out;
}

@keyframes bounce {
    0% { transform: scale(0.8); opacity: 0; }
    70% { transform: scale(1.05); }
    100% { transform: scale(1); opacity: 1; }
}

/* Typing Indicator */
.typing-indicator {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.typing-indicator span {
    width: 6px;
    height: 6px;
    background: #133A82;
    border-radius: 50%;
    animation: typing 1s infinite alternate;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    from { transform: translateY(0); opacity: 0.3; }
    to { transform: translateY(-5px); opacity: 1; }
}

#ai-status-text {
    margin-left: 10px;
    color: #133A82;
    font-weight: 500;
    font-size: 0.75rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('ai-chat-container');
    const toggleBtn = document.getElementById('chat-toggle');
    const closeBtn = document.getElementById('close-chat');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const chatMessages = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('ai-typing');
    const statusText = document.getElementById('ai-status-text');

    // Toggle Chat
    toggleBtn.onclick = () => {
        chatContainer.classList.toggle('ai-chat-minimized');
        if(!chatContainer.classList.contains('ai-chat-minimized')) chatInput.focus();
    };
    closeBtn.onclick = () => chatContainer.classList.add('ai-chat-minimized');

    // Send Message
    async function handleSend() {
        const text = chatInput.value.trim();
        if (!text) return;

        // 1. Mostrar mensaje del usuario
        addMessage(text, 'user');
        chatInput.value = '';

        // 2. Mostrar indicador de escritura
        showTyping('Pensando...');
        scrollToBottom();

        // 3. Llamar a la API
        try {
            const formData = new FormData();
            formData.append('message', text);

            const response = await fetch('<?= site_url("api/chat") ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            // 4. Quitar loading y mostrar respuesta
            hideTyping();
            if (data.reply) {
                addMessage(data.reply, 'ai bounce-in');
            } else {
                addMessage('Lo siento, algo salió mal.', 'ai');
            }
        } catch (err) {
            hideTyping();
            addMessage('Error de conexión con la IA.', 'ai');
        }
        
        scrollToBottom();
    }

    function showTyping(status) {
        statusText.innerText = status;
        typingIndicator.style.display = 'flex';
    }

    function hideTyping() {
        typingIndicator.style.display = 'none';
    }

    function addMessage(text, type) {
        const div = document.createElement('div');
        div.className = `msg ${type}-msg`;
        div.innerText = text;
        chatMessages.appendChild(div);
        return div;
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    sendBtn.onclick = handleSend;
    chatInput.onkeypress = (e) => { if(e.key === 'Enter') handleSend(); };
});
</script>
