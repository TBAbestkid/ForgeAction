/* room-manager.js
   Version: 1.0
   Requirements in the page:
   - SockJS + Stomp available as global
   - Bootstrap modal + toasts in the page
   - window.CHAT_CONFIG = { userId, userLogin, salaId, wsUrl, isMestre (optional), role (optional) }
   - personagem-card elements with data-id and data-usuario-id and data-vida and data-iniciativa etc.
*/
console.log('Gerenciador de sala de RPG iniciado.');
(function () {
    // ====== CONFIG / STATE ======
    const CHAT = window.CHAT_CONFIG || {};
    const userId = String(CHAT.userId ?? '');
    const userLogin = CHAT.userLogin ?? 'Player';
    const salaId = CHAT.salaId ?? null;
    const wsUrl = CHAT.wsUrl ?? null;
    const isMestre = !!(CHAT.isMestre || CHAT.role?.toUpperCase() === 'MESTRE' || CHAT.role?.toUpperCase() === 'MASTER');

    console.log("🧩 DEBUG CONFIG", {
        userId,
        userLogin,
        salaId,
        wsUrl,
        isMestre,
        role: CHAT.role
    });

    document.querySelectorAll('.personagem-card').forEach(card => {
        console.log("📜 CARD", {
            nome: card.dataset.nome,
            personagemId: card.dataset.id,
            usuarioId: card.dataset.usuarioId
        });
    });

    // DOM elements (assumed present)
    const personagensContainer = document.getElementById('personagens-container');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const toastEl = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastBootstrap = bootstrap?.Toast?.getOrCreateInstance(toastEl);

    // Turn / UI controls
    const btnIniciar = document.getElementById('btnIniciarTurno');
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');
    const placeholder = document.getElementById('dice-placeholder');
    const turnControls = document.getElementById('turn-controls');
    const btnLancarMestre = document.getElementById('btn-lancar-mestre');

    // Master action buttons (left column)
    const btnDano = document.getElementById('btn-dano');
    const btnCurar = document.getElementById('btn-curar');
    const btnUpar = document.getElementById('btn-upar');

    // Modal unified
    const modalValorEl = document.getElementById('modalValor');
    const modalValor = modalValorEl ? new bootstrap.Modal(modalValorEl) : null;
    const inputValor = document.getElementById('inputValor');
    const btnConfirmarValor = document.getElementById('btnConfirmarValor');

    // stomp
    let stompClient = null;

    // Game state
    let ordemTurnos = []; // [{nome, iniciativa, card, personagemId, usuarioId}]
    let turnoIndex = 0;
    let rodada = 1;
    let rodadaAtiva = false;
    let phase = 'idle'; // 'player', 'master', 'idle'
    let currentPlayerId = null; // personagemId of current player's turn
    let jogadorPodeAgir = false;

    // master mode flags
    let modoDanoAtivo = false;
    let modoCurarAtivo = false;
    let modoUparAtivo = false;

    let personagemSelecionado = null;
    let tipoValor = null; // 'dano' or 'cura' or 'up'

    // ========== UTIL / UI ==========

    function debugLog(...args) { console.log('[RM]', ...args); }

    function ChatRoom(text, sender = '🧠 Sistema') {
        if (!chatMessages) return;
        const messageDiv = document.createElement('div');
        messageDiv.className = 'd-flex align-items-start gap-2 mb-1';
        const icon = document.createElement('i');
        icon.className = sender === '🧠 Sistema' ? 'fa-solid fa-robot text-warning mt-1' : 'fa-solid fa-user text-primary mt-1';
        messageDiv.appendChild(icon);
        const msgBox = document.createElement('div');
        msgBox.className = sender === '🧠 Sistema' ? 'bg-dark text-warning rounded px-2 py-1 small' : 'bg-secondary rounded px-2 py-1';
        msgBox.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messageDiv.appendChild(msgBox);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showToast(text, type = 'primary') {
        if (!toastEl || !toastMessage) return;
        toastMessage.textContent = text;
        toastEl.className = `toast align-items-center text-bg-${type} border-0`;
        toastBootstrap?.show();
    }

    function enviarSistema(payloadText) {
        // wrapper to send a human-readable system message to all
        if (!stompClient || !salaId) return;
        const payload = {
            tipo: 'sistema',
            conteudo: payloadText,
            autor: '🧠 Sistema',
            salaId: salaId
        };
        stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify(payload));
        debugLog('enviarSistema ->', payload);
    }

    function enviarAcao(obj) {
        if (!stompClient || !salaId) {
            console.warn("❌ enviarAcao cancelado: sem conexão ou salaId");
            return;
        }

        const payload = { tipo: 'acao', salaId, ...obj };
        stompClient.send(`/app/enviar/${salaId}`, {}, JSON.stringify(payload));
        debugLog('📤 enviarAcao ->', payload);
    }

    function getCardById(pid) {
        return document.querySelector(`.personagem-card[data-id="${pid}"]`);
    }

    function getCardMaxHP(card) {
        if (!card) return 0;
        const vidaMaxAttr = card.dataset.vidaMax || card.dataset['vida-max'];
        if (vidaMaxAttr) return parseInt(vidaMaxAttr, 10);
        const progress = card.querySelector('.progress-bar');
        if (progress) {
            const parts = (progress.textContent || '').split('/');
            if (parts[1]) return parseInt(parts[1], 10);
        }
        return parseInt(card.dataset.vida, 10) || 0;
    }

    function atualizarBarraVida(card, vidaAtual, vidaMax) {
        const progress = card.querySelector('.progress-bar');
        if (!progress) return;
        const pct = vidaMax > 0 ? Math.max(0, Math.min(100, (vidaAtual / vidaMax) * 100)) : 0;
        // use CSS transition for smoothness
        progress.style.transition = 'width 700ms linear';
        progress.style.width = `${pct}%`;
        progress.textContent = `${vidaAtual}/${vidaMax} HP`;
    }

    function animarBarraVida(card, vidaAtual, vidaNova, vidaMax, duracao = 700) {
        const progress = card.querySelector('.progress-bar');
        if (!progress) return;

        const inicio = performance.now();
        const delta = vidaNova - vidaAtual;

        // add temporary color
        progress.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
        progress.classList.add(delta < 0 ? 'bg-danger' : 'bg-success');

        function step(ts) {
            const t = Math.min((ts - inicio) / duracao, 1);
            const valorInterpolado = Math.round(vidaAtual + delta * t);
            const pct = vidaMax > 0 ? Math.max(0, Math.min(100, (valorInterpolado / vidaMax) * 100)) : 0;
            progress.style.width = `${pct}%`;
            progress.textContent = `${valorInterpolado}/${vidaMax} HP`;
            if (t < 1) requestAnimationFrame(step);
            else {
                // finalize
                card.dataset.vida = vidaNova;
                // restore default color after short delay
                setTimeout(() => {
                    progress.classList.remove('bg-danger', 'bg-success');
                    progress.classList.add('bg-secondary');
                }, 300);
            }
        }
        requestAnimationFrame(step);
    }

    // Reseta bordas decorativas
    function resetCardBorders() {
        document.querySelectorAll('.personagem-card').forEach(card => {
            card.classList.remove('border-danger', 'border-success', 'border-info', 'border-primary', 'border-warning', 'border-3');
            card.style.cursor = '';
        });
    }

    function destacarPersonagem(card) {
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.remove('border-warning', 'border-3');
        });
        if (!card) return;
        card.classList.add('border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Enable/disable player's local controls (roll/skip)
    function setPlayerControlsEnabled(enabled, personagemId) {
        const card = getCardById(personagemId);

        // Caso não exista o card
        if (!card) {
            console.warn("❌ Nenhum card encontrado para personagemId:", personagemId);
            return;
        }

        const donoDoCard = String(card.dataset.usuarioId);
        const souDono = donoDoCard === userId;

        console.log("🎯 DEBUG TURNO", {
            personagemEmTurno: personagemId,
            donoDoCard,
            userId,
            souDono,
            enabled,
            isMestre,
            nome: card.dataset.nome
        });

        // 🔒 Se for o mestre, nunca mostrar botões de jogador
        if (isMestre) {
            console.log("🧙 É o mestre, escondendo botões de jogador.");
            if (turnControls) turnControls.classList.add('d-none');
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
            return;
        }

        // 🎯 Se é o dono e é o turno dele
        if (souDono && enabled) {
            console.log(`✅ É sua vez, ${userLogin} (ID ${userId}) - Personagem: ${card.dataset.nome}`);
            turnControls.classList.remove('d-none');
            if (btnRoll) btnRoll.disabled = false;
            if (btnSkip) btnSkip.disabled = false;
        } else {
            console.log(`⏳ Não é sua vez (${userLogin}), ou o personagem não pertence a você.`);
            turnControls.classList.add('d-none');
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
        }
    }

    function notifyOrderToAll() {
        // broadcast ordemTurnos so everyone sees the order
        const ordem = ordemTurnos.map(o => ({ personagemId: o.personagemId, nome: o.nome, usuarioId: o.usuarioId }));
        enviarAcao({ acao: 'ordemTurnos', ordem });
    }

    // ========== TURN FLOW ==========

    function ordenarIniciativas(personagens) {
        let lista = personagens.map(card => ({
            nome: card.dataset.nome,
            iniciativa: parseInt(card.dataset.iniciativa || 0, 10),
            card,
            personagemId: String(card.dataset.id),
            usuarioId: String(card.dataset.usuarioId || '')
        }));
        lista.sort((a, b) => b.iniciativa - a.iniciativa);
        // tie-break shuffle
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
        enviarSistema(`🕒 Rodada ${rodada} iniciada! Ordem de turnos: ${ordemTurnos.map(p => p.nome).join(', ')}`);
        notifyOrderToAll();

        // ⚔️ Habilita controles locais do jogador atual (apenas no mestre)
        setPlayerControlsEnabled(true, currentPlayerId);

        // 🧩 Envia evento WS para todos os clientes
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        atualizarTurnoUI();
    }

    function atualizarTurnoUI() {
        if (!rodadaAtiva) {
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
            return;
        }
        const atual = ordemTurnos[turnoIndex];
        if (!atual) return;
        if (placeholder) placeholder.textContent = `🕒 Turno de ${atual.nome}`;
        // highlight
        destacarPersonagem(atual.card);
        // if local user is owner, they will see turnControls via setPlayerControlsEnabled
    }

    function proximoPhaseDepoisDaAcaoDoJogador() {
        // chamado quando jogador executou ação (roll/skip)
        // disable player's controls and move to master phase
        setPlayerControlsEnabled(false, currentPlayerId);
        phase = 'master';
        // inform everyone
        enviarSistema(`👉 Jogador terminou sua ação. Agora é a vez do Mestre decidir.`);
        // On clients, master UI already visible for isMestre
        // Wait for master actions (master can: roll, apply damage/curar/up, or re-enable player to allow extra roll)
    }

    function proximoTurno() {
        // finish master, go next player
        turnoIndex++;
        if (turnoIndex >= ordemTurnos.length) {
            enviarSistema(`🏁 Fim da rodada ${rodada}.`);
            rodada++;
            rodadaAtiva = false;
            phase = 'idle';
            currentPlayerId = null;
            // hide player controls everywhere
            setPlayerControlsEnabled(false, null);
            // show start button for master
            if (btnIniciar) btnIniciar.disabled = false;
            if (turnControls) turnControls.classList.add('d-none');
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
            return;
        }

        const proximo = ordemTurnos[turnoIndex];
        currentPlayerId = proximo.personagemId;
        phase = 'player';
        // tell everyone whose turn is now
        enviarSistema(`👉 Turno de ${proximo.nome}`);
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });
        // enable the new player's controls (only that player will see)
        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
    }

    // ========== MODES (MASTER) ==========

    function desativarTodosOsModos() {
        modoDanoAtivo = modoCurarAtivo = modoUparAtivo = false;
        resetCardBorders();
    }

    function ativarModoDano() {
        desativarTodosOsModos();
        modoDanoAtivo = true;
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.add('border', 'border-danger');
            c.style.cursor = 'pointer';
        });
        enviarSistema('💥 Modo DANO ativado pelo Mestre.');
        showToast('💥 Modo DANO ativado. Clique em um personagem.', 'danger');
    }

    function ativarModoCura() {
        desativarTodosOsModos();
        modoCurarAtivo = true;
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.add('border', 'border-success');
            c.style.cursor = 'pointer';
        });
        enviarSistema('❤️ Modo CURA ativado pelo Mestre.');
        showToast('❤️ Modo CURA ativado. Clique em um personagem.', 'success');
    }

    function ativarModoUp() {
        desativarTodosOsModos();
        modoUparAtivo = true;
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.add('border', 'border-info', 'border-3');
            c.style.cursor = 'pointer';
        });
        enviarSistema('✨ Modo UP ativado pelo Mestre.');
        showToast('✨ Modo UP ativado. Clique em um personagem.', 'info');
    }

    // aplicar ação mestre (after modal confirm)
    function confirmarModalAplicarValor() {
        const valor = parseInt(inputValor?.value ?? 0, 10);
        if (!personagemSelecionado || !tipoValor || isNaN(valor) || valor <= 0) {
            showToast('Valor inválido.', 'warning');
            return;
        }

        // apply locally (master sees instant)
        if (tipoValor === 'dano' || tipoValor === 'cura') {
            // animate via enviarAcao which everyone listens to
            const pid = String(personagemSelecionado.dataset.id);
            const vidaAtual = parseInt(personagemSelecionado.dataset.vida ?? 0, 10);
            const vidaMax = getCardMaxHP(personagemSelecionado);
            const vidaNova = tipoValor === 'dano' ? Math.max(0, vidaAtual - Math.abs(valor)) : Math.min(vidaMax, vidaAtual + Math.abs(valor));
            animarBarraVida(personagemSelecionado, vidaAtual, vidaNova, vidaMax);

            // send action object so everyone updates visually
            enviarAcao({
                acao: tipoValor === 'dano' ? 'aplicarDano' : 'curar',
                personagemId: pid,
                valor: valor,
                autor: userLogin
            });

            // also send a readable system message
            enviarSistema(`${tipoValor === 'dano' ? '💥' : '❤️'} ${personagemSelecionado.dataset.nome} ${tipoValor === 'dano' ? 'sofreu' : 'recuperou'} ${valor} de HP!`);
        } else if (tipoValor === 'up') {
            // increment level and announce. The owner will receive a 'permitirDistribuirPontos' control.
            let level = parseInt(personagemSelecionado.dataset.level ?? 1, 10) + 1;
            personagemSelecionado.dataset.level = level;
            const levelText = personagemSelecionado.querySelector('.level-text');
            if (levelText) levelText.textContent = `Lv. ${level}`;
            enviarAcao({
                acao: 'upar',
                personagemId: String(personagemSelecionado.dataset.id),
                novoLevel: level,
                autor: userLogin
            });
            enviarSistema(`⚡ ${personagemSelecionado.dataset.nome} subiu para o nível ${level}! Dono poderá distribuir +5 pontos.`);
            // send control so only the owner opens a distribution UI (not implemented here)
            enviarAcao({ acao: 'permitirDistribuir', personagemId: personagemSelecionado.dataset.id, pontos: 5 });
        }

        // clear modal state
        personagemSelecionado = null;
        tipoValor = null;
        inputValor.value = '';
        modalValor?.hide();
        desativarTodosOsModos();
    }

    // =================== SOCKET HANDLERS ===================
    function onReceiveAction(data) {
        if (!data || !data.acao) return;
        debugLog('📥 Ação recebida:', data);

        const card = data.personagemId ? getCardById(String(data.personagemId)) : null;

        switch (data.acao) {
            case 'aplicarDano':
            case 'curar': {
                if (!card) return;
                const vidaAtual = parseInt(card.dataset.vida ?? 0, 10);
                const vidaMax = getCardMaxHP(card);
                const valor = Math.abs(parseInt(data.valor || 0, 10));
                const vidaNova = data.acao === 'aplicarDano'
                    ? Math.max(0, vidaAtual - valor)
                    : Math.min(vidaMax, vidaAtual + valor);
                animarBarraVida(card, vidaAtual, vidaNova, vidaMax);
                break;
            }

            case 'ordemTurnos': {
                const ordemNames = (data.ordem || []).map(o => o.nome).join(', ');
                ChatRoom(`🕒 Ordem de Iniciativa: ${ordemNames}`, '🧠 Sistema');
                break;
            }

            case 'turnoAtual': {
                if (!card) return;
                destacarPersonagem(card);
                const isMyTurn = String(card.dataset.usuarioId) === userId;
                setPlayerControlsEnabled(isMyTurn, data.personagemId);
                ChatRoom(isMyTurn ? '✅ É sua vez!' : `⏳ Turno de ${card.dataset.nome}`, '🧠 Sistema');
                break;
            }

            case 'permitirDistribuir': {
                if (card && String(card.dataset.usuarioId) === userId) {
                    ChatRoom(`✨ Você pode distribuir ${data.pontos} pontos em atributos!`, userLogin);
                }
                break;
            }

            case 'permitirJogada': {
                if (String(data.personagemId) === String(currentPlayerId)) {
                    setPlayerControlsEnabled(true, currentPlayerId);
                    enviarSistema(`🔁 Mestre permitiu jogada extra para ${card?.dataset.nome}`);
                }
                break;
            }

            case 'playerActionDone': {
                if (isMestre) {
                    ChatRoom(`🧾 ${data.descricao}`, '🧠 Sistema');
                }
                break;
            }

            case 'upar': {
                if (card && data.novoLevel) {
                    card.dataset.level = data.novoLevel;
                    const levelText = card.querySelector('.level-text');
                    if (levelText) levelText.textContent = `Lv. ${data.novoLevel}`;
                }
                break;
            }

            default:
                console.warn('⚠️ Ação desconhecida:', data.acao);
                ChatRoom(`🟡 Ação desconhecida recebida (${data.acao}).`, '🧠 Sistema');
        }
    }

    // ========== SOCKET CONNECT ==========
    function connectSocket() {
        if (!wsUrl || !salaId) {
            debugLog('⚠️ WS URL ou salaId ausente. Conexão ignorada.');
            return;
        }

        const socket = new SockJS(wsUrl);
        stompClient = Stomp.over(socket);

        stompClient.connect({}, () => {
            debugLog('🔌 Conectado ao WebSocket com sucesso!');
            stompClient.subscribe('/topic/' + salaId, handleSocketMessage);

            // Anunciar entrada na sala
            const entrada = {
                tipo: 'entrada',
                autor: userLogin,
                conteudo: `${userLogin} entrou na sala.`,
                salaId,
            };
            stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify(entrada));

        }, (err) => {
            console.error('❌ Falha ao conectar ao WebSocket:', err);
        });
    }

    // ========== SOCKET MESSAGE HANDLER ==========
    function handleSocketMessage(message) {
        try {
            const data = JSON.parse(message.body);
            if (!data) return;

            debugLog('📨 Mensagem recebida:', data);

            switch (data.tipo) {
                case 'acao':
                    onReceiveAction(data);
                    break;

                case 'chat':
                    ChatRoom(data.conteudo, data.autor || 'Jogador');
                    break;

                case 'sistema':
                    ChatRoom(data.conteudo, data.autor || '🧠 Sistema');
                    break;

                case 'entrada':
                    ChatRoom(`🔵 ${data.autor} entrou na sala.`, '🧠 Sistema');
                    // exemplo: animarEntradaUsuario(data.autor);
                    break;

                case 'saida':
                    ChatRoom(`🔴 ${data.autor} saiu da sala.`, '🧠 Sistema');
                    // exemplo: animarSaidaUsuario(data.autor);
                    break;

                case 'erro':
                    ChatRoom(`⚠️ ${data.conteudo}`, '❌ Sistema');
                    break;

                default:
                    console.warn('⚠️ Tipo de mensagem desconhecido:', data.tipo, data);
                    ChatRoom(`🟡 Mensagem desconhecida recebida (${data.tipo}).`, '🧠 Sistema');
            }

        } catch (err) {
            console.error('Erro ao processar mensagem WS:', err);
        }
    }

    // ========== EVENTS: UI interactions ==========

    // Master action buttons
    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            if (!isMestre) {
                showToast('Somente o Mestre pode iniciar a rodada.', 'warning');
                return;
            }
            if (rodadaAtiva) {
                showToast('Rodada já ativa.', 'warning');
                return;
            }
            btnIniciar.disabled = true;
            iniciarRodada();
        });
    }

    if (btnRoll) {
        btnRoll.addEventListener('click', () => {
            // only allowed when it's player's phase and they are owner
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            const isOwner = String(atual.usuarioId) === userId;
            if (!isOwner) return;
            // roll dice (player chooses dice type from diceOptions - fallback to d20)
            const lados = [4, 6, 8, 10, 12, 20];
            const dado = lados[Math.floor(Math.random() * lados.length)];
            const valor = Math.floor(Math.random() * dado) + 1;
            ChatRoom(`🎲 ${atual.nome} rolou um D${dado} e tirou ${valor}`, userLogin);
            // send a player action done so master can react
            enviarAcao({ acao: 'playerActionDone', personagemId: atual.personagemId, descricao: `${atual.nome} rolou D${dado} = ${valor}`, autor: userLogin });
            // move to master phase
            proximoPhaseDepoisDaAcaoDoJogador();
        });
    }

    if (btnSkip) {
        btnSkip.addEventListener('click', () => {
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            const isOwner = String(atual.usuarioId) === userId;
            if (!isOwner) return;
            ChatRoom(`⏭️ ${atual.nome} decidiu pular o turno.`, userLogin);
            enviarAcao({ acao: 'playerActionDone', personagemId: atual.personagemId, descricao: `${atual.nome} pulou.`, autor: userLogin });
            proximoPhaseDepoisDaAcaoDoJogador();
        });
    }

    // Master roll button
    if (btnLancarMestre) {
        btnLancarMestre.addEventListener('click', () => {
            if (!isMestre || !rodadaAtiva) return;
            // master rolls a die (secret unless reveal true)
            const lados = [4, 6, 8, 10, 12, 20];
            const dado = lados[Math.floor(Math.random() * lados.length)];
            const valor = Math.floor(Math.random() * dado) + 1;
            // For simplicity master chooses to reveal via confirm
            const revelar = confirm(`Revelar rolagem do Mestre? (D${dado} = ${valor})`);
            if (revelar) {
                enviarSistema(`🎲 Mestre rolou D${dado} = ${valor}`);
                enviarAcao({ acao: 'mestreRolou', dado, valor, autor: userLogin });
            } else {
                enviarSistema(`⏳ Mestre rolou dados (valor oculto).`);
                enviarAcao({ acao: 'mestreRolou', dado, valor: null, autor: userLogin });
            }
            // after master action, decide: if master ends turn, advance to next turn
            // Provide UI to master to "Continuar" (we'll use proximoTurno when master confirms via prompt)
            const continuar = confirm('Deseja encerrar a vez do Mestre e passar para o próximo jogador?');
            if (continuar) proximoTurno();
            else {
                // master might re-enable player's controls for extra roll
                const permitir = confirm('Permitir que o jogador atual jogue novamente?');
                if (permitir) {
                    enviarAcao({ acao: 'permitirJogada', personagemId: currentPlayerId });
                    setPlayerControlsEnabled(true, currentPlayerId);
                }
            }
        });
    }

    // Master mode toggles
    if (btnDano) {
        btnDano.addEventListener('click', () => {
            if (!isMestre) { showToast('Apenas Mestre', 'warning'); return; }
            if (!modoDanoAtivo) ativarModoDano();
            else { desativarTodosOsModos(); enviarSistema('💥 Modo DANO desativado pelo Mestre.'); }
        });
    }

    if (btnCurar) {
        btnCurar.addEventListener('click', () => {
            if (!isMestre) { showToast('Apenas Mestre', 'warning'); return; }
            if (!modoCurarAtivo) ativarModoCura();
            else { desativarTodosOsModos(); enviarSistema('❤️ Modo CURA desativado pelo Mestre.'); }
        });
    }

    if (btnUpar) {
        btnUpar.addEventListener('click', () => {
            if (!isMestre) { showToast('Apenas Mestre', 'warning'); return; }
            if (!modoUparAtivo) ativarModoUp();
            else { desativarTodosOsModos(); enviarSistema('✨ Modo UP desativado pelo Mestre.'); }
        });
    }

    // Click on character cards (for master modes or for owners)
    if (personagensContainer) {
        personagensContainer.addEventListener('click', (e) => {
            const card = e.target.closest('.personagem-card');
            if (!card) return;

            // If master has a mode active, clicking triggers modal for damage/cura/up
            if (isMestre && (modoDanoAtivo || modoCurarAtivo || modoUparAtivo)) {
                personagemSelecionado = card;
                if (modoDanoAtivo) {
                    tipoValor = 'dano';
                    abrirModalValor('dano');
                } else if (modoCurarAtivo) {
                    tipoValor = 'cura';
                    abrirModalValor('cura');
                } else if (modoUparAtivo) {
                    tipoValor = 'up';
                    // For UP we might not need a numeric value, but we'll still use modal to confirm
                    abrirModalValor('up');
                }
                return;
            }

            // If it's the player owner's click during their turn, maybe open a quick info or allow local actions (not roll/dano)
            const atual = ordemTurnos[turnoIndex];
            const isOwnerOfClicked = String(card.dataset.usuarioId) === userId;
            if (rodadaAtiva && phase === 'player' && atual && String(atual.personagemId) === String(card.dataset.id) && isOwnerOfClicked) {
                // Player clicked on own card during their turn - we can allow local quick actions or show details
                // For simplicity, just flash feedback
                showToast(`É seu turno: ${card.dataset.nome}`, 'primary');
            }
        });
    }

    // Modal open helper
    function abrirModalValor(tipo) {
        if (!modalValorEl) return;
        const titulo = modalValorEl.querySelector('.modal-title');
        const btn = btnConfirmarValor;
        if (tipo === 'dano') {
            titulo.textContent = 'Aplicar Dano';
            if (btn) btn.className = 'btn btn-danger';
            inputValor.placeholder = 'Valor de dano (ex: 5)';
            inputValor.value = '';
        } else if (tipo === 'cura') {
            titulo.textContent = 'Aplicar Cura';
            if (btn) btn.className = 'btn btn-success';
            inputValor.placeholder = 'Valor de cura (ex: 5)';
            inputValor.value = '';
        } else if (tipo === 'up') {
            titulo.textContent = 'Upar Personagem (confirme)';
            if (btn) btn.className = 'btn btn-info';
            inputValor.placeholder = 'Confirmar aumento de nível';
            inputValor.value = '1';
        }
        modalValor?.show();
    }

    if (btnConfirmarValor) {
        btnConfirmarValor.addEventListener('click', () => {
            confirmarModalAplicarValor();
        });
    }

    // Chat send
    if (chatSend && chatInput) {
        chatSend.addEventListener('click', () => {
            const text = chatInput.value.trim();
            if (!text || !stompClient) return;
            const payload = { tipo: 'chat', conteudo: text, autor: userLogin, userId, salaId };
            stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify(payload));
            chatInput.value = '';
        });
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') chatSend.click();
        });
    }

    // ========== INIT: populate initial hp bars & connect ==========
    function initCards() {
        document.querySelectorAll('.personagem-card').forEach(card => {
            const vidaAtual = parseInt(card.dataset.vida ?? 0, 10);
            const vidaMax = getCardMaxHP(card);
            atualizarBarraVida(card, vidaAtual, vidaMax);
        });
    }
    // ====== Clique nas cartas (para mestre) ======
    document.querySelectorAll('.personagem-card').forEach(card => {
        card.addEventListener('click', () => {
            // Só aplica se algum modo estiver ativo
            if (modoDanoAtivo) tipoValor = 'dano';
            else if (modoCurarAtivo) tipoValor = 'cura';
            else if (modoUparAtivo) tipoValor = 'up';
            else return; // nenhum modo ativo

            personagemSelecionado = card;

            // Se for dano ou cura, abre modal para inserir valor
            if (tipoValor === 'dano' || tipoValor === 'cura') {
                modalValor?.show();
            } else if (tipoValor === 'up') {
                // aplica diretamente Up
                confirmarModalAplicarValor();
            }
        });
    });

    // Start socket and init
    connectSocket();
    initCards();

    // Expose some functions for debug in console (optional)
    window.roomManager = {
        enviarAcao,
        enviarSistema,
        iniciarRodada,
        proximoTurno,
        getEstado: () => ({ ordemTurnos, turnoIndex, rodadaAtiva, phase, currentPlayerId, isMestre })
    };

    debugLog('room-manager inicializado. isMestre=', isMestre, 'userId=', userId);
})();
