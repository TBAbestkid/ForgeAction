function adicionarPersonagemOnline(usuarioId, salaId) {
    const colunaPersonagens = document.getElementById('coluna-personangens');
    $.ajax({
        url: `/salas/personagens/listar/${salaId}`,
        method: "GET",
        data: { _token: csrfToken },

}



