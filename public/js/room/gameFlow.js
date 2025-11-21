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

window.getCardById = getCardById;
window.destacarPersonagem = destacarPersonagem;
window.setPlayerControlsEnabled = setPlayerControlsEnabled;
