/**
 * =================================
 * 🎯 SISTEMA UNIFICADO
 * Convites e Cópia de Código da Sala
 * =================================
 *
 * Gerencia automaticamente:
 * - Modais de convite para múltiplos usuários
 * - Cópia de código da sala
 *
 * Detecta dinamicamente:
 * - Elementos com classe .btn-invite ou atributo [data-action="convidar"]
 * - Elementos com classe .btn-copy ou atributo [data-action="copiar-codigo"]
 *
 * Uso:
 * <button class="btn-invite" data-id="123">Convidar</button>
 * <button class="btn-copy" data-code="ABC123">Copiar Código</button>
 * <a href="#" class="dropdown-item" data-action="convidar">Convidar</a>
 * <a href="#" data-action="copiar-codigo" data-code="ABC123">Copiar</a>
 */

$(document).ready(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                      (typeof window.csrfToken !== 'undefined' ? window.csrfToken : '');

    let usuarios = [];

    /**
     * =================================
     * 📋 CONFIGURAÇÕES DINÂMICAS
     * =================================
     */
    function getSalaId(btn = null) {
        // Se estiver em uma sala (room.blade.php)
        if (typeof salaId !== 'undefined' && salaId !== null) {
            return salaId;
        }

        // Se o botão tiver data-id
        if (btn && $(btn).data('id')) {
            return $(btn).data('id');
        }

        // Se estiver na página inicial (index.blade.php)
        const pathMatch = window.location.pathname.match(/\/salas\/(\d+)/);
        if (pathMatch) {
            return pathMatch[1];
        }

        return null;
    }

    /**
     * =================================
     * 👥 GERENCIAR CONVITES
     * =================================
     */

    // ✅ Detectar cliques nos botões de convite
    $(document).on('click', '[data-action="convidar"], .btn-invite, .dropdown-item', function (e) {
        // Verificar se é um botão de convite
        if ($(this).data('action') === 'convidar' || $(this).hasClass('btn-invite')) {
            e.preventDefault();
            const salaId = getSalaId(this);

            if (!salaId) {
                showAlert?.('Erro: não foi possível identificar a sala.') || alert('Erro: sala não identificada');
                return;
            }

            carregarUsuariosDisponiveis(salaId);
            const modal = new bootstrap.Modal(document.getElementById('inviteModal'));
            modal.show();
        }
        // Alternativa: dropdown items que contenham "Convidar"
        else if ($(this).hasClass('dropdown-item') && $(this).text().includes('Convidar')) {
            e.preventDefault();
            const salaId = getSalaId(this);

            if (!salaId) {
                showAlert?.('Erro: não foi possível identificar a sala.') || alert('Erro: sala não identificada');
                return;
            }

            carregarUsuariosDisponiveis(salaId);
            const modal = new bootstrap.Modal(document.getElementById('inviteModal'));
            modal.show();
        }
    });

    /**
     * Carregar usuários disponíveis para convite
     * @param {number} salaId - ID da sala
     */
    function carregarUsuariosDisponiveis(salaId) {
        fetch(`/api/salas/personagens/listar/${salaId}`)
            .then(res => res.json())
            .then(personagens => {
                const membrosAtuais = personagens.map(p => p.usuarioId);

                fetch('/usuario')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status !== 'success' || !Array.isArray(response.data)) {
                            showAlert?.('Erro ao carregar usuários.') || alert('Erro ao carregar usuários.');
                            return;
                        }

                        usuarios = response.data;
                        const select = document.getElementById('selectUser');
                        if (!select) return;

                        select.innerHTML = '<option></option>';

                        const disponiveis = usuarios.filter(u => !membrosAtuais.includes(u.id));

                        disponiveis.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.email;
                            option.textContent = `${user.login} (${user.email})`;
                            select.appendChild(option);
                        });

                        // Inicializar Select2 se disponível
                        if (typeof $ !== 'undefined' && $.fn.select2) {
                            if (jQuery(select).hasClass('select2-hidden-accessible')) {
                                jQuery(select).select2('destroy');
                            }

                            jQuery(select).select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Selecione um ou mais usuários...',
                                dropdownParent: jQuery('#inviteModal'),
                                width: '100%',
                                allowClear: true
                            });

                            jQuery(select).on('change', atualizarListaUsuariosSelecionados);
                        }
                    })
                    .catch(() => showAlert?.('Erro ao carregar usuários.') || alert('Erro ao carregar usuários.'));
            })
            .catch(() => showAlert?.('Erro ao carregar membros da sala.') || alert('Erro ao carregar membros.'));
    }

    /**
     * Atualizar lista visual de usuários selecionados
     */
    function atualizarListaUsuariosSelecionados() {
        const select = document.getElementById('selectUser');
        if (!select) return;

        const emails = typeof $ !== 'undefined' ? jQuery(select).val() : Array.from(select.selectedOptions).map(o => o.value);
        const div = document.getElementById('selectedUsers');
        if (!div) return;

        div.innerHTML = '';

        if (!emails || emails.length === 0) return;

        emails.forEach(email => {
            const user = usuarios.find(u => u.email === email);
            const badge = document.createElement('span');
            badge.className = 'badge bg-success px-3 py-2 d-flex align-items-center gap-2';
            badge.innerHTML = `<i class="fa-solid fa-user"></i> ${user ? user.login : email}`;
            div.appendChild(badge);
        });
    }

    /**
     * Enviar convites aos usuários selecionados
     */
    $(document).on('click', '#btnSendInvite', function () {
        const salaId = getSalaId();
        const select = document.getElementById('selectUser');

        if (!salaId || !select) {
            showAlert?.('Erro ao identificar sala.') || alert('Erro ao identificar sala');
            return;
        }

        const emails = typeof $ !== 'undefined' ? jQuery(select).val() : Array.from(select.selectedOptions).map(o => o.value);

        if (!emails || emails.length === 0) {
            showAlert?.('Selecione ao menos um usuário para enviar convite.') || alert('Nenhum usuário selecionado');
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Enviando...');

        fetch('/api/enviar-invite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                salaId: salaId,
                emails: emails
            })
        })
        .then(res => res.json())
        .then(data => {
            showToast?.(data.message || 'Convites enviados!') || alert('Convites enviados!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('inviteModal'));
            if (modal) modal.hide();

            // Resetar select
            if (jQuery(select).hasClass('select2-hidden-accessible')) {
                jQuery(select).val(null).trigger('change');
            } else {
                select.value = '';
                atualizarListaUsuariosSelecionados();
            }
        })
        .catch(() => {
            showAlert?.('Erro ao enviar convite.') || alert('Erro ao enviar convite');
        })
        .finally(() => {
            btn.prop('disabled', false).html(originalText);
        });
    });

    /**
     * =================================
     * 📋 COPIAR CÓDIGO DA SALA
     * =================================
     */

    /**
     * Abrir modal de cópia de código
     */
    $(document).on('click', '[data-action="copiar-codigo"], .btn-copy, .dropdown-item', function (e) {
        // Verificar se é um botão de cópia
        if ($(this).data('action') === 'copiar-codigo' || $(this).hasClass('btn-copy')) {
            e.preventDefault();
            const codigo = $(this).data('code');

            if (codigo) {
                const input = document.getElementById('inputCodigoSala');
                if (input) {
                    input.value = codigo;
                    const modal = new bootstrap.Modal(document.getElementById('modalCopiarCodigo'));
                    modal.show();
                }
            }
        }
        // Alternativa: dropdown items que contenham "Copiar"
        else if ($(this).hasClass('dropdown-item') && $(this).text().includes('Copiar')) {
            e.preventDefault();
            const codigo = $(this).data('code');

            if (codigo) {
                const input = document.getElementById('inputCodigoSala');
                if (input) {
                    input.value = codigo;
                    const modal = new bootstrap.Modal(document.getElementById('modalCopiarCodigo'));
                    modal.show();
                }
            }
        }
    });

    /**
     * Botão para copiar código
     */
    $(document).on('click', '#btnCopiarCodigoSala', function () {
        const input = document.getElementById('inputCodigoSala');
        const mensagem = document.getElementById('mensagemCopiaoCodigo');

        if (!input || !input.value) {
            showAlert?.('Nenhum código disponível para copiar.') || alert('Nenhum código disponível');
            return;
        }

        const btn = $(this);
        const originalHTML = btn.html();

        input.select();
        const success = document.execCommand('copy');

        if (success && mensagem) {
            mensagem.style.display = 'block';
            btn.html('<i class="fa-solid fa-check"></i> Copiado!').prop('disabled', true);

            setTimeout(() => {
                mensagem.style.display = 'none';
                btn.html(originalHTML).prop('disabled', false);
            }, 2000);
        } else {
            showAlert?.('Falha ao copiar o código.') || alert('Falha ao copiar');
        }
    });
});
