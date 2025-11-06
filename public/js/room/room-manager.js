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
    const btnOcultarDados = document.getElementById('ocultarDados'); // Adicionar para futuramente inserir isso

    // ========== UTILS ==========
    // Função de debug que imprime mensagens no console com um prefixo [RM]
    function debugLog(...args) { console.log('[RM]', ...args); }

    // Função para enviar uma mensagem do tipo "sistema" para a sala via WebSocket
    function enviarSistema(msg) {
        // Se o WebSocket não estiver conectado ou não houver sala, não faz nada
        if (!stompClient || !salaId) return;
        // Envia a mensagem para o servidor, no endpoint '/app/enviar/{salaId}'
        stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify({
            tipo: 'sistema',
            conteudo: msg,
            autor: '🤖 Sistema',
            salaId: salaId
        }));
    }

    // Função para enviar uma ação (tipo evento ou comando) para a sala via WebSocket
    function enviarAcao(obj) {
        if (!stompClient || !salaId) return;
        stompClient.send('/app/enviar/' + salaId, {}, JSON.stringify({
            tipo: 'acao',
            salaId,
            ...obj
        }));
    }

    // ========== GAME FLOW ==========

    // Função para obter o card do personagem pelo ID
    function getCardById(pid) {
        // Procura primeiro no container de personagens da esquerda
        let card = personagensContainer.querySelector(`.personagem-card[data-id="${pid}"]`);
        if (!card) {
            // Se não encontrou, procura em todo o documento
            card = document.querySelector(`.personagem-card[data-id="${pid}"]`);
        }
        // Retorna o card encontrado ou null
        return card;
    }

    // Função para destacar visualmente o card do personagem atual
    function destacarPersonagem(card) {
        // Remove destaque de todos os cards
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.remove('border-warning', 'border-3');
        });

        // Se não houver card, sai da função
        if (!card) return;

        // Adicionar classes de destaque
        card.classList.add('border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Função para habilitar/desabilitar controles do jogador
    function setPlayerControlsEnabled(enabled, personagemId) {
        // Obtém o card do personagem
        const card = getCardById(personagemId);

        // Se não houver card, sai da função
        if (!card) return;

        // Obtém o dono do card
        const donoDoCard = String(card.dataset.usuarioId);

        // Verifica se o usuário atual é o dono do personagem
        const souDono = donoDoCard === userId;

        // Reset estado dos controles
        diceOptions.classList.add('d-none');
        btnRoll.disabled = false;
        btnSkip.disabled = false;
        ultimoDadoRolado = null;

        // Se for mestre, desabilita controles
        if (isMestre) {
            turnControls.classList.add('d-none');
            btnRoll.disabled = true;
            btnSkip.disabled = true;
            return;
        }

        // Se for dono do personagem e controles habilitados
        if (souDono && enabled) {
            turnControls.classList.remove('d-none');
            btnRoll.disabled = false;
            btnSkip.disabled = false;
        } else {
            // Caso contrário, desabilita controles
            turnControls.classList.add('d-none');
            btnRoll.disabled = true;
            btnSkip.disabled = true;
        }
    }

    // Função para ordenar personagens por iniciativa
    function ordenarIniciativas(personagens) {

        // Cria uma lista de objetos com nome, iniciativa, card, personagemId e usuarioId
        let lista = personagens.map(card => ({
            // Pega o nome do personagem
            nome: card.dataset.nome,
            // Pega a iniciativa como número inteiro
            iniciativa: parseInt(card.dataset.iniciativa || 0, 10),
            // o card em si
            card,
            // o id do personagem como string
            personagemId: String(card.dataset.id),
            // o id do usuario dono do personagem como string
            usuarioId: String(card.dataset.usuarioId || '')
        }));

        // Ordena a lista por iniciativa decrescente
        lista.sort((a, b) => b.iniciativa - a.iniciativa);

        // ===================== EMBARALHAMENTO DE INICIATIVAS IGUAIS =====================
        // tie-break shuffle
        // Percorre a lista de personagens ordenada por iniciativa
        for (let i = 0; i < lista.length - 1; i++) {
            // Se dois personagens consecutivos têm a mesma iniciativa
            if (lista[i].iniciativa === lista[i + 1].iniciativa) {
                // Troca a posição deles aleatoriamente com 50% de chance
                // Isso evita que sempre o mesmo personagem com iniciativa igual vá primeiro
                // [lista[i], lista[i + 1]] = [lista[i + 1], lista[i]] é a sintaxe de destruturação do JS
                // que troca os valores sem precisar de variável temporária
                if (Math.random() < 0.5) [lista[i], lista[i + 1]] = [lista[i + 1], lista[i]];
            }
        }

        return lista;
    }

    // Função para iniciar uma nova rodada
    function iniciarRodada() {
        // Pega todos os cards de personagens
        const cards = Array.from(document.querySelectorAll('.personagem-card'));

        // Se não houver personagens, sai da função
        if (cards.length === 0) return;

        // Cria a ordem dos turnos e já ordena inicialmente
        ordemTurnos = ordenarIniciativas(cards);

        // Inicializa variáveis de estado
        turnoIndex = 0;

        // Rodada começa ativa
        rodadaAtiva = true;

        // Fase agora é PLAYER
        phase = 'player';

        // Rodada começa em 1
        rodada = rodada || 1;

        // Define o jogador atual como o primeiro da ordem
        const primeiro = ordemTurnos[turnoIndex];

        // Define o currentPlayerId
        currentPlayerId = primeiro.personagemId;

        // Destaca o personagem inicial
        destacarPersonagem(primeiro.card);

        // Envia mensagens com as funções iniciadas
        enviarSistema(`🕒 Rodada ${rodada} iniciada! Ordem de turnos: ${ordemTurnos.map(p => p.nome).join(', ')}`);
        // Enviando a ordem dos turnos para todos os clientes
        enviarAcao({ acao: 'ordemTurnos', ordem: ordemTurnos });
        // Enviando o turno atual
        enviarAcao({ acao: 'turnoAtual', personagemId: currentPlayerId });

        //
        setPlayerControlsEnabled(true, currentPlayerId);
        atualizarTurnoUI();
        atualizarBotoesMestre();
    }

    // Função para atualizar a UI do turno atual
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

    // Função para atualizar o estado dos botões do mestre
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

    // Função para avançar para a fase do mestre após ação do jogador
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
    // Função que processa todas as ações recebidas via WebSocket do tipo "acao"
    function onReceiveAction(data) {
        // Se não houver dados ou não tiver o campo 'acao', ignora
        if (!data || !data.acao) return;
        // Log para debug no console
        debugLog('📥 Ação recebida:', data);

        // Pega o "card" do personagem, se houver personagemId na ação
        const card = data.personagemId ? getCardById(String(data.personagemId)) : null;

        // Switch para tratar cada tipo de ação específica
        switch (data.acao) {
            // ===================== ORDEM DOS TURNOS =====================
            case 'ordemTurnos':
                // Atualiza a ordem de turno com base no que veio do servidor
                ordemTurnos = (data.ordem || []).map(o => ({
                    nome: o.nome,
                    personagemId: String(o.personagemId),
                    usuarioId: String(o.usuarioId || ''),
                    card: getCardById(String(o.personagemId)) // pega o card de cada personagem
                }));
                debugLog('ordemTurnos recebida:', ordemTurnos);
                break;

            // ===================== TURNO ATUAL =====================
            case 'turnoAtual':
                if (!card) { // se não achar o card, loga e retorna
                    debugLog('⚠️ Card não encontrado para personagemId:', data.personagemId);
                    return;
                }

                // Atualiza estado do jogo
                rodadaAtiva = true;
                // Fase agora é PLAYER
                phase = 'player';
                currentPlayerId = String(data.personagemId);

                // Procura no array 'ordemTurnos' a posição do personagem atual
                // 'turnoIndex' é usado para controlar quem é o próximo na ordem de turno
                const turnoAtualIndex = ordemTurnos.findIndex(p => p.personagemId === currentPlayerId);

                // Se encontrado, atualiza a variável global 'turnoIndex'
                if (turnoAtualIndex !== -1) {
                    turnoIndex = turnoAtualIndex;
                    debugLog('🎯 Atualizando turnoIndex para:', turnoIndex);
                }

                // Destaca o personagem atual visualmente
                destacarPersonagem(card);

                // Verifica se é o jogador atual
                const isMyTurn = String(card.dataset.usuarioId) === userId;

                // Habilita ou desabilita controles do jogador
                setPlayerControlsEnabled(isMyTurn, data.personagemId);

                // Atualiza a UI do turno
                atualizarTurnoUI();

                // Debug do estado após atualização
                debugLog('Estado após turnoAtual:', {
                    currentPlayerId,
                    turnoIndex,
                    isMyTurn,
                    phase
                });
                break;

            // ===================== AÇÃO DO JOGADOR FINALIZADA =====================
            case 'playerActionDone':
                // Quando um jogador finaliza a ação, o mestre deve ser notificado
                // Atualiza estado geral
                rodadaAtiva = true;
                phase = 'master'; // passa o controle para o mestre
                currentPlayerId = String(data.personagemId);

                // Loga a ação
                debugLog('🎯 Jogador finalizou ação:', {
                    phase,
                    isMestre,
                    personagemId: currentPlayerId,
                    btnLancarMestre: document.getElementById('btn-lancar-mestre')?.disabled
                });

                // Destaca o card do personagem
                if (card) destacarPersonagem(card);

                // Se não for mestre, desativa controles do jogador
                if (!isMestre) {
                    // para clientes players, garantir que controles do jogador estejam desativados
                    setPlayerControlsEnabled(false, currentPlayerId);
                }

                // Forçar atualização do estado dos botões
                atualizarBotoesMestre();
                atualizarTurnoUI();

                // Dupla verificação: corrige caso botão do mestre ainda esteja desabilitado
                setTimeout(() => {
                    if (isMestre && phase === 'master' && document.getElementById('btn-lancar-mestre')?.disabled) {
                        debugLog('⚠️ Correção: btnLancarMestre ainda desabilitado após playerActionDone');
                        atualizarBotoesMestre();
                    }
                }, 100);
                break;

            // ===================== PERMITIR JOGADA EXTRA =====================
            case 'permitirJogada':
                // Ativa controles do jogador atual para uma jogada extra
                if (String(data.personagemId) === String(currentPlayerId)) {
                    phase = 'player';
                    setPlayerControlsEnabled(true, currentPlayerId);
                }
                break;

            case 'mestreRolou':
                // Apenas mostra a rolagem, sem afetar o estado do jogo
                debugLog('🎲 Mestre rolou:', data);
                break;

            // ===================== UP DISPONÍVEL PARA ATRIBUTOS =====================
            case 'upDisponivel':
                // Verifica se o jogador atual é o dono do personagem
                if (String(data.usuarioId) === userId) {
                    debugLog('🔼 Up disponível para:', data);

                    const collapse = document.getElementById('collapseAtributos');
                    collapse.classList.add('show'); // Abre o collapse da ficha do personagem

                    // Remove todos os botões + e - que podem ter sido criados em atualizações anteriores
                    collapse.querySelectorAll('.btn-up-atributo').forEach(btn => btn.remove());

                    const atributos = collapse.querySelectorAll('.bg-dark'); // Seleciona cada div que representa um atributo
                    let pontosDisponiveis = 5; // Número total de pontos que o jogador pode distribuir
                    let pontosDistribuidos = {}; // Armazena quantos pontos cada atributo recebeu

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

                    // ===================== FUNÇÃO DE ATUALIZAÇÃO DA INTERFACE =====================
                    function atualizarInterface() {
                        // Atualiza o texto de pontos disponíveis
                        spanPontos.textContent = `Pontos disponíveis: ${pontosDisponiveis}`;

                        // Habilita/desabilita botão Reset dependendo se há pontos distribuídos
                        btnReset.disabled = Object.values(pontosDistribuidos).every(v => v === 0);

                        // Atualiza cada atributo visualmente
                        atributos.forEach(div => {
                            const nome = div.dataset.nome; // Nome do atributo (forca, agilidade, etc.)
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
                    };
                }
                break;


            case 'danoRecebido':
            case 'curaRecebida':
                // Pega o card do personagem que sofreu dano ou recebeu cura
                const cardVida = getCardById(String(data.personagemId));
                if (!cardVida) return; // Se não existir card correspondente, sai da função

                // Atualiza a barra de vida do personagem
                const progressBar = cardVida.querySelector('.progress-bar'); // procura o elemento da barra de progresso
                if (progressBar) {
                    // vidaMaxima vem do dataset do card (atributo data-vida-max)
                    const vidaMaxima = parseInt(cardVida.dataset.vidaMax, 10);
                    // Calcula largura percentual da barra de vida com base no valor atual
                    progressBar.style.width = `${(data.vidaAtual / vidaMaxima) * 100}%`;
                    // Atualiza o texto visível da barra para mostrar "vidaAtual/vidaMaxima HP"
                    progressBar.textContent = `${data.vidaAtual}/${vidaMaxima} HP`;
                }

                // Atualiza o dataset do card para manter o valor de vida atual sincronizado com o DOM
                // Isso é útil caso outras funções precisem ler o valor atual da vida diretamente do card
                cardVida.dataset.vida = data.vidaAtual;
                break;

            default:
                debugLog('Ação ignorada:', data.acao);
                break;
        }
    }

    // Configuração da integração com o WebSocket do chat-room.js
    function setupSocketIntegration() {
        // Tenta reaproveitar o stompClient global já existente
        if (window.chatStomp && window.chatStomp.stompClient) {
            stompClient = window.chatStomp.stompClient;
            debugLog('🔁 Reaproveitando stomp client do chat');
        } else {
            debugLog('⚙️ Nenhum stompClient encontrado — aguardando conexão ou criando fallback...');

            // Aguarda o evento stomp.connected (disparado pelo chat-room.js)
            document.addEventListener('stomp.connected', (ev) => {
                try {
                    stompClient = ev.detail?.stompClient || window.chatStomp?.stompClient;
                    if (stompClient) {
                        debugLog('🔌 Conectado ao stomp via chat-room');
                    } else {
                        debugLog('⚠️ Evento stomp.connected recebido, mas sem stompClient válido.');
                    }
                } catch (e) {
                    console.warn('Erro ao integrar com chat-room:', e);
                }
            });

            // fallback: se passar um tempo e o stompClient ainda for nulo, cria um novo
            setTimeout(() => {
                if (!stompClient) {
                    debugLog('⏱️ Nenhum stompClient detectado — criando nova conexão local.');
                    WebSocketService.connect(
                        wsUrl,
                        channel,
                        processMessage,
                        () => {
                            stompClient = WebSocketService.stompClient;
                            window.chatStomp = WebSocketService; // define globalmente
                            debugLog('🆕 Fallback stompClient criado.');
                        },
                        (err) => console.error('❌ Erro ao criar fallback STOMP:', err)
                    );
                }
            }, 3000); // espera 3 segundos antes do fallback
        }

        // Listener para mensagens via WebSocket
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
