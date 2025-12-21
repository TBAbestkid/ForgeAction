// // ====== DYNAMIC MEMBERS MANAGEMENT ======
// function findPersonagensContainer() {
//     // Prioriza a coluna direita explícita `#coluna-personangens`, depois fallback para games-section
//     return document.getElementById('coluna-personangens') || document.querySelector('#games-section') || document.body;
// }

// function buildPersonagemCard(member) {
//     const id = String(member.personagemId ?? member.id ?? '');
//     const usuarioId = String(member.usuarioId ?? member.userId ?? '');
//     const nome = member.nome ?? member.userLogin ?? 'Jogador';
//     const vida = member.vida ?? member.hp ?? 0;
//     const vidaMax = member.vidaMax ?? vida ?? 0;
//     const iniciativa = member.iniciativa ?? 0;

//     const card = document.createElement('div');
//     card.className = 'bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card';
//     card.style.cursor = 'pointer';

//     card.setAttribute('data-bs-toggle', 'collapse');
//     card.setAttribute('data-bs-target', `#info-personagem-${id}`);
//     card.setAttribute('aria-expanded', 'false');
//     card.setAttribute('aria-controls', `info-personagem-${id}`);

//     card.dataset.cardId = id;
//     card.dataset.id = id;
//     card.dataset.vidaMax = vidaMax;
//     card.dataset.nome = nome;
//     card.dataset.vida = vida;
//     card.dataset.usuarioId = usuarioId;
//     card.dataset.iniciativa = iniciativa;

//     // default online flag (can be overridden via member.online)
//     card.dataset.online = (member?.online ? 'true' : 'false');

//     // Build inner structure with explicit status dot
//     const nomeEl = document.createElement('strong');
//     nomeEl.className = 'small personagem-nome';
//     nomeEl.textContent = nome;

//     const statusDot = document.createElement('span');
//     statusDot.setAttribute('data-online-dot', '');
//     statusDot.className = (member?.online ? 'status-dot online' : 'status-dot offline');
//     nomeEl.appendChild(statusDot);

//     const wrapper = document.createElement('div');
//     wrapper.className = 'd-flex align-items-center gap-1';

//     const progressWrap = document.createElement('div');
//     progressWrap.className = 'progress mt-1 flex-grow-1';
//     progressWrap.style.height = '14px';
//     progressWrap.style.fontSize = '0.7rem';

//     const progressBar = document.createElement('div');
//     progressBar.className = 'progress-bar bg-success d-flex justify-content-center align-items-center';
//     progressBar.setAttribute('role', 'progressbar');
//     const percent = (vidaMax ? (vida / vidaMax * 100) : 0);
//     progressBar.style.width = `${percent}%`;
//     progressBar.textContent = `${vida}/${vidaMax}`;

//     progressWrap.appendChild(progressBar);
//     wrapper.appendChild(progressWrap);

//     card.appendChild(nomeEl);
//     card.appendChild(wrapper);

//     const collapse = document.createElement('div');
//     collapse.id = `info-personagem-${id}`;
//     collapse.className = 'collapse mt-1';
//     collapse.style.minHeight = 'auto';
//     collapse.style.maxHeight = '25vh';
//     collapse.style.overflow = 'hidden';

//     const collapseInner = document.createElement('div');
//     collapseInner.className = 'bg-dark rounded p-1 text-start text-light';
//     collapseInner.style.fontSize = '0.7rem';
//     collapseInner.innerHTML = `<strong>Jogador:</strong> ${nome}<br>`;

//     collapse.appendChild(collapseInner);
//     card.appendChild(collapse);

//     return card;
// }

// function addOrUpdatePersonagem(member) {
//     if (!member) return;
//     const id = String(member.personagemId ?? member.id ?? '');
//     const usuarioId = String(member.usuarioId ?? member.userId ?? member.usuario_id ?? '');
//     const usuarioLogin = member.usuarioLogin ?? member.userLogin ?? member.login ?? '';

//     // 1) Try to find existing card by personagem id
//     let existing = id ? getCardById(id) : null;

//     // 2) If not found by id, try to find by usuarioId or usuarioLogin or nome
//     if (!existing && usuarioId) {
//         existing = document.querySelector(`.personagem-card[data-usuario-id="${usuarioId}"]`);
//     }
//     if (!existing && usuarioLogin) {
//         existing = Array.from(document.querySelectorAll('.personagem-card')).find(c => {
//             const cLogin = String(c.dataset.usuarioLogin || '');
//             const cUsuarioId = String(c.dataset.usuarioId || c.dataset['usuarioId'] || '');
//             const cNome = String(c.dataset.nome || '');
//             return cLogin === usuarioLogin || cUsuarioId === usuarioLogin || cNome === usuarioLogin || c.textContent.includes(usuarioLogin);
//         });
//     }

//     // 3) If found, update fields and return (avoid creating duplicate)
//     if (existing) {
//         if (id) existing.dataset.id = id;
//         if (id) existing.dataset.cardId = id;
//         if (usuarioId) existing.dataset.usuarioId = usuarioId;
//         if (usuarioLogin) existing.dataset.usuarioLogin = usuarioLogin;
//         existing.dataset.nome = member.nome ?? existing.dataset.nome;
//         const vidaVal = (member.vida ?? member.hp ?? existing.dataset.vida ?? 0);
//         const vidaMaxVal = (member.vidaMax ?? member.hpMax ?? existing.dataset.vidaMax ?? vidaVal);
//         existing.dataset.vida = String(vidaVal);
//         existing.dataset.vidaMax = String(vidaMaxVal);

//         const progressBar = existing.querySelector('.progress-bar');
//         if (progressBar) {
//             const v = parseInt(vidaVal, 10) || 0;
//             const vm = parseInt(vidaMaxVal, 10) || v || 1;
//             progressBar.style.width = `${(vm ? (v / vm * 100) : 0)}%`;
//             progressBar.textContent = `${v}/${vm} HP`;
//         }

//         // Update online flag if provided
//         if (typeof member.online !== 'undefined') {
//             existing.dataset.online = member.online ? 'true' : 'false';
//             const dot = existing.querySelector('[data-online-dot]');
//             if (dot) {
//                 dot.classList.toggle('online', !!member.online);
//                 dot.classList.toggle('offline', !member.online);
//             }
//             // Also update any indicator inside offcanvas/mobile lists
//             const listaItem = document.querySelector(`#lista-membros [data-personagem-id="${id}"]`);
//             if (listaItem) {
//                 const dotEl = listaItem.querySelector('.members-list-dot');
//                 if (dotEl) {
//                     dotEl.classList.toggle('online', !!member.online);
//                     dotEl.classList.toggle('offline', !member.online);
//                 }
//             }
//         }

//         // Ensure card is visible (remove d-none if hidden)
//         existing.classList.remove('d-none');
//         updateMembersListsAdd(member);
//         return;
//     }

//     // 4) No existing card found -> create new one
//     const container = findPersonagensContainer();
//     const card = buildPersonagemCard(member);

//     // Insere no container dedicado `#coluna-personangens` quando presente
//     const desktopWrapper = document.getElementById('coluna-personangens') || container;
//     if (desktopWrapper) {
//         desktopWrapper.appendChild(card);
//     }

//     // Atualiza listas mobile/offcanvas
//     updateMembersListsAdd(member);
// }

// function removePersonagem(personagemId) {
//     if (!personagemId) return;
//     const id = String(personagemId);
//     const card = getCardById(id);
//     if (card && card.parentNode) card.parentNode.removeChild(card);

//     // Remove collapse element if exists
//     const collapseEl = document.getElementById(`info-personagem-${id}`);
//     if (collapseEl && collapseEl.parentNode) collapseEl.parentNode.removeChild(collapseEl);

//     // Atualiza mobile/offcanvas
//     updateMembersListsRemove(id);
// }

// function updateMembersListsAdd(member) {
//     // Offcanvas members list
//     const lista = document.getElementById('lista-membros');
//     if (lista) {
//         const exists = lista.querySelector(`[data-personagem-id="${member.personagemId}"]`);
//         if (!exists) {
//             const li = document.createElement('li');
//             li.className = 'list-group-item bg-dark text-light d-flex justify-content-between align-items-center';
//             li.dataset.personagemId = member.personagemId;
//             const name = document.createElement('span');
//             name.textContent = (member.usuarioLogin ?? member.nome ?? 'Jogador');
//             const dotWrap = document.createElement('span');
//             const dot = document.createElement('span');
//             dot.className = (member.online ? 'members-list-dot online' : 'members-list-dot offline');
//             dotWrap.appendChild(dot);
//             li.appendChild(name);
//             li.appendChild(dotWrap);
//             lista.appendChild(li);
//         }
//     }

//     // Mobile players list
//     const mobilePlayers = document.querySelector('#mobile-players .d-flex') || document.querySelector('#mobile-players');
//     if (mobilePlayers) {
//         // mobile section has multiple child items; we append a simple card
//         const existing = document.querySelector(`#mobile-players [data-personagem-id="${member.personagemId}"]`);
//         if (!existing) {
//             const el = document.createElement('div');
//             el.className = 'bg-dark rounded p-2 text-white text-center';
//             el.dataset.personagemId = member.personagemId;
//             el.innerHTML = `<strong class="small">${member.nome ?? member.usuarioLogin ?? 'Jogador'}</strong><div class="progress mt-1" style="height: 12px;"><div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div></div>`;
//             const dot = document.createElement('span');
//             dot.className = (member.online ? 'members-list-dot online' : 'members-list-dot offline');
//             el.appendChild(dot);
//             const target = document.querySelector('#mobile-players .d-flex') || document.querySelector('#mobile-players');
//             target.appendChild(el);
//         }
//     }
// }

// function updateMembersListsRemove(personagemId) {
//     const id = String(personagemId);
//     const lista = document.getElementById('lista-membros');
//     if (lista) {
//         const li = lista.querySelector(`[data-personagem-id="${id}"]`);
//         if (li && li.parentNode) li.parentNode.removeChild(li);
//     }

//     const mobileEl = document.querySelector(`#mobile-players [data-personagem-id="${id}"]`);
//     if (mobileEl && mobileEl.parentNode) mobileEl.parentNode.removeChild(mobileEl);
// }

// window.findPersonagensContainer = findPersonagensContainer;
// window.buildPersonagemCard = buildPersonagemCard;
// window.addOrUpdatePersonagem = addOrUpdatePersonagem;
// window.removePersonagem = removePersonagem;
// window.updateMembersListsAdd = updateMembersListsAdd;
// window.updateMembersListsRemove = updateMembersListsRemove;

// function setPersonagemOnline(personagemId, online) {
//     if (!personagemId) return;
//     const id = String(personagemId);
//     const card = document.querySelector(`.personagem-card[data-id="${id}"]`);
//     if (card) {
//         card.dataset.online = online ? 'true' : 'false';
//         const dot = card.querySelector('[data-online-dot]');
//         if (dot) {
//             dot.classList.toggle('online', !!online);
//             dot.classList.toggle('offline', !online);
//         }
//     }
//     // update offcanvas list
//     const li = document.querySelector(`#lista-membros [data-personagem-id="${id}"]`);
//     if (li) {
//         const dotEl = li.querySelector('.members-list-dot');
//         if (dotEl) {
//             dotEl.classList.toggle('online', !!online);
//             dotEl.classList.toggle('offline', !online);
//         }
//     }
// }

// window.setPersonagemOnline = setPersonagemOnline;
