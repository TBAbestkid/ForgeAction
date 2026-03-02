// ===== MOSTRA DADO COM TIMEOUT DE LIMPEZA =====
function mostrarDado(dado, valor, autor, ocultar = false) {
    // Limpa timeout anterior se existir
    if (timeoutLimpezaDado) clearTimeout(timeoutLimpezaDado);

    if (placeholder) {
        if (ocultar) {
            // Se for mestre ocultando, mostra apenas mensagem genérica
            placeholder.textContent = `🎲 ${autor} está rolando...`;
        } else {
            placeholder.textContent = `🎲 ${autor} rolou D${dado} = ${valor}`;
        }
    }

    // Limpa o dado após 4 segundos
    timeoutLimpezaDado = setTimeout(() => {
        if (placeholder && rodadaAtiva) {
            const atual = ordemTurnos[turnoIndex];
            if (atual) {
                placeholder.textContent = `🕒 Turno de ${atual.nome}`;
                const isMyTurn = String(atual.usuarioId) === userId;
                if (isMyTurn) placeholder.textContent += ' (Sua vez!)';
            } else {
                placeholder.textContent = '🎲 Aguardando...';
            }
        }
        timeoutLimpezaDado = null;
    }, 4000);
}

function atualizarTurnoUI() {
    if (!rodadaAtiva) {
        if (placeholder) placeholder.textContent = '🎲 Aguardando início do turno...';
        return;
    }

    const atual = ordemTurnos[turnoIndex];
    if (!atual) return;

    if (placeholder) {
        placeholder.textContent = `🕒 Turno de ${atual.nome}`;
        const isMyTurn = String(atual.usuarioId) === userId;
        if (isMyTurn) placeholder.textContent += ' (Sua vez!)';
    }

    destacarPersonagem(atual.card);
    const isMyTurn = String(atual.usuarioId) === userId;
    setPlayerControlsEnabled(isMyTurn && phase === 'player', atual.personagemId);

    atualizarBotoesMestre();
}

function atualizarBotoesMestre() {
    if (!isMestre) return;

    debugLog('🔄 Atualizando botões do mestre');

    const btnInicio = document.getElementById('btnIniciarTurno');
    const btnMestre = document.getElementById('btn-lancar-mestre');
    const btnPermitir = document.getElementById('btn-permitir-jogada');
    const iconInicio = btnInicio?.querySelector('i.fa-solid');
    const btnDano = document.getElementById('btn-dano');
    const btnCura = document.getElementById('btn-curar');
    const btnUpar = document.getElementById('btn-upar');

    if (!btnInicio) {
        debugLog('⚠️ Botão iniciar não encontrado');
        return;
    }

    debugLog('Estado atual:', { rodadaAtiva, phase });

    // Ativar/desativar botões baseado no estado
    if (btnMestre) btnMestre.disabled = !rodadaAtiva;
    if (btnDano) btnDano.disabled = !rodadaAtiva;
    if (btnCura) btnCura.disabled = !rodadaAtiva;
    if (btnUpar) btnUpar.disabled = !rodadaAtiva;

    if (!rodadaAtiva) {
        btnInicio.disabled = false;
        btnInicio.classList.remove('btn-outline-secondary');
        btnInicio.classList.add('btn-outline-success');
        iconInicio.classList.remove('fa-pause', 'fa-forward');
        iconInicio.classList.add('fa-play');
        if (btnPermitir) btnPermitir.disabled = true;
        return;
    }

    if (phase === 'master') {
        btnInicio.disabled = false;
        btnInicio.classList.remove('btn-outline-secondary');
        btnInicio.classList.add('btn-outline-success');
        iconInicio.classList.remove('fa-pause', 'fa-play');
        iconInicio.classList.add('fa-forward');
        if (btnPermitir) btnPermitir.disabled = false;

        // Habilitar ações do mestre
        if (btnMestre) btnMestre.disabled = false;
        if (btnDano) btnDano.disabled = false;
        if (btnCura) btnCura.disabled = false;
        if (btnUpar) btnUpar.disabled = false;
        return;
    }

    if (phase === 'player') {
        btnInicio.disabled = true;
        btnInicio.classList.remove('btn-outline-success');
        btnInicio.classList.add('btn-outline-secondary');
        iconInicio.classList.remove('fa-play', 'fa-forward');
        iconInicio.classList.add('fa-pause');
        if (btnPermitir) btnPermitir.disabled = false;
    }
}

function handleVidaChange(personagemId, valor, tipo) {
    if (!isMestre || !rodadaAtiva) return;

    debugLog('🩺 Alterando vida:', { personagemId, valor, tipo });

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

    const progressBar = card.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = `${(novaVida / vidaMaxima) * 100}%`;
        progressBar.textContent = `${novaVida}/${vidaMaxima} HP`;
    }

    card.dataset.vida = novaVida;

    enviarAcao({
        acao: tipo === 'dano' ? 'danoRecebido' : 'curaRecebida',
        personagemId,
        valor,
        vidaAtual: novaVida
    });
}

function ativarModoMestre(modo) {
    if (!isMestre || !rodadaAtiva) return;
    debugLog('🎯 Ativando modo mestre:', modo);

    const btnDano = document.getElementById('btn-dano');
    const btnCurar = document.getElementById('btn-curar');
    const btnUpar = document.getElementById('btn-upar');

    [btnDano, btnCurar, btnUpar].forEach(btn => {
        if (btn) {
            btn.classList.remove('active');
            btn.removeAttribute('data-active');
        }
    });

    document.querySelectorAll('.personagem-card').forEach(c => {
        const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);
        c.classList.remove(
            'border-primary', 'border-danger',
            'border-success', 'border-info'
        );
        if (!isCurrentPlayer) c.classList.remove('border-3');
        c.style.cursor = 'default';
    });

    if (modoMestre === modo) {
        debugLog('🔄 Desativando modo:', modo);
        // Restaurar atributos de collapse removidos e limpar estado visual
        document.querySelectorAll('.personagem-card').forEach(c => {
            if (c.dataset.prevToggle) {
                c.setAttribute('data-bs-toggle', c.dataset.prevToggle);
                if (c.dataset.prevTarget) c.setAttribute('data-bs-target', c.dataset.prevTarget);
                if (c.dataset.prevExpanded) c.setAttribute('aria-expanded', c.dataset.prevExpanded);
                if (c.dataset.prevAriaControls) c.setAttribute('aria-controls', c.dataset.prevAriaControls);
                delete c.dataset.prevToggle;
                delete c.dataset.prevTarget;
                delete c.dataset.prevExpanded;
                delete c.dataset.prevAriaControls;
            }
            c.style.cursor = '';
            c.classList.remove('border-primary', 'border-3', 'border-info');
        });

        // Reabrir os collapses que estavam abertos antes
        document.querySelectorAll('.collapse').forEach(el => {
            if (el.dataset.prevShown === 'true') {
                try {
                    const inst = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                    inst.show();
                    delete el.dataset.prevShown;
                } catch (err) {
                    // ignore
                }
            }
        });

        modoMestre = null;
        return;
    }

    modoMestre = modo;

    const btnAtivo = modo === 'dano' ? btnDano :
                    modo === 'cura' ? btnCurar :
                    modo === 'up' ? btnUpar : null;

    if (btnAtivo) {
        btnAtivo.classList.add('active');
        btnAtivo.setAttribute('data-active', 'true');
    }

    const cards = document.querySelectorAll('.personagem-card');
    // Fecha todos os collapses abertos e registra quais estavam abertos
    document.querySelectorAll('.collapse.show').forEach(el => {
        if (el.id && el.id.startsWith('info-personagem-')) {
            try {
                const inst = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
                // marca que estava aberto
                el.dataset.prevShown = 'true';
                inst.hide();
            } catch (err) {
                // ignore
            }
        }
    });

    cards.forEach(c => {
        const isCurrentPlayer = String(c.dataset.id) === String(currentPlayerId);

        // Remove atributos de collapse temporariamente para evitar abrir detalhes enquanto mestre está em modo
        if (!c.dataset.prevToggle) {
            const prevToggle = c.getAttribute('data-bs-toggle');
            if (prevToggle) {
                c.dataset.prevToggle = prevToggle;
                c.dataset.prevTarget = c.getAttribute('data-bs-target') || '';
                c.dataset.prevExpanded = c.getAttribute('aria-expanded') || 'false';
                c.dataset.prevAriaControls = c.getAttribute('aria-controls') || '';
                c.removeAttribute('data-bs-toggle');
                c.removeAttribute('data-bs-target');
                c.removeAttribute('aria-expanded');
                c.removeAttribute('aria-controls');
            }
        }

        if (!isCurrentPlayer) {
            c.classList.add('border-primary', 'border-3');
            c.style.cursor = 'pointer';
        } else {
            // Destacado — permite ação também
            c.classList.add('border-info');
            c.style.cursor = 'pointer';
        }
    });

    debugLog('✅ Modo ativado:', modo, 'Cards encontrados:', cards.length);
}

window.mostrarDado = mostrarDado;
window.atualizarTurnoUI = atualizarTurnoUI;
window.handleVidaChange = handleVidaChange;
window.ativarModoMestre = ativarModoMestre;
