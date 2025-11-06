document.addEventListener('DOMContentLoaded', () => {
    // Referência ao serviço WebSocket global
    const ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ AppWebSocket não encontrado. Verifique se webSocketService.js está carregado.');
        return;
    }

    // ======= VARIÁVEIS INJETADAS PELO BLADE =======
    const { userId, userLogin, salaId, wsUrl } = window.CHAT_CONFIG || {};
    if (!wsUrl || !salaId) {
        console.error('❌ CHAT_CONFIG inválido — wsUrl ou salaId ausente.');
        return;
    }

    // ======= ELEMENTOS =======
    const messages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    if (!messages || !chatInput || !chatSend) {
        console.warn('⚠️ Elementos de chat não encontrados no DOM.');
        return;
    }

    // ======= CONFIG =======
    const userName = userLogin || 'Desconhecido';
    const channel = salaId.toString();

    // ======= FUNÇÃO DE ADIÇÃO DE MENSAGEM =======
    function addMessage(text, sender = 'Sistema') {
        const div = document.createElement('div');
        const isSelf = sender === userName;

        div.className = `d-flex flex-column mb-2 ${isSelf ? 'align-items-end' : 'align-items-start'}`;
        div.innerHTML = `
            <div class="p-2 rounded ${isSelf ? 'bg-primary text-white' : 'bg-secondary text-light'}">
                <small class="d-block fw-bold opacity-75">${sender}</small>
                <span>${text}</span>
            </div>
        `;

        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    // ======= PROCESSAR MENSAGEM =======
    function processMessage(data) {
        if (!data) return;

        // Processa mensagens específicas do chat
        if (!data.tipo || data.tipo === 'chat') {
            if (data.conteudo) addMessage(data.conteudo, data.autor || 'Sistema');
            return;
        }

        switch (data.tipo) {
            case 'sistema':
                addMessage(data.conteudo, '🤖 Sistema');
                break;
            case 'entrada':
                addMessage(`🟢 ${data.autor} entrou na sala`, '🤖 Sistema');
                break;
            case 'saida':
                addMessage(`🔴 ${data.autor} saiu da sala`, '🤖 Sistema');
                break;
            case 'erro':
                addMessage(`⚠️ ${data.conteudo}`, '❌ Sistema');
                break;
            default:
                // outros tipos são tratados por room-manager
                break;
        }
    }

    // ======= CONECTAR AO WEBSOCKET =======
    function connectChat() {
        addMessage(`🟢 Conectando ao chat como "${userName}"...`);

        // Registra handlers de eventos
        document.addEventListener('stomp.connected', () => {
            addMessage('✅ Conectado ao servidor!', 'Sistema');

            const entradaMsg = {
                tipo: 'entrada',
                conteudo: `${userName} entrou na sala "${channel}"`,
                autor: userName,
                userId,
                salaId
            };
            ws.send('/app/enviar/' + channel, entradaMsg);

            // Inscreve no canal da sala com callback de processamento
            ws.subscribe(channel, processMessage);

            console.log('📡 Chat conectado e inscrito no canal', channel);
        });

        document.addEventListener('stomp.error', (event) => {
            console.error('❌ Erro ao conectar:', event.detail?.error);
            addMessage('⚠️ Falha ao conectar ao chat.', 'Sistema');
        });

        document.addEventListener('stomp.disconnected', () => {
            addMessage('🔴 Desconectado do chat.', 'Sistema');
        });

        // Inicia conexão
        if (!ws.getStatus().isConnected) {
            ws.connect(wsUrl, channel, processMessage);
        } else {
            // Se já estiver conectado, apenas inscreve no canal
            ws.subscribe(channel, processMessage);
        }
    }

    // ======= FUNÇÃO DE ENVIO DE MENSAGEM =======
    function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg) return;

        const status = ws.getStatus();
        if (!status.isConnected) {
            console.warn('⚠️ Chat não conectado, mensagem não enviada.');
            addMessage('⚠️ Chat não conectado. Tentando reconectar...', 'Sistema');
            connectChat();
            return;
        }

        const payload = {
            tipo: 'chat',
            conteudo: msg,
            autor: userName,
            userId,
            salaId
        };

        ws.send('/app/enviar/' + channel, payload);
        chatInput.value = '';
        chatInput.focus();
    }

    // ======= EVENTOS =======
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // ======= INICIAR CHAT =======
    connectChat();
});
