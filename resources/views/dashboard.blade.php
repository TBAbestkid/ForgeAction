@extends('partials/app')
@section('title', 'Dashboard - ForgeAction')
@section('content')

<div class="container mt-4">

    <h1 class="text-center mb-4">Seus Personagens</h1>

    <!-- Pesquisa e filtros simples -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <input type="text" id="searchPersonagem" class="form-control" style="flex:1;" placeholder="Pesquisar por nome, raça ou classe...">

        <select id="filterClasse" class="form-select" style="width:auto;">
            <option value="">Todas as Classes</option>
            @foreach($classes as $c)
                <option value="{{ $c['constante'] }}">{{ $c['descricao'] }}</option>
            @endforeach
        </select>

        <select id="filterRaca" class="form-select" style="width:auto;">
            <option value="">Todas as Raças</option>
            @foreach($racas as $r)
                <option value="{{ $r['constante'] }}">{{ $r['descricao'] }}</option>
            @endforeach
        </select>
    </div>

    <!-- Lista de personagens -->
    <div id="characterListContainer">
        <div id="loadingCharacters" class="text-center mb-2 bg-dark text-white text-center">
            <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando personagens...
        </div>
        <div id="characterCards" class="d-flex flex-column gap-2"></div>
    </div>

</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este personagem?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

@include('partials.alerts')

<!-- No head ou antes do fechamento do body -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        const $cards = $("#characterCards");
        const $loading = $("#loadingCharacters");

        const searchInput = document.getElementById('searchPersonagem');
        const filterClasse = document.getElementById('filterClasse');
        const filterRaca = document.getElementById('filterRaca');

        const alertNenhum = document.createElement('div');
        alertNenhum.className = 'alert bg-dark text-light fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm';
        alertNenhum.innerHTML = `<i class="fas fa-dragon fa-lg"></i> Nenhum personagem encontrado!`;

        async function loadPersonagens() {
            try {
                const res = await fetch("/personagem/usuario/{{ session('user_id') }}");
                const json = await res.json();

                $loading.hide(); // Remove loading

                const personagens = json.data || [];

                if (!personagens.length) {
                    $cards.html(alertNenhum.outerHTML);
                    return;
                }

                $cards.empty();

                personagens.forEach(p => {
                    const cardHtml = $(`
                        <div class="card bg-dark text-white border border-secondary shadow-sm rounded-3 p-3 personagem-card mb-3"
                            data-nome="${p.nome.toLowerCase()}"
                            data-classe="${p.classe.toLowerCase()}"
                            data-raca="${p.raca.toLowerCase()}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h5 class="mb-1 text-info fw-bold">${p.nome}</h5>
                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                        <span class="badge bg-primary">${p.classe}</span>
                                        <span class="badge bg-secondary">${p.raca}</span>
                                    </div>
                                    <small class="text-light">Idade: ${p.idade} | ${p.genero}</small>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-success select-btn" data-character='${JSON.stringify(p)}'>
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${p.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-light w-100 toggle-details-btn mb-2" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#detalhes-${p.id}">
                                <i class="bi bi-chevron-down"></i> Ver detalhes
                            </button>
                            <div class="collapse" id="detalhes-${p.id}">
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <h6 class="fw-bold text-info">Atributos</h6>
                                        <ul class="list-unstyled small mb-0">
                                            <li>Força: ${p.forca}</li>
                                            <li>Agilidade: ${p.agilidade}</li>
                                            <li>Inteligência: ${p.inteligencia}</li>
                                            <li>Destreza: ${p.destreza}</li>
                                            <li>Vitalidade: ${p.vitalidade}</li>
                                            <li>Percepção: ${p.percepcao}</li>
                                            <li>Sabedoria: ${p.sabedoria}</li>
                                            <li>Carisma: ${p.carisma}</li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <h6 class="fw-bold text-warning">Status & Bônus</h6>
                                        <ul class="list-unstyled small mb-0">
                                            <li>Vida: ${p.vida}</li>
                                            <li>Mana: ${p.mana}</li>
                                            <li>Iniciativa: ${p.iniciativa}</li>
                                            <li>Bônus Vida: ${p.bonus?.bonusVida||0}</li>
                                            <li>Bônus Mana: ${p.bonus?.bonusMana||0}</li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <h6 class="fw-bold text-danger">Ataque & Dano</h6>
                                        <ul class="list-unstyled small mb-0">
                                            <li>Fisico Corpo: ${p.ataqueFisicoCorpo}</li>
                                            <li>Fisico Distância: ${p.ataqueFisicoDistancia}</li>
                                            <li>Mágico: ${p.ataqueMagico}</li>
                                            <li>Dano Físico: ${p.danoBase?.fisico||0}</li>
                                            <li>Dano Mágico: ${p.danoBase?.magico||0}</li>
                                            <li>Defesa: ${p.defesaPersonagem}</li>
                                            <li>Esquiva: ${p.esquivaPersonagem}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    $cards.append(cardHtml);
                });

                attachCardEvents(); // ativa toggles, selects e deletes
                filtrarPersonagens(); // aplica filtros iniciais

            } catch(e) {
                $loading.hide();
                $cards.html(`<div class="alert bg-dark text-danger">Erro ao carregar personagens.</div>`);
            }
        }

        function attachCardEvents() {
            // Toggle detalhes
            $('.toggle-details-btn').each(function() {
                const btn = $(this);
                const icon = btn.find('i');
                const target = $(btn.data('bs-target'));

                // Remove listeners antigos caso existam
                target.off('shown.bs.collapse hidden.bs.collapse');

                // Liga apenas uma vez
                target.on('shown.bs.collapse', () => icon.removeClass('bi-chevron-down').addClass('bi-chevron-up'));
                target.on('hidden.bs.collapse', () => icon.removeClass('bi-chevron-up').addClass('bi-chevron-down'));
            });

            // Delete
            let personagemToDelete = null;
            $('.delete-btn').off('click').on('click', function() {
                personagemToDelete = $(this).data('id');
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });

            $('#confirmDelete').off('click').on('click', function() {
                if(!personagemToDelete) return;

                $.ajax({
                    url: `/personagem/${personagemToDelete}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        const card = $(`.delete-btn[data-id='${personagemToDelete}']`).closest('.personagem-card');
                        card.fadeOut(400, () => card.remove()); // animação suave
                        personagemToDelete = null;
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();

                        // Toast de sucesso
                        toastMessage.textContent = "Personagem excluído com sucesso!";
                        toastEl.classList.remove('bg-danger');
                        toastEl.classList.add('bg-success');
                        toast.show();
                    },
                    error: function(xhr) {
                        alert(xhr.status === 419 ? 'Sessão expirada.' : 'Erro ao deletar personagem: ' + xhr.status);
                    }
                });
            });

            // Select personagem
            const toastEl = document.getElementById('liveToast');
            const toastMessage = document.getElementById('toastMessage');
            const toast = new bootstrap.Toast(toastEl);

            $('.select-btn').off('click').on('click', function() {
                const character = JSON.parse(this.dataset.character);

                fetch("{{ route('character.select') }}", {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                    body: JSON.stringify(character)
                })
                .then(res => res.json())
                .then(data => {
                    toastMessage.textContent = data.success ? "Personagem selecionado com sucesso!" : data.message || "Erro ao selecionar personagem.";
                    toastEl.classList.toggle('bg-success', data.success);
                    toastEl.classList.toggle('bg-danger', !data.success);
                    toast.show();
                    if(data.success) setTimeout(()=>location.reload(), 1000);
                })
                .catch(() => {
                    toastMessage.textContent = "Erro na requisição.";
                    toastEl.classList.remove('bg-success');
                    toastEl.classList.add('bg-danger');
                    toast.show();
                });
            });
        }

        function filtrarPersonagens() {
            const termo = searchInput?.value.toLowerCase() || '';
            const classe = filterClasse?.value.toLowerCase() || '';
            const raca = filterRaca?.value.toLowerCase() || '';

            let algumVisivel = false;
            $('.personagem-card').each(function() {
                const card = $(this);
                const nome = card.data('nome');
                const cardClasse = card.data('classe');
                const cardRaca = card.data('raca');

                const match = nome.includes(termo) && (!classe || cardClasse === classe) && (!raca || cardRaca === raca);
                card.toggle(match);
                if(match) algumVisivel = true;
            });

            if(!algumVisivel) {
                if(!$('#characterCards').find('.alert').length) {
                    $('#characterCards').append(alertNenhum);
                }
            } else {
                $('#characterCards').find('.alert').remove();
            }
        }

        searchInput?.addEventListener('input', filtrarPersonagens);
        filterClasse?.addEventListener('change', filtrarPersonagens);
        filterRaca?.addEventListener('change', filtrarPersonagens);

        loadPersonagens();
    });
</script>
@endsection
