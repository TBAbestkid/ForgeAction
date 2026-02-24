(function() {
    'use strict';

    // ====== HELPERS ======
    const debugLog = window.debugLog 
        ? (...args) => window.debugLog(...args) 
        : (...args) => console.log('[MessageUtils]', ...args);

    function getDefaultSalaId() {
        const salaId = window.salaId;
        if (!salaId) {
            debugLog('⚠️ window.salaId não está definido');
        }
        return salaId;
    }

    function buildDestination(topic, salaId) {
        if (!salaId) {
            debugLog('❌ SalaId não fornecido para buildDestination');
            return null;
        }
        return `/app/${topic}/${salaId}`;
    }

    // ====== FUNÇÃO PRINCIPAL ======
    function enviarMensagem(options) {
        const { 
            destination, 
            payload, 
            waitForConnection = true, 
            onSuccess, 
            onError 
        } = options;

        // Validação: WebSocket disponível?
        if (!window.ws) {
            const error = 'WebSocket service (window.ws) não está disponível';
            debugLog('❌', error);
            onError?.(error);
            return;
        }

        // Validação: Destination fornecido?
        if (!destination) {
            const error = 'Destination não fornecido';
            debugLog('❌', error);
            onError?.(error);
            return;
        }

        // Obter status da conexão
        const status = window.ws.getStatus();

        // Se conectado: enviar imediatamente
        if (status.isConnected) {
            try {
                window.ws.send(destination, payload);
                debugLog('✅ Mensagem enviada para', destination, payload);
                onSuccess?.();
            } catch (err) {
                debugLog('❌ Erro ao enviar mensagem:', err);
                onError?.(err);
            }
            return;
        }

        // Se não conectado e deve aguardar
        if (waitForConnection) {
            debugLog('⚠️ WebSocket não conectado, aguardando conexão...');
            document.addEventListener('stomp.connected', () => {
                try {
                    window.ws.send(destination, payload);
                    debugLog('✅ Mensagem enviada após reconexão:', destination);
                    onSuccess?.();
                } catch (err) {
                    debugLog('❌ Erro ao enviar após reconexão:', err);
                    onError?.(err);
                }
            }, { once: true });
            return;
        }

        // Se não conectado e não deve aguardar
        const error = 'WebSocket desconectado e waitForConnection = false';
        debugLog('❌', error);
        onError?.(error);
    }

    // ====== ENVIAR SISTEMA ======
    function enviarSistema(msg, options = {}) {
        const {
            salaId = getDefaultSalaId(),
            topic = 'enviar',
            userId = window.userId,
            autor = '🤖 Sistema',
            ...restOptions
        } = options;

        const destination = buildDestination(topic, salaId);
        if (!destination) {
            debugLog('❌ Não foi possível construir destination para enviarSistema');
            return;
        }

        const payload = {
            acao: 'sistema',
            conteudo: msg,
            autor: autor,
            usuarioId: userId,
            salaId: salaId
        };

        enviarMensagem({
            destination,
            payload,
            ...restOptions
        });
    }

    // ====== ENVIAR AÇÃO ======
    function enviarAcao(obj, options = {}) {
        const {
            salaId = getDefaultSalaId(),
            topic = 'enviar',
            ...restOptions
        } = options;

        const destination = buildDestination(topic, salaId);
        if (!destination) {
            debugLog('❌ Não foi possível construir destination para enviarAcao');
            return;
        }

        const payload = {
            salaId: salaId,
            ...obj
        };

        enviarMensagem({
            destination,
            payload,
            ...restOptions
        });
    }

    // ====== ENVIAR CUSTOMIZADO ======
    function enviarCustom(fullPath, payload, options = {}) {
        if (!fullPath) {
            debugLog('❌ fullPath não fornecido para enviarCustom');
            return;
        }

        enviarMensagem({
            destination: fullPath,
            payload,
            ...options
        });
    }

    // ====== EXPORTS ======
    window.MessageUtils = {
        enviarMensagem,
        enviarSistema,
        enviarAcao,
        enviarCustom
    };

    // Retrocompatibilidade
    window.enviarSistema = enviarSistema;
    window.enviarAcao = enviarAcao;

    debugLog('✅ MessageUtils carregado e pronto para uso');

})();