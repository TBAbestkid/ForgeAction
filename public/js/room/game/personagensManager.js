async function carregarPersonagensSala(salaId) {
    try {
        const response = await $.ajax({
            url: `/api/salas/personagens/listar/${salaId}`,
            method: "GET",
            data: { _token: csrfToken }
        });
        console.log('✅ Personagens carregados com sucesso:', response);
        return response || [];
    } catch (error) {
        console.error('❌ Falha ao carregar personagens:', error);
        return null;
    }
}

function limparListaPersonagensOnline() {
    const colunaPersonagens = document.getElementById('coluna-personagens');
    const colunaMobile = document.getElementById('coluna-personagens-mobile');

    if (colunaPersonagens) colunaPersonagens.innerHTML = '';
    if (colunaMobile) colunaMobile.innerHTML = '';

    console.log('🧹 Lista de personagens online limpa');
}

function criarCardPersonagem(personagem, sufixo) {

    const personagemDiv = document.createElement('div');
    personagemDiv.className =
        'bg-dark rounded p-2 text-center d-flex flex-column align-items-center position-relative';
    personagemDiv.style.cursor = 'pointer';
    personagemDiv.style.minWidth = '140px';

    personagemDiv.id = `personagem-online-${personagem.id}-${sufixo}`;

    personagemDiv.dataset.id = personagem.id;
    personagemDiv.dataset.usuarioId = personagem.usuarioId || '';
    personagemDiv.dataset.nome = personagem.nome;
    personagemDiv.dataset.vida = personagem.vida;
    personagemDiv.dataset.classe = personagem.classe;
    personagemDiv.dataset.raca = personagem.raca;
    personagemDiv.dataset.level = personagem.level;
    personagemDiv.dataset.forca = personagem.forca || 0;
    personagemDiv.dataset.agilidade = personagem.agilidade || 0;
    personagemDiv.dataset.inteligencia = personagem.inteligencia || 0;
    personagemDiv.dataset.destreza = personagem.destreza || 0;
    personagemDiv.dataset.vitalidade = personagem.vitalidade || 0;
    personagemDiv.dataset.percepcao = personagem.percepcao || 0;
    personagemDiv.dataset.sabedoria = personagem.sabedoria || 0;
    personagemDiv.dataset.carisma = personagem.carisma || 0;
    personagemDiv.dataset.mana = personagem.mana || 0;
    personagemDiv.dataset.iniciativa = personagem.iniciativa || 0;
    personagemDiv.dataset.ataqueMagico = personagem.ataqueMagico || 0;
    personagemDiv.dataset.ataqueFisicoCorpo = personagem.ataqueFisicoCorpo || 0;
    personagemDiv.dataset.ataqueFisicoDistancia = personagem.ataqueFisicoDistancia || 0;
    personagemDiv.dataset.defesaPersonagem = personagem.defesaPersonagem || 0;
    personagemDiv.dataset.esquivaPersonagem = personagem.esquivaPersonagem || 0;

    personagemDiv.innerHTML = `
        <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
            <strong class="small personagem-nome" style="flex: 1; text-align: left;">${personagem.nome}</strong>
            <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center"
                    style="padding: 0.35rem; width: 28px; height: 28px; border-radius: 50%; font-size: 0.7rem; flex-shrink: 0;"
                    onclick="event.stopPropagation(); abrirFichaPersonagem(this.closest('.bg-dark'))"
                    title="Ver Ficha Completa">
                <i class="fa-solid fa-info"></i>
            </button>
        </div>

        <div class="progress mt-2 w-100" style="height: 16px; font-size:0.7rem;">
            <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                role="progressbar"
                style="width: 100%;">
                ${personagem.vida}/${personagem.vida}
            </div>
        </div>
    `;

    // 🔥 Listener inteligente
    personagemDiv.addEventListener('click', (e) => {

        if (!acaoMestreAtual) return;

        e.preventDefault();
        e.stopPropagation();

        selecionarPersonagem(personagem.usuarioId, personagem.id);
    });

    return personagemDiv;
}

function adicionarPersonagemOnline(personagem) {

    const idDesktop = `personagem-online-${personagem.id}-pc`;
    const idMobile  = `personagem-online-${personagem.id}-mb`;

    // 🚫 Se já existir, não adiciona de novo
    if (document.getElementById(idDesktop) || document.getElementById(idMobile)) {
        console.log(`⚠️ Personagem ${personagem.nome} já existe, ignorando duplicação.`);
        return;
    }

    const colunaPersonagens =
        document.getElementById('coluna-personagens');

    const colunaMobile =
        document.getElementById('coluna-personagens-mobile');

    const personagemDiv =
        criarCardPersonagem(personagem, 'pc');

    const personagemMobile =
        criarCardPersonagem(personagem, 'mb');

    if (colunaPersonagens)
        colunaPersonagens.appendChild(personagemDiv);

    if (colunaMobile)
        colunaMobile.appendChild(personagemMobile);

    console.log(`➕ Personagem ${personagem.nome} adicionado`);
}

/**
 * Atualiza a vida de um personagem no card (desktop e mobile)
 */
function atualizarVidaPersonagemCard(personagemId, novaVida) {
    console.log(`📊 atualizarVidaPersonagemCard chamada - ID: ${personagemId}, Nova Vida: ${novaVida}`);

    const cardDesktop = document.getElementById(`personagem-online-${personagemId}-pc`);
    const cardMobile = document.getElementById(`personagem-online-${personagemId}-mb`);

    const atualizarCard = (card) => {
        if (!card) return;

        // Busca o progress bar
        const progressBar = card.querySelector('.progress-bar');
        if (progressBar) {
            // Limita entre 0 e vida máxima (usa vida original do card)
            const vidaMax = parseInt(card.dataset.vida) || 100;
            const vidaFinal = Math.max(0, Math.min(novaVida, vidaMax));

            // Atualiza o texto
            progressBar.innerText = `${vidaFinal}/${vidaMax}`;

            // Calcula percentual
            const percentual = (vidaFinal / vidaMax) * 100;
            progressBar.style.width = percentual + '%';

            // Muda a cor baseado na saúde
            progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            if (percentual > 50) {
                progressBar.classList.add('bg-success');
            } else if (percentual > 25) {
                progressBar.classList.add('bg-warning');
            } else {
                progressBar.classList.add('bg-danger');
            }

            // Anima a mudança
            card.style.animation = 'pulse-vida 0.5s ease-in-out';
            setTimeout(() => {
                card.style.animation = '';
            }, 500);
        }
    };

    atualizarCard(cardDesktop);
    atualizarCard(cardMobile);

    // Atualiza a barra de vida do jogador se for o seu personagem
    atualizarBarraVidaJogador(personagemId, novaVida);

    console.log(`❤️ Vida do personagem ${personagemId} atualizada para ${novaVida}`);
}

/**
 * Atualiza a barra de vida do jogador no HUD
 */
function atualizarBarraVidaJogador(personagemId, novaVida) {
    const barraVida = document.getElementById('playerHealthBar');
    if (!barraVida) return; // Silencioso se não existir (mestre não tem barra)

    // Comparação string para garantir que funcione com ambos tipos
    const personagemIdBar = String(barraVida.dataset.personagemId);
    const personagemIdCompare = String(personagemId);

    if (personagemIdCompare !== personagemIdBar) return; // Não é do jogador

    const vidaMax = parseInt(barraVida.dataset.vidaMax) || 100;
    const vidaFinal = Math.max(0, Math.min(novaVida, vidaMax));

    // Atualiza o texto
    barraVida.innerText = `${vidaFinal}/${vidaMax}`;

    // Calcula percentual
    const percentual = (vidaFinal / vidaMax) * 100;
    barraVida.style.width = percentual + '%';

    // Muda a cor baseado na saúde
    barraVida.classList.remove('bg-success', 'bg-warning', 'bg-danger');
    if (percentual > 50) {
        barraVida.classList.add('bg-success');
    } else if (percentual > 25) {
        barraVida.classList.add('bg-warning');
    } else {
        barraVida.classList.add('bg-danger');
    }

    // Anima a mudança
    barraVida.style.animation = 'none';
    setTimeout(() => {
        barraVida.style.animation = 'pulse-vida 0.5s ease-in-out';
    }, 10);
}

function AtualizarListaOnline(salaId, usuariosOnline) {
    console.log('🔄 RECONSTRUINDO lista completa...');
    console.log('👥 Usuários online recebidos:', usuariosOnline);

    limparListaPersonagensOnline();

    carregarPersonagensSala(salaId).then(listaPersonagens => {
        if (!listaPersonagens || !Array.isArray(listaPersonagens)) {
            console.error('❌ Lista de personagens inválida');
            return;
        }

        console.log(`🎯 Reconstruindo ${listaPersonagens.length} personagens`);

        listaPersonagens.forEach(personagem => {
            const usuarioIdStr = personagem.usuarioId?.toString();
            const isOnline = usuariosOnline.includes(usuarioIdStr);

            if (isOnline) {
                adicionarPersonagemOnline(personagem);
            }
        });

        console.log('✅ Lista reconstruída com sucesso!');
    }).catch(error => {
        console.error('❌ Erro ao reconstruir lista:', error);
    });
}

// Expor funções globalmente
window.atualizarVidaPersonagemCard = atualizarVidaPersonagemCard;
window.atualizarBarraVidaJogador = atualizarBarraVidaJogador;

/**
 * Abre o offcanvas com a ficha completa do personagem
 */
function abrirFichaPersonagem(cardElement) {
    const personagem = {
        id: cardElement.dataset.id,
        nome: cardElement.dataset.nome,
        raca: cardElement.dataset.raca,
        classe: cardElement.dataset.classe,
        level: cardElement.dataset.level,
        vida: cardElement.dataset.vida,
        mana: cardElement.dataset.mana,
        forca: cardElement.dataset.forca,
        agilidade: cardElement.dataset.agilidade,
        inteligencia: cardElement.dataset.inteligencia,
        destreza: cardElement.dataset.destreza,
        vitalidade: cardElement.dataset.vitalidade,
        percepcao: cardElement.dataset.percepcao,
        sabedoria: cardElement.dataset.sabedoria,
        carisma: cardElement.dataset.carisma,
        iniciativa: cardElement.dataset.iniciativa,
        ataqueMagico: cardElement.dataset.ataqueMagico,
        ataqueFisicoCorpo: cardElement.dataset.ataqueFisicoCorpo,
        ataqueFisicoDistancia: cardElement.dataset.ataqueFisicoDistancia,
        defesaPersonagem: cardElement.dataset.defesaPersonagem,
        esquivaPersonagem: cardElement.dataset.esquivaPersonagem,
    };

    // Preenche o offcanvas genérico com os dados
    const offcanvas = document.getElementById('offcanvasFichaPersonagem');
    if (!offcanvas) {
        console.error('Offcanvas #offcanvasFichaPersonagem não encontrado');
        return;
    }

    // Atualiza o título
    const titulo = offcanvas.querySelector('#offcanvasFichaPersonagemLabel');
    if (titulo) {
        titulo.innerHTML = `<i class="fa-solid fa-scroll me-2"></i>Ficha de ${personagem.nome}`;
    }

    // Atualiza o conteúdo (encontra o body do offcanvas)
    const bodyOffcanvas = offcanvas.querySelector('.offcanvas-body');
    if (bodyOffcanvas) {
        bodyOffcanvas.innerHTML = `
            <div class="row g-2">
                <!-- Header do Personagem -->
                <div class="col-12 mb-3 border-bottom border-secondary pb-2">
                    <div><small><strong><i class="fa-solid fa-user-shield"></i> Raça:</strong> ${personagem.raca}</small></div>
                    <div><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Classe:</strong> ${personagem.classe}</small></div>
                    <div><small><strong><i class="fa-solid fa-signal"></i> Nível:</strong> ${personagem.level}</small></div>
                </div>

                <!-- Atributos Principais -->
                <div class="col-12 mb-2">
                    <small class="text-warning"><strong>⚔️ Atributos Principais</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-dumbbell"></i> Força:</strong> ${personagem.forca}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-bolt"></i> Agilidade:</strong> ${personagem.agilidade}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-brain"></i> Inteligência:</strong> ${personagem.inteligencia}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand"></i> Destreza:</strong> ${personagem.destreza}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-heart"></i> Vitalidade:</strong> ${personagem.vitalidade}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-eye"></i> Percepção:</strong> ${personagem.percepcao}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-book"></i> Sabedoria:</strong> ${personagem.sabedoria}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-comments"></i> Carisma:</strong> ${personagem.carisma}</small></div>

                <!-- Recursos de Vida/Mana -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-info"><strong>❤️ Recursos</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-heart"></i> Vida:</strong> ${personagem.vida}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-droplet"></i> Mana:</strong> ${personagem.mana}</small></div>

                <!-- Iniciativa e Bônus -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-success"><strong>⚡ Ações</strong></small>
                </div>
                <div class="col-12"><small><strong><i class="fa-solid fa-forward"></i> Iniciativa:</strong> ${personagem.iniciativa}</small></div>

                <!-- Ataques -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-danger"><strong>⚔️ Ataques</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Atk Mágico:</strong> ${personagem.ataqueMagico}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand-fist"></i> Atk Corpo:</strong> ${personagem.ataqueFisicoCorpo}</small></div>
                <div class="col-12 mt-2"><small><strong><i class="fa-solid fa-bullseye"></i> Atk Distância:</strong> ${personagem.ataqueFisicoDistancia}</small></div>

                <!-- Defesa -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-secondary"><strong>🛡️ Defesa</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-halved"></i> Defesa:</strong> ${personagem.defesaPersonagem}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-feather"></i> Esquiva:</strong> ${personagem.esquivaPersonagem}</small></div>
            </div>
        `;
    }

    // Abre o offcanvas usando Bootstrap
    const offcanvasInstance = new bootstrap.Offcanvas(offcanvas);
    offcanvasInstance.show();
}

window.abrirFichaPersonagem = abrirFichaPersonagem;
