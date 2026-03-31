@extends('partials/app')

@section('title', "Seleção de Personagem - {$sala['nome']}")

@section('content')
<div class="container py-5 text-center">

    <!-- Cabeçalho -->
    <div class="mb-5">
        <img src="{{ asset('assets/images/forgeicon.png') }}" alt="Logo" class="mb-3" style="width:90px;">
        <h1 class="fw-bold text-warning text-uppercase font">{{ $sala['nome'] }}</h1>
        <p class="text-light-50 fs-5">🎭 Escolha seu herói para a aventura!</p>
    </div>

    <!-- Filtros -->
    {{-- <div class="mb-5">
        <div class="input-group w-75 mx-auto mb-3">
            <span class="input-group-text bg-dark text-light border-0"><i class="fas fa-search"></i></span>
            <input type="text" id="searchCharacter" class="form-control bg-dark text-light border-0"
                   placeholder="Buscar por nome, raça ou classe...">
        </div>

    </div> --}}

    @php
        // Mapas de cores e ícones
        $raceColors = [
            'DRACONATO' => 'text-danger',
            'HUMANO' => 'text-warning',
            'ELFO' => 'text-success',
            'TIEFLING' => 'text-purple',
            'ANAO' => 'text-secondary',
            'ORC' => 'text-dark'
        ];

        $raceIcons = [
            'DRACONATO' => 'fas fa-dragon',
            'HUMANO' => 'fas fa-user-astronaut',
            'ELFO' => 'fas fa-feather-alt',
            'TIEFLING' => 'fas fa-fire',
            'ANAO' => 'fas fa-hammer',
            'ORC' => 'fas fa-skull'
        ];

        $classIcons = [
            'GUERREIRO' => 'fas fa-sword',
            'MAGO' => 'fas fa-hat-wizard',
            'ASSASSINO' => 'fas fa-skull-crossbones',
            'PALADINO' => 'fas fa-shield-alt',
            'ARTIFICE' => 'fas fa-cogs',
            'ATIRADOR' => 'fas fa-bullseye',
            'CACADOR' => 'fas fa-binoculars'
        ];
    @endphp

    <div id="carouselPersonagens" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner" id="characterList">
            @forelse($personagens as $index => $personagem)
            @php
                $raceColor = $raceColors[$personagem['raca']] ?? 'text-light';
                $raceIcon = $raceIcons[$personagem['raca']] ?? 'fas fa-user';
                $classIcon = $classIcons[$personagem['classe']] ?? 'fas fa-question';
            @endphp

            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} personagem-item"
                data-nome="{{ strtolower($personagem['nome']) }}"
                data-raca="{{ strtolower($personagem['raca'] ?? '') }}"
                data-classe="{{ strtolower($personagem['classe'] ?? '') }}">

                <div class="card bg-dark text-light border border-warning mx-auto" style="max-width: 22rem;">
                    <div class="card-body text-center">
                        <i class="{{ $raceIcon }} fa-4x mb-3 {{ $raceColor }}"></i>
                        <h5 class="card-title fw-bold {{ $raceColor }} mb-3">{{ $personagem['nome'] }}</h5>
                        <p class="mb-2"><i class="{{ $classIcon }} me-2"></i>{{ $personagem['classe'] ?? 'Desconhecida' }}</p>
                        <p class="mb-2"><i class="fas fa-star text-warning me-2"></i>Nível: {{ $personagem['level'] ?? 1 }}</p>

                        <!-- Progress bars de atributos -->
                        <div class="mb-2 text-start">
                            <small>Força</small>
                            <div class="progress mb-1">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $personagem['forca']*10 }}%" aria-valuenow="{{ $personagem['forca']*10 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small>Agilidade</small>
                            <div class="progress mb-1">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $personagem['agilidade']*10 }}%"></div>
                            </div>
                            <small>Inteligência</small>
                            <div class="progress mb-1">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $personagem['inteligencia']*10 }}%"></div>
                            </div>
                        </div>
                        <hr class="text-warning">
                        <form method="POST" action="{{ url('salas/personagens/adicionar/'.$sala['id']) }}">
                            @csrf
                            <input type="hidden" name="personagemId" value="{{ $personagem['id'] }}">
                            <button type="submit" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-hand-pointer me-2"></i>Selecionar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
                <div class="carousel-item active personagem-item" style="width: 100%;">
                    <div class="card bg-dark text-light border border-warning mx-auto" style="max-width: 22rem;">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-plus fa-4x mb-3 text-warning"></i>
                            <h5 class="card-title fw-bold text-warning mb-3">Nenhum Personagem</h5>
                            <p class="text-light mb-4">Você ainda não possui personagens disponíveis. Crie um novo para entrar na sala!</p>
                            <a href="{{ route('registerPerson', ['salaId' => $sala['id']]) }}" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-plus me-2"></i> Criar Personagem
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Controles -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPersonagens" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-warning rounded-circle p-3" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselPersonagens" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-warning rounded-circle p-3" aria-hidden="true"></span>
            <span class="visually-hidden">Próximo</span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchCharacter');
        const filterClasse = document.getElementById('filterClasse');
        const personagens = document.querySelectorAll('.personagem-item');

        function filtrar() {
            const termo = searchInput.value.toLowerCase();
            const classe = filterClasse.value.toLowerCase();

            personagens.forEach(p => {
                const nome = p.dataset.nome;
                const raca = p.dataset.raca;
                const classeChar = p.dataset.classe;
                const match =
                    (!termo || nome.includes(termo) || raca.includes(termo) || classeChar.includes(termo)) &&
                    (!classe || classeChar.includes(classe));
                p.style.display = match ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filtrar);
        filterClasse.addEventListener('change', filtrar);
    });
</script>
@endsection
