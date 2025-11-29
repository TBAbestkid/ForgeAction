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
    const isMestre = !!(CHAT.isMestre || CHAT.role?.toUpperCase() === 'MESTRE');
    const wsUrl = CHAT.wsUrl;
    const channel = String(salaId);
    const backChannel = String("backchannel/" + salaId);

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    // ====== WEBSOCKET INTEGRATION ======
    function setupWebSocket() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Registra handlers para eventos do WebSocket
        document.addEventListener('stomp.connected', () => {
            ws.subscribe(channel, onReceiveAction);
            ws.subscribe(backChannel, onReceiveAction);
        });

        // Eventos de erro e desconexão
        document.addEventListener('stomp.error', (event) => {
            debugLog('❌ Erro de conexão:', event.detail?.error);
        });

        // Evento de desconexão
        document.addEventListener('stomp.disconnected', () => {
            debugLog('🔴 WebSocket desconectado');
            enviarSaida();
        });

        window.addEventListener("beforeunload", () => {
            enviarSaida();
            ws.disconnect();
        });

        const status = ws.getStatus();
        if (status.isConnected) {
            ws.subscribe(channel, onReceiveAction);
            ws.subscribe(backChannel, onReceiveAction);
        }
    }

    // ===== MESSAGE SENDING ======
    function enviarSistema(msg) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        // Envia mensagem de sistema
        ws.send('/app/enviar/' + salaId, {
            acao: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
            usuarioId: userId,
            salaId: salaId
        });
    }

    // Envia ação genérica
    function enviarAcao(obj) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        // Verifica conexão
        const status = ws.getStatus();
        if (!status.isConnected) {
            debugLog('⚠️ WebSocket não conectado, aguardando conexão...');
            // Usa { once: true } para evitar múltiplos listeners
            document.addEventListener('stomp.connected', () => {
                ws.send('/app/enviar/' + salaId, {
                    acao: 'acao',
                    salaId,
                    timestamp: Date.now(),
                    ...obj
                });
            }, { once: true });
            return;
        }

        // Envia ação
        ws.send('/app/enviar/' + salaId, {
            acao: 'acao',
            salaId,
            timestamp: Date.now(),
            ...obj
        });
    }

    // sair da sala
    function enviarSaida() {
        if (!salaId) return;
    }

    //receber dados/ação
    function onReceiveAction(data) {
        if (!data) return;
        // Log claro e único para depuração de payloads
        console.log('[RM:onReceiveAction] payload recebido =>', data);
        debugLog('📥 Ação recebida:', data);

        switch (data.acao) {

            case 'sistema':
                console.log(data.conteudo);
                break;
            
            case 'listaUsers':
                AtualizarListaOnline(data.salaId, data.conteudo);
                break;
            case 'iniciativa':
                debugLog('Iniciativa recebida:', data.conteudo);
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
