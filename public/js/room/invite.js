// ===============================
// 🎯 Invite JS – Sistema de Convites de Sala
// ===============================

let usuarios = [];

console.log('Invite JS loaded (Select2 ativo)');

$(document).ready(function () {
    // ======================
    // 🧩 Configurações globais
    // ======================
    const token = typeof csrfToken !== 'undefined' ? csrfToken : '';
    const routeSalasIndex = typeof window.routeSalasIndex !== 'undefined' ? window.routeSalasIndex : '/salas';

    // ======================
    // 🧭 Função para obter o ID da sala
    // ======================
    function getSalaId(clickedButton = null) {
        if (typeof salaId !== 'undefined' && salaId !== null) return salaId;
        if (clickedButton && $(clickedButton).data('id')) return $(clickedButton).data('id');
        console.warn("⚠️ Nenhum ID de sala encontrado!");
        return null;
    }

    // ======================
    // 🧙‍♂️ Abrir modal e carregar usuários disponíveis
    // ======================
    $(document).on('click', '.btn-invite', function () {
        const salaIdClicada = getSalaId(this);
        if (!salaIdClicada) return showAlert('Erro: não foi possível identificar a sala.');

        $('#inviteModal').data('sala-id', salaIdClicada);
        new bootstrap.Modal('#inviteModal').show();

        // 1️⃣ Busca lista de personagens da sala para saber quem já é membro
        $.ajax({
            url: `/api/salas/personagens/listar/${salaIdClicada}`,
            type: 'GET',
            success: function (personagens) {
                const membrosAtuais = personagens.map(p => p.usuarioId);

                // 2️⃣ Depois busca todos os usuários
                $.ajax({
                    url: '/usuarios',
                    type: 'GET',
                    success: function (response) {
                        if (response.status !== 'success' || !Array.isArray(response.data)) {
                            showAlert('Erro ao carregar usuários.');
                            return;
                        }

                        usuarios = response.data;
                        const select = $('#selectUser');
                        select.empty().append('<option></option>'); // placeholder inicial

                        // 3️⃣ Filtra apenas usuários que não estão na sala
                        const disponiveis = usuarios.filter(u => !membrosAtuais.includes(u.id));

                        // 4️⃣ Popula o select
                        disponiveis.forEach(user => {
                            select.append(new Option(`${user.login} (${user.email})`, user.email));
                        });

                        // 5️⃣ Reinstancia o Select2
                        if ($.fn.select2) {
                            if (select.hasClass('select2-hidden-accessible')) {
                                select.select2('destroy');
                            }

                            select.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Selecione ou procure um usuário...',
                                dropdownParent: $('#inviteModal'),
                                width: 'resolve',
                                allowClear: true
                            });
                        }

                        $('#btnSendInvite').data('sala-id', salaIdClicada);
                    },
                    error: function () {
                        showAlert('Erro ao carregar usuários.');
                    }
                });
            },
            error: function () {
                showAlert('Erro ao carregar membros da sala.');
            }
        });
    });

    // ======================
    // ✉️ Enviar convite para o usuário selecionado
    // ======================
    $('#btnSendInvite').click(function () {
        const salaId = $(this).data('sala-id') || getSalaId();
        const email = $('#selectUser').val();

        if (!email || email.length === 0)
            return showAlert('Selecione um usuário para enviar o convite.');

        $.ajax({
            url: '/api/enviar-invite',
            type: 'POST',
            data: { _token: token, salaId: salaId, email: email },
            success: function (res) {
                showToast(res.message || 'Convite enviado!');
                bootstrap.Modal.getInstance('#inviteModal').hide();
            },
            error: function (xhr) {
                showAlert(xhr.responseJSON?.message || 'Erro ao enviar convite.');
            }
        });
    });
});
