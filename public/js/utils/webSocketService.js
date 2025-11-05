// webSocketService.js
// Serviço simples para lidar com WebSocket via SockJS + STOMP

window.WebSocketService = (() => {
    // ======== VARIÁVEIS INTERNAS ========
    let stompClient = null; // Cliente STOMP
    let isConnected = false; // Indica se a conexão está ativa

    // ======== CONECTAR ========
    function connect(wsUrl, channel, onMessage, onConnect, onError) {
        console.log('🔌 Conectando ao servidor WebSocket:', wsUrl);

        // Cria a conexão SockJS
        const socket = new SockJS(wsUrl);

        // Usa o protocolo STOMP por cima do SockJS
        stompClient = Stomp.over(socket);

        // Faz a conexão
        stompClient.connect({}, () => {
            console.log('✅ Conectado com sucesso!');
            isConnected = true;

            // Escuta mensagens do canal indicado
            const subscriptionPath = '/topic/' + channel;
            console.log('📡 Inscrito no canal:', subscriptionPath);

            stompClient.subscribe(subscriptionPath, (message) => {
                try {
                    const data = JSON.parse(message.body);
                    console.log('📩 Mensagem recebida:', data);
                    if (onMessage) onMessage(data);
                } catch (e) {
                    console.error('⚠️ Erro ao ler mensagem:', e);
                }
            });

            // Executa callback se fornecido
            if (onConnect) onConnect(stompClient);

        }, (error) => {
            console.error('❌ Falha ao conectar ao WebSocket:', error);
            isConnected = false;
            if (onError) onError(error);
        });
    }

    // ======== ENVIAR MENSAGEM ========
    function send(destination, payload) {
        if (!isConnected || !stompClient) {
            console.warn('⚠️ Não é possível enviar: não há conexão ativa.');
            return;
        }

        console.log('📤 Enviando para', destination, payload);
        stompClient.send(destination, {}, JSON.stringify(payload));
    }

    // ======== DESCONECTAR ========
    function disconnect() {
        if (stompClient) {
            stompClient.disconnect(() => {
                console.log('🔌 Desconectado do servidor WebSocket.');
                isConnected = false;
            });
        } else {
            console.warn('⚠️ Nenhum cliente conectado para desconectar.');
        }
    }

    // ======== STATUS ========
    function getConnectionStatus() {
        return isConnected;
    }

    // ========= RECONECTAR ========
    function reconnect(wsUrl, channel, onMessage, onConnect, onError) {
        if (isConnected) {
            console.log('🔄 Já conectado, não é necessário reconectar.');
        } else {
            console.log('🔄 Tentando reconectar ao WebSocket...');
            connect(wsUrl, channel, onMessage, onConnect, onError);
        }
    }

    // ======== EXPORTAR ========
    return {
        connect,
        send,
        disconnect,
        reconnect,
        getConnectionStatus,
    };
})();
