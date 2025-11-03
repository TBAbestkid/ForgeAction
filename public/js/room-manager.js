/* room-manager.js
   Version: 2.0
   Requirements in the page:
   - chat-room.js carregado antes
   - Bootstrap
   - window.CHAT_CONFIG = { userId, userLogin, salaId, wsUrl, isMestre, role }
   - personagem-card elements
*/
(function () {
    // ====== CONFIG / STATE ======
    const CHAT = window.CHAT_CONFIG || {};
    const userId = String(CHAT.userId ?? '');
    const userLogin = CHAT.userLogin ?? 'Player';
    const salaId = CHAT.salaId ?? null;
    const isMestre = !!(CHAT.isMestre || CHAT.role?.toUpperCase() === 'MESTRE');

    // stomp client compartilhado (exposto por chat-room.js)
    let stompClient = null;

    // Game state
    let ordemTurnos = [];
    let turnoIndex = 0;
    let rodada = 1;
    let rodadaAtiva = false;
    let phase = 'idle'; // 'player', 'master', 'idle'
    let currentPlayerId = null;
    let ultimoDadoRolado = null;
    let modoMestre = null; // 'dano', 'cura', null
    let modalValor = null; // Referência ao modal Bootstrap

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

    // ========== UTILS ==========
    function debugLog(...args) { console.log('[RM]', ...args); }

    function enviarSistema(msg) {
        if (!stompClient || !salaId) return;
        stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify({
            tipo: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
            salaId: salaId
        }));
    }

    function enviarAcao(obj) {
        if (!stompClient || !salaId) return;
        stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify({
            tipo: 'acao',
            salaId,
            ...obj
        }));
    }

    // ========== GAME FLOW ==========

    function getCardById(pid) {
        // Procura primeiro no container de personagens da esquerda
        let card = personagensContainer.querySelector(`.personagem-card[data-id="${pid}"]`);
        if (!card) {
            // Se não encontrou, procura em todo o documento
            card = document.querySelector(`.personagem-card[data-id="${pid}"]`);
        }
        return card;
    }

    function destacarPersonagem(card) {
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.remove('border-warning', 'border-3');
        });
        if (!card) return;
        card.classList.add('border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function setPlayerControlsEnabled(enabled, personagemId) {
        const card = getCardById(personagemId);
        if (!card) return;

        const donoDoCard = String(card.dataset.usuarioId);
        const souDono = donoDoCard === userId;

        // Reset estado dos controles
        diceOptions.classList.add('d-none');
        btnRoll.disabled = false;
        btnSkip.disabled = false;
        ultimoDadoRolado = null;

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
        enviarAcao({ acao: 'ordemTurnos', ordem: ordemTurnos });
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
        atualizarBotoesMestre();
    }

    function atualizarTurnoUI() {
        debugLog('🔄 Atualizando UI do turno:', { rodadaAtiva, turnoIndex, currentPlayerId });

        if (!rodadaAtiva) {
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
            if (btnRoll) btnRoll.disabled = true;
            if (btnSkip) btnSkip.disabled = true;
            diceOptions.classList.add('d-none');
            return;
        }

        const atual = ordemTurnos[turnoIndex];
        if (!atual) {
            debugLog('⚠️ Jogador atual não encontrado no índice:', turnoIndex);
            return;
        }

        if (placeholder) {
            placeholder.textContent = `🕒 Turno de ${atual.nome}`;
            // Verifica se é minha vez usando tanto usuarioId quanto currentPlayerId
            const isMyTurn = String(atual.usuarioId) === userId &&
                           String(atual.personagemId) === currentPlayerId;

            if (isMyTurn) {
                placeholder.textContent += ' (Sua vez!)';
            }
        }

        destacarPersonagem(atual.card);

        // Atualiza controles do jogador
        const isMyTurn = String(atual.usuarioId) === userId &&
                        String(atual.personagemId) === currentPlayerId;
        setPlayerControlsEnabled(isMyTurn && phase === 'player', atual.personagemId);

        atualizarBotoesMestre();

        // Debug do estado final
        debugLog('Estado após atualização UI:', {
            jogadorAtual: atual.nome,
            isMyTurn: String(atual.usuarioId) === userId,
            phase,
            currentPlayerId
        });
    }

    function atualizarBotoesMestre() {
        debugLog('🎮 Atualizando botões mestre:', { isMestre, phase, rodadaAtiva });
        if (!isMestre) return;

        // Garantir que temos referências aos botões
        const btnMestre = document.getElementById('btn-lancar-mestre');
        const btnInicio = document.getElementById('btnIniciarTurno');
        const btnPermitir = document.getElementById('btn-permitir-jogada');
        const iconInicio = btnInicio?.querySelector('i');

        if (!btnMestre || !btnInicio || !btnPermitir) {
            debugLog('⚠️ Botões não encontrados no DOM');
            return;
        }

        // Debug do estado atual
        debugLog('Estado dos botões antes:', {
            btnMestre: btnMestre.disabled,
            btnInicio: btnInicio.disabled,
            btnPermitir: btnPermitir.disabled,
            phase,
            rodadaAtiva
        });

        // Configuração do botão de dados do mestre (sempre disponível durante rodada)
        btnMestre.disabled = !rodadaAtiva;

        // Se não há rodada ativa
        if (!rodadaAtiva) {
            btnInicio.disabled = false;
            btnPermitir.disabled = true;
            btnInicio.title = 'Iniciar Rodada';
            if (iconInicio) {
                iconInicio.className = 'fa-solid fa-play fs-4';
            }
            debugLog('🔄 Rodada inativa - configurado para iniciar');
            return;
        }

        // Durante a fase do mestre (após ação do jogador)
        if (phase === 'master') {
            btnInicio.disabled = false;  // Pode avançar para próximo
            btnPermitir.disabled = false; // Pode permitir jogada extra
            btnInicio.title = 'Avançar para Próximo Jogador';
            if (iconInicio) {
                iconInicio.className = 'fa-solid fa-forward fs-4';
            }
            debugLog('🎯 Fase do mestre - configurado para avançar');
            return;
        }

        // Durante a fase do jogador
        if (phase === 'player') {
            btnInicio.disabled = true;   // Não pode avançar durante jogada
            btnPermitir.disabled = true;  // Não pode permitir durante jogada
            btnInicio.title = 'Aguardando Jogador';
            if (iconInicio) {
                iconInicio.className = 'fa-solid fa-pause fs-4';
            }
            debugLog('👥 Fase do jogador - aguardando ação');
            return;
        }

        // Estado idle com rodada ativa não deveria acontecer
        debugLog('⚠️ Estado inesperado:', { phase, rodadaAtiva });
        btnInicio.disabled = true;
        btnPermitir.disabled = true;
    }

    function proximoPhaseDepoisDaAcaoDoJogador() {
        setPlayerControlsEnabled(false, currentPlayerId);
        phase = 'master';
        enviarSistema(`👉 Jogador terminou sua ação. Aguardando Mestre...`);
        atualizarBotoesMestre();
    }

    function proximoTurno() {
        turnoIndex++;
        if (turnoIndex >= ordemTurnos.length) {
            enviarSistema(`🏁 Fim da rodada ${rodada}.`);
            rodada++;
            rodadaAtiva = false;
            phase = 'idle';
            currentPlayerId = null;
            setPlayerControlsEnabled(false, null);
            if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
            atualizarBotoesMestre();
            return;
        }

        const proximo = ordemTurnos[turnoIndex];
        currentPlayerId = proximo.personagemId;
    phase = 'player';
    // novo jogador começou — mestre não pode avançar até ação do jogador
    if (btnLancarMestre) btnLancarMestre.disabled = true;
        enviarSistema(`👉 Turno de ${proximo.nome}`);
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });
        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
    }

    // ========== EVENT HANDLERS ==========

    function onDiceButtonClick(e) {
        const btn = e.target.closest('.dice-btn');
        if (!btn) return;

        const sides = parseInt(btn.dataset.sides, 10);
        if (isNaN(sides)) return;

        const valor = Math.floor(Math.random() * sides) + 1;
        const atual = ordemTurnos[turnoIndex];

        if (isMestre) {
            // Rolagem do mestre - não afeta o fluxo do jogo
            enviarSistema(`🎲 Mestre rolou D${sides} = ${valor}`);
            enviarAcao({
                acao: 'mestreRolou',
                dado: sides,
                valor,
                autor: 'Mestre'
            });

            // Mantém os dados visíveis para permitir mais rolagens
            ultimoDadoRolado = { sides, valor };
        } else {
            // Rolagem do jogador - segue o fluxo normal
            enviarSistema(`🎲 ${atual.nome} rolou D${sides} = ${valor}`);
            enviarAcao({
                acao: 'playerActionDone',
                personagemId: atual.personagemId,
                descricao: `${atual.nome} rolou D${sides} = ${valor}`,
                autor: userLogin,
                dado: sides,
                valor
            });

            // Desabilita botões após rolar
            btnRoll.disabled = true;
            diceOptions.classList.add('d-none');
            ultimoDadoRolado = { sides, valor };

            // Move para fase do mestre
            proximoPhaseDepoisDaAcaoDoJogador();
        }
    }

    // ========== UI EVENT LISTENERS ==========

    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            if (!isMestre) return;

            if (!rodadaAtiva) {
                // Inicia nova rodada
                iniciarRodada();
            } else if (phase === 'master') {
                // Avança para próximo jogador
                proximoTurno();
            }
        });
    }

    if (btnRoll) {
        btnRoll.addEventListener('click', () => {
            if (!rodadaAtiva || phase !== 'player') return;
            const atual = ordemTurnos[turnoIndex];
            if (String(atual.usuarioId) !== userId) return;

            // Toggle dice options
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

            // Desabilita controles e move para fase do mestre
            btnRoll.disabled = true;
            btnSkip.disabled = true;
            diceOptions.classList.add('d-none');
            proximoPhaseDepoisDaAcaoDoJogador();
        });
    }

    if (diceOptions) {
        diceOptions.addEventListener('click', onDiceButtonClick);
    }

    // Botão para lançar dado do mestre
    if (btnLancarMestre) {
        btnLancarMestre.addEventListener('click', () => {
            if (!isMestre || !rodadaAtiva) return;

            // Mostrar opções de dados para o mestre (mesmo painel dos jogadores)
            if (diceOptions.classList.contains('d-none')) {
                diceOptions.classList.remove('d-none');
                turnControls.classList.remove('d-none'); // Mostra o container dos dados

                // Esconde os botões de controle do jogador
                if (btnRoll) btnRoll.style.display = 'none';
                if (btnSkip) btnSkip.style.display = 'none';
            } else {
                diceOptions.classList.add('d-none');
                turnControls.classList.add('d-none');
            }
        });
    }

    // Botão para permitir jogada extra
    const btnPermitirJogada = document.getElementById('btn-permitir-jogada');
    if (btnPermitirJogada) {
        btnPermitirJogada.addEventListener('click', () => {
            if (!isMestre || !rodadaAtiva || phase !== 'master') return;

            // Permite jogada extra diretamente, sem confirmação
            phase = 'player';
            enviarSistema(`🔄 Mestre permitiu jogada extra para ${ordemTurnos[turnoIndex].nome}`);
            enviarAcao({
                acao: 'permitirJogada',
                personagemId: currentPlayerId
            });
            setPlayerControlsEnabled(true, currentPlayerId);
            atualizarBotoesMestre();
        });
    }

    // ========== WEBSOCKET INTEGRATION ==========

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
                debugLog('ordemTurnos recebida:', ordemTurnos);
                break;

            case 'turnoAtual':
                if (!card) {
                    debugLog('⚠️ Card não encontrado para personagemId:', data.personagemId);
                    return;
                }
                rodadaAtiva = true;
                phase = 'player';
                currentPlayerId = String(data.personagemId);

                // Atualiza o índice do turno baseado no personagem atual
                const turnoAtualIndex = ordemTurnos.findIndex(p => p.personagemId === currentPlayerId);
                if (turnoAtualIndex !== -1) {
                    turnoIndex = turnoAtualIndex;
                    debugLog('🎯 Atualizando turnoIndex para:', turnoIndex);
                }

                destacarPersonagem(card);
                const isMyTurn = String(card.dataset.usuarioId) === userId;
                setPlayerControlsEnabled(isMyTurn, data.personagemId);
                atualizarTurnoUI();

                // Debug do estado após atualização
                debugLog('Estado após turnoAtual:', {
                    currentPlayerId,
                    turnoIndex,
                    isMyTurn,
                    phase
                });
                break;

            case 'playerActionDone':
                // Quando um jogador finaliza a ação, o mestre deve ser notificado
                rodadaAtiva = true;
                phase = 'master';
                currentPlayerId = String(data.personagemId);
                debugLog('🎯 Jogador finalizou ação:', {
                    phase,
                    isMestre,
                    personagemId: currentPlayerId,
                    btnLancarMestre: document.getElementById('btn-lancar-mestre')?.disabled
                });

                if (card) destacarPersonagem(card);
                if (!isMestre) {
                    // para clientes players, garantir que controles do jogador estejam desativados
                    setPlayerControlsEnabled(false, currentPlayerId);
                }

                // Forçar atualização do estado dos botões
                atualizarBotoesMestre();
                atualizarTurnoUI();

                // Dupla verificação do estado após atualização
                setTimeout(() => {
                    if (isMestre && phase === 'master' && document.getElementById('btn-lancar-mestre')?.disabled) {
                        debugLog('⚠️ Correção: btnLancarMestre ainda desabilitado após playerActionDone');
                        atualizarBotoesMestre();
                    }
                }, 100);
                break;

            case 'permitirJogada':
                if (String(data.personagemId) === String(currentPlayerId)) {
                    phase = 'player';
                    setPlayerControlsEnabled(true, currentPlayerId);
                }
                break;

            case 'mestreRolou':
                // Apenas mostra a rolagem, sem afetar o estado do jogo
                debugLog('🎲 Mestre rolou:', data);
                break;

            case 'upDisponivel':
                // Verifica se o jogador é o dono do personagem
                if (String(data.usuarioId) === userId) {
                    debugLog('🔼 Up disponível para:', data);
                    // Abre o drawer da ficha
                    const drawer = document.getElementById('personagemDrawer');
                    const bsDrawer = new bootstrap.Offcanvas(drawer);
                    bsDrawer.show();

                    // Abre a seção de atributos
                    document.getElementById('collapseAtributos')?.classList.add('show');

                    // Adiciona os botões de up nos atributos se ainda não existirem
                    const atributosDiv = document.getElementById('collapseAtributos');
                    if (atributosDiv) {
                        // Remove botões anteriores
                        atributosDiv.querySelectorAll('.btn-up-atributo').forEach(btn => btn.remove());

                        // Adiciona novos botões
                        const atributos = atributosDiv.querySelectorAll('.bg-dark');
                        let pontosDisponiveis = 5;
                        let pontosDistribuidos = {};

                        // Cria container para pontos e botão de reset
                        const headerContainer = document.createElement('div');
                        headerContainer.className = 'text-center mt-2 mb-3';

                        const spanPontos = document.createElement('div');
                        spanPontos.className = 'text-warning mb-2';
                        spanPontos.textContent = `Pontos disponíveis: ${pontosDisponiveis}`;
                        headerContainer.appendChild(spanPontos);

                        // Botão para resetar distribuição
                        const btnReset = document.createElement('button');
                        btnReset.className = 'btn btn-sm btn-outline-warning';
                        btnReset.textContent = 'Resetar Distribuição';
                        btnReset.disabled = true;
                        headerContainer.appendChild(btnReset);

                        atributosDiv.querySelector('.card-body').insertBefore(
                            headerContainer,
                            atributosDiv.querySelector('.card-body').firstChild
                        );

                        atributos.forEach(div => {
                            // Container para o atributo e seus controles
                            const container = document.createElement('div');
                            container.className = 'd-flex align-items-center justify-content-between';

                            // Extrai nome e valor base do atributo
                            const texto = div.textContent;
                            const [nome, valorBase] = texto.split(': ');
                            const baseValue = parseInt(valorBase);
                            pontosDistribuidos[nome] = 0;

                            // Texto do atributo
                            const textoAtributo = document.createElement('div');
                            textoAtributo.className = 'flex-grow-1';
                            textoAtributo.textContent = `${nome}: ${baseValue}`;

                            // Container dos botões
                            const botoesContainer = document.createElement('div');
                            botoesContainer.className = 'ms-2 d-flex gap-1';

                            // Valor adicional
                            const spanAdicional = document.createElement('span');
                            spanAdicional.className = 'text-info ms-1';
                            spanAdicional.style.display = 'none';

                            // Botões + e -
                            const btnMenos = document.createElement('button');
                            btnMenos.className = 'btn btn-sm btn-outline-danger btn-up-atributo';
                            btnMenos.innerHTML = '<i class="fa-solid fa-minus"></i>';
                            btnMenos.disabled = true;

                            const btnMais = document.createElement('button');
                            btnMais.className = 'btn btn-sm btn-outline-info btn-up-atributo';
                            btnMais.innerHTML = '<i class="fa-solid fa-plus"></i>';

                            function atualizarInterface() {
                                const adicional = pontosDistribuidos[nome];
                                spanAdicional.textContent = adicional > 0 ? ` (+${adicional})` : '';
                                spanAdicional.style.display = adicional > 0 ? 'inline' : 'none';
                                btnMenos.disabled = adicional === 0;
                                btnMais.disabled = pontosDisponiveis === 0;
                                btnReset.disabled = Object.values(pontosDistribuidos).every(v => v === 0);
                                spanPontos.textContent = `Pontos disponíveis: ${pontosDisponiveis}`;

                                // Envia atualização em tempo real
                                enviarAcao({
                                    acao: 'atributoUpado',
                                    personagemId: data.personagemId,
                                    atributo: nome.toLowerCase(),
                                    valorBase: baseValue,
                                    adicional: adicional
                                });
                            }

                            btnMais.onclick = function() {
                                if (pontosDisponiveis > 0) {
                                    pontosDistribuidos[nome]++;
                                    pontosDisponiveis--;
                                    atualizarInterface();
                                }
                            };

                            btnMenos.onclick = function() {
                                if (pontosDistribuidos[nome] > 0) {
                                    pontosDistribuidos[nome]--;
                                    pontosDisponiveis++;
                                    atualizarInterface();
                                }
                            };

                            btnReset.onclick = function() {
                                Object.keys(pontosDistribuidos).forEach(attr => {
                                    pontosDisponiveis += pontosDistribuidos[attr];
                                    pontosDistribuidos[attr] = 0;
                                });
                                document.querySelectorAll('.text-info[style]').forEach(span => {
                                    span.style.display = 'none';
                                });
                                atualizarInterface();
                            };
                            // Monta a estrutura
                            botoesContainer.appendChild(btnMenos);
                            botoesContainer.appendChild(btnMais);
                            container.appendChild(textoAtributo);
                            container.appendChild(spanAdicional);
                            container.appendChild(botoesContainer);

                            // Substitui o div original pelo novo container
                            div.parentNode.replaceChild(container, div);
                        });
                    }
                }
                break;

            case 'danoRecebido':
            case 'curaRecebida':
                const cardVida = getCardById(String(data.personagemId));
                if (!cardVida) return;

                // Atualizar barra de vida
                const progressBar = cardVida.querySelector('.progress-bar');
                if (progressBar) {
                    const vidaMaxima = parseInt(cardVida.dataset.vidaMax, 10);
                    progressBar.style.width = `${(data.vidaAtual / vidaMaxima) * 100}%`;
                    progressBar.textContent = `${data.vidaAtual}/${vidaMaxima} HP`;
                }

                // Atualizar dataset
                cardVida.dataset.vida = data.vidaAtual;
                break;

            default:
                debugLog('Ação ignorada:', data.acao);
                break;
        }
    }

    function setupSocketIntegration() {
        if (window.chatStomp) {
            stompClient = window.chatStomp;
            debugLog('🔁 Reaproveitando stomp client do chat');
        }

        document.addEventListener('stomp.connected', (ev) => {
            try {
                stompClient = ev.detail.stompClient;
                debugLog('🔌 Conectado ao stomp via chat-room');
            } catch (e) {
                console.warn('Erro ao integrar com chat-room:', e);
            }
        });

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
    }

    // ========== INIT ==========

    // Manipulação de vida dos personagens
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

        // Atualizar barra de vida
        const progressBar = card.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${(novaVida / vidaMaxima) * 100}%`;
            progressBar.textContent = `${novaVida}/${vidaMaxima} HP`;
        }

        // Atualizar dataset
        card.dataset.vida = novaVida;

        // Enviar ação para todos
        enviarAcao({
            acao: tipo === 'dano' ? 'danoRecebido' : 'curaRecebida',
            personagemId,
            valor,
            vidaAtual: novaVida
        });

        // Resetar seleção mantendo o modo ativo
        document.querySelectorAll('.personagem-card').forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

            // Remove classes de seleção
            c.classList.remove('card-selecionavel', 'card-selecionado');

            // Reseta para borda azul se não for o jogador atual
            if (!isCurrentPlayer) {
                c.classList.remove('border-danger', 'border-success', 'border-info', 'border-warning');
                c.classList.add('border', 'border-primary', 'border-3');
            }
        });
    }

    function ativarModoMestre(modo) {
        if (!isMestre || !rodadaAtiva) return;
        debugLog('🎯 Ativando modo mestre:', modo);

        // Referências aos botões
        const btnDano = document.getElementById('btn-dano');
        const btnCurar = document.getElementById('btn-curar');
        const btnUpar = document.getElementById('btn-upar');

        // Reseta estado visual dos botões
        [btnDano, btnCurar, btnUpar].forEach(btn => {
            if (btn) {
                btn.classList.remove('active');
                btn.setAttribute('data-active', 'false');
            }
        });

        // Reseta estado dos cards mantendo a borda de turno ativo
        document.querySelectorAll('.personagem-card').forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

            // Remove todas as bordas de modo
            c.classList.remove(
                'border-primary', 'border-danger',
                'border-success', 'border-info'
            );

            // Remove border-3 apenas se não for o jogador atual
            if (!isCurrentPlayer) {
                c.classList.remove('border-3');
            }

            c.style.cursor = 'default';
        });

        // Se clicar no mesmo modo, desativa
        if (modoMestre === modo) {
            debugLog('🔄 Desativando modo:', modo);
            modoMestre = null;
            return;
        }

        // Ativa o novo modo
        modoMestre = modo;

        // Atualiza estado visual do botão ativo
        const btnAtivo = modo === 'dano' ? btnDano :
                        modo === 'cura' ? btnCurar :
                        modo === 'up' ? btnUpar : null;
        if (btnAtivo) {
            btnAtivo.classList.add('active');
            btnAtivo.setAttribute('data-active', 'true');
        }

        // Destacar todos os personagens como selecionáveis
        const cards = document.querySelectorAll('.personagem-card');
        cards.forEach(c => {
            const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

            // Adiciona borda azul para cards não ativos
            if (!isCurrentPlayer) {
                c.classList.add('border', 'border-primary', 'border-3');
            }

            c.style.cursor = 'pointer';
        });

        debugLog('✅ Modo ativado:', modo, 'Cards encontrados:', cards.length);
    }

    function init() {
        debugLog('🎲 room-manager v2.0 iniciando...');
        setupSocketIntegration();

        // Setup do modal de valor
        modalValor = new bootstrap.Modal(document.getElementById('modalValor'));

        // Botões do mestre
        const btnDano = document.getElementById('btn-dano');
        const btnCurar = document.getElementById('btn-curar');

        if (btnDano) {
            btnDano.addEventListener('click', () => ativarModoMestre('dano'));
        }

        if (btnCurar) {
            btnCurar.addEventListener('click', () => ativarModoMestre('cura'));
        }

        const btnUpar = document.getElementById('btn-upar');
        if (btnUpar) {
            btnUpar.addEventListener('click', () => ativarModoMestre('up'));
        }

        // Handler para click nos cards
        function cardClickHandler(event) {
            const card = event.target.closest('.personagem-card');
            if (!card || !modoMestre || !isMestre) return;

            debugLog('🎯 Card clicado:', {
                id: card.dataset.id,
                nome: card.dataset.nome,
                modo: modoMestre
            });

            // Reseta bordas mantendo o estado do jogador atual
            document.querySelectorAll('.personagem-card').forEach(c => {
                const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

                // Remove todas as bordas de modo
                c.classList.remove('border-danger', 'border-success', 'border-info');

                // Para os cards que não são o atual nem o clicado, adiciona borda azul
                if (!isCurrentPlayer && c !== card) {
                    c.classList.remove('border-warning');
                    c.classList.add('border', 'border-primary', 'border-3');
                }
            });

            // Adiciona a borda específica do modo no card clicado
            card.classList.remove('border-primary');
            const borderClass = modoMestre === 'dano' ? 'border-danger' :
                              modoMestre === 'cura' ? 'border-success' :
                              modoMestre === 'up' ? 'border-info' : 'border-primary';
            card.classList.add(borderClass);

            // Se for modo up, envia notificação para o jogador
            if (modoMestre === 'up') {
                // Envia ação para todos
                enviarAcao({
                    acao: 'upDisponivel',
                    personagemId: card.dataset.id,
                    usuarioId: card.dataset.usuarioId,
                    nome: card.dataset.nome
                });

                // Notifica o sistema
                enviarSistema(`🔼 ${card.dataset.nome} pode distribuir 5 pontos em seus atributos!`);
                return;
            }

            // Reseta modal e input
            const btnConfirmar = document.getElementById('btnConfirmarValor');
            const inputValor = document.getElementById('inputValor');
            inputValor.value = '';

            // Configura título do modal baseado no modo
            const modalTitle = document.querySelector('#modalValor .modal-title');
            if (modalTitle) {
                modalTitle.textContent = modoMestre === 'dano' ? 'Aplicar Dano' : 'Aplicar Cura';
            }

            // Configura botão confirmar
            const btnModal = document.getElementById('btnConfirmarValor');
            if (btnModal) {
                btnModal.className = modoMestre === 'dano' ?
                    'btn btn-danger' : 'btn btn-success';
                btnModal.textContent = modoMestre === 'dano' ?
                    'Aplicar Dano' : 'Aplicar Cura';
            }

            modalValor.show();

            // Focus no input após modal abrir
            modalValor._element.addEventListener('shown.bs.modal', () => {
                inputValor.focus();
            }, { once: true });

            // Handler para confirmar valor
            const confirmarHandler = () => {
                const valor = parseInt(inputValor.value, 10);
                if (isNaN(valor) || valor < 0) return;

                debugLog('💫 Aplicando', modoMestre, 'valor:', valor);

                // Remove todas as classes de seleção
                document.querySelectorAll('.personagem-card').forEach(c => {
                    c.classList.remove('card-selecionavel', 'card-selecionado');
                    c.style.cursor = 'default';
                });

                handleVidaChange(card.dataset.id, valor, modoMestre);
                modalValor.hide();

                // Mantém o botão ativo e o modo
                debugLog('✅ Valor aplicado, mantendo modo:', modoMestre);

                // Limpar handler
                btnConfirmar.removeEventListener('click', confirmarHandler);
            };

            // Remove handlers anteriores e adiciona o novo
            btnConfirmar.removeEventListener('click', confirmarHandler);
            btnConfirmar.addEventListener('click', confirmarHandler);
        }

        // Adiciona o handler no container pai para usar event delegation
        personagensContainer.addEventListener('click', cardClickHandler);

        // Debug helpers
        window.roomManager = {
            enviarAcao,
            enviarSistema,
            iniciarRodada,
            proximoTurno,
            getEstado: () => ({
                ordemTurnos,
                turnoIndex,
                rodadaAtiva,
                phase,
                currentPlayerId,
                isMestre,
                ultimoDadoRolado
            })
        };

        debugLog('✅ Inicializado. isMestre:', isMestre, 'userId:', userId);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
