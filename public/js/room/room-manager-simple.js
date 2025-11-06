/* room-manager-simple.js
   Versão simplificada focada apenas no sistema de turnos básico
*/
(function() {
    // ====== CONFIG / STATE ======
    const CHAT = window.CHAT_CONFIG || {};
    const userId = String(CHAT.userId ?? '');
    const userLogin = CHAT.userLogin ?? 'PLAYER';
    const salaId = CHAT.salaId ?? null;
    const isMestre = !!(CHAT.isMestre || CHAT.role?.toUpperCase() === 'MESTRE');
    const wsUrl = CHAT.wsUrl;
    const channel = String(salaId);

    let stompClient = null;
    let ordemTurnos = [];
    let turnoIndex = 0;
    let rodada = 1;
    let rodadaAtiva = false;
    let phase = 'idle'; // 'player', 'master', 'idle'
    let currentPlayerId = null;

    // ====== UI ELEMENTS ======
    const personagensContainer = document.getElementById('personagens-container') || document.getElementById('games-section') || document;
    const placeholder = document.getElementById('dice-placeholder');
    const turnControls = document.getElementById('turn-controls');
    const diceOptions = document.getElementById('dice-options');
    const btnIniciar = document.getElementById('btnIniciarTurno');
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');

    // ====== UTILS ======
    function debugLog(...args) { console.log('[RM]', ...args); }

    function enviarMensagem(mensagem, tentativas = 3) {
        return new Promise((resolve, reject) => {
            if (!stompClient) {
                debugLog('❌ Sem cliente STOMP');
                reject(new Error('Sem cliente STOMP'));
                return;
            }

            if (!stompClient.connected) {
                debugLog('❌ Cliente STOMP não está conectado');
                reject(new Error('Cliente STOMP não conectado'));
                return;
            }

            try {
                stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify(mensagem));
                debugLog('📤 Mensagem enviada:', mensagem);
                resolve();
            } catch (error) {
                debugLog('❌ Erro ao enviar mensagem:', error);
                if (tentativas > 1) {
                    setTimeout(() => {
                        enviarMensagem(mensagem, tentativas - 1)
                            .then(resolve)
                            .catch(reject);
                    }, 1000);
                } else {
                    reject(error);
                }
            }
        });
    }

    function enviarSistema(msg) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        enviarMensagem({
            tipo: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
            salaId: salaId
        }).catch(error => {
            console.error('Erro ao enviar mensagem de sistema:', error);
        });
    }

    function enviarAcao(obj) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        enviarMensagem({
            tipo: 'acao',
            salaId,
            timestamp: Date.now(),
            ...obj
        }).catch(error => {
            console.error('Erro ao enviar ação:', error);
        });
    }

    // ====== GAME FLOW ======
    function getCardById(pid) {
        return personagensContainer.querySelector(`.personagem-card[data-id="${pid}"]`) ||
               document.querySelector(`.personagem-card[data-id="${pid}"]`);
    }

    function destacarPersonagem(card) {
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.remove('border-warning', 'border-3');
        });
        if (card) {
            card.classList.add('border', 'border-warning', 'border-3');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function setPlayerControlsEnabled(enabled, personagemId) {
        const card = getCardById(personagemId);
        if (!card) return;

        const donoDoCard = String(card.dataset.usuarioId);
        const souDono = donoDoCard === userId;

        diceOptions.classList.add('d-none');
        btnRoll.disabled = !enabled;
        btnSkip.disabled = !enabled;

        if (isMestre) {
            turnControls.classList.add('d-none');
            btnRoll.disabled = true;
            btnSkip.disabled = true;
            return;
        }

        if (souDono && enabled) {
            turnControls.classList.remove('d-none');
            btnRoll.disabled = false;
            btnSkip.disabled = false;
        } else {
            turnControls.classList.add('d-none');
            btnRoll.disabled = true;
            btnSkip.disabled = true;
        }
    }

    function ordenarIniciativas(personagens) {
        let lista = personagens.map(card => ({
            nome: card.dataset.nome,
            iniciativa: parseInt(card.dataset.iniciativa || 0, 10),
            card,
            personagemId: String(card.dataset.id),
            usuarioId: String(card.dataset.usuarioId || '')
        }));

        lista.sort((a, b) => b.iniciativa - a.iniciativa);

        // Embaralha empates
        for (let i = 0; i < lista.length - 1; i++) {
            if (lista[i].iniciativa === lista[i + 1].iniciativa) {
                if (Math.random() < 0.5) [lista[i], lista[i + 1]] = [lista[i + 1], lista[i]];
            }
        }

        return lista;
    }

    function iniciarRodada() {
        const cards = Array.from(document.querySelectorAll('.personagem-card'));
        if (cards.length === 0) return;

        ordemTurnos = ordenarIniciativas(cards);
        turnoIndex = 0;
        rodadaAtiva = true;
        phase = 'player';
        rodada = rodada || 1;

        const primeiro = ordemTurnos[turnoIndex];
        currentPlayerId = primeiro.personagemId;
        destacarPersonagem(primeiro.card);

        enviarSistema(`🎲 Rodada ${rodada} iniciada! Ordem: ${ordemTurnos.map(p => p.nome).join(', ')}`);
        enviarAcao({ acao: 'ordemTurnos', ordem: ordemTurnos });
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
    }

    function finalizarRodada() {
        enviarSistema(`🏁 Fim da rodada ${rodada}.`);
        rodada++;
        rodadaAtiva = false;
        phase = 'idle';
        currentPlayerId = null;
        turnoIndex = 0;

        setPlayerControlsEnabled(false, null);
        if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
        destacarPersonagem(null);
    }

    function proximoTurno() {
        turnoIndex++;
        if (turnoIndex >= ordemTurnos.length) {
            finalizarRodada();
            return;
        }

        const proximo = ordemTurnos[turnoIndex];
        currentPlayerId = proximo.personagemId;
        phase = 'player';

        enviarSistema(`👉 Turno de ${proximo.nome}`);
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
    }

    function atualizarTurnoUI() {
        if (!rodadaAtiva) {
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
            diceOptions.classList.add('d-none');
            return;
        }

        const atual = ordemTurnos[turnoIndex];
        if (!atual) return;

        if (placeholder) {
            placeholder.textContent = `🕒 Turno de ${atual.nome}`;
            const isMyTurn = String(atual.usuarioId) === userId;
            if (isMyTurn) placeholder.textContent += ' (Sua vez!)';
        }

        destacarPersonagem(atual.card);

        const isMyTurn = String(atual.usuarioId) === userId;
        setPlayerControlsEnabled(isMyTurn && phase === 'player', atual.personagemId);
    }

    function onDiceButtonClick(e) {
        const btn = e.target.closest('.dice-btn');
        if (!btn) return;

        const sides = parseInt(btn.dataset.sides, 10);
        if (isNaN(sides)) return;

        const valor = Math.floor(Math.random() * sides) + 1;
        const atual = ordemTurnos[turnoIndex];

        enviarSistema(`🎲 ${atual.nome} rolou D${sides} = ${valor}`);
        enviarAcao({
            acao: 'playerActionDone',
            personagemId: atual.personagemId,
            descricao: `${atual.nome} rolou D${sides} = ${valor}`,
            autor: userLogin,
            dado: sides,
            valor
        });

        btnRoll.disabled = true;
        diceOptions.classList.add('d-none');

        phase = 'master';
        setPlayerControlsEnabled(false, currentPlayerId);
    }

    // ====== WEBSOCKET INTEGRATION ======
    function setupSocketIntegration() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Aguarda o WebSocketService estar disponível
        if (!window.WebSocketService) {
            debugLog('⚠️ WebSocketService ainda não disponível, aguardando...');
            setTimeout(setupSocketIntegration, 500);
            return;
        }

        // Se já tiver uma conexão global funcionando, usa ela
        if (window.chatStomp?.getConnectionStatus()) {
            stompClient = window.chatStomp.stompClient;
            debugLog('✅ Usando conexão global existente');
            return;
        }

        // Se ainda não tem conexão global, aguarda o evento do chat
        document.addEventListener('stomp.connected', (ev) => {
            if (ev.detail?.stompClient) {
                stompClient = ev.detail.stompClient;
                debugLog('✅ Conectado via evento stomp.connected');
            }
        });

        // Garante que o chat vai iniciar a conexão
        if (!window.chatStomp) {
            debugLog('� Iniciando conexão via chat...');
            // Pequeno delay para garantir que o chat está pronto
            setTimeout(() => {
                const chatScript = document.querySelector('script[src*="chat-room.js"]');
                if (chatScript) {
                    // Dispara o evento DOMContentLoaded para iniciar o chat
                    document.dispatchEvent(new Event('DOMContentLoaded'));
                } else {
                    debugLog('❌ chat-room.js não encontrado!');
                }
            }, 300);
        }
    }

    function onReceiveAction(data) {
        if (!data || !data.acao) return;
        debugLog('📥 Ação recebida:', data);

        const card = data.personagemId ? getCardById(String(data.personagemId)) : null;

        switch (data.acao) {
            case 'ordemTurnos':
                ordemTurnos = (data.ordem || []).map(o => ({
                    nome: o.nome,
                    personagemId: String(o.personagemId),
                    usuarioId: String(o.usuarioId || ''),
                    card: getCardById(String(o.personagemId))
                }));
                break;

            case 'turnoAtual':
                if (!card) return;
                rodadaAtiva = true;
                phase = 'player';
                currentPlayerId = String(data.personagemId);

                const turnoAtualIndex = ordemTurnos.findIndex(p => p.personagemId === currentPlayerId);
                if (turnoAtualIndex !== -1) {
                    turnoIndex = turnoAtualIndex;
                }

                destacarPersonagem(card);
                const isMyTurn = String(card.dataset.usuarioId) === userId;
                setPlayerControlsEnabled(isMyTurn, data.personagemId);
                atualizarTurnoUI();
                break;

            case 'playerActionDone':
                rodadaAtiva = true;
                phase = 'master';
                currentPlayerId = String(data.personagemId);

                if (card) destacarPersonagem(card);
                if (!isMestre) {
                    setPlayerControlsEnabled(false, currentPlayerId);
                }
                atualizarTurnoUI();
                break;
        }
    }

    // ====== EVENT LISTENERS ======
    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            if (!isMestre) return;

            if (!rodadaAtiva) {
                iniciarRodada();
            } else if (phase === 'master') {
                proximoTurno();
            }
        });
    }

    if (btnRoll) {
        btnRoll.addEventListener('click', () => {
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            if (String(atual.usuarioId) !== userId) return;

            if (diceOptions.classList.contains('d-none')) {
                diceOptions.classList.remove('d-none');
            } else {
                diceOptions.classList.add('d-none');
            }
        });
    }

    if (btnSkip) {
        btnSkip.addEventListener('click', () => {
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            if (String(atual.usuarioId) !== userId) return;

            enviarSistema(`⏭️ ${atual.nome} pulou seu turno.`);
            enviarAcao({
                acao: 'playerActionDone',
                personagemId: atual.personagemId,
                descricao: `${atual.nome} pulou o turno.`,
                autor: userLogin
            });

            btnRoll.disabled = true;
            btnSkip.disabled = true;
            diceOptions.classList.add('d-none');
            phase = 'master';
            setPlayerControlsEnabled(false, currentPlayerId);
        });
    }

    if (diceOptions) {
        diceOptions.addEventListener('click', onDiceButtonClick);
    }

    document.addEventListener('ws.message', (ev) => {
        try {
            const data = ev.detail;
            if (data.tipo === 'acao') {
                onReceiveAction(data);
            }
        } catch (e) {
            console.warn('Erro ao processar mensagem:', e);
        }
    });

    // ====== INIT ======
    function verificarConexao() {
        // Usa o status do serviço global
        if (!window.WebSocketService?.getConnectionStatus()) {
            debugLog('⚠️ Conexão perdida, aguardando reconexão do chat...');
            // O próprio chat vai tentar reconectar automaticamente
            return;
        }

        // Se o serviço está conectado mas nosso cliente não
        if (!stompClient?.connected) {
            debugLog('⚠️ Cliente STOMP desconectado, reconectando...');
            stompClient = window.chatStomp?.stompClient;
        }
    }

    function init() {
        debugLog('🎲 room-manager-simple iniciando...');

        // Garante que o script só inicia após o chat estar pronto
        if (!window.WebSocketService || !window.chatStomp) {
            debugLog('⏳ Aguardando serviços essenciais...');
            setTimeout(init, 500);
            return;
        }

        setupSocketIntegration();

        // Monitora a conexão a cada 3 segundos
        setInterval(verificarConexao, 3000);

        debugLog('✅ Inicializado. isMestre:', isMestre, 'userId:', userId);

        // Expõe funções úteis globalmente para debug
        window.roomManagerDebug = {
            getState: () => ({
                connected: window.WebSocketService?.getConnectionStatus(),
                stompConnected: stompClient?.connected,
                rodadaAtiva,
                phase,
                turnoIndex,
                currentPlayerId
            }),
            forceReconnect: setupSocketIntegration
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
