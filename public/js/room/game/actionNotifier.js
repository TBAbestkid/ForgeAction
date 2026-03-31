/**
 * System Action Notifier
 * Gerencia eventos do sistema (turno, dados, dano, cura, etc)
 * Envia para o chat via WebSocket e gerencia notificações
 */

class ActionNotifier {
    constructor() {
        this.ws = null;
        this.chatCollapse = null;
        this.notificationBadge = null;
        this.notificationCount = 0;
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.ws = window.AppWebSocket;
            this.chatCollapse = document.getElementById('chatCollapse');
            this.notificationBadge = document.getElementById('chat-notification-badge');

            if (!this.ws) {
                console.error('❌ ActionNotifier: WebSocket não disponível');
                return;
            }

            // Monitorar abertura/fechamento do chat
            if (this.chatCollapse) {
                this.chatCollapse.addEventListener('show.bs.collapse', () => {
                    this.resetNotificationCount();
                });

                this.chatCollapse.addEventListener('hide.bs.collapse', () => {
                    // Nada por enquanto
                });
            }

            console.log('✅ ActionNotifier inicializado');
        });
    }

    /**
     * Envia uma ação de sistema para todos os jogadores
     * @param {string} acao - Tipo de ação (turno, dados, dano, cura, upar, etc)
     * @param {object} dados - Dados adicionais da ação
     */
    EnviarAcao(acao, dados = {}) {
        if (!this.ws) {
            console.error('❌ WebSocket não disponível para EnviarAcao');
            return;
        }

        const salaId = window.CHAT_CONFIG?.salaId;
        const userId = window.CHAT_CONFIG?.userId;
        const userLogin = window.CHAT_CONFIG?.userLogin;

        const payload = {
            acao: 'sistema',
            tipo: acao,
            conteudo: this.formatarMensagem(acao, dados),
            usuarioId: userId,
            autor: userLogin,
            salaId: salaId,
            timestamp: new Date().toISOString(),
            dados: dados
        };

        console.log('📤 EnviarAcao:', payload);

        this.ws.send('/app/enviar/' + salaId, payload);

        // Mostra notificação se chat estiver fechado
        this.mostrarNotificacao(acao, dados);
    }

    /**
     * Formata a mensagem de sistema baseado no tipo de ação
     */
    formatarMensagem(acao, dados = {}) {
        const nomeJogador = dados.nomeJogador || 'Um jogador';
        const nomePersonagem = dados.nomePersonagem || 'personagem';
        const valor = dados.valor || 0;

        const mensagens = {
            'turnoIniciado': () => `🎬 ${nomeJogador} iniciou a rodada!`,
            'turnoDo': () => `🔥 É a vez de ${nomeJogador}!`,
            'turnoMestre': () => `🧙 É turno do Mestre!`,
            'proximoTurno': () => `⏭️ ${nomeJogador} passou a vez para o próximo.`,
            'lancarDados': () => `🎲 ${nomeJogador} rolou um dado e tirou: **${valor}**!`,
            'lancarDadosOculto': () => `🎲 ${nomeJogador} rolou um dado (resultado oculto)`,
            'causarDano': () => `💥 ${nomeJogador} causou **${valor}** de dano em ${nomePersonagem}!`,
            'curarPersonagem': () => `✨ ${nomeJogador} curou ${nomePersonagem} em **${valor}** HP!`,
            'uparPersonagem': () => `⬆️ ${nomeJogador} fez ${nomePersonagem} fazer **Level Up**!`,
            'cederTurno': () => `🤝 ${nomeJogador} cedeu o turno para ${dados.nomeAlvo || 'outro jogador'}`,
            'playerEnter': () => `🟢 ${nomeJogador} entrou na sala!`,
            'playerExit': () => `🔴 ${nomeJogador} saiu da sala!`,
            'default': () => `📝 ${acao}: ${JSON.stringify(dados)}`
        };

        const formatter = mensagens[acao] || mensagens['default'];
        return formatter();
    }

    /**
     * Mostra notificação de badge se o chat estiver fechado
     */
    mostrarNotificacao(acao, dados) {
        // Só mostra notificação se o chat estiver fechado
        if (this.chatCollapse && !this.chatCollapse.classList.contains('show')) {
            this.notificationCount++;

            if (this.notificationBadge) {
                this.notificationBadge.style.display = 'inline-block';
                document.getElementById('chat-notification-count').textContent = this.notificationCount;
            }

            console.log(`🔔 Notificação adicionada: ${this.notificationCount}`);
        }
    }

    /**
     * Reseta o contador de notificações
     */
    resetNotificationCount() {
        this.notificationCount = 0;
        if (this.notificationBadge) {
            this.notificationBadge.style.display = 'none';
            document.getElementById('chat-notification-count').textContent = '0';
        }
        console.log('🔔 Notificações limpas');
    }
}

// Instância global
window.actionNotifier = new ActionNotifier();

// Função global de atalho
window.EnviarAcao = function(acao, dados = {}) {
    if (window.actionNotifier) {
        window.actionNotifier.EnviarAcao(acao, dados);
    } else {
        console.error('❌ ActionNotifier não está disponível');
    }
};

console.log('✅ actionNotifier.js carregado biridinho');
