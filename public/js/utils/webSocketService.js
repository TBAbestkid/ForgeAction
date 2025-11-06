// webSocketService.js
// Serviço global unificado para WebSocket via SockJS + STOMP

window.AppWebSocket = (() => {
    // ======== VARIÁVEIS INTERNAS ========
    let stompClient = null;         // Cliente STOMP atual
    let isConnected = false;        // Status da conexão
    let reconnectTimer = null;      // Timer para reconexão
    let subscriptions = new Map();  // Mapa de inscrições ativas
    let connectionConfig = null;    // Configuração atual da conexão

    // Configurações padrão
    const DEFAULT_CONFIG = {
        reconnectDelay: 3000,       // Delay para reconexão (ms)
        debug: true                 // Modo debug
    };

    // ======== UTILITÁRIOS INTERNOS ========
    function debugLog(...args) {
        if (DEFAULT_CONFIG.debug) {
            console.log('[WS]', ...args);
        }
    }

    function clearReconnectTimer() {
        if (reconnectTimer) {
            clearTimeout(reconnectTimer);
            reconnectTimer = null;
        }
    }

    // ======== GERENCIAMENTO DE EVENTOS ========
    function dispatchGlobalEvent(eventName, detail) {
        document.dispatchEvent(new CustomEvent(eventName, {
            bubbles: true,
            detail
        }));
        debugLog(`📡 Evento disparado: ${eventName}`, detail);
    }

    // ======== GERENCIAMENTO DE SUBSCRIÇÕES ========
    function subscribe(channel, callback) {
        if (!stompClient?.connected) {
            debugLog('❌ Não é possível se inscrever: cliente não conectado');
            return null;
        }

        try {
            const topic = '/topic/' + channel;
            debugLog(`📡 Inscrevendo no canal: ${topic}`);

            const subscription = stompClient.subscribe(topic, (message) => {
                try {
                    const data = JSON.parse(message.body);
                    // Dispara evento global para qualquer módulo interessado
                    dispatchGlobalEvent('ws.message', data);
                    // Executa callback específico se fornecido
                    if (callback) callback(data);
                } catch (e) {
                    console.error('❌ Erro ao processar mensagem:', e);
                }
            });

            // Armazena a subscrição para recriar em reconexões
            subscriptions.set(channel, { topic, callback });
            debugLog(`✅ Inscrito com sucesso no canal: ${topic}`);

            return subscription;
        } catch (e) {
            console.error('❌ Erro ao se inscrever:', e);
            return null;
        }
    }

    function resubscribeAll() {
        debugLog('🔄 Reinscrevendo em todos os canais...');
        subscriptions.forEach(({ topic, callback }, channel) => {
            subscribe(channel, callback);
        });
    }

    // ======== CONEXÃO PRINCIPAL ========
    function connect(wsUrl, channel, onMessage) {
        // Evita conexões duplicadas
        if (isConnected) {
            debugLog('ℹ️ Já conectado, ignorando nova tentativa');
            return;
        }

        debugLog('� Iniciando conexão:', wsUrl);

        // Armazena configuração para reconexões
        connectionConfig = { wsUrl, channel, onMessage };

        // Cria conexão SockJS
        const socket = new SockJS(wsUrl);
        stompClient = Stomp.over(socket);

        // Desativa logs do STOMP
        stompClient.debug = DEFAULT_CONFIG.debug ? console.log : null;

        stompClient.connect({},
            // Sucesso
            () => {
                debugLog('✅ Conectado com sucesso!');
                isConnected = true;
                clearReconnectTimer();

                // Inscreve no canal principal
                if (channel) {
                    subscribe(channel, onMessage);
                }

                // Reinscreve em todos os canais anteriores
                resubscribeAll();

                // Notifica todos os módulos
                dispatchGlobalEvent('stomp.connected', {
                    stompClient,
                    isReconnect: reconnectTimer !== null
                });
            },
            // Erro
            (error) => {
                console.error('❌ Erro de conexão:', error);
                isConnected = false;

                // Agenda reconexão
                clearReconnectTimer();
                reconnectTimer = setTimeout(() => {
                    debugLog('🔄 Tentando reconectar...');
                    connect(connectionConfig.wsUrl,
                           connectionConfig.channel,
                           connectionConfig.onMessage);
                }, DEFAULT_CONFIG.reconnectDelay);

                // Notifica erro
                dispatchGlobalEvent('stomp.error', { error });
            }
        );
    }

    // ======== ENVIO DE MENSAGENS ========
    function send(destination, payload) {
        if (!isConnected || !stompClient?.connected) {
            console.warn('⚠️ Não é possível enviar: sem conexão ativa');
            return false;
        }

        try {
            stompClient.send(destination, {}, JSON.stringify(payload));
            debugLog('📤 Mensagem enviada:', destination, payload);
            return true;
        } catch (e) {
            console.error('❌ Erro ao enviar mensagem:', e);
            return false;
        }
    }

    // ======== DESCONEXÃO ========
    function disconnect() {
        clearReconnectTimer();

        if (stompClient?.connected) {
            stompClient.disconnect(() => {
                debugLog('� Desconectado do servidor');
                isConnected = false;
                dispatchGlobalEvent('stomp.disconnected', {});
            });
        }

        // Limpa estado
        stompClient = null;
        isConnected = false;
        subscriptions.clear();
        connectionConfig = null;
    }

    // ======== INTERFACE PÚBLICA ========
    return {
        connect,
        disconnect,
        send,
        subscribe,
        getStatus: () => ({
            isConnected,
            subscriptions: Array.from(subscriptions.keys()),
            hasReconnectPending: reconnectTimer !== null
        })
    };
})();
