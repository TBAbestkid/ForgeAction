// turnManager.js
document.addEventListener('DOMContentLoaded', () => {
    ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ WebSocket não disponível');
        return;
    }

    document.querySelectorAll('.diceBtn').forEach(btn => {

        btn.addEventListener('click', () => {

            const faces = parseInt(btn.dataset.sides);

            emitirLancamentoDados(faces);

            // Fecha menu depois de escolher
            document.getElementById('diceOptions')?.classList.add('d-none');

        });

    });

    // Botoes do Mestre
    const btnIniciar = document.getElementById('btnIniciarTurno');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            if (!window.turnState.rodadaIniciada) {
                iniciarRodada();
            } else {
                avancarTurno();
            }
        });
    }

    const btnMestre = document.getElementById('btnLancarMestre');
    if (btnMestre) {
        btnMestre.addEventListener('click', toggleOpcoesDados);
    }

    const btnPermitir = document.getElementById('btnPermitirJogadaExtra');
    if (btnPermitir) {
        btnPermitir.addEventListener('click', () => {
            permitirJogada();
        });
    }

    const btnDano = document.getElementById('btnDano');
    if (btnDano) {
        btnDano.addEventListener('click', () => {
            acaoMestreAtual = 'causarDano';
            ativarModoSelecao();
        });
    }

    const btnCurar = document.getElementById('btnCurar');
    if (btnCurar) {
        btnCurar.addEventListener('click', () => {
            acaoMestreAtual = 'curarPersonagem';
            ativarModoSelecao();
        });
    }

    const btnUpar = document.getElementById('btnUpar');
    if (btnUpar) {
        btnUpar.addEventListener('click', () => {
            acaoMestreAtual = 'uparPersonagem';
            ativarModoSelecao();
        });
    }

    // Botoes do Player
    const btnRoll = document.getElementById('btn-roll');
    if (btnRoll) {
        btnRoll.addEventListener('click', toggleOpcoesDados);
    }

    const btnSkip = document.getElementById('btn-skip');
    if (btnSkip) {
        btnSkip.addEventListener('click', () => {
            avancarTurno();
        });
    }
});

// Função para iniciar a rodada
function iniciarRodada() {
    console.log(' 🚀 Iniciando rodada...');

    ws.send('/app/backchannel/rodadas', {
            acao: "iniciarRodada",
            salaId: window.CHAT_CONFIG?.salaId
        }
    );
}

// Função para avançar para o próximo turno
function avancarTurno() {
    console.log(' ⏭️ Avançando para o próximo turno...');
    ws.send('/app/backchannel/rodadas', {
        acao: "proximoTurno",
        salaId: window.CHAT_CONFIG?.salaId
    });
}

function toggleOpcoesDados() {

    const turnControls = document.getElementById('turnControls');
    const diceOptions  = document.getElementById('diceOptions');

    if (!turnControls || !diceOptions) return;

    turnControls.classList.remove('d-none');
    diceOptions.classList.toggle('d-none');
}

function emitirLancamentoDados(faces) {

    console.log('🎲 Lançar dados acionado');

    const valor = Math.floor(Math.random() * faces) + 1;

    const salaId = window.CHAT_CONFIG?.salaId;

    let oculto = false;

    if (window.isMestre) {
        const checkbox = document.getElementById('ocultarDados');
        oculto = checkbox?.checked ?? false;
    }

    ws.send('/app/enviar/' + salaId, {
        acao: "lancarDados",
        salaId,
        faces,
        valor,
        oculto
    });
}

function permitirJogada() {
    console.log(' 🎲 Permitir jogada extra acionada');
    // Vai voltar pro jogador anterior pra pode Lançar dado ou só pular

    ws.send('/app/backchannel/rodadas', {
        acao: "cederTurno",
        salaId: window.CHAT_CONFIG?.salaId,
    });
}

function ativarModoSelecao() {
    document.querySelectorAll('[id^="personagem-online-"]').forEach(el => {
        el.classList.add('selecionavel');
    });
}

function selecionarPersonagem(personagemId) {

    if (!acaoMestreAtual) return;

    personagemSelecionadoId = personagemId;

    console.log(`👆 Personagem selecionado: ${personagemId}`);

    // Remove destaque visual
    document.querySelectorAll('.selecionavel').forEach(el => {
        el.classList.remove('selecionavel');
    });

    if (acaoMestreAtual === 'upar') {
        enviarAcaoMestre(0); // sem valor
        resetarSelecao();
        return;
    }

    // Abre modal para dano ou cura
    const modal = new bootstrap.Modal(document.getElementById('modalValor'));
    modal.show();
}

document.getElementById('btnConfirmarValor')
    .addEventListener('click', () => {

        const valor = parseInt(document.getElementById('inputValor').value);

        if (isNaN(valor) || valor < 0) return;

        enviarAcaoMestre(valor);
        resetarSelecao();
});

function enviarAcaoMestre(valor) {

    ws.send('/app/backchannel/rodadas', {
        acao: acaoMestreAtual, // 'dano', 'cura' ou 'upar'
        salaId: window.CHAT_CONFIG?.salaId,
        conteudo: {
            personagemId: personagemSelecionadoId,
            valor
        }
    });

}

function resetarSelecao() {
    acaoMestreAtual = null;
    personagemSelecionadoId = null;
    document.getElementById('inputValor').value = '';
}
