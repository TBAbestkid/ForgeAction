@extends('partials/app')
@section('title', 'ForgeAction - Dashboard')
@section('content')

<div class="container mt-4">
    <h1 class="font-medieval text-center mb-4">Seus Personagens</h1>

    <!-- Barra de pesquisa e filtros -->
    <div class="card bg-dark text-white shadow-sm border-0 rounded-3 mb-4 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" id="searchPersonagem" class="form-control bg-secondary text-white border-0" placeholder="Pesquisar por nome, raça ou classe...">
            </div>
            @php
                $racas = [
                    ["descricao" => "Draconato", "constante" => "DRACONATO"],
                    ["descricao" => "Tiefling", "constante" => "TIEFLING"],
                    ["descricao" => "Halfling", "constante" => "HALFLING"],
                    ["descricao" => "Anão", "constante" => "ANAO"],
                    ["descricao" => "Humano", "constante" => "HUMANO"],
                    ["descricao" => "Elfo", "constante" => "ELFO"],
                    ["descricao" => "Orc", "constante" => "ORC"],
                    ["descricao" => "Brute (meio-orc + humano)", "constante" => "BRUTE_MEIO_ORC_HUMANO"],
                    ["descricao" => "Brute (meio-orc + elfo)", "constante" => "BRUTE_MEIO_ORC_ELFO"],
                    ["descricao" => "Tarnished (elfo + humano)", "constante" => "TARNISHED_ELFO_HUMANO"],
                    ["descricao" => "Tarnished (elfo + tiefling)", "constante" => "TARNISHED_ELFO_TIEFLING"]
                ];

                $classes = [
                    ["descricao" => "Atirador", "constante" => "ATIRADOR"],
                    ["descricao" => "Caçador", "constante" => "CACADOR"],
                    ["descricao" => "Guerreiro", "constante" => "GUERREIRO"],
                    ["descricao" => "Paladino", "constante" => "PALADINO"],
                    ["descricao" => "Espadachim", "constante" => "ESPADACHIM"],
                    ["descricao" => "Assassino", "constante" => "ASSASSINO"],
                    ["descricao" => "Ladrão", "constante" => "LADRAO"],
                    ["descricao" => "Feiticeiro", "constante" => "FEITICEIRO"],
                    ["descricao" => "Bruxo", "constante" => "BRUXO"],
                    ["descricao" => "Mago", "constante" => "MAGO"],
                    ["descricao" => "Clérigo", "constante" => "CLERIGO"],
                    ["descricao" => "Monge", "constante" => "MONGE"],
                    ["descricao" => "Xamã", "constante" => "XAMA"],
                    ["descricao" => "Druida", "constante" => "DRUIDA"],
                    ["descricao" => "Artífice", "constante" => "ARTIFICE"],
                    ["descricao" => "Bardo", "constante" => "BARDO"]
                ];
            @endphp

            <div class="col-md-3">
                <select id="filterClasse" class="form-select bg-secondary text-white border-0">
                    <option value="">Todas as Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c['constante'] }}">{{ $c['descricao'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterRaca" class="form-select bg-secondary text-white border-0">
                    <option value="">Todas as Raças</option>
                    @foreach($racas as $r)
                        <option value="{{ $r['constante'] }}">{{ $r['descricao'] }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <!-- Lista de personagens -->
    <div class="d-flex flex-column gap-4" id="listaPersonagens">
        @forelse($personagens as $p)
            @php
                $info = $p['infoPersonagem'];
                $atributos = $p['atributos'];
                $bonus = $p['bonus'];
                $status = $p['status'];
                $ataque = $p['ataque'];
                $danoBase = $p['danoBase'];
                $racas = [
                    "DRACONATO" => "Draconato",
                    "TIEFLING" => "Tiefling",
                    "HALFLING" => "Halfling",
                    "ANAO" => "Anão",
                    "HUMANO" => "Humano",
                    "ELFO" => "Elfo",
                    "ORC" => "Orc",
                    "BRUTE_MEIO_ORC_HUMANO" => "Brute (meio-orc + humano)",
                    "BRUTE_MEIO_ORC_ELFO" => "Brute (meio-orc + elfo)",
                    "TARNISHED_ELFO_HUMANO" => "Tarnished (elfo + humano)",
                    "TARNISHED_ELFO_TIEFLING" => "Tarnished (elfo + tiefling)"
                ];

                $classes = [
                    "ATIRADOR" => "Atirador",
                    "CACADOR" => "Caçador",
                    "GUERREIRO" => "Guerreiro",
                    "PALADINO" => "Paladino",
                    "ESPADACHIM" => "Espadachim",
                    "ASSASSINO" => "Assassino",
                    "LADRAO" => "Ladrão",
                    "FEITICEIRO" => "Feiticeiro",
                    "BRUXO" => "Bruxo",
                    "MAGO" => "Mago",
                    "CLERIGO" => "Clérigo",
                    "MONGE" => "Monge",
                    "XAMA" => "Xamã",
                    "DRUIDA" => "Druida",
                    "ARTIFICE" => "Artífice",
                    "BARDO" => "Bardo"
                ];
            @endphp

            <div class="card bg-dark text-white shadow-sm border-0 rounded-3 p-3 personagem-card"
                 data-nome="{{ strtolower($info['nome']) }}"
                 data-classe="{{ strtolower($info['classe']) }}"
                 data-raca="{{ strtolower($info['raca']) }}">

                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h4 class="mb-1">{{ $info['nome'] }}</h4>
                        <span class="badge bg-primary">
                            {{ $classes[$info['classe']] ?? $info['classe'] }}
                        </span>
                        <span class="badge bg-secondary">
                            {{ $racas[$info['raca']] ?? $info['raca'] }}
                        </span>
                        <small class="d-block text-muted">
                            Idade: {{ $info['idade'] }} | Gênero: {{ $info['genero'] }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-success select-btn"
                                data-character='@json($p)'>
                            Selecionar
                        </button>

                        <button class="btn btn-sm btn-outline-warning">Editar</button>
                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $p['id'] }}">Excluir</button>
                    </div>
                </div>

                <hr class="border-secondary">

                <!-- Botão expandir -->
                <button class="btn btn-sm btn-outline-light mb-2"
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#detalhes-{{ $p['id'] }}"
                        aria-expanded="false" aria-controls="detalhes-{{ $p['id'] }}">
                    Ver mais detalhes
                </button>

                <!-- Infos expansíveis -->
                <div class="collapse" id="detalhes-{{ $p['id'] }}">
                    <div class="row g-3 mt-2">
                        <!-- Atributos -->
                        <div class="col-md-4">
                            <h6 class="fw-bold text-info">Atributos</h6>
                            <ul class="list-unstyled small mb-0">
                                <li>Força: <span class="fw-bold">{{ $atributos['forca'] }}</span></li>
                                <li>Agilidade: <span class="fw-bold">{{ $atributos['agilidade'] }}</span></li>
                                <li>Inteligência: <span class="fw-bold">{{ $atributos['inteligencia'] }}</span></li>
                                <li>Sabedoria: <span class="fw-bold">{{ $atributos['sabedoria'] }}</span></li>
                                <li>Destreza: <span class="fw-bold">{{ $atributos['destreza'] }}</span></li>
                                <li>Vitalidade: <span class="fw-bold">{{ $atributos['vitalidade'] }}</span></li>
                                <li>Percepção: <span class="fw-bold">{{ $atributos['percepcao'] }}</span></li>
                                <li>Carisma: <span class="fw-bold">{{ $atributos['carisma'] }}</span></li>
                            </ul>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <h6 class="fw-bold text-warning">Status</h6>
                            <ul class="list-unstyled small mb-0">
                                <li>Vida: <span class="fw-bold">{{ $status['vida'] }}</span></li>
                                <li>Mana: <span class="fw-bold">{{ $status['mana'] }}</span></li>
                                <li>Iniciativa: <span class="fw-bold">{{ $status['iniciativa'] }}</span></li>
                                <li>Bônus Vida: {{ $bonus['bonupVida'] }}</li>
                                <li>Bônus Mana: {{ $bonus['bonupMana'] }}</li>
                            </ul>
                        </div>

                        <!-- Ataques -->
                        <div class="col-md-4">
                            <h6 class="fw-bold text-danger">Ataque & Dano</h6>
                            <ul class="list-unstyled small mb-0">
                                <li>Atk Corpo a Corpo: {{ $ataque['ataqueFisicoCorpo'] }}</li>
                                <li>Atk Distância: {{ $ataque['ataqueFisicoDistancia'] }}</li>
                                <li>Atk Mágico: {{ $ataque['ataqueMagico'] }}</li>
                                <li>Dano Físico: {{ $danoBase['fisico'] }}</li>
                                <li>Dano Mágico: {{ $danoBase['magico'] }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-white">Nenhum personagem encontrado.</p>
        @endforelse
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

<style>
    .personagem-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .personagem-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,255,255,0.2);
    }
    .btn-group-vertical button {
        min-width: 80px;
    }
</style>
<!-- No head ou antes do fechamento do body -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const searchInput = document.getElementById('searchPersonagem');
    const filterClasse = document.getElementById('filterClasse');
    const filterRaca = document.getElementById('filterRaca');
    const personagens = document.querySelectorAll('.personagem-card');

    function filtrarPersonagens() {
        const termo = searchInput.value.toLowerCase();
        const classe = filterClasse.value.toLowerCase();
        const raca = filterRaca.value.toLowerCase();

        personagens.forEach(card => {
            const nome = card.dataset.nome;
            const cardClasse = card.dataset.classe;
            const cardRaca = card.dataset.raca;

            const matchNome = nome.includes(termo);
            const matchClasse = !classe || cardClasse === classe;
            const matchRaca = !raca || cardRaca === raca;

            if (matchNome && matchClasse && matchRaca) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filtrarPersonagens);
    filterClasse.addEventListener('change', filtrarPersonagens);
    filterRaca.addEventListener('change', filtrarPersonagens);

    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    let personagemToDelete = null;

    // Quando clicar em "Excluir"
    $('.delete-btn').click(function() {
        personagemToDelete = $(this).data('id');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });

    // Confirma exclusão
    $('#confirmDelete').click(function() {
        if(!personagemToDelete) return;

        $.ajax({
            url: `/personagem/${personagemToDelete}`,
            type: 'DELETE',
            success: function(res) {
                // Remove o card da tela de forma confiável
                $(`.delete-btn[data-id='${personagemToDelete}']`).closest('.personagem-card').remove();
                personagemToDelete = null;

                // Fecha modal
                const modalEl = document.getElementById('deleteModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            },
            error: function(xhr) {
                if(xhr.status === 419){
                    alert('Sessão expirada. Atualize a página e tente novamente.');
                } else {
                    alert('Erro ao deletar personagem: ' + xhr.status);
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {

        const toastEl = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        const toast = new bootstrap.Toast(toastEl);

        const selectButtons = document.querySelectorAll('.select-btn');
        selectButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const character = JSON.parse(this.dataset.character);

                fetch("{{ route('character.select') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(character)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        toastMessage.textContent = "Personagem selecionado com sucesso!";
                        toastEl.classList.remove('bg-danger');
                        toastEl.classList.add('bg-success');
                        toast.show();
                        setTimeout(() => location.reload(), 1000); // atualiza após 1s
                    } else {
                        toastMessage.textContent = data.message || "Erro ao selecionar personagem.";
                        toastEl.classList.remove('bg-success');
                        toastEl.classList.add('bg-danger');
                        toast.show();
                    }
                })
                .catch(err => {
                    toastMessage.textContent = "Erro na requisição.";
                    toastEl.classList.remove('bg-success');
                    toastEl.classList.add('bg-danger');
                    toast.show();
                    console.error(err);
                });
            });
        });

    });

</script>
@endsection
