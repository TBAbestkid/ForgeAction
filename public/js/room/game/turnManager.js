// turnManager.js
// Variáveis globais para controle de ações do mestre
let acaoMestreAtual = null;
let personagemSelecionadoId = null;
let usuarioSelecionadoId = null;
let vidaSelecionada = 0;
let ws = null;

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
    // Event listeners serão adicionados dinamicamente no turnUIManager.js

    // Botoes do Player
    // Event listeners serão adicionados dinamicamente no turnUIManager.js
});

// Função para iniciar a rodada
function iniciarRodada() {
    console.log(' 🚀 Iniciando rodada...');

    // Envia notificação de sistema
    window.EnviarAcao('turnoIniciado', {
        nomeJogador: window.CHAT_CONFIG?.userLogin || 'Mestre'
    });

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
    console.log(' ⏭️ Avançando para o próximo turno...');

    // Verifica se é realmente a vez do jogador
    if (!window.turnState.rodadaIniciada) {
        console.warn('❌ Rodada não iniciada');
        return;
    }

    const isMyTurn = window.turnState.turnoAtual === String(window.CHAT_CONFIG?.userId) ||
                     (window.isMestre && window.turnState.turnoAtual === "mestre");

    if (!isMyTurn) {
        console.warn('❌ Não é sua vez!');
        return;
    }

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
        oculto,
        nomeJogador: window.CHAT_CONFIG?.userLogin || 'Jogador',
        usuarioId: window.CHAT_CONFIG?.userId
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

    // Pega dados do card na hora do clique
    const card = document.getElementById(`personagem-online-${personagemId}-pc`) ||
                 document.getElementById(`personagem-online-${personagemId}-mb`);

    const vidaAtual = card ? parseInt(card.dataset.vida || 100) : 100;

    personagemSelecionadoId = personagemId;
    usuarioSelecionadoId = usuarioId;
    vidaSelecionada = vidaAtual; // Armazena vida atual

    console.log(`👆 Personagem selecionado: ${personagemId} (vida: ${vidaAtual})`);
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

const btnConfirmarValor = document.getElementById('btnConfirmarValor');
if (btnConfirmarValor) {
    btnConfirmarValor.addEventListener('click', () => {

        const valor = parseInt(document.getElementById('inputValor').value);

        if (isNaN(valor) || valor < 0) return;

        enviarAcaoMestre(valor);
        resetarSelecao();
    });
}

function enviarAcaoMestre(valor) {
    const targetUsuarioId = usuarioSelecionadoId || personagemSelecionadoId;

    console.log(`🎯 Enviando ação do mestre: ${acaoMestreAtual} (usuário ${targetUsuarioId}, personagem ${personagemSelecionadoId}) com valor ${valor}`);
    console.log(`💾 personagemSelecionadoId type: ${typeof personagemSelecionadoId}, value: ${personagemSelecionadoId}`);

    // Get personagem name from card for better logs
    const card = document.getElementById(`personagem-online-${personagemSelecionadoId}-pc`) ||
                 document.getElementById(`personagem-online-${personagemSelecionadoId}-mb`);
    const nomePersonagem = card?.dataset.nome || 'Personagem';

    // 🔥 Aplicar mudança de vida localmente usando a vida já armazenada
    if (acaoMestreAtual === 'causarDano') {
        const novaVida = vidaSelecionada - valor; // Dano reduz vida
        console.log(`⚔️ DANO: vida atual ${vidaSelecionada} - dano ${valor} = ${novaVida}`);
        window.atualizarVidaPersonagemCard(personagemSelecionadoId, novaVida);

        // Envia notificação
        window.EnviarAcao('causarDano', {
            nomeJogador: window.CHAT_CONFIG?.userLogin || 'Mestre',
            nomePersonagem: nomePersonagem,
            valor: valor
        });
    } else if (acaoMestreAtual === 'curarPersonagem') {
        const novaVida = vidaSelecionada + valor; // Cura aumenta vida
        console.log(`💚 CURA: vida atual ${vidaSelecionada} + cura ${valor} = ${novaVida}`);
        window.atualizarVidaPersonagemCard(personagemSelecionadoId, novaVida);

        // Envia notificação
        window.EnviarAcao('curarPersonagem', {
            nomeJogador: window.CHAT_CONFIG?.userLogin || 'Mestre',
            nomePersonagem: nomePersonagem,
            valor: valor
        });
    } else if (acaoMestreAtual === 'uparPersonagem') {
        // Envia notificação de upgrade para o player
        const card = document.getElementById(`personagem-online-${personagemSelecionadoId}-pc`) ||
                     document.getElementById(`personagem-online-${personagemSelecionadoId}-mb`);

        // Prepara dados do personagem para upgrade
        const dadosUpgrade = {
            id: personagemSelecionadoId,
            nome: nomePersonagem,
            level: card?.dataset.level || 1,
            forca: card?.dataset.forca || 0,
            agilidade: card?.dataset.agilidade || 0,
            inteligencia: card?.dataset.inteligencia || 0,
            destreza: card?.dataset.destreza || 0,
            vitalidade: card?.dataset.vitalidade || 0,
            percepcao: card?.dataset.percepcao || 0,
            sabedoria: card?.dataset.sabedoria || 0,
            carisma: card?.dataset.carisma || 0
        };

        // Envia notificação
        window.EnviarAcao('uparPersonagem', {
            nomeJogador: window.CHAT_CONFIG?.userLogin || 'Mestre',
            nomePersonagem: nomePersonagem
        });

        // Envia evento via WebSocket para abrir offcanvas no player
        ws.send('/app/enviar/' + window.CHAT_CONFIG?.salaId, {
            acao: 'abrirUpgradePersonagem',
            usuarioAlvo: targetUsuarioId,
            personagemId: personagemSelecionadoId,
            dadosUpgrade: dadosUpgrade
        });
    } else if (acaoMestreAtual === 'cederTurno') {
        // Envia notificação
        window.EnviarAcao('cederTurno', {
            nomeJogador: window.CHAT_CONFIG?.userLogin || 'Mestre',
            nomeAlvo: 'Próximo Jogador'
        });
    }

    ws.send('/app/backchannel/rodadas', {
        acao: acaoMestreAtual, // 'dano', 'cura' ou 'upar'
        salaId: window.CHAT_CONFIG?.salaId,
        usuarioId: targetUsuarioId,
        valor: valor // Envia o valor para o servidor notificar
    });

    // 🔥 Broadcast da atualização de vida para todos os jogadores
    if (acaoMestreAtual === 'causarDano' || acaoMestreAtual === 'curarPersonagem') {
        const salaId = window.CHAT_CONFIG?.salaId;
        const novaVida = acaoMestreAtual === 'causarDano'
            ? vidaSelecionada - valor
            : vidaSelecionada + valor;

        ws.send('/app/enviar/' + salaId, {
            acao: 'atualizacaoVida',
            personagemId: personagemSelecionadoId,
            novaVida: novaVida,
            salaId: salaId
        });
    }

    // Se a ação for o cederTurno, passa o pro proximo turno
    if (acaoMestreAtual === 'cederTurno') {
        avancarTurno();
    }

}

function resetarSelecao() {
    acaoMestreAtual = null;
    personagemSelecionadoId = null;
    usuarioSelecionadoId = null;
    vidaSelecionada = 0;
    document.getElementById('inputValor').value = '';
    if (typeof window.limparModoAcao === 'function') {
        window.limparModoAcao();
    }
}

// Ativa o modo de ação do mestre (dano, cura, upgrade, cederTurno)
function ativarModoAcao(modo) {
    console.log(`🎬 Ativando modo: ${modo}`);

    // Mapeia o modo visual para a ação do mestre
    const modoMap = {
        'dano': 'causarDano',
        'cura': 'curarPersonagem',
        'upgrade': 'uparPersonagem'
    };

    acaoMestreAtual = modoMap[modo] || null;

    if (acaoMestreAtual) {
        window.definirModoAcao?.(modo);
        ativarModoSelecao();
    }
}

// Ativa o modo de ceder turno (permite selecionar próximo jogador)
function ativarModoCederTurno() {
    console.log(`🎬 Ativando modo: cederTurno`);
    acaoMestreAtual = 'cederTurno';
    ativarModoSelecao();
}

// Expor função globalmente para outros módulos usarem
window.avancarTurno = avancarTurno;
window.toggleOpcoesDados = toggleOpcoesDados;
window.permitirJogada = permitirJogada;
window.ativarModoSelecao = ativarModoSelecao;
window.ativarModoAcao = ativarModoAcao;
window.ativarModoCederTurno = ativarModoCederTurno;
