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
                avancarTurnoMestre();
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
            console.log('🎯 Mestre permitiu uma jogada extra');
            acaoMestreAtual = 'cederTurno';
            ativarModoSelecao();
        });
    }

    const btnDano = document.getElementById('btnDano');
    if (btnDano) {
        btnDano.addEventListener('click', () => {
            console.log('🎯 Mestre causou dano');
            acaoMestreAtual = 'causarDano';
            window.definirModoAcao('dano');
            ativarModoSelecao();
        });
    }

    const btnCurar = document.getElementById('btnCurar');
    if (btnCurar) {
        btnCurar.addEventListener('click', () => {
            console.log('🎯 Mestre curou um personagem');
            acaoMestreAtual = 'curarPersonagem';
            window.definirModoAcao('cura');
            ativarModoSelecao();
        });
    }

    const btnUpar = document.getElementById('btnUpar');
    if (btnUpar) {
        btnUpar.addEventListener('click', () => {
            console.log('🎯 Mestre upou um personagem');
            acaoMestreAtual = 'uparPersonagem';
            window.definirModoAcao('upgrade');
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
            acao: "turnoMestre",
            salaId: window.CHAT_CONFIG?.salaId
        }
    );
}

// Função para avançar para o próximo turno
function avancarTurnoMestre() {
    console.log(' ⏭️ Avançando para o próximo turno...');
    ws.send('/app/backchannel/rodadas', {
        acao: "proximoTurno",
        salaId: window.CHAT_CONFIG?.salaId
    });
}

function avancarTurno() {
    console.log(' ⏭️ Mestre avançando para o próximo turno...');
    ws.send('/app/backchannel/rodadas', {
        acao: "turnoMestre",
        salaId: window.CHAT_CONFIG?.salaId
    });
}

function toggleOpcoesDados() {

    const turnControls = document.getElementById('turnControls');
    const diceOptions  = document.getElementById('diceOptions');

    if (!turnControls || !diceOptions) return;

    turnControls.classList.remove('d-none');
    diceOptions.classList.toggle('d-none');

    if (window.isMestre) {
        // Se for mestre, mostra opção de ocultar dados
        const ocultarOption = document.getElementById('ocultarDadosOption');
        if (ocultarOption) {
            ocultarOption.classList.remove('d-none');
        }
    }
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

// Não tô usando, só tá aq mesmo.
function permitirJogada() {
    console.log(' 🎲 Permitir jogada extra acionada');
    // Vai voltar pro jogador anterior pra pode Lançar dado ou só pular

    ws.send('/app/backchannel/rodadas', {
        acao: "cederTurno",
        salaId: window.CHAT_CONFIG?.salaId

    });
}

function ativarModoSelecao() {
    document.querySelectorAll('[id^="personagem-online-"]').forEach(el => {
        el.classList.add('selecionavel');
    });
}

function selecionarPersonagem(usuarioId, personagemId) {

    if (!acaoMestreAtual) return;

    personagemSelecionadoId = personagemId;
    usuarioSelecionadoId = usuarioId;

    console.log(`👆 Personagem selecionado: ${personagemId}`);
    console.log(`👆 Usuario selecionado: ${usuarioId}`);

    // Remove destaque visual
    document.querySelectorAll('.selecionavel').forEach(el => {
        el.classList.remove('selecionavel');
    });

    // upar ou dar jogada extra não precisa de valor, só clicar e pronto
    if (acaoMestreAtual === 'uparPersonagem') {
        enviarAcaoMestre(0); // sem valor
        resetarSelecao();
        return;
    } if (acaoMestreAtual === 'cederTurno') {
        // O id do usuário já está em `usuarioSelecionadoId`, então só precisamos enviar a ação.
        enviarAcaoMestre(0); // sem valor numérico
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
    const targetUsuarioId = usuarioSelecionadoId || personagemSelecionadoId;

    console.log(`🎯 Enviando ação do mestre: ${acaoMestreAtual} (usuário ${targetUsuarioId}, personagem ${personagemSelecionadoId}) com valor ${valor}`);

    ws.send('/app/backchannel/rodadas', {
        acao: acaoMestreAtual, // 'dano', 'cura' ou 'upar'
        salaId: window.CHAT_CONFIG?.salaId,
        usuarioId: targetUsuarioId
    });

    // Se a ação for o cederTurno, passa o pro proximo turno
    if (acaoMestreAtual === 'cederTurno') {
        avancarTurno();
    }

}

function resetarSelecao() {
    acaoMestreAtual = null;
    personagemSelecionadoId = null;
    usuarioSelecionadoId = null;
    document.getElementById('inputValor').value = '';
    window.limparModoAcao();
}
