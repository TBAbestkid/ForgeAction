// Global WebSocket service using SockJS + STOMP.
window.AppWebSocket = (() => {
    let stompClient = null;
    let isConnected = false;
    let reconnectTimer = null;
    let subscriptions = new Map();
    let connectionConfig = null;

    const DEFAULT_CONFIG = {
        reconnectDelay: 3000,
        debug: false
    };

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

    function dispatchGlobalEvent(eventName, detail) {
        document.dispatchEvent(new CustomEvent(eventName, {
            bubbles: true,
            detail
        }));
        debugLog(`Evento disparado: ${eventName}`, detail);
    }

    function attachStompSubscription(channel, entry) {
        if (!stompClient?.connected) return null;

        const topic = entry.topic || `/topic/${channel}`;
        entry.topic = topic;
        entry.subscription = stompClient.subscribe(topic, (message) => {
            try {
                const data = JSON.parse(message.body);
                dispatchGlobalEvent('ws.message', data);

                entry.callbacks.forEach((callback) => {
                    try {
                        callback(data);
                    } catch (callbackError) {
                        console.error('Erro em callback de mensagem:', callbackError);
                    }
                });
            } catch (error) {
                console.error('Erro ao processar mensagem:', error);
            }
        });

        debugLog(`Inscrito no canal: ${topic}`);
        return entry.subscription;
    }

    function subscribe(channel, callback) {
        if (!stompClient?.connected) {
            debugLog('Nao e possivel se inscrever: cliente nao conectado');
            return null;
        }

        if (subscriptions.has(channel)) {
            const existing = subscriptions.get(channel);
            if (callback && !existing.callbacks.includes(callback)) {
                existing.callbacks.push(callback);
            }
            return existing.subscription;
        }

        try {
            const entry = {
                topic: `/topic/${channel}`,
                callbacks: callback ? [callback] : [],
                subscription: null
            };

            const subscription = attachStompSubscription(channel, entry);
            subscriptions.set(channel, entry);
            return subscription;
        } catch (error) {
            console.error('Erro ao se inscrever:', error);
            return null;
        }
    }

    function resubscribeAll() {
        debugLog('Reinscrevendo em todos os canais...');
        subscriptions.forEach((entry, channel) => {
            attachStompSubscription(channel, entry);
        });
    }

    function connect(wsUrl, channel, onMessage, headers = {}) {
        if (isConnected) {
            debugLog('Ja conectado, ignorando nova tentativa');
            return;
        }

        connectionConfig = { wsUrl, channel, onMessage, headers };

        const socket = new SockJS(wsUrl);
        stompClient = Stomp.over(socket);
        stompClient.debug = DEFAULT_CONFIG.debug ? console.log : null;

        stompClient.connect(headers,
            () => {
                isConnected = true;
                clearReconnectTimer();

                resubscribeAll();

                if (channel) {
                    subscribe(channel, onMessage);
                }

                dispatchGlobalEvent('stomp.connected', {
                    stompClient,
                    isReconnect: reconnectTimer !== null
                });
            },
            (error) => {
                console.error('Erro de conexao:', error);
                isConnected = false;

                clearReconnectTimer();
                reconnectTimer = setTimeout(() => {
                    if (!connectionConfig) return;

                    connect(
                        connectionConfig.wsUrl,
                        connectionConfig.channel,
                        connectionConfig.onMessage,
                        connectionConfig.headers
                    );
                }, DEFAULT_CONFIG.reconnectDelay);

                dispatchGlobalEvent('stomp.error', { error });
            }
        );
    }

    function send(destination, payload) {
        if (!isConnected || !stompClient?.connected) {
            console.warn('Nao e possivel enviar: sem conexao ativa');
            return false;
        }

        try {
            stompClient.send(destination, {}, JSON.stringify(payload));
            debugLog('Mensagem enviada:', destination, payload);
            return true;
        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            return false;
        }
    }

    function disconnect() {
        clearReconnectTimer();

        if (stompClient?.connected) {
            stompClient.disconnect(() => {
                isConnected = false;
                dispatchGlobalEvent('stomp.disconnected', {});
            });
        }

        stompClient = null;
        isConnected = false;
        subscriptions.clear();
        connectionConfig = null;
    }

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
