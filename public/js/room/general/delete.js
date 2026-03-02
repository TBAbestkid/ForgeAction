// Delete module loaded

// a Logica dessa bomba é parecida como exit.js
$(document).ready(function () {
    // ======================
    // 🗑️ Clique no botão de exclusão
    // ======================
    $(document).on("click", ".btn-delete", function () {
        const salaId = $(this).data("id");
        if (!salaId) return showModal("Erro: ID da sala não encontrado.");

        // Exibe o modal de confirmação padrão
        showConfirm("Tem certeza que deseja excluir esta sala?", function () {
            showToast("Excluindo sala...");

            $.ajax({
                url: `/api/salas/${salaId}`,
                method: "DELETE",
                data: { _token: csrfToken },
                success: function (response) {
                    showToast(response.message || "Sala excluída com sucesso!");
                    if (typeof loadSalas === "function") {
                        loadSalas(); // atualiza a lista
                    } else {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    showModal(xhr.responseJSON?.message || "Erro ao excluir sala.");
                }
            });
        });
    });
});
