function adicionarPersonagemOnline(usuarioId, salaId) {
    const colunaPersonagens = document.getElementById('coluna-personangens');
    let personagem;
    $.ajax({
        url: `/salas/personagens/listar/${salaId}`,
        method: "GET",
        data: { _token: csrfToken },
        success: function (response) {
            if (!response || response.length === 0) {
                console.log('Nenhum personagem encontrado');
                return;
            }
            personagem = response.find(p => p.usuarioId === usuarioId);
            if (!personagem) {
                console.log('Personagem do usuário não encontrado na sala');
                return;
            }


            const personagemDiv = document.createElement('div');
            personagemDiv.className = 'bg-dark rounded p-1 text-center d-flex flex-column align-items-center';
            personagemDiv.style.cursor = 'pointer';
            personagemDiv.id = `personagem-online-${personagem.id}`;

            colunaPersonagens.appendChild(personagemDiv);
            console.log(`Personagem ${personagem.nome} adicionado à lista de online.`);
        }
    });
}



