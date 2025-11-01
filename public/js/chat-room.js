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

    // ======= CONECTAR AO WEBSOCKET =======
    function connectChat() {
        addMessage(`🟢 Entrou no canal "${channel}" como "${userName}"`);

        const socket = new SockJS(wsUrl);
        stompClient = Stomp.over(socket);

        stompClient.connect({}, () => {
            // Inscreve no canal
            stompClient.subscribe('/topic/' + channel, (message) => {
                const data = JSON.parse(message.body);
                addMessage(data.conteudo, data.autor);
            });

            // Envia mensagem automática de entrada
            const entradaMsg = {
                conteudo: `${userName} entrou na sala "${channel}"`,
                autor: 'Sistema',
                userId: userId,
                salaId: salaId
            };
            stompClient.send('/app/enviar/' + channel, {}, JSON.stringify(entradaMsg));

        }, (error) => {
            console.error('Erro ao conectar ao WebSocket:', error);
            addMessage('⚠️ Falha ao conectar ao chat.');
        });
    }

    // ======= ENVIAR MENSAGEM =======
    function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg || !stompClient) return;

        const payload = {
            conteudo: msg,
            autor: userName,
            userId: userId,
            salaId: salaId
        };

        stompClient.send('/app/enviar/' + channel, {}, JSON.stringify(payload));
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
