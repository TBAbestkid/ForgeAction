/* room-manager.js
   Version: 4.0 - Unified WebSocket System
   Requirements:
   - webSocketService.js loaded
   - Bootstrap
   - window.CHAT_CONFIG = { userId, userLogin, salaId, wsUrl, isMestre, role }
*/
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

    // Estado do jogo
    let ordemTurnos = [];
    let turnoIndex = 0;
    let rodada = 1;
    let rodadaAtiva = false;
    let phase = 'idle'; // 'player', 'master', 'idle'
    let currentPlayerId = null;
    let modoMestre = null; // 'dano', 'cura', null
    let ultimoDadoRolado = null;

    // ====== UI ELEMENTS ======
    const personagensContainer = document.getElementById('personagens-container') ||
                               document.getElementById('games-section') ||
                               document;
    const placeholder = document.getElementById('dice-placeholder');
    const turnControls = document.getElementById('turn-controls');
    const diceOptions = document.getElementById('dice-options');
    const btnIniciar = document.getElementById('btnIniciarTurno');
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');
    const btnLancarMestre = document.getElementById('btn-lancar-mestre');
    const btnOcultarDados = document.getElementById('ocultarDados');

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    function enviarSistema(msg) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        const status = ws.getStatus();
        if (!status.isConnected) {
            debugLog('⚠️ WebSocket não conectado, aguardando conexão...');
            document.addEventListener('stomp.connected', () => {
                ws.send('/app/enviar/' + salaId, {
                    tipo: 'sistema',
                    conteudo: msg,
                    autor: '🤖 Sistema',
                    salaId: salaId
                });
            }, { once: true });
            return;
        }

        ws.send('/app/enviar/' + salaId, {
            tipo: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
            salaId: salaId
        });
    }

    function enviarAcao(obj) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        const status = ws.getStatus();
        if (!status.isConnected) {
            debugLog('⚠️ WebSocket não conectado, aguardando conexão...');
            document.addEventListener('stomp.connected', () => {
                ws.send('/app/enviar/' + salaId, {
                    tipo: 'acao',
                    salaId,
                    timestamp: Date.now(),
                    ...obj
                });
            }, { once: true });
            return;
        }

        ws.send('/app/enviar/' + salaId, {
            tipo: 'acao',
            salaId,
            timestamp: Date.now(),
            ...obj
        });
    }

    // ====== WEBSOCKET INTEGRATION ======
    function setupWebSocket() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Registra handlers para eventos do WebSocket
        document.addEventListener('stomp.connected', () => {
            debugLog('✅ WebSocket conectado!');
            ws.subscribe(channel, onReceiveAction);

            // Envia mensagem de entrada
            enviarSistema(`🟢 ${userLogin} entrou na sala`);
        });

        document.addEventListener('stomp.error', (event) => {
            debugLog('❌ Erro de conexão:', event.detail?.error);
        });

        document.addEventListener('stomp.disconnected', () => {
            debugLog('🔴 WebSocket desconectado');
        });

        // Processa mensagens recebidas
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

        // Se o WebSocket já estiver conectado, inscreve imediatamente
        const status = ws.getStatus();
        if (status.isConnected) {
            ws.subscribe(channel, onReceiveAction);
            enviarSistema(`🟢 ${userLogin} entrou na sala`);
        }
    }

    // Resto do código do room-manager.js aqui...
    // (getCardById, destacarPersonagem, setPlayerControlsEnabled, etc.)

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
        atualizarBotoesMestre();
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
        atualizarBotoesMestre();
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

        if (btnLancarMestre) btnLancarMestre.disabled = true;

        enviarSistema(`👉 Turno de ${proximo.nome}`);
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
        atualizarBotoesMestre();
    }

    function proximoPhaseDepoisDaAcaoDoJogador() {
        setPlayerControlsEnabled(false, currentPlayerId);
        phase = 'master';
        enviarSistema(`👉 Jogador terminou sua ação. Aguardando Mestre...`);
        atualizarBotoesMestre();
    }

    function atualizarTurnoUI() {
        if (!rodadaAtiva) {
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
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

        atualizarBotoesMestre();
    }

    function atualizarBotoesMestre() {
        if (!isMestre) return;

        const btnInicio = document.getElementById('btnIniciarTurno');
        const btnMestre = document.getElementById('btn-lancar-mestre');
        const btnPermitir = document.getElementById('btn-permitir-jogada');
        const iconInicio = btnInicio?.querySelector('i.fa-solid');
        const btnDano = document.getElementById('btn-dano');
        const btnCura = document.getElementById('btn-curar');
        const btnUpar = document.getElementById('btn-upar');

        if (!btnMestre || !btnInicio || !btnPermitir || !iconInicio) {
            debugLog('⚠️ Alguns botões do mestre não foram encontrados');
            return;
        }

        // Botão de lançar dados do mestre sempre ativo durante rodada
        btnMestre.disabled = !rodadaAtiva;
        btnDano.disabled = !rodadaAtiva;
        btnCura.disabled = !rodadaAtiva;
        btnUpar.disabled = !rodadaAtiva;

        if (!rodadaAtiva) {
            // Estado inicial: botão de play habilitado
            btnInicio.disabled = false;
            btnInicio.classList.remove('btn-outline-secondary');
            btnInicio.classList.add('btn-outline-success');
            iconInicio.classList.remove('fa-pause', 'fa-forward');
            iconInicio.classList.add('fa-play');
            btnPermitir.disabled = true;
            return;
        }

        if (phase === 'master') {
            // Vez do mestre: botão de avançar habilitado
            btnInicio.disabled = false;
            btnInicio.classList.remove('btn-outline-secondary');
            btnInicio.classList.add('btn-outline-success');
            iconInicio.classList.remove('fa-pause', 'fa-play');
            iconInicio.classList.add('fa-forward');
            btnPermitir.disabled = false;

            // Habilitar ações do mestre
            btnMestre.disabled = false;
            btnDano.disabled = false;
            btnCura.disabled = false;
            btnUpar.disabled = false;
            return;
        }

        if (phase === 'player') {
            // Vez do jogador: botão pausado e desabilitado
            btnInicio.disabled = true;
            btnInicio.classList.remove('btn-outline-success');
            btnInicio.classList.add('btn-outline-secondary');
            iconInicio.classList.remove('fa-play', 'fa-forward');
            iconInicio.classList.add('fa-pause');
            btnPermitir.disabled = false; // Permitir que o mestre dê jogadas extras
            return;
        }
    }

    function handleVidaChange(personagemId, valor, tipo) {
        if (!isMestre || !rodadaAtiva) return;

        const card = getCardById(personagemId);
        if (!card) return;

        const vidaAtual = parseInt(card.dataset.vida, 10);
        const vidaMaxima = parseInt(card.dataset.vidaMax, 10);
        let novaVida;

        if (tipo === 'dano') {
            novaVida = Math.max(0, vidaAtual - valor);
            enviarSistema(`💥 ${card.dataset.nome} recebeu ${valor} de dano!`);
        } else {
            novaVida = Math.min(vidaMaxima, vidaAtual + valor);
            enviarSistema(`❤️ ${card.dataset.nome} foi curado em ${valor} pontos!`);
        }

        const progressBar = card.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${(novaVida / vidaMaxima) * 100}%`;
            progressBar.textContent = `${novaVida}/${vidaMaxima} HP`;
        }

        card.dataset.vida = novaVida;

        enviarAcao({
            acao: tipo === 'dano' ? 'danoRecebido' : 'curaRecebida',
            personagemId,
            valor,
            vidaAtual: novaVida
        });
    }

    function ativarModoMestre(modo) {
        if (!isMestre || !rodadaAtiva) return;
        debugLog('🎯 Ativando modo mestre:', modo);

        const btnDano = document.getElementById('btn-dano');
        const btnCurar = document.getElementById('btn-curar');
        const btnUpar = document.getElementById('btn-upar');

        [btnDano, btnCurar, btnUpar].forEach(btn => {
            if (btn) {
                btn.classList.remove('active');
                btn.removeAttribute('data-active');
            }
        });

        document.querySelectorAll('.personagem-card').forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);
            c.classList.remove(
                'border-primary', 'border-danger',
                'border-success', 'border-info'
            );
            if (!isCurrentPlayer) c.classList.remove('border-3');
            c.style.cursor = 'default';
        });

        if (modoMestre === modo) {
            debugLog('🔄 Desativando modo:', modo);
            modoMestre = null;
            return;
        }

        modoMestre = modo;

        const btnAtivo = modo === 'dano' ? btnDano :
                      modo === 'cura' ? btnCurar :
                      modo === 'up' ? btnUpar : null;

        if (btnAtivo) {
            btnAtivo.classList.add('active');
            btnAtivo.setAttribute('data-active', 'true');
        }

        const cards = document.querySelectorAll('.personagem-card');
        cards.forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);
            if (!isCurrentPlayer) {
                c.classList.add('border-primary', 'border-3');
                c.style.cursor = 'pointer';
            }
        });

        debugLog('✅ Modo ativado:', modo, 'Cards encontrados:', cards.length);
    }

    // ========== EVENT HANDLERS ==========
    function onDiceButtonClick(e) {
        const btn = e.target.closest('.dice-btn');
        if (!btn) return;

        const sides = parseInt(btn.dataset.sides, 10);
        if (isNaN(sides)) return;

        const valor = Math.floor(Math.random() * sides) + 1;
        const atual = ordemTurnos[turnoIndex];

        if (!rodadaAtiva) {
            debugLog('⚠️ Não há rodada ativa para lançar dados');
            return;
        }

        if (isMestre) {
            enviarSistema(`🎲 Mestre rolou D${sides} = ${valor}`);
            enviarAcao({
                acao: 'playerActionDone',
                personagemId: currentPlayerId,
                descricao: `Mestre rolou D${sides} = ${valor}`,
                autor: 'Mestre',
                dado: sides,
                valor
            });
            // Passar turno automaticamente após ação do mestre
            setTimeout(() => proximoTurno(), 1000);
        } else {
            if (!phase === 'player') {
                debugLog('⚠️ Não é a fase do jogador');
                return;
            }
            enviarSistema(`🎲 ${atual.nome} rolou D${sides} = ${valor}`);
            enviarAcao({
                acao: 'playerActionDone',
                personagemId: atual.personagemId,
                descricao: `${atual.nome} rolou D${sides} = ${valor}`,
                autor: userLogin,
                dado: sides,
                valor
            });

            proximoPhaseDepoisDaAcaoDoJogador();
        }

        diceOptions.classList.add('d-none');
        ultimoDadoRolado = { dado: sides, valor, autor: isMestre ? 'Mestre' : atual.nome };
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
                if (data.dado && data.valor) {
                    ultimoDadoRolado = {
                        dado: data.dado,
                        valor: data.valor,
                        autor: data.autor
                    };
                }

                rodadaAtiva = true;
                phase = 'master';
                currentPlayerId = String(data.personagemId);

                if (card) destacarPersonagem(card);
                if (!isMestre) {
                    setPlayerControlsEnabled(false, currentPlayerId);
                }

                atualizarTurnoUI();
                atualizarBotoesMestre();
                break;

            case 'danoRecebido':
            case 'curaRecebida':
                if (!card) return;
                const vidaAtual = data.vidaAtual;
                const vidaMaxima = parseInt(card.dataset.vidaMax, 10);

                card.dataset.vida = vidaAtual;
                const progressBar = card.querySelector('.progress-bar');
                if (progressBar) {
                    progressBar.style.width = `${(vidaAtual / vidaMaxima) * 100}%`;
                    progressBar.textContent = `${vidaAtual}/${vidaMaxima} HP`;
                }
                break;
        }
    }

    // ========== INIT ==========
    function init() {
        debugLog('🎲 room-manager v4.0 iniciando...');

        // Configura WebSocket
        setupWebSocket();

        // Registra listeners de interface
        if (btnRoll) btnRoll.addEventListener('click', () => {
            if (!rodadaAtiva) {
                debugLog('⚠️ Não há rodada ativa para lançar dados');
                return;
            }
            diceOptions.classList.remove('d-none');
        });

        if (btnIniciar) btnIniciar.addEventListener('click', () => {
            if (!isMestre) return;
            if (!rodadaAtiva) {
                iniciarRodada();
            } else if (phase === 'master') {
                proximoTurno();
            }
        });

        if (diceOptions) diceOptions.addEventListener('click', onDiceButtonClick);
        if (btnSkip) btnSkip.addEventListener('click', () => {
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            if (!atual) return;

            enviarSistema(`⏭️ ${atual.nome} passou a vez`);
            enviarAcao({
                acao: 'playerActionDone',
                personagemId: atual.personagemId,
                descricao: `${atual.nome} passou a vez`,
                autor: userLogin
            });

            proximoPhaseDepoisDaAcaoDoJogador();
        });

        // Configura listeners para botões do mestre
        if (btnLancarMestre) {
            btnLancarMestre.addEventListener('click', () => {
                if (!rodadaAtiva) return;
                diceOptions.classList.remove('d-none');
            });
        }

        // Configura botão de permitir jogada extra
        const btnPermitir = document.getElementById('btn-permitir-jogada');
        if (btnPermitir) {
            btnPermitir.addEventListener('click', () => {
                if (!rodadaAtiva || !isMestre) return;

                const jogadorAtual = ordemTurnos[turnoIndex];
                if (!jogadorAtual) return;

                phase = 'player';
                enviarSistema(`🎯 Mestre permitiu uma jogada extra para ${jogadorAtual.nome}`);
                setPlayerControlsEnabled(true, jogadorAtual.personagemId);
                atualizarTurnoUI();
            });
        }    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
