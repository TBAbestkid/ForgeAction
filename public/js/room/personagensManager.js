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
    const usuarioId = String(member.usuarioId ?? member.userId ?? member.usuario_id ?? '');
    const usuarioLogin = member.usuarioLogin ?? member.userLogin ?? member.login ?? '';

    // 1) Try to find existing card by personagem id
    let existing = id ? getCardById(id) : null;

    // 2) If not found by id, try to find by usuarioId or usuarioLogin or nome
    if (!existing && usuarioId) {
        existing = document.querySelector(`.personagem-card[data-usuario-id="${usuarioId}"]`);
    }
    if (!existing && usuarioLogin) {
        existing = Array.from(document.querySelectorAll('.personagem-card')).find(c => {
            const cLogin = String(c.dataset.usuarioLogin || '');
            const cUsuarioId = String(c.dataset.usuarioId || c.dataset['usuarioId'] || '');
            const cNome = String(c.dataset.nome || '');
            return cLogin === usuarioLogin || cUsuarioId === usuarioLogin || cNome === usuarioLogin || c.textContent.includes(usuarioLogin);
        });
    }

    // 3) If found, update fields and return (avoid creating duplicate)
    if (existing) {
        if (id) existing.dataset.id = id;
        if (id) existing.dataset.cardId = id;
        if (usuarioId) existing.dataset.usuarioId = usuarioId;
        if (usuarioLogin) existing.dataset.usuarioLogin = usuarioLogin;
        existing.dataset.nome = member.nome ?? existing.dataset.nome;
        const vidaVal = (member.vida ?? member.hp ?? existing.dataset.vida ?? 0);
        const vidaMaxVal = (member.vidaMax ?? member.hpMax ?? existing.dataset.vidaMax ?? vidaVal);
        existing.dataset.vida = String(vidaVal);
        existing.dataset.vidaMax = String(vidaMaxVal);

        const progressBar = existing.querySelector('.progress-bar');
        if (progressBar) {
            const v = parseInt(vidaVal, 10) || 0;
            const vm = parseInt(vidaMaxVal, 10) || v || 1;
            progressBar.style.width = `${(vm ? (v / vm * 100) : 0)}%`;
            progressBar.textContent = `${v}/${vm} HP`;
        }

        // Ensure card is visible (remove d-none if hidden)
        existing.classList.remove('d-none');
        updateMembersListsAdd(member);
        return;
    }

    // 4) No existing card found -> create new one
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

window.findPersonagensContainer = findPersonagensContainer;
window.buildPersonagemCard = buildPersonagemCard;
window.addOrUpdatePersonagem = addOrUpdatePersonagem;
window.removePersonagem = removePersonagem;
window.updateMembersListsAdd = updateMembersListsAdd;
window.updateMembersListsRemove = updateMembersListsRemove;
