document.addEventListener('DOMContentLoaded', () => {
    // ======= VARIÁVEIS INJETADAS PELO BLADE =======
    const { userId, userLogin, salaId, wsUrl } = window.CHAT_CONFIG;

    // ======= ELEMENTOS =======
    const messages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    let userName = userLogin || 'Desconhecido';
    let channel = `${salaId}`;
    let stompClient = null;

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

    // ======= FUNÇÃO DE ENVIO DE MENSAGEM =======
    function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg || !stompClient) return;

        const payload = {
            tipo: 'chat',
            conteudo: msg,
            autor: userName,
            userId: userId,
            salaId: salaId
        };

        stompClient.send('/app/enviar/' + channel, {}, JSON.stringify(payload));
        chatInput.value = '';
        chatInput.focus();
    }

    // ======= PROCESSAR MENSAGEM =======
    function processMessage(data) {
        if (!data) return;

        // Primeiro dispara evento para outros módulos poderem reagir
        document.dispatchEvent(new CustomEvent('ws.message', {
            detail: data,
            bubbles: true
        }));

        // Depois processa mensagens relevantes para o chat
        switch (data.tipo) {
            case 'chat':
            case undefined:
                if (data.conteudo) {
                    addMessage(data.conteudo, data.autor || 'Sistema');
                }
                break;
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
                // Ignora outros tipos (ex: 'acao') que serão tratados pelo room-manager
                break;
        }
    }

    // ======= CONECTAR AO WEBSOCKET =======
    function connectChat() {
        addMessage(`🟢 Conectando ao chat como "${userName}"...`);

        const socket = new SockJS(wsUrl);
        stompClient = Stomp.over(socket);

        stompClient.connect({}, () => {
            // Inscreve no canal
            stompClient.subscribe('/topic/' + channel, (message) => {
                try {
                    const data = JSON.parse(message.body);
                    processMessage(data);
                } catch (e) {
                    console.error('Erro ao processar mensagem:', e);
                    addMessage('⚠️ Erro ao processar mensagem recebida', 'Sistema');
                }
            });

            // Envia mensagem automática de entrada
            const entradaMsg = {
                tipo: 'entrada',
                conteudo: `${userName} entrou na sala "${channel}"`,
                autor: userName,
                userId: userId,
                salaId: salaId
            };
            stompClient.send('/app/enviar/' + channel, {}, JSON.stringify(entradaMsg));

            // Expor stompClient globalmente para outros módulos
            try {
                window.chatStomp = stompClient;
                // Notifica outros listeners que o stomp está pronto
                document.dispatchEvent(new CustomEvent('stomp.connected', {
                    detail: { stompClient },
                    bubbles: true
                }));
            } catch (e) {
                console.warn('Não foi possível expor window.chatStomp', e);
                addMessage('⚠️ Aviso: Modo offline para outros recursos', 'Sistema');
            }

        }, (error) => {
            console.error('Erro ao conectar ao WebSocket:', error);
            addMessage('⚠️ Falha ao conectar ao chat.', 'Sistema');
        });
    }

    // ======= EVENTOS =======
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // ======= INICIAR CHAT =======
    connectChat();
});
