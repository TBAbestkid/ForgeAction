// E isso também foi?
document.addEventListener('DOMContentLoaded', () => {
    const ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ AppWebSocket não encontrado. Verifique se webSocketService.js está carregado.');
        return;
    }

    const { userId, userLogin, salaId, wsUrl } = window.CHAT_CONFIG || {};
    if (!wsUrl || !salaId) {
        console.error('❌ CHAT_CONFIG inválido — wsUrl ou salaId ausente.');
        return;
    }

    // ======= ELEMENTOS (desktop + mobile) =======
    const messagesDesktop = document.getElementById('chat-messages');
    const messagesMobile = document.getElementById('chat-messages-mobile');
    const systemLogs = document.getElementById('system-logs');
    const systemLogsMobile = document.getElementById('system-logs-mobile');

    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatInputMobile = document.getElementById('chat-input-mobile');
    const chatSendMobile = document.getElementById('chat-send-mobile');

    // Fallback warnings but continue (mobile or desktop might be missing)
    if (!messagesDesktop && !messagesMobile) console.warn('⚠️ Nenhum container de mensagens encontrado.');
    if (!systemLogs && !systemLogsMobile) console.warn('⚠️ Nenhum container de logs do sistema encontrado.');

    const userName = userLogin || 'Desconhecido';
    const channel = salaId.toString();

    function appendToContainers(containers, el) {
        containers.forEach(c => {
            if (!c) return;
            c.appendChild(el.cloneNode(true));
            c.scrollTop = c.scrollHeight;
        });
    }

    function makeMessageDiv(text, sender, isSystemMessage) {
        const div = document.createElement('div');
        const isSelf = !isSystemMessage && sender === userName;
        div.className = `d-flex flex-column mb-2 ${isSelf ? 'align-items-end' : 'align-items-start'}`;
        div.innerHTML = `
            <div class="p-2 rounded ${isSelf ? 'bg-primary text-white' : isSystemMessage ? 'bg-info text-dark' : 'bg-secondary text-light'}">
                <small class="d-block fw-bold opacity-75">${sender}</small>
                <span>${text}</span>
            </div>
        `;
        return div;
    }

    function addMessage(text, sender = 'Sistema', isSystemMessage = false) {
        // Em mobile, ambos chat e logs vão para o chat-mobile (não separa)
        // Em desktop, separa entre chat-messages e system-logs
        let containers = [];

        if (isSystemMessage) {
            // Mensagens de sistema vão para logs
            if (systemLogs) containers.push(systemLogs);
            if (systemLogsMobile) containers.push(systemLogsMobile);
        } else {
            // Mensagens de chat vão para chat
            if (messagesDesktop) containers.push(messagesDesktop);
            if (messagesMobile) containers.push(messagesMobile);
        }

        const el = makeMessageDiv(text, sender, isSystemMessage);
        appendToContainers(containers, el);
    }

    function processMessage(data) {
        if (!data) return;
        if (!data.acao || data.acao === 'chat') {
            if (data.conteudo) addMessage(data.conteudo, data.autor || 'Sistema', false);
            return;
        }

        switch (data.acao) {
            case 'sistema':
                addMessage(data.conteudo, '🤖 Sistema', true);
                break;
            case 'playerEnter':
                // ✅ Usa userLogin (enviado do roomManager)
                const nomeEntrada = 'jogador' + data.usuarioId;
                addMessage(`🟢 ${nomeEntrada} entrou na sala`, '🤖 Sistema', false);
                break;
            case 'playerExit':
                const nomeSaida = 'jogador' + data.usuarioId;
                addMessage(`🔴 ${nomeSaida} saiu da sala`, '🤖 Sistema', false);
                break;
            case 'erro':
                addMessage(`⚠️ ${data.conteudo}`, '❌ Sistema', true);
                break;

            case 'lancarDados':

                console.log("🔥 EU RECEBI? lancarDados", data);

                const { faces, valor, oculto } = data;


                if (oculto) {

                    if (window.isMestre) {
                        // Mestre vê normalmente
                        window.funcaoChamarDados(faces, valor);
                    } else {
                        // Jogadores só recebem aviso
                        console.log("🎲 Mestre rolou um dado secretamente...");

                        addMessage("🎲 O mestre rolou um dado em segredo...", "🤖 Sistema", true);
                    }

                } else {
                    // Rolagem normal
                    window.funcaoChamarDados(faces, valor);
                }

                break;


            case 'atualizacaoVida':
                console.log('❤️ Atualização de vida recebida:', data);
                if (typeof window.atualizarVidaPersonagemCard === 'function') {
                    window.atualizarVidaPersonagemCard(data.personagemId, data.novaVida);
                }
                break;

            case 'uparPersonagem':
                addMessage(`📈 ${data.conteudo}`, '🤖 Sistema', true);
                break;

            default:
                console.warn('⚠️ Ação desconhecida recebida:', data);
        }
    }

    let isFirstConnect = true;

    function connectChat() {
        if (isFirstConnect) addMessage(`🟢 Conectando ao chat...`, 'Sistema', false);

        document.addEventListener('stomp.connected', () => {
            if (isFirstConnect) isFirstConnect = false;
            ws.subscribe(channel, (msg) => {
                try {
                    const data = typeof msg === 'string' ? JSON.parse(msg) : msg;
                    processMessage(data);
                } catch (err) {
                    // some ws libs pass already-parsed objects
                    processMessage(msg);
                }
            });
        }, { once: true });

        document.addEventListener('stomp.error', onStompError, { once: true });
        document.addEventListener('stomp.disconnected', onStompDisconnected, { once: true });

        if (!ws.getStatus().isConnected) {
            ws.connect(wsUrl,  null,  null, {"usuarioId":  userId, "salaId":  salaId});
        } else {
            ws.subscribe(channel, processMessage);
        }
    }

    function onStompError(event) { console.error('❌ Erro de conexão:', event.detail?.error); }
    function onStompDisconnected() { console.log('🔴 Chat desconectado'); }

    function sendMessageFrom(inputEl) {
        if (!inputEl) return;
        const msg = inputEl.value.trim();
        if (!msg) return;

        const status = ws.getStatus();
        if (!status.isConnected) {
            addMessage('⚠️ Chat não conectado. Tentando reconectar...', 'Sistema', true);
            connectChat();
            return;
        }

        const payload = {
            acao: 'chat',
            conteudo: msg,
            autor: userName,
            userId,
            salaId
        };

        ws.send('/app/enviar/' + channel, payload);
        inputEl.value = '';
        inputEl.focus();
    }

    // Eventos: liga os botões/inputs desktop e mobile (se existirem)
    if (chatSend) chatSend.addEventListener('click', () => sendMessageFrom(chatInput));
    if (chatInput) chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessageFrom(chatInput); });
    if (chatSendMobile) chatSendMobile.addEventListener('click', () => sendMessageFrom(chatInputMobile));
    if (chatInputMobile) chatInputMobile.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessageFrom(chatInputMobile); });

    connectChat();
});
