@extends('partials/app')
@section('title', 'Salas - ForgeAction')

@section('content')
<div class="container mt-4 d-flex flex-column flex-lg-row gap-4">

    <!-- Coluna principal -->
    <div class="flex-fill d-flex flex-column gap-5">

        <!-- Botão de Voltar -->
        <div class="text-start">
            <a href="{{ url('/') }}" class="btn btn-outline-light mb-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Home
            </a>
        </div>

        <!-- Barra de pesquisa -->
        <div class="mb-3 position-relative">
            <label for="filterSalas" class="form-label">Buscar sala</label>
            <input type="text" id="filterSalas"
                class="form-control form-control-sm bg-dark text-white border-secondary ps-5" placeholder="Buscar sala...">

            <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                🔍
            </span>
        </div>

        <div id="salas-container">
            <!-- Minhas Salas -->
            @if(session('user_role') === 'MASTER')
                <div>
                    <h2 class="font-medieval mb-3">Minhas Salas</h2>
                    <div id="minhas-salas" class="d-flex flex-column gap-3">
                        @forelse($minhasSalas as $sala)
                            <div class="sala-card p-3 rounded d-flex justify-content-between align-items-center bg-dark text-white">
                                <div>
                                    <strong>{{ $sala['nome'] }}</strong><br>
                                    <small class="text-light">{{ $sala['descricao'] }}</small>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary me-2">
                                        {{ $sala['total_jogadores'] ?? 0 }} jogador(es)
                                    </span>

                                    {{-- 🔹 Botões de ação iguais aos do JS --}}
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-sm btn-outline-warning">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $sala['id'] }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success btn-invite" data-id="{{ $sala['id'] }}">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>
                                        <a href="/salas/{{ $sala['id'] }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-door-open"></i> Entrar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                <i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala criada.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Salas que participo -->
            <div>
                <h2 class="font-medieval mb-3">Salas que participo</h2>
                <div id="salas-participando" class="d-flex flex-column gap-3">
                    @forelse($outrasSalas as $sala)
                        @if(is_array($sala))
                            <div class="sala-card p-3 rounded d-flex justify-content-between align-items-center bg-dark text-white">
                                <div>
                                    <strong>{{ $sala['nome'] ?? '—' }}</strong><br>
                                    <small class="text-light">{{ $sala['descricao'] ?? '' }}</small>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary me-2">
                                        {{ $sala['total_jogadores'] ?? 0 }} jogador(es)
                                    </span>

                                    {{-- 🔹 Apenas botão de sair para jogador --}}
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-danger btn-leave" data-id="{{ $sala['id'] }}">
                                            <i class="fa-solid fa-door-open"></i> Sair
                                        </button>
                                        <a href="/salas/{{ $sala['id'] ?? '#' }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-door-open"></i> Entrar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="alert alert-info">
                            <i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala participando.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Coluna lateral: apenas para MASTER -->
    @if(session('user_role') === 'MASTER')
        <div class="flex-grow-0 flex-shrink-1" style="flex-basis: 280px; min-width: 200px;">
            <div class="card shadow border-0 h-100 bg-dark text-white">
                <div class="card-body rounded-3 p-4 d-flex flex-column gap-3">
                    {{-- Tá meio sem sentido isso, a principio precisará de revisão --}}
                    <h5 class="text-center text-white mb-2">
                        <i class="fa-solid fa-hat-wizard me-2"></i> Painel do Mestre
                    </h5>
                    <hr class="border-secondary">

                    {{-- 🔹 Gerenciamento de Salas --}}
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('salas.create') }}" class="btn btn-outline-primary w-100">
                            <i class="fa-solid fa-plus me-2"></i> Criar Nova Sala
                        </a>
                        {{-- Bem isso não funcionará, iremos ocultar --}}
                        {{-- <button class="btn btn-outline-warning w-100" id="btnInviteGeneral">
                            <i class="fa-solid fa-user-plus me-2"></i> Convidar Jogador
                        </button> --}}
                        {{-- Editar será futuro, então aguardaremos... --}}
                        {{-- <button class="btn btn-outline-secondary w-100" id="btnRefreshSalas">
                            <i class="fa-solid fa-rotate me-2"></i> Atualizar Lista
                        </button> --}}
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
@include('partials/alerts')
@include('partials/invite')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
{{-- Passando infos do Blade para o script... --}}
<script>
    window.userId = "{{ session('user_id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>
<script src="{{ asset('js/room/invite.js') }}"></script>
<script src="{{ asset('js/room/exit.js') }}"></script>
<script src="{{ asset('js/room/delete.js') }}"></script>
<script>
    // ======== FILTRO DE SALAS (com debounce e feedback) ========
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('filterSalas');
        const allCards = document.querySelectorAll('.sala-card');
        const container = document.getElementById('salas-container');

        // Mensagem padrão para "nenhum resultado"
        const noResults = document.createElement('div');
        noResults.className = 'alert alert-info mt-3';
        noResults.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala encontrada.';
        noResults.style.display = 'none';
        container.appendChild(noResults);

        let debounceTimer;
        input?.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const term = e.target.value.trim().toLowerCase();
                let visibleCount = 0;

                allCards.forEach(card => {
                    const nome = card.querySelector('strong')?.innerText.toLowerCase() || '';
                    const desc = card.querySelector('small')?.innerText.toLowerCase() || '';
                    const match = nome.includes(term) || desc.includes(term);
                    card.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                // Mostra ou esconde mensagem de "nenhum resultado"
                noResults.style.display = visibleCount === 0 ? '' : 'none';
            }, 200);
        });
    });
</script>
@endsection

