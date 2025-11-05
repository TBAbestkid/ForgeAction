document.addEventListener('DOMContentLoaded', () => {
    // ======= VARIÁVEIS INJETADAS PELO BLADE =======
    const { userId, userLogin, salaId, wsUrl } = window.CHAT_CONFIG;

    // ======= ELEMENTOS =======
    const messages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    // ======= CONFIG =======
    let userName = userLogin || 'Desconhecido';
    let channel = `${salaId}`;

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
        if (!msg || !window.chatStomp.getConnectionStatus()) return;

        const payload = {
            tipo: 'chat',
            conteudo: msg,
            autor: userName,
            userId,
            salaId
        };

        WebSocketService.send('/app/enviar/' + channel, payload);
        chatInput.value = '';
        chatInput.focus();
    }

    // ======= PROCESSAR MENSAGEM =======
    function processMessage(data) {
        if (!data) return;

        // Primeiro dispara evento para outros módulos poderem reagir
        // Essa linha cria e dispara um evento personalizado (CustomEvent)
        // chamado 'ws.message' dentro do document, ou seja:
        // "Ei, todo mundo que estiver ouvindo o evento ws.message, aqui vai uma nova mensagem WebSocket!"
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

        WebSocketService.connect(
            wsUrl,
            channel,
            processMessage, // callback para mensagens recebidas
            () => {
                // Quando conectar:
                // addMessage('✅ Conectado ao servidor!', 'Sistema');

                const entradaMsg = {
                    tipo: 'entrada',
                    conteudo: `${userName} entrou na sala "${channel}"`,
                    autor: userName,
                    userId,
                    salaId
                };
                WebSocketService.send('/app/enviar/' + channel, entradaMsg);

                // Cria uma variável global chamada chatStomp.
                // Outros scripts na página podem acessar essa variável para enviar ou
                // receber mensagens do WebSocket.
                // Serve pra compartilhar o serviço entre diferentes scripts,
                // sem precisar importar nada.
                window.chatStomp = WebSocketService;

                // Dispara um evento personalizado chamado 'stomp.connected'.
                // É um sinal de que o WebSocket já está realmente conectado e pronto para uso.
                // Outros scripts podem “escutar” esse evento e só então começar a interagir com o
                // WebSocket, garantindo que não vão tentar usar antes da hora.
                document.dispatchEvent(new CustomEvent('stomp.connected', { bubbles: true }));
            },
            (error) => {
                console.error('❌ Erro ao conectar:', error);
                addMessage('⚠️ Falha ao conectar ao chat.', 'Sistema');
            }
        );
    }

    // ======= EVENTOS =======
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // ======= INICIAR CHAT =======
    connectChat();
});
