/* room-manager.js
   Version: 4.0 - Unified WebSocket System
   Requirements:
   - webSocketService.js loaded
   - Bootstrap
   - window.CHAT_CONFIG = { userId, userLogin, salaId, wsUrl, isMestre, role }
   - Revisar codigo js do chat-room.js
   - Separar funcoes de mestre e player
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
    let timeoutLimpezaDado = null; // Para controlar timeout de limpeza do dado
    let ocultarDadosAtivo = false; // Flag para ocultar valores dos dados

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

// Start with controls hidden/disabled until it's the player's turn
if (turnControls) turnControls.classList.add('d-none');
if (diceOptions) diceOptions.classList.add('d-none');
if (btnRoll) btnRoll.disabled = true;
if (btnSkip) btnSkip.disabled = true;

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    // ====== WEBSOCKET INTEGRATION ======
    function setupWebSocket() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Registra handlers para eventos do WebSocket
        document.addEventListener('stomp.connected', () => {
            debugLog('✅ WebSocket conectado!');
            ws.subscribe(channel, onReceiveAction);
            enviarSistema(`🟢 ${userLogin} entrou na sala`);
        });

        // Eventos de erro e desconexão
        document.addEventListener('stomp.error', (event) => {
            debugLog('❌ Erro de conexão:', event.detail?.error);
        });

        // Evento de desconexão
        document.addEventListener('stomp.disconnected', () => {
            debugLog('🔴 WebSocket desconectado');
        });

        // Se o WebSocket já estiver conectado, inscreve imediatamente
        const status = ws.getStatus();
        if (status.isConnected) {
            ws.subscribe(channel, onReceiveAction);
            enviarSistema(`🟢 ${userLogin} entrou na sala`);
        }
    }

    // ===== MESSAGE SENDING ======
    function enviarSistema(msg) {
        if (!salaId) {
            debugLog('❌ Sem salaId definido');
            return;
        }

        // Verifica conexão
        const status = ws.getStatus();
        if (!status.isConnected) {
            // Aguarda conexão antes de enviar
            debugLog('⚠️ WebSocket não conectado, aguardando conexão...');
            // Usa { once: true } para evitar múltiplos listeners
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

        // Envia mensagem de sistema
        ws.send('/app/enviar/' + salaId, {
            tipo: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
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
                    tipo: 'acao',
                    salaId,
                    timestamp: Date.now(),
                    ...obj
                });
            }, { once: true });
            return;
        }

        // Envia ação
        ws.send('/app/enviar/' + salaId, {
            tipo: 'acao',
            salaId,
            timestamp: Date.now(),
            ...obj
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

        // Mestre nunca usa os controles de jogador
        if (isMestre) {
            if (turnControls) turnControls.classList.add('d-none');
            if (diceOptions) diceOptions.classList.add('d-none');
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
            return;
        }

        // Para jogadores: por padrão escondemos/desabilitamos controles
        if (!turnControls) return;

        if (souDono && enabled) {
            // Mostrar controles apenas para o dono do personagem atual quando habilitado
            turnControls.classList.remove('d-none');
            if (diceOptions) diceOptions.classList.remove('d-none');
            if (btnRoll) btnRoll.disabled = false;
            if (btnSkip) btnSkip.disabled = false;
        } else {
            // Esconder/desabilitar para outros jogadores
            turnControls.classList.add('d-none');
            if (diceOptions) diceOptions.classList.add('d-none');
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
        }
    }

    // ====== DYNAMIC MEMBERS MANAGEMENT ======
    function findPersonagensContainer() {
        // Prioriza a coluna direita explícita `#coluna-personangens`, depois fallback para games-section
        return document.getElementById('coluna-personangens') || document.querySelector('#games-section') || document.body;
    }

    function buildPersonagemCard(member) {
        const id = String(member.personagemId ?? member.id ?? '');
        const usuarioId = String(member.usuarioId ?? member.userId ?? '');
        const nome = member.nome ?? member.userLogin ?? 'Jogador';
        const vida = member.vida ?? member.hp ?? 0;
        const vidaMax = member.vidaMax ?? vida ?? 0;
        const iniciativa = member.iniciativa ?? 0;

        const card = document.createElement('div');
        card.className = 'bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card';
        card.style.cursor = 'pointer';

        card.setAttribute('data-bs-toggle', 'collapse');
        card.setAttribute('data-bs-target', `#info-personagem-${id}`);
        card.setAttribute('aria-expanded', 'false');
        card.setAttribute('aria-controls', `info-personagem-${id}`);

        card.dataset.cardId = id;
        card.dataset.id = id;
        card.dataset.vidaMax = vidaMax;
        card.dataset.nome = nome;
        card.dataset.vida = vida;
        card.dataset.usuarioId = usuarioId;
        card.dataset.iniciativa = iniciativa;

        card.innerHTML = `
            <strong class="small">${nome}</strong>
            <div class="progress mt-1 w-100" style="height: 14px; font-size:0.7rem;">
                <div class="progress-bar bg-success d-flex justify-content-center align-items-center" role="progressbar" style="width: ${(vidaMax? (vida/vidaMax*100):0)}%;">${vida}/${vidaMax}</div>
            </div>
            <div id="info-personagem-${id}" class="collapse mt-1" style="min-height: auto; max-height: 25vh; overflow: hidden;">
                <div class="bg-dark rounded p-1 text-start text-light" style="font-size: 0.7rem;">
                    <strong>Jogador:</strong> ${nome}<br>
                </div>
            </div>
        `;

        return card;
    }

    function addOrUpdatePersonagem(member) {
        if (!member) return;
        const id = String(member.personagemId ?? member.id ?? '');

        const existing = getCardById(id);
        if (existing) {
            // atualiza atributos
            if (member.nome) existing.dataset.nome = member.nome;
            if (member.vida !== undefined) {
                existing.dataset.vida = member.vida;
                const bar = existing.querySelector('.progress-bar');
                const vidaMax = parseInt(existing.dataset.vidaMax || member.vidaMax || member.vida || 0, 10) || 0;
                if (bar) {
                    bar.style.width = `${vidaMax ? (member.vida / vidaMax) * 100 : 0}%`;
                    bar.textContent = `${member.vida}/${vidaMax}`;
                }
            }
            return;
        }

        const container = findPersonagensContainer();
        const card = buildPersonagemCard(member);

        // Insere no container dedicado `#coluna-personangens` quando presente
        const desktopWrapper = document.getElementById('coluna-personangens') || container;
        if (desktopWrapper) {
            desktopWrapper.appendChild(card);
        }

        // Atualiza listas mobile/offcanvas
        updateMembersListsAdd(member);
    }

    function removePersonagem(personagemId) {
        if (!personagemId) return;
        const id = String(personagemId);
        const card = getCardById(id);
        if (card && card.parentNode) card.parentNode.removeChild(card);

        // Remove collapse element if exists
        const collapseEl = document.getElementById(`info-personagem-${id}`);
        if (collapseEl && collapseEl.parentNode) collapseEl.parentNode.removeChild(collapseEl);

        // Atualiza mobile/offcanvas
        updateMembersListsRemove(id);
    }

    function updateMembersListsAdd(member) {
        // Offcanvas members list
        const lista = document.getElementById('lista-membros');
        if (lista) {
            const exists = lista.querySelector(`[data-personagem-id="${member.personagemId}"]`);
            if (!exists) {
                const li = document.createElement('li');
                li.className = 'list-group-item bg-dark text-light d-flex justify-content-between align-items-center';
                li.dataset.personagemId = member.personagemId;
                li.innerHTML = `${member.usuarioLogin ?? member.nome ?? 'Jogador'}<span><i class="fa-solid fa-circle text-success"></i></span>`;
                lista.appendChild(li);
            }
        }

        // Mobile players list
        const mobilePlayers = document.querySelector('#mobile-players .d-flex') || document.querySelector('#mobile-players');
        if (mobilePlayers) {
            // mobile section has multiple child items; we append a simple card
            const existing = document.querySelector(`#mobile-players [data-personagem-id="${member.personagemId}"]`);
            if (!existing) {
                const el = document.createElement('div');
                el.className = 'bg-dark rounded p-2 text-white text-center';
                el.dataset.personagemId = member.personagemId;
                el.innerHTML = `<strong class="small">${member.nome ?? member.usuarioLogin ?? 'Jogador'}</strong><div class="progress mt-1" style="height: 12px;"><div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div></div>`;
                const target = document.querySelector('#mobile-players .d-flex') || document.querySelector('#mobile-players');
                target.appendChild(el);
            }
        }
    }

    function updateMembersListsRemove(personagemId) {
        const id = String(personagemId);
        const lista = document.getElementById('lista-membros');
        if (lista) {
            const li = lista.querySelector(`[data-personagem-id="${id}"]`);
            if (li && li.parentNode) li.parentNode.removeChild(li);
        }

        const mobileEl = document.querySelector(`#mobile-players [data-personagem-id="${id}"]`);
        if (mobileEl && mobileEl.parentNode) mobileEl.parentNode.removeChild(mobileEl);
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
            if (lista[i].iniciativa === lista[i + 1].iniciativa && Math.random() < 0.5) {
                [lista[i], lista[i + 1]] = [lista[i + 1], lista[i]];
            }
        }

        return lista;
    }

    function iniciarRodada() {
        debugLog('🎲 Tentando iniciar rodada...');
        const cards = Array.from(document.querySelectorAll('.personagem-card'));
        if (cards.length === 0) {
            debugLog('⚠️ Nenhum personagem encontrado');
            return;
        }

        ordemTurnos = ordenarIniciativas(cards);
        turnoIndex = 0;
        rodadaAtiva = true;
        phase = 'player';
        rodada = rodada || 1;

        const primeiro = ordemTurnos[turnoIndex];
        currentPlayerId = primeiro.personagemId;
        destacarPersonagem(primeiro.card);

        debugLog('✅ Rodada iniciada:', { currentPlayerId, phase, rodada });
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

        // Limpa timeout do dado anterior antes de trocar de turno
        if (timeoutLimpezaDado) {
            clearTimeout(timeoutLimpezaDado);
            timeoutLimpezaDado = null;
        }

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

    // ===== MOSTRA DADO COM TIMEOUT DE LIMPEZA =====
    function mostrarDado(dado, valor, autor, ocultar = false) {
        // Limpa timeout anterior se existir
        if (timeoutLimpezaDado) clearTimeout(timeoutLimpezaDado);

        if (placeholder) {
            if (ocultar) {
                // Se for mestre ocultando, mostra apenas mensagem genérica
                placeholder.textContent = `🎲 ${autor} está rolando...`;
            } else {
                placeholder.textContent = `🎲 ${autor} rolou D${dado} = ${valor}`;
            }
        }

        // Limpa o dado após 4 segundos
        timeoutLimpezaDado = setTimeout(() => {
            if (placeholder && rodadaAtiva) {
                const atual = ordemTurnos[turnoIndex];
                if (atual) {
                    placeholder.textContent = `🕒 Turno de ${atual.nome}`;
                    const isMyTurn = String(atual.usuarioId) === userId;
                    if (isMyTurn) placeholder.textContent += ' (Sua vez!)';
                } else {
                    placeholder.textContent = '🎲 Aguardando...';
                }
            }
            timeoutLimpezaDado = null;
        }, 4000);
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

        debugLog('🔄 Atualizando botões do mestre');

        const btnInicio = document.getElementById('btnIniciarTurno');
        const btnMestre = document.getElementById('btn-lancar-mestre');
        const btnPermitir = document.getElementById('btn-permitir-jogada');
        const iconInicio = btnInicio?.querySelector('i.fa-solid');
        const btnDano = document.getElementById('btn-dano');
        const btnCura = document.getElementById('btn-curar');
        const btnUpar = document.getElementById('btn-upar');

        if (!btnInicio) {
            debugLog('⚠️ Botão iniciar não encontrado');
            return;
        }

        debugLog('Estado atual:', { rodadaAtiva, phase });

        // Ativar/desativar botões baseado no estado
        if (btnMestre) btnMestre.disabled = !rodadaAtiva;
        if (btnDano) btnDano.disabled = !rodadaAtiva;
        if (btnCura) btnCura.disabled = !rodadaAtiva;
        if (btnUpar) btnUpar.disabled = !rodadaAtiva;

        if (!rodadaAtiva) {
            btnInicio.disabled = false;
            btnInicio.classList.remove('btn-outline-secondary');
            btnInicio.classList.add('btn-outline-success');
            iconInicio.classList.remove('fa-pause', 'fa-forward');
            iconInicio.classList.add('fa-play');
            if (btnPermitir) btnPermitir.disabled = true;
            return;
        }

        if (phase === 'master') {
            btnInicio.disabled = false;
            btnInicio.classList.remove('btn-outline-secondary');
            btnInicio.classList.add('btn-outline-success');
            iconInicio.classList.remove('fa-pause', 'fa-play');
            iconInicio.classList.add('fa-forward');
            if (btnPermitir) btnPermitir.disabled = false;

            // Habilitar ações do mestre
            if (btnMestre) btnMestre.disabled = false;
            if (btnDano) btnDano.disabled = false;
            if (btnCura) btnCura.disabled = false;
            if (btnUpar) btnUpar.disabled = false;
            return;
        }

        if (phase === 'player') {
            btnInicio.disabled = true;
            btnInicio.classList.remove('btn-outline-success');
            btnInicio.classList.add('btn-outline-secondary');
            iconInicio.classList.remove('fa-play', 'fa-forward');
            iconInicio.classList.add('fa-pause');
            if (btnPermitir) btnPermitir.disabled = false;
        }
    }

    function handleVidaChange(personagemId, valor, tipo) {
        if (!isMestre || !rodadaAtiva) return;

        debugLog('🩺 Alterando vida:', { personagemId, valor, tipo });

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
            // Restaurar atributos de collapse removidos e limpar estado visual
            document.querySelectorAll('.personagem-card').forEach(c => {
                if (c.dataset.prevToggle) {
                    c.setAttribute('data-bs-toggle', c.dataset.prevToggle);
                    if (c.dataset.prevTarget) c.setAttribute('data-bs-target', c.dataset.prevTarget);
                    if (c.dataset.prevExpanded) c.setAttribute('aria-expanded', c.dataset.prevExpanded);
                    if (c.dataset.prevAriaControls) c.setAttribute('aria-controls', c.dataset.prevAriaControls);
                    delete c.dataset.prevToggle;
                    delete c.dataset.prevTarget;
                    delete c.dataset.prevExpanded;
                    delete c.dataset.prevAriaControls;
                }
                c.style.cursor = '';
                c.classList.remove('border-primary', 'border-3', 'border-info');
            });

            // Reabrir os collapses que estavam abertos antes
            document.querySelectorAll('.collapse').forEach(el => {
                if (el.dataset.prevShown === 'true') {
                    try {
                        const inst = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                        inst.show();
                        delete el.dataset.prevShown;
                    } catch (err) {
                        // ignore
                    }
                }
            });

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
        // Fecha todos os collapses abertos e registra quais estavam abertos
        document.querySelectorAll('.collapse.show').forEach(el => {
            if (el.id && el.id.startsWith('info-personagem-')) {
                try {
                    const inst = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                    // marca que estava aberto
                    el.dataset.prevShown = 'true';
                    inst.hide();
                } catch (err) {
                    // ignore
                }
            }
        });

        cards.forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

            // Remove atributos de collapse temporariamente para evitar abrir detalhes enquanto mestre está em modo
            if (!c.dataset.prevToggle) {
                const prevToggle = c.getAttribute('data-bs-toggle');
                if (prevToggle) {
                    c.dataset.prevToggle = prevToggle;
                    c.dataset.prevTarget = c.getAttribute('data-bs-target') || '';
                    c.dataset.prevExpanded = c.getAttribute('aria-expanded') || 'false';
                    c.dataset.prevAriaControls = c.getAttribute('aria-controls') || '';
                    c.removeAttribute('data-bs-toggle');
                    c.removeAttribute('data-bs-target');
                    c.removeAttribute('aria-expanded');
                    c.removeAttribute('aria-controls');
                }
            }

            if (!isCurrentPlayer) {
                c.classList.add('border-primary', 'border-3');
                c.style.cursor = 'pointer';
            } else {
                // Destacado — permite ação também
                c.classList.add('border-info');
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

        // Faz o dado rolar localmente
        // if (typeof window.funcaoChamarDados === 'function') {
        //     window.funcaoChamarDados(sides, valor);
        // }

        if (isMestre) {
            debugLog('🎲 Mestre rolando dados', { ocultarDadosAtivo });
            if (ocultarDadosAtivo) {
                // Mestre está ocultando dados - envia mensagem genérica
                enviarSistema(`🎲 Mestre está rolando dados...`);
                mostrarDado(sides, valor, 'Mestre', true); // true = ocultar valor
            } else {
                // Mostra valor normalmente
                enviarSistema(`🎲 Mestre rolou D${sides} = ${valor}`);
                mostrarDado(sides, valor, 'Mestre', false);
            }
            // ⚠️ Mestre NÃO pula turno ao rolar dados - pode rolar múltiplos dados
            // Apenas mostra animação, sem enviar 'playerActionDone'
            if (window.funcaoChamarDados) {
                setTimeout(() => {
                    window.funcaoChamarDados(sides, valor);
                }, 300);
            }
        } else {
            if (phase !== 'player') {
                debugLog('⚠️ Não é a fase do jogador');
                return;
            }
            debugLog('🎲 Jogador rolando dados');
            enviarSistema(`🎲 ${atual.nome} rolou D${sides} = ${valor}`);
            mostrarDado(sides, valor, atual.nome);
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
        if (!data) return;
        debugLog('📥 Ação recebida:', data);

        // Handle system messages like: "🟢 <userLogin> entrou na sala" or "<user> saiu da sala"
        if (data.tipo === 'sistema' && typeof data.conteudo === 'string') {
            const msg = data.conteudo;

            // Entrou na sala (uses the same format used by enviarSistema)
            const entrou = msg.match(/🟢\s*(.+?)\s+entrou na sala/i);
            if (entrou) {
                const login = entrou[1].trim();
                // Cria um membro mínimo a partir do login (servidor não enviou dados completos)
                const member = {
                    personagemId: `p_${login}`,
                    usuarioId: `u_${login}`,
                    nome: login,
                    usuarioLogin: login,
                    vida: 0,
                    vidaMax: 0
                };
                addOrUpdatePersonagem(member);
                debugLog('➕ Sistema: jogador entrou, adicionando membro baseado em sistema:', login);
                return;
            }

            // Saiu da sala
            const saiu = msg.match(/(🔴|🟥|⚪|⛔)?\s*(.+?)\s+(saiu da sala|saiu)/i);
            if (saiu) {
                const login = saiu[2].trim();
                // tenta localizar card pelo nome/usuarioLogin e remover
                const cards = Array.from(document.querySelectorAll('.personagem-card'));
                const found = cards.find(c => String(c.dataset.nome) === login || String(c.dataset.usuarioLogin) === login || c.textContent.includes(login));
                if (found) {
                    const pid = found.dataset.id || found.dataset.cardId || found.dataset.personagemId;
                    removePersonagem(pid);
                    debugLog('➖ Sistema: jogador saiu, removendo membro baseado em sistema:', login, pid);
                }
                return;
            }
        }

        const card = data.personagemId ? getCardById(String(data.personagemId)) : null;

        switch (data.acao) {
            case 'playerJoined':
                // Espera-se que o servidor envie um objeto 'member' ou 'personagem'
                const member = data.member || data.personagem || data.user;
                if (member) {
                    addOrUpdatePersonagem(member);
                    debugLog('➕ Player joined, updated list:', member);
                }
                break;

            case 'playerLeft':
                // Espera-se que o servidor envie 'personagemId' ou 'id'
                const pidLeft = data.personagemId || data.id || (data.personagem && data.personagem.id);
                if (pidLeft) {
                    removePersonagem(pidLeft);
                    // Também remover da ordem de turnos se presente
                    ordemTurnos = ordemTurnos.filter(p => String(p.personagemId) !== String(pidLeft));
                    debugLog('➖ Player left, removed:', pidLeft);
                }
                break;

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
                if (isMyTurn) {
                    debugLog('É minha vez!', { phase });
                    setPlayerControlsEnabled(true, data.personagemId);
                } else {
                    setPlayerControlsEnabled(false, data.personagemId);
                }
                atualizarTurnoUI();
                break;

            case 'playerActionDone':
                if (data.dado && data.valor) {
                    ultimoDadoRolado = {
                        dado: data.dado,
                        valor: data.valor,
                        autor: data.autor
                    };

                    // Mostra o dado no placeholder com timeout
                    mostrarDado(data.dado, data.valor, data.autor);

                    // Todos veem o dado rolar se a função existir
                    if (window.funcaoChamarDados) {
                        // Pequeno delay pra sincronizar visualmente
                        setTimeout(() => {
                            window.funcaoChamarDados(data.dado, data.valor);
                        }, 300);
                    }
                }

                rodadaAtiva = true;
                phase = 'master';
                currentPlayerId = String(data.personagemId);

                if (card) {
                    destacarPersonagem(card);
                }
                setPlayerControlsEnabled(false, data.personagemId);

                debugLog('🔄 Ação do jogador recebida:', { phase, currentPlayerId });

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

            case 'jogadaExtra':
                if (!card) return;
                debugLog('🎯 Recebido evento jogadaExtra:', { cardUserId: card.dataset.usuarioId, myId: userId });
                if (String(card.dataset.usuarioId) === userId) {
                    phase = 'player';
                    setPlayerControlsEnabled(true, data.personagemId);
                    turnControls.classList.remove('d-none');
                    atualizarTurnoUI();
                    debugLog('✅ Jogada extra liberada para o jogador');
                }
                break;
            case 'playerPassed':
                // Pode ser usado para outras lógicas se necessário
                break;
            case 'upgradeReceived':
                if (String(data.usuarioId) === userId) {
                    debugLog('🎉 Você recebeu um upgrade!');
                    const collapse = document.getElementById('collapseAtributos');
                    collapse.classList.add('show');

                    collapse.querySelectorAll('.btn-up-atributo').forEach(btn => btn.remove());

                    const atributos = collapse.querySelectorAll('.bg-dark'); // Seleciona cada div que representa um atributo
                    let pontosDisponiveis = 5; // Número total de pontos que o jogador pode distribuir
                    let pontosDistribuidos = {};

                    // Contém texto de pontos disponíveis + botões Reset e Salvar
                    const headerContainer = document.createElement('div');
                    headerContainer.className = 'd-flex justify-content-center align-items-center gap-2 mt-2 mb-3';

                    // Texto de pontos disponíveis
                    const spanPontos = document.createElement('div');
                    spanPontos.className = 'text-warning';
                    spanPontos.textContent = `Pontos disponíveis: ${pontosDisponiveis}`;
                    headerContainer.appendChild(spanPontos);

                    // Botão para resetar distribuição
                    const btnReset = document.createElement('button');
                    btnReset.className = 'btn btn-sm btn-outline-warning';
                    btnReset.textContent = 'Resetar Distribuição';
                    btnReset.disabled = true; // Desabilitado inicialmente, será habilitado quando houver pontos distribuídos
                    headerContainer.appendChild(btnReset);

                    // Botão de salvar (com ícone fa-save)
                    const btnSalvar = document.createElement('button');
                    btnSalvar.className = 'btn btn-sm btn-outline-success';
                    btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar';
                    headerContainer.appendChild(btnSalvar);

                    // Insere o header no topo do collapse, antes do primeiro filho da card-body
                    collapse.querySelector('.card-body').insertBefore(
                        headerContainer,
                        collapse.querySelector('.card-body').firstChild
                    );

                    function atualizarInterface() {
                        // Atualiza o texto de pontos disponíveis
                        spanPontos.textContent = `Pontos disponíveis: ${pontosDisponiveis}`;
                        // Habilita/desabilita botão Reset dependendo se há pontos distribuídos
                        btnReset.disabled = Object.values(pontosDistribuidos).every(v => v === 0);

                        // Habilita/desabilita botões + e - baseado nos pontos
                        atributos.forEach(div => {
                            const nome = div.dataset.nome; // Nome do atributo (forca, agilidade, etc.)
                            const btnMenos = div.querySelector('.btn-outline-danger');
                            const btnMais = div.querySelector('.btn-outline-info');

                            if (btnMenos) btnMenos.disabled = !pontosDistribuidos[nome];
                            if (btnMais) btnMais.disabled = pontosDisponiveis <= 0;
                            const adicional = pontosDistribuidos[nome] || 0; // Pontos distribuídos nesse atributo
                            // Procura span existente para mostrar pontos adicionais ou cria novo
                            const spanAdicional = div.querySelector('.text-info') || div.querySelector('span') || document.createElement('span');
                            spanAdicional.className = 'text-info ms-1';
                            spanAdicional.textContent = adicional > 0 ? ` (+${adicional})` : '';
                            spanAdicional.style.display = adicional > 0 ? 'inline' : 'none';

                            // Garante que o span está no DOM
                            div.appendChild(spanAdicional);

                            // Envia atualização para o backend/WebSocket em tempo real
                            enviarAcao({
                                acao: 'atributoUpado',
                                personagemId: data.personagemId,
                                atributo: nome.toLowerCase(),
                                valorBase: parseInt(div.dataset.valorBase, 10),
                                adicional: adicional
                            });
                        });
                    }

                    // ===================== BOTÕES + E - PARA CADA ATRIBUTO =====================
                    atributos.forEach(div => {
                        const nome = div.dataset.nome; // Nome do atributo
                        const valorBase = parseInt(div.dataset.valorBase, 10); // Valor base do atributo
                        pontosDistribuidos[nome] = 0; // Inicializa contador de pontos distribuídos

                        // Container para os botões + e -
                        const botoesContainer = document.createElement('div');
                        botoesContainer.className = 'ms-2 d-flex gap-1 align-items-center';

                        // Botão de remover ponto
                        const btnMenos = document.createElement('button');
                        btnMenos.className = 'btn btn-sm btn-outline-danger btn-up-atributo';
                        btnMenos.innerHTML = '<i class="fa-solid fa-minus"></i>';
                        btnMenos.disabled = true; // Desabilitado inicialmente, pois não há pontos distribuídos

                        // Botão de adicionar ponto
                        const btnMais = document.createElement('button');
                        btnMais.className = 'btn btn-sm btn-outline-info btn-up-atributo';
                        btnMais.innerHTML = '<i class="fa-solid fa-plus"></i>';

                        // Funções de clique
                        btnMais.onclick = () => {
                            if (pontosDisponiveis > 0) {
                                pontosDistribuidos[nome]++;
                                pontosDisponiveis--;
                                atualizarInterface(); // Atualiza UI e envia WS
                            }
                        };
                        btnMenos.onclick = () => {
                            if (pontosDistribuidos[nome] > 0) {
                                pontosDistribuidos[nome]--;
                                pontosDisponiveis++;
                                atualizarInterface(); // Atualiza UI e envia WS
                            }
                        };

                        // Adiciona os botões ao container
                        botoesContainer.appendChild(btnMenos);
                        botoesContainer.appendChild(btnMais);

                        // Adiciona o container dentro da div do atributo
                        div.appendChild(botoesContainer);
                    });

                    // ===================== EVENTOS DO HEADER =====================
                    // Resetar todos os pontos
                    btnReset.onclick = () => {
                        Object.keys(pontosDistribuidos).forEach(attr => pontosDistribuidos[attr] = 0);
                        pontosDisponiveis = 5;
                        atualizarInterface();
                    };

                    // Botão salvar: aqui você implementaria o envio dos dados para o backend
                    btnSalvar.onclick = () => {
                        if (pontosDisponiveis > 0) {
                            modalShow('Você ainda tem pontos disponíveis para distribuir!');
                            return;
                        }

                        console.log('💾 Salvando atributos...', pontosDistribuidos);
                        /*
                            Exemplo de payload para enviar ao backend:
                            {
                                "forca": x,
                                "agilidade": x,
                                "inteligencia": x,
                                "destreza": x,
                                "vitalidade": x,
                                "percepcao": x,
                                "sabedoria": x,
                                "carisma": x
                            }
                        */

                        // Desativar modo de upgrade
                        ativarModoMestre(null);
                    };
                }
                break;
            case 'receiveDice':
                // Pode ser usado para mostrar histórico de dados rolados
                /*
                * const verificar = autor.id === userId ? 'Você' : data.autor.nome;
                * if(!veiricar) return;
                * else {
                *   debugLog('🎲 Dado rolado recebido:', data);
                *   function rodarDado(dado, valor) {
                *      // Animação ou lógica para mostrar o dado rolando pra todos
                *   }
                * }
                * enviarSistema(`🎲 ${verificar} rolou D${data.dado} = ${data.valor}`);
                */
                break;

        }
    }

    // ========== INIT ==========
    function init() {
        debugLog('🎲 room-manager v4.0 iniciando...');

        // Configura WebSocket
        setupWebSocket();

        // Registra listeners de interface
        if (btnRoll) {
            debugLog('✅ Registrando listener para btnRoll');
            btnRoll.addEventListener('click', () => {
                if (!rodadaAtiva) {
                    debugLog('⚠️ Não há rodada ativa para lançar dados');
                    return;
                }
                diceOptions.classList.remove('d-none');
            });
        }

        if (btnIniciar) {
            debugLog('✅ Registrando listener para btnIniciar');
            btnIniciar.addEventListener('click', () => {
                debugLog('🎯 Botão iniciar clicado');
                if (!isMestre) {
                    debugLog('Não é mestre, ignorando clique');
                    return;
                }
                if (!rodadaAtiva) {
                    debugLog('Iniciando nova rodada');
                    iniciarRodada();
                } else if (phase === 'master') {
                    debugLog('Avançando para próximo turno');
                    proximoTurno();
                }
            });
        }

        if (diceOptions) {
            debugLog('✅ Registrando listener para diceOptions');
            diceOptions.addEventListener('click', onDiceButtonClick);
        }

        if (btnSkip) {
            debugLog('✅ Registrando listener para btnSkip');
            btnSkip.addEventListener('click', () => {
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
        }

        // Configura botões do mestre
        const btnDano = document.getElementById('btn-dano');
        if (btnDano) {
            debugLog('✅ Registrando listener para btnDano');
            btnDano.addEventListener('click', () => ativarModoMestre('dano'));
        }

        const btnCurar = document.getElementById('btn-curar');
        if (btnCurar) {
            debugLog('✅ Registrando listener para btnCurar');
            btnCurar.addEventListener('click', () => ativarModoMestre('cura'));
        }

        const btnUpar = document.getElementById('btn-upar');
        if (btnUpar) {
            debugLog('✅ Registrando listener para btnUpar');
            btnUpar.addEventListener('click', () => ativarModoMestre('up'));
        }

        if (btnLancarMestre) {
            debugLog('✅ Registrando listener para btnLancarMestre');
            btnLancarMestre.addEventListener('click', () => {
                if (!rodadaAtiva) {
                    debugLog('⚠️ Não há rodada ativa');
                    return;
                }
                debugLog('🎲 Mestre clicou para lançar dados');
                diceOptions.classList.remove('d-none');
                turnControls.classList.remove('d-none'); // Mostrar container de dados
            });
        }

        const btnPermitir = document.getElementById('btn-permitir-jogada');
        if (btnPermitir) {
            debugLog('✅ Registrando listener para btnPermitir');
            btnPermitir.addEventListener('click', () => {
                if (!rodadaAtiva || !isMestre) {
                    debugLog('⚠️ Não pode permitir jogada extra agora');
                    return;
                }

                const jogadorAtual = ordemTurnos[turnoIndex];
                if (!jogadorAtual) return;

                phase = 'player';
                enviarSistema(`🎯 Mestre permitiu uma jogada extra para ${jogadorAtual.nome}`);
                enviarAcao({
                    acao: 'jogadaExtra',
                    personagemId: jogadorAtual.personagemId,
                    autor: 'Mestre'
                });
                setPlayerControlsEnabled(true, jogadorAtual.personagemId);
                atualizarTurnoUI();
            });
        }

        // Registra listener para checkbox de ocultar dados
        const chkOcultarDados = document.getElementById('ocultarDados');
        if (chkOcultarDados) {
            debugLog('✅ Registrando listener para ocultarDados');
            chkOcultarDados.addEventListener('change', (e) => {
                ocultarDadosAtivo = e.target.checked;
                debugLog('🔒 Ocultar dados:', ocultarDadosAtivo);
                if (ocultarDadosAtivo) {
                    enviarSistema('🔒 Mestre está ocultando os valores dos dados');
                } else {
                    enviarSistema('🔓 Mestre está mostrando os valores dos dados');
                }
            });
        }

        // Registra click nos cards para ações do mestre
        personagensContainer.addEventListener('click', (event) => {
            const card = event.target.closest('.personagem-card');
            if (!card) return;

            // Se for o mestre e tiver um modo ativo, processa a ação do mestre
            if (isMestre && modoMestre && rodadaAtiva) {
                const personagemId = card.dataset.id;
                // Note: allow acting on the currently highlighted character as well

            if (modoMestre === 'dano' || modoMestre === 'cura') {
                const modal = document.getElementById('modalValor');
                if (!modal) return;

                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();

                const btnConfirmar = document.getElementById('btnConfirmarValor');
                const inputValor = document.getElementById('inputValor');

                const onConfirmar = () => {
                    const valor = parseInt(inputValor.value, 10);
                    if (isNaN(valor) || valor < 0) return;
                    handleVidaChange(personagemId, valor, modoMestre);
                    modalInstance.hide();
                    // Após aplicar a ação, desativar o modo do mestre
                    ativarModoMestre(modoMestre);
                };

                btnConfirmar.addEventListener('click', onConfirmar, { once: true });
                inputValor.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        onConfirmar();
                    }
                }, { once: true });
                }
            }

            // Se não for ação do mestre, abre o collapse do card
            if (!isMestre || !modoMestre) {
                const collapseId = `info-personagem-${card.dataset.id}`;
                const collapseEl = document.getElementById(collapseId);
                if (collapseEl) {
                    const collapse = new bootstrap.Collapse(collapseEl);
                    collapse.toggle();
                }
            }
        });

        debugLog('✅ Inicialização concluída');
    }

    // Inicia quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
