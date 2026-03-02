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
        'bg-dark rounded p-1 text-center d-flex flex-column align-items-center';
    personagemDiv.style.cursor = 'pointer';

    personagemDiv.id = `personagem-online-${personagem.id}-${sufixo}`;

    // Collapse único por sufixo
    const collapseId = `info-personagem-${personagem.id}-${sufixo}`;

    personagemDiv.setAttribute('data-bs-toggle', 'collapse');
    personagemDiv.setAttribute('data-bs-target', `#${collapseId}`);
    personagemDiv.setAttribute('aria-expanded', 'false');

    personagemDiv.dataset.id = personagem.id;
    personagemDiv.dataset.usuarioId = personagem.usuarioId || '';
    personagemDiv.dataset.nome = personagem.nome;
    personagemDiv.dataset.vida = personagem.vida;

    personagemDiv.innerHTML = `
        <strong class="small personagem-nome">${personagem.nome}</strong>

        <div class="progress mt-1 w-100" style="height: 14px; font-size:0.7rem;">
            <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                role="progressbar"
                style="width: 100%;">
                ${personagem.vida}/${personagem.vida}
            </div>
        </div>

        <div id="${collapseId}" class="collapse mt-1"
            style="min-height: auto; max-height: 25vh; overflow: hidden;">

            <div class="bg-dark rounded p-1 text-start text-light"
                 style="font-size: 0.7rem;">

                <strong>Classe:</strong> ${personagem.classe}<br>
                <strong>Raça:</strong> ${personagem.raca}<br>
                <strong>Nível:</strong> ${personagem.level}<br>

            </div>
        </div>
    `;

    // 🔥 Listener inteligente
    personagemDiv.addEventListener('click', (e) => {

        if (!acaoMestreAtual) return;

        e.preventDefault();
        e.stopPropagation();

        selecionarPersonagem(personagem.id);
    });

    return personagemDiv;
}

function adicionarPersonagemOnline(personagem) {

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
