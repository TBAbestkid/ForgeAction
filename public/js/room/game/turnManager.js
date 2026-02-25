// turnManager.js
document.addEventListener('DOMContentLoaded', () => {
    ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ WebSocket não disponível');
        return;
    }

    const btnIniciar = document.getElementById('btnIniciarTurno');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            iniciarRodada();
        });
    }

    const btnMestre = document.getElementById('btnLancarMestre');
    if (btnMestre) {
        btnMestre.addEventListener('click', () => {
            lancarDadosMestre();
        });
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
});

// Função para iniciar a rodada
function iniciarRodada() {
    const salaId = window.CHAT_CONFIG?.salaId;
    console.log(' 🚀 Iniciando rodada...');

    ws.send('/app/backchannel/rodadas', {
            acao: "iniciarRodada",
            salaId
        }
    );
}

function lancarDadosMestre() {

    const turnControls = document.getElementById('turnControls');
    const diceOptions  = document.getElementById('diceOptions');

    if (!turnControls) return;

    const estaAberto = !turnControls.classList.contains('d-none');

    if (estaAberto) {
        // 🔒 FECHAR
        turnControls.classList.add('d-none');
        diceOptions?.classList.add('d-none');
        console.log('🔒 Mestre não irá lançar mais dados');
    } else {
        // 🔓 ABRIR
        turnControls.classList.remove('d-none');
        diceOptions?.classList.remove('d-none');
        console.log('🧙‍♂️ Mestre irá lançar dados');
    }
}

document.querySelectorAll('.diceBtn').forEach(btn => {

    btn.addEventListener('click', () => {

        const faces = parseInt(btn.getAttribute('data-sides'));
        const ocultar = document.getElementById('ocultarDados')?.checked ?? false;

        const valor = Math.floor(Math.random() * faces) + 1;

        ws.send('/app/backchannel/rodadas', {
            acao: "lancarDadosMestre",
            salaId: window.CHAT_CONFIG?.salaId,
            conteudo: {
                faces,
                valor,
                ocultar
            }
        });

    });

});

function permitirJogada() {
    console.log(' 🎲 Permitir jogada extra acionada');
    // Vai voltar pro jogador anterior pra pode Lançar dado ou só pular

    ws.send('/app/backchannel/rodadas', {
            acao: "permitirJogadaExtra",
            salaId
        }
    );
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
