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
        });

        window.addEventListener("beforeunload", () => {
            ws.disconnect();
        });

        const status = ws.getStatus();
        if (status.isConnected) {
            ws.subscribe(channel, onReceiveAction);
            ws.subscribe(backChannel, onReceiveAction);
        }
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
