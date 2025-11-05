// public/js/rooms/exit.js

$(document).ready(function () {
    /* -------------------------------------------------------------
    🚪 SAIR DA SALA (AJAX)
    ------------------------------------------------------------- */
    $(document).on('click', '.btn-leave', function () {
        const salaId = $(this).data('id');
        const userId = window.userId;

        showConfirm('Tem certeza que deseja sair desta sala?', function () {
            showToast('Saindo da sala...');

            // 🔹 Passo 1: buscar os personagens da sala
            $.ajax({
                url: `/api/salas/personagens/listar/${salaId}`,
                type: 'GET',
                data: { _token: window.csrfToken },
                success: function (personagens) {
                    console.log("Personagens recebidos:", personagens);
                    const personagem = personagens.find(p => p.usuarioId == userId);

                    if (!personagem) {
                        showToast('Seu personagem não foi encontrado nesta sala.');
                        return;
                    }

                    const personagemId = personagem.personagemId;

                    // 🔹 Passo 2: chamar a rota de saída
                    $.ajax({
                        url: `/api/salas/personagens/remover/${salaId}/${personagemId}`,
                        type: 'DELETE',
                        data: { _token: window.csrfToken },
                        success: function (res) {
                            showToast(res.message || 'Você saiu da sala.');
                            if (typeof loadSalas === 'function') {
                                loadSalas(); // recarrega a listagem sem refresh
                            } else {
                                location.reload(); // fallback
                            }
                        },
                        error: function (xhr) {
                            showToast(xhr.responseJSON?.message || 'Erro ao sair da sala.');
                        }
                    });
                },
                error: function () {
                    showToast('Erro ao carregar os personagens da sala.');
                }
            });
        });
    });
});
