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
    let usuariosOn = []; // Lista de usuários online com { usuarioId, usuarioLogin }

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

    // Pegas as funções
    // gameFlow.js
    window.getCardById = getCardById;
    window.destacarPersonagem = destacarPersonagem;
    window.setPlayerControlsEnabled = setPlayerControlsEnabled;

    // turnUIManager.js
    window.mostrarDado = mostrarDado;
    window.atualizarTurnoUI = atualizarTurnoUI;
    window.handleVidaChange = handleVidaChange;
    window.ativarModoMestre = ativarModoMestre;

    // turnManager.js
    window.ordenarIniciativas = ordenarIniciativas;
    window.iniciarRodada = iniciarRodada;
    window.finalizarRodada = finalizarRodada;
    window.proximoTurno = proximoTurno;
    window.proximoPhaseDepoisDaAcaoDoJogador = proximoPhaseDepoisDaAcaoDoJogador;

    // personagensManager.js
    window.findPersonagensContainer = findPersonagensContainer;
    window.buildPersonagemCard = buildPersonagemCard;
    window.addOrUpdatePersonagem = addOrUpdatePersonagem;
    window.removePersonagem = removePersonagem;
    window.updateMembersListsAdd = updateMembersListsAdd;
    window.updateMembersListsRemove = updateMembersListsRemove;

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    // ====== WEBSOCKET INTEGRATION ======
    function setupWebSocket() {
        debugLog('⚙️ Iniciando integração WebSocket...');

        // Registra handlers para eventos do WebSocket
        document.addEventListener('stomp.connected', () => {
            debugLog('✅ WebSocket conectado!');
            ws.subscribe(channel, onReceiveAction);

            enviarAcao({
                acao: 'entrada',
                usuarioId: userId,
                salaId: salaId,
                userLogin: userLogin
            });
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
                    usuarioId: userId,
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

    function enviarSaida() {
        if (!salaId) return;
        enviarAcao({
            acao: 'saida',
            usuarioId: userId,
            salaId: salaId,
            userLogin: userLogin
        });
    }
    // ===== GERENCIAMENTO DE PRESENÇA =====
    function adicionarUsuarioOn(usuarioId, usuarioLogin) {
        const usuarioId_str = String(usuarioId);

        // Verifica se já existe
        if (usuariosOn.find(u => String(u.usuarioId) === usuarioId_str)) {
            return; // Já está na lista
        }

        // Adiciona à lista
        usuariosOn.push({ usuarioId: usuarioId_str, usuarioLogin });
        debugLog('🟢 Usuário online:', { usuarioId: usuarioId_str, usuarioLogin, total: usuariosOn.length });

        // Atualiza visual do card
        atualizarStatusPersonagem(usuarioId_str, true);
    }

    function removerUsuarioOn(usuarioId) {
        const usuarioId_str = String(usuarioId);
        const index = usuariosOn.findIndex(u => String(u.usuarioId) === usuarioId_str);

        if (index !== -1) {
            const usuario = usuariosOn[index];
            usuariosOn.splice(index, 1);
            debugLog('🔴 Usuário offline:', { usuarioId: usuarioId_str, usuario, total: usuariosOn.length });

            // Atualiza visual do card
            atualizarStatusPersonagem(usuarioId_str, false);
        }
    }

    function atualizarStatusPersonagem(usuarioId, isOnline) {
        const usuarioId_str = String(usuarioId);

        // Encontra o(s) personagem(s) deste usuário e atualiza o indicador
        const cards = document.querySelectorAll('.personagem-card');
        cards.forEach(card => {
            if (String(card.dataset.usuarioId) === usuarioId_str) {
                // Update dataset
                card.dataset.online = isOnline ? 'true' : 'false';

                // Prefer helper if available
                if (typeof window.setPersonagemOnline === 'function') {
                    const pid = card.dataset.id || card.dataset.cardId;
                    if (pid) window.setPersonagemOnline(pid, !!isOnline);
                    return;
                }

                // Fallback: update dot directly
                const dot = card.querySelector('[data-online-dot]') || card.querySelector('.status-online-indicator');
                if (dot) {
                    dot.classList.toggle('online', !!isOnline);
                    dot.classList.toggle('offline', !isOnline);
                    dot.title = isOnline ? 'Online' : 'Offline';
                }

                // Also update offcanvas list if present (by personagem id)
                const pid = card.dataset.id || card.dataset.cardId;
                if (pid) {
                    const li = document.querySelector(`#lista-membros [data-personagem-id="${pid}"]`);
                    if (li) {
                        const listDot = li.querySelector('.members-list-dot');
                        if (listDot) {
                            listDot.classList.toggle('online', !!isOnline);
                            listDot.classList.toggle('offline', !isOnline);
                        }
                    }
                }
            }
        });
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

            // Entrou na sala
            const entrou = msg.match(/🟢\s*(.+?)\s+entrou na sala/i);
            if (entrou && data.usuarioId) {
                const usuarioLogin = entrou[1];
                adicionarUsuarioOn(data.usuarioId, usuarioLogin);
                debugLog('✅ Capturado entrada:', { usuarioId: data.usuarioId, usuarioLogin });
            }

            // Saiu da sala
            const saiu = msg.match(/🔴\s*(.+?)\s+saiu da sala/i);
            if (saiu && data.usuarioId) {
                removerUsuarioOn(data.usuarioId);
                debugLog('✅ Capturada saída:', { usuarioId: data.usuarioId });
            }
            return;
        }

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

        }
    }

    // ========== INIT ==========
    function init() {
        debugLog('🎲 room-manager v4.0 iniciando...');

        // Configura WebSocket
        setupWebSocket();

        // Inicializa presença do usuário atual
        verificarEInicializarPresenca();

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
