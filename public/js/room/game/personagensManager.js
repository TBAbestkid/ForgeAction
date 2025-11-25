function adicionarPersonagemOnline(usuarioId, salaId, isMestre) {
    const colunaPersonagens = document.getElementById('coluna-personangens');
    const colunaMobile = document.getElementById('coluna-personangens-mobile');
    const listaMembros = document.getElementById('lista-membros');

    let personagem;
    const arrayPlayersOnline = new Array();
    $.ajax({
        url: `/api/salas/personagens/listar/${salaId}`,
        method: "GET",
        data: { _token: csrfToken },
        success: function (response) {
            if (!response || response.length === 0) {
                console.log('Nenhum personagem encontrado');
                return;
            }

            const personagem = response.find(p => String(p.usuarioId) === String(usuarioId));
            if (!personagem) {
                console.log('Personagem do usuário não encontrado na sala');
                return;
            }

            if (arrayPlayersOnline.some(p => p.id === personagem.id)) {
                console.log(`Personagem ${personagem.nome} já está na lista de online.`);
                return;
            }

            const personagemDiv = document.createElement('div');
            personagemDiv.className = 'bg-dark rounded p-1 text-center d-flex flex-column align-items-center';
            personagemDiv.style.cursor = 'pointer';
            personagemDiv.id = `personagem-online-${personagem.id}`;

            personagemDiv.setAttribute('data-bs-toggle', 'collapse');
            personagemDiv.setAttribute('data-bs-target', `#info-personagem-${personagem.id}`);
            personagemDiv.setAttribute('aria-expanded', 'false');
            personagemDiv.setAttribute('aria-controls', `info-personagem-${personagem.id}`);

            personagemDiv.dataset.cardId = personagem.id;
            personagemDiv.dataset.id = personagem.id;
            personagemDiv.dataset.online = "true";
            personagemDiv.dataset.vidaMax = personagem.vida;
            personagemDiv.dataset.nome = personagem.nome;
            personagemDiv.dataset.raca = personagem.raca;
            personagemDiv.dataset.classe = personagem.classe;
            personagemDiv.dataset.level = personagem.level;
            personagemDiv.dataset.vida = personagem.vida;
            personagemDiv.dataset.mana = personagem.mana;
            personagemDiv.dataset.usuarioId = personagem.usuarioId || '';
            personagemDiv.dataset.usuarioLogin = personagem.usuarioLogin || '';
            personagemDiv.dataset.forca = personagem.forca;
            personagemDiv.dataset.agilidade = personagem.agilidade;
            personagemDiv.dataset.inteligencia = personagem.inteligencia;
            personagemDiv.dataset.destreza = personagem.destreza;
            personagemDiv.dataset.vitalidade = personagem.vitalidade;
            personagemDiv.dataset.percepcao = personagem.percepcao;
            personagemDiv.dataset.sabedoria = personagem.sabedoria;
            personagemDiv.dataset.carisma = personagem.carisma;
            personagemDiv.dataset.ataqueMagico = personagem.ataqueMagico;
            personagemDiv.dataset.ataqueCorpo = personagem.ataqueFisicoCorpo;
            personagemDiv.dataset.ataqueDistancia = personagem.ataqueFisicoDistancia;
            personagemDiv.dataset.defesa = personagem.defesaPersonagem;
            personagemDiv.dataset.esquiva = personagem.esquivaPersonagem;
            personagemDiv.dataset.iniciativa = personagem.iniciativa;

            personagemDiv.innerHTML = `
                <strong class="small personagem-nome">${personagem.nome}</strong>

                <div class="progress mt-1 w-100" style="height: 14px; font-size:0.7rem;">
                    <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                        role="progressbar"
                        style="width: 100%;">
                        ${personagem.vida}/${personagem.vida}
                    </div>
                </div>

                <div id="info-personagem-${personagem.id}" class="collapse mt-1"
                    style="min-height: auto; max-height: 25vh; overflow: hidden;">
                    <div class="bg-dark rounded p-1 text-start text-light" style="font-size: 0.7rem;">
                        <strong>Classe:</strong> ${personagem.classe}<br>
                        <strong>Raça:</strong> ${personagem.raca}<br>
                        <strong>Nível:</strong> ${personagem.level}<br>
                    </div>
                </div>
            `;

            // 🔹 PC
            colunaPersonagens.appendChild(personagemDiv);

            // 🔹 Mobile (clone)
            const personagemMobile = personagemDiv.cloneNode(true);
            colunaMobile.appendChild(personagemMobile);

            // 🔹 Atualizar DOT no OFFCANVAS
            const item = document.querySelector(
                `#lista-membros li[data-personagem-id="${personagem.id}"]`
            );

            if (item) {
                const dot = item.querySelector('.members-list-dot');
                if (dot) {
                    dot.classList.remove('offline');
                    dot.classList.add('online');
                }
            }

            console.log(`Personagem ${personagem.nome} adicionado à lista de online.`);
            arrayPlayersOnline.push(personagem);
        }
    });
}



