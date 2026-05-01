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

    // ======= ELEMENTOS DESKTOP =======
    const messagesDesktop = document.getElementById('chat-messages');
    const systemLogs = document.getElementById('system-logs');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    // Validação
    if (!messagesDesktop) console.warn('⚠️ Chat messages container não encontrado.');
    if (!systemLogs) console.warn('⚠️ System logs container não encontrado.');

    const userName = userLogin || 'Desconhecido';
    const channel = salaId.toString();

    function scrollToBottom(container) {
        if (container) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 0);
        }
    }

    function getNomeExibicao(data) {
        // Usar o nome que vem na mensagem (já foi processado corretamente no servidor/cliente que enviou)
        return data.nomePersonagem || data.nomeJogador || data.autor || 'Desconhecido';
    }

    function makeMessageDiv(text, sender, isSystemMessage) {
        const div = document.createElement('div');
        const isSelf = !isSystemMessage && sender === userName;
        div.className = `d-flex flex-column mb-2 ${isSelf ? 'align-items-end' : 'align-items-start'}`;
        div.innerHTML = `
            <div class="p-2 rounded ${isSelf ? 'bg-warning text-white' : isSystemMessage ? 'bg-info text-dark' : 'bg-primary text-light'}" style="max-width: 70%;">
                <small class="d-block fw-bold opacity-75">${sender}</small>
                <span style="word-wrap: break-word;">${text}</span>
            </div>
        `;
        return div;
    }

    function addMessage(text, sender = 'Sistema', isSystemMessage = false, container = null) {
        let targetContainer = container;

        if (!targetContainer) {
            // Se não especificar container, decidir automaticamente
            targetContainer = isSystemMessage ? systemLogs : messagesDesktop;
        }

        if (!targetContainer) return;

        const el = makeMessageDiv(text, sender, isSystemMessage);
        targetContainer.appendChild(el);
        scrollToBottom(targetContainer);
    }

    function processMessage(data) {
        if (!data) return;

        const nomeExibicao = getNomeExibicao(data);

        // Chat normal (usuário digitou mensagem)
        if (!data.acao || data.acao === 'chat') {
            if (data.conteudo) {
                addMessage(data.conteudo, nomeExibicao, false, messagesDesktop);
            }
            return;
        }

        // Ações de sistema
        switch (data.acao) {
            case 'sistema':
                addMessage(data.conteudo, '🤖 Sistema', true, systemLogs);
                break;

            case 'playerEnter':
                addMessage(`🟢 ${nomeExibicao} entrou na sala`, '🤖 Sistema', true, systemLogs);
                break;

            case 'playerExit':
                addMessage(`🔴 ${nomeExibicao} saiu da sala`, '🤖 Sistema', true, systemLogs);
                break;

            case 'erro':
                addMessage(`⚠️ ${data.conteudo}`, '❌ Sistema', true, systemLogs);
                break;

            case 'lancarDados':
                console.log("🎲 Dados recebidos:", data);
                const { faces, valor, oculto, usuarioId } = data;
                const nomeJogadorDados = data.nomeJogador || 'Jogador ' + usuarioId;

                if (oculto) {
                    if (window.isMestre) {
                        // Mestre vê os dados rolados
                        window.funcaoChamarDados(faces, valor);
                        addMessage(`🎲 ${nomeJogadorDados} rolou D${faces} (resultado: ${valor}) - OCULTO`, "🤖 Sistema", true, systemLogs);
                    } else {
                        // Jogadores recebem aviso
                        addMessage("🎲 O mestre rolou um dado em segredo...", "🤖 Sistema", true, systemLogs);
                    }
                } else {
                    // Rolagem normal - TODOS VEEM
                    window.funcaoChamarDados(faces, valor);
                    addMessage(`🎲 ${nomeJogadorDados} rolou D${faces}: <strong>${valor}</strong>`, "🤖 Sistema", true, systemLogs);
                }
                break;

            case 'atualizacaoVida':
                console.log('❤️ Atualização de vida recebida:', data);
                if (typeof window.atualizarVidaPersonagemCard === 'function') {
                    window.atualizarVidaPersonagemCard(data.personagemId, data.novaVida);
                }
                addMessage(data.conteudo, '🤖 Sistema', true, systemLogs);
                break;

            case 'uparPersonagem':
                addMessage(data.conteudo, '🤖 Sistema', true, systemLogs);
                break;

            case 'abrirUpgradePersonagem':
                // Verifica se a ação é para este usuário
                if (data.usuarioAlvo == window.CHAT_CONFIG?.userId) {
                    console.log('⭐ Abrindo offcanvas de upgrade:', data);
                    if (typeof window.abrirUpgradePersonagem === 'function') {
                        window.abrirUpgradePersonagem(data.dadosUpgrade);
                    }
                } else {
                    console.log('⏭️ Upgrade não é para este usuário');
                }
                break;

            case 'upgradeCompleto':
            case 'upgradeCompletado':
                addMessage(data.conteudo || `✅ ${data.nomeJogador || 'Jogador'} completou o upgrade! Nível: ${data.novoLevel}`, '⭐ Sistema', true, systemLogs);
                break;

            default:
                console.warn('⚠️ Ação desconhecida recebida:', data);
        }
    }

    let isFirstConnect = true;

    function connectChat() {
        if (isFirstConnect) addMessage(`🟢 Conectando ao chat...`, 'Sistema', false, messagesDesktop);

        document.addEventListener('stomp.connected', () => {
            if (isFirstConnect) {
                addMessage(`✅ Conectado!`, 'Sistema', false, messagesDesktop);
                isFirstConnect = false;
            }
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

    function onStompError(event) {
        console.error('❌ Erro de conexão:', event.detail?.error);
        addMessage('❌ Erro ao conectar no chat!', 'Sistema', true, messagesDesktop);
    }

    function onStompDisconnected() {
        console.log('🔴 Chat desconectado');
        addMessage('🔴 Desconectado do chat', 'Sistema', false, messagesDesktop);
    }

    function sendMessageFrom(inputEl) {
        if (!inputEl) return;
        const msg = inputEl.value.trim();
        if (!msg) return;

        const status = ws.getStatus();
        if (!status.isConnected) {
            addMessage('⚠️ Chat não conectado. Tentando reconectar...', 'Sistema', true, messagesDesktop);
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

    // Eventos dos botões
    if (chatSend) chatSend.addEventListener('click', () => sendMessageFrom(chatInput));
    if (chatInput) chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessageFrom(chatInput); });

    connectChat();
});
