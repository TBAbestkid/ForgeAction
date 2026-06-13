document.addEventListener('DOMContentLoaded', () => {
    const ws = window.AppWebSocket;
    if (!ws) {
        console.error('AppWebSocket nao encontrado. Verifique se webSocketService.js esta carregado.');
        return;
    }

    const { userId, userLogin, salaId, wsUrl, nomePersonagem } = window.CHAT_CONFIG || {};
    if (!wsUrl || !salaId) {
        console.error('CHAT_CONFIG invalido: wsUrl ou salaId ausente.');
        return;
    }

    const messagesDesktop = document.getElementById('chat-messages');
    const systemLogs = document.getElementById('system-logs');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const userName = nomePersonagem || userLogin || 'Desconhecido';
    const channel = salaId.toString();

    function scrollToBottom(container) {
        if (!container) return;
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 0);
    }

    function getNomeExibicao(data) {
        return data.nomePersonagem || data.nomeJogador || data.autor || 'Desconhecido';
    }

    function makeMessageDiv(text, sender, isSystemMessage) {
        const div = document.createElement('div');
        const isSelf = !isSystemMessage && sender === userName;
        div.className = `d-flex flex-column mb-2 ${isSelf ? 'align-items-end' : 'align-items-start'}`;

        const bubble = document.createElement('div');
        bubble.className = `p-2 rounded ${isSelf ? 'bg-warning text-white' : isSystemMessage ? 'bg-info text-dark' : 'bg-primary text-light'}`;
        bubble.style.maxWidth = '70%';

        const senderEl = document.createElement('small');
        senderEl.className = 'd-block fw-bold opacity-75';
        senderEl.textContent = sender;
        senderEl.style.maxWidth = '100%';
        senderEl.style.overflow = 'hidden';
        senderEl.style.textOverflow = 'ellipsis';
        senderEl.style.whiteSpace = 'nowrap';
        senderEl.title = sender;

        const textEl = document.createElement('span');
        textEl.style.wordWrap = 'break-word';
        textEl.style.overflowWrap = 'anywhere';
        textEl.textContent = text ?? '';

        bubble.appendChild(senderEl);
        bubble.appendChild(textEl);
        div.appendChild(bubble);

        return div;
    }

    function addMessage(text, sender = 'Sistema', isSystemMessage = false, container = null) {
        const targetContainer = container || (isSystemMessage ? systemLogs : messagesDesktop);
        if (!targetContainer) return;

        targetContainer.appendChild(makeMessageDiv(text, sender, isSystemMessage));
        scrollToBottom(targetContainer);
    }

    function playLifeSound(data) {
        const isDano = data.tipo === 'dano' || Number(data.novaVida) < Number(data.vidaAnterior);
        window.audioManager?.play(isDano ? 'dano' : 'vida');
    }

    function processMessage(data) {
        if (!data) return;

        const nomeExibicao = getNomeExibicao(data);

        if (!data.acao || data.acao === 'chat') {
            if (data.conteudo) {
                addMessage(data.conteudo, nomeExibicao, false, messagesDesktop);
            }
            return;
        }

        switch (data.acao) {
            case 'sistema':
                addMessage(data.conteudo, 'Sistema', true, systemLogs);
                break;

            case 'playerEnter':
                addMessage(`${nomeExibicao} entrou na sala`, 'Sistema', true, systemLogs);
                break;

            case 'playerExit':
                addMessage(`${nomeExibicao} saiu da sala`, 'Sistema', true, systemLogs);
                break;

            case 'erro':
                addMessage(data.conteudo, 'Sistema', true, systemLogs);
                break;

            case 'lancarDados': {
                const { faces, valor, oculto, usuarioId } = data;
                const nomeJogadorDados = data.nomeJogador || 'Jogador ' + usuarioId;

                if (oculto) {
                    if (window.isMestre) {
                        window.funcaoChamarDados?.(faces, valor);
                        addMessage(`${nomeJogadorDados} rolou D${faces} (resultado: ${valor}) - OCULTO`, 'Sistema', true, systemLogs);
                    } else {
                        addMessage('O mestre rolou um dado em segredo...', 'Sistema', true, systemLogs);
                    }
                } else {
                    window.funcaoChamarDados?.(faces, valor);
                    addMessage(`${nomeJogadorDados} rolou D${faces}: ${valor}`, 'Sistema', true, systemLogs);
                }
                break;
            }

            case 'atualizacaoVida':
                window.atualizarVidaPersonagemCard?.(data.personagemId, data.novaVida);
                playLifeSound(data);
                addMessage(data.conteudo || 'Vida atualizada.', 'Sistema', true, systemLogs);
                break;

            case 'uparPersonagem':
                window.audioManager?.play('up');
                addMessage(data.conteudo || 'Personagem recebeu upgrade.', 'Sistema', true, systemLogs);
                break;

            case 'abrirUpgradePersonagem':
                if (data.usuarioAlvo == window.CHAT_CONFIG?.userId && typeof window.abrirUpgradePersonagem === 'function') {
                    window.abrirUpgradePersonagem(data.dadosUpgrade);
                }
                break;

            case 'upgradeCompleto':
            case 'upgradeCompletado':
                addMessage(data.conteudo || `${data.nomeJogador || 'Jogador'} completou o upgrade! Nivel: ${data.novoLevel}`, 'Sistema', true, systemLogs);
                break;

            default:
                console.warn('Acao desconhecida recebida:', data);
        }
    }

    let isFirstConnect = true;

    function connectChat() {
        if (isFirstConnect) addMessage('Conectando ao chat...', 'Sistema', false, messagesDesktop);

        document.addEventListener('stomp.connected', () => {
            if (isFirstConnect) {
                addMessage('Conectado!', 'Sistema', false, messagesDesktop);
                isFirstConnect = false;
            }
            ws.subscribe(channel, (msg) => {
                try {
                    processMessage(typeof msg === 'string' ? JSON.parse(msg) : msg);
                } catch (err) {
                    processMessage(msg);
                }
            });
        }, { once: true });

        document.addEventListener('stomp.error', () => {
            addMessage('Erro ao conectar no chat!', 'Sistema', true, messagesDesktop);
        }, { once: true });

        document.addEventListener('stomp.disconnected', () => {
            addMessage('Desconectado do chat', 'Sistema', false, messagesDesktop);
        }, { once: true });

        if (!ws.getStatus().isConnected) {
            ws.connect(wsUrl, null, null, { usuarioId: userId, salaId });
        } else {
            ws.subscribe(channel, processMessage);
        }
    }

    function sendMessageFrom(inputEl) {
        if (!inputEl) return;
        const msg = inputEl.value.trim();
        if (!msg) return;

        if (!ws.getStatus().isConnected) {
            addMessage('Chat nao conectado. Tentando reconectar...', 'Sistema', true, messagesDesktop);
            connectChat();
            return;
        }

        ws.send('/app/enviar/' + channel, {
            acao: 'chat',
            conteudo: msg,
            autor: userName,
            nomePersonagem: userName,
            userId,
            salaId,
        });

        inputEl.value = '';
        inputEl.focus();
    }

    if (chatSend) chatSend.addEventListener('click', () => sendMessageFrom(chatInput));
    if (chatInput) chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessageFrom(chatInput);
    });

    connectChat();
});
