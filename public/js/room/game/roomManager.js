(function () {
    // ====== CONFIG / STATE ======
    const ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ AppWebSocket não encontrado. Verifique se webSocketService.js está carregado.');
        return;
    }

    // Configuração da sala
    const CHAT = window.CHAT_CONFIG || {};
    const userId = String(CHAT.userId ?? '');
    const userLogin = CHAT.userLogin ?? 'PLAYER';
    const salaId = CHAT.salaId ?? null;
    window.isMestre = !!(CHAT.isMestre || CHAT.role?.toUpperCase() === 'MESTRE');
    const wsUrl = CHAT.wsUrl;
    const channel = String(salaId);
    const backChannel = String("backchannel/" + salaId);
    let connectNotified = false;

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    // ========= ONLINE USERS MANAGEMENT ==========
    function notifyPresence(acao) {
        if (!salaId) return;

        const status = ws.getStatus();
        if (!status.isConnected) {
            debugLog(`⚠️ Não foi possível enviar ${acao}: sem conexão ativa`);
            return;
        }

        ws.send('/app/backchannel/presenca', {
            acao,
            usuarioId: userId,
            salaId
        });
        debugLog(`📤 Presença enviada: ${acao}`);
    }

    // ====== WEBSOCKET INTEGRATION ======
    function setupWebSocket() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Registra handlers para eventos do WebSocket
        document.addEventListener('stomp.connected', () => {
            ws.subscribe(channel, onReceiveAction);
            ws.subscribe(backChannel, onReceiveAction);

            if (!connectNotified) {
                notifyPresence('PlayerEnter');
                connectNotified = true;
            }
        });

        // Eventos de erro e desconexão
        document.addEventListener('stomp.error', (event) => {
            debugLog('❌ Erro de conexão:', event.detail?.error);
        });

        // Evento de desconexão
        document.addEventListener('stomp.disconnected', () => {
            debugLog('🔴 WebSocket desconectado');
            connectNotified = false;
        });

        window.addEventListener("beforeunload", () => {
            notifyPresence('PlayerExit');
            ws.disconnect();
        });

        const status = ws.getStatus();
        if (status.isConnected) {
            ws.subscribe(channel, onReceiveAction);
            ws.subscribe(backChannel, onReceiveAction);

            if (!connectNotified) {
                notifyPresence('PlayerEnter');
                connectNotified = true;
            }
        }
    }

    //receber dados/ação
    function onReceiveAction(data) {
        if (!data) return;
        // Log claro e único para depuração de payloads
        debugLog('📥 Ação recebida:', data);

        switch (data.acao) {

            case 'sistema':
                debugLog('📝 Sistema:', data.conteudo);
                break;

            case 'listaUsers':
                AtualizarListaOnline(data.salaId, data.conteudo);
                break;

            case 'round':

                debugLog('🎲 Rodada iniciada');

                debugLog('É o mestre?', isMestre);

                // Marcar que a rodada iniciou
                window.turnState.rodadaIniciada = true;
                // Definir de quem é o turno atual
                window.turnState.turnoAtual = data.usuarioId;

                let turnoEhMeu = false;

                if (data.usuarioId === "mestre") {

                    // Se o backend disse que é turno do mestre,
                    // então só é meu turno se eu for o mestre
                    turnoEhMeu = isMestre;

                } else {

                    // Se não é mestre, então é ID numérico de player
                    turnoEhMeu = String(data.usuarioId) === String(userId);

                }

                debugLog('🎲 Turno eh meu?', turnoEhMeu);

                atualizarInterfaceTurno(turnoEhMeu);

                break;

            case 'rodadaEncerrada':
                debugLog(' 🛑 Rodada encerrada');
                break;

            case 'atualizacaoVida':
                debugLog('❤️ Atualização de vida recebida:', data);
                if (typeof window.atualizarVidaPersonagemCard === 'function') {
                    window.atualizarVidaPersonagemCard(data.personagemId, data.novaVida);
                }
                break;

            default:
                console.warn("Evento desconhecido:", data);
        }

    }

    // ========== INIT ==========
    function init() {
        debugLog('🎲 room-manager v4.0 iniciando...');

        // Configura WebSocket
        setupWebSocket();
    }

    // Inicia quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
