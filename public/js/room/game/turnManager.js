// turnManager.js

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

window.ordenarIniciativas = ordenarIniciativas;
window.iniciarRodada = iniciarRodada;
window.finalizarRodada = finalizarRodada;
window.proximoTurno = proximoTurno;
window.proximoPhaseDepoisDaAcaoDoJogador = proximoPhaseDepoisDaAcaoDoJogador;
