@extends('partials/app')
@section('title', 'Salas - ForgeAction')

@section('content')
<div class="container mt-4 d-flex flex-column flex-lg-row gap-4">

    <!-- Coluna principal -->
    <div class="flex-fill d-flex flex-column gap-5">

        <!-- Botão de Voltar -->
        <div class="text-start">
            <a href="{{ url('/') }}" class="btn btn-outline-light mb-4 px-4 py-2 shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar para Home
            </a>
        </div>

        <!-- Barra de pesquisa + botão Entrar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <!-- Barra de pesquisa -->
            <div class="search-wrapper flex-grow-1">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-secondary text-light border-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="filterSalas"
                        class="form-control bg-dark text-white border-0"
                        placeholder="Pesquisar salas por nome ou descrição...">
                </div>
            </div>

            <!-- Botão de entrar -->
            <div class="text-end">
                <button class="btn btn-primary px-4 py-2 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalSalabyCode">
                    <i class="fa-solid fa-door-open me-2"></i> Entrar em Sala
                </button>
            </div>
        </div>

        <!-- Lista de Salas -->
        <div id="salas-container" class="d-flex flex-column gap-5">

            <!-- Minhas Salas -->
            @if(session('user_role') === 'MASTER')
                <div>
                    <h2 class="font-medieval mb-3 text-warning">Minhas Salas</h2>
                    <div id="minhas-salas" class="d-flex flex-column gap-3">
                        @forelse($minhasSalas as $sala)
                            <div class="sala-card p-3 rounded-4 bg-dark text-white shadow-sm hover-glow d-flex justify-content-between align-items-center">
                                <a href="/salas/{{ $sala['id'] }}" class="stretched-link text-decoration-none text-white">
                                    <strong class="fs-5">{{ $sala['nome'] }}</strong><br>
                                    <small class="text-light opacity-75">{{ $sala['descricao'] }}</small>
                                </a>

                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-secondary px-3 py-2">
                                        {{ $sala['total_jogadores'] ?? 0 }} jogador(es)
                                    </span>

                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $sala['id'] }}" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success btn-invite" data-id="{{ $sala['id'] }}" title="Convidar">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-light btn-copy" data-code="{{ $sala['codigo'] }}" title="Copiar código" id="btnCopyCode">
                                            <i class="fa-solid fa-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info bg-opacity-10 border-dark text-dark">
                                <i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala criada.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Salas que participo -->
            <div>
                <h2 class="font-medieval mb-3 text-warning">Salas que participo</h2>
                <div id="salas-participando" class="d-flex flex-column gap-3">
                    @forelse($outrasSalas as $sala)
                        @if(is_array($sala))
                            <div class="sala-card p-3 rounded-4 bg-dark text-white shadow-sm hover-glow d-flex justify-content-between align-items-center"
                                onclick="window.location.href='/salas/{{ $sala['id'] }}'">
                                <a href="/salas/{{ $sala['id'] }}" class="stretched-link text-decoration-none text-white">
                                    <strong class="fs-5">{{ $sala['nome'] ?? '—' }}</strong><br>
                                    <small class="text-light opacity-75">{{ $sala['descricao'] ?? '' }}</small>
                                </a>

                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-secondary px-3 py-2">
                                        {{ $sala['total_jogadores'] ?? 0 }} jogador(es)
                                    </span>

                                    <button class="btn btn-sm btn-outline-danger btn-leave" data-id="{{ $sala['id'] }}" title="Sair da sala">
                                        <i class="fa-solid fa-door-open"></i> Sair
                                    </button>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="alert alert-info bg-opacity-10 border-dark text-dark">
                            <i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala participando.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna lateral: apenas para MASTER -->
    {{-- @if(session('user_role') === 'MASTER') --}}
        {{-- <div class="flex-grow-0 flex-shrink-1" style="flex-basis: 280px; min-width: 200px;">
            <div class="card shadow border-0 h-100 bg-dark text-white">
                <div class="card-body rounded-3 p-4 d-flex flex-column gap-3"> --}}
                    {{-- Tá meio sem sentido isso, a principio precisará de revisão --}}
                    {{-- <h5 class="text-center text-white mb-2">
                        <i class="fa-solid fa-hat-wizard me-2"></i> Painel do Mestre
                    </h5>
                    <hr class="border-secondary"> --}}

                    {{-- 🔹 Gerenciamento de Salas --}}
                    {{-- <div class="d-flex flex-column gap-2"> --}}
                        {{-- <a href="{{ route('salas.create') }}" class="btn btn-outline-primary w-100">
                            <i class="fa-solid fa-plus me-2"></i> Criar Nova Sala
                        </a> --}}
                        {{-- Bem isso não funcionará, iremos ocultar --}}
                        {{-- <button class="btn btn-outline-warning w-100" id="btnInviteGeneral">
                            <i class="fa-solid fa-user-plus me-2"></i> Convidar Jogador
                        </button> --}}
                        {{-- Editar será futuro, então aguardaremos... --}}
                        {{-- <button class="btn btn-outline-secondary w-100" id="btnRefreshSalas">
                            <i class="fa-solid fa-rotate me-2"></i> Atualizar Lista
                        </button> --}}
                    {{-- </div> --}}

                {{-- </div>
            </div>
        </div> --}}
    {{-- @endif --}}
    <!-- Modal: Inserir Código -->
    <div class="modal fade" id="modalSalabyCode" tabindex="-1" aria-labelledby="modalCodeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="modal-header bg-light">
                    <h1 class="modal-title fs-5 d-flex align-items-center" id="modalCodeLabel">
                        <i class="fa-solid fa-key text-primary me-2"></i> Entrar na sala com código
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary mb-3">Digite o código da sala que você recebeu:</p>
                    <input type="text" id="inputCodigoSala" class="form-control form-control-lg text-center" placeholder="Ex: ABC123" maxlength="10">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnEntrarSalaCodigo">
                        <i class="fa-solid fa-door-open me-1"></i> Entrar
                    </button>
                </div>
            </div>
        </div>
    </div>
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

    // ======== ENTRAR NA SALA PELO CÓDIGO ========
    document.addEventListener('DOMContentLoaded', () => {
        const btnEntrarSalaCodigo = document.getElementById('btnEntrarSalaCodigo');
        const inputCodigoSala = document.getElementById('inputCodigoSala');

        btnEntrarSalaCodigo?.addEventListener('click', async () => {
            const codigo = inputCodigoSala.value.trim();
            if (!codigo) {
                showToast('Por favor, insira um código.', 'danger');
                return;
            }

            try {
                const response = await fetch(`/codigo/${codigo}`, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error();
                const data = await response.json();

                if (data?.status === 'success' && data?.data?.id) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalSalabyCode'));
                    modal?.hide();

                    // 🔹 Redireciona o usuário para o fluxo controlado por Laravel
                    window.location.href = `/salas/entrar/codigo?codigo=${codigo}`;
                } else {
                    showToast('Código inválido ou sala não encontrada.', 'danger');
                }
            } catch (e) {
                showToast('Código inválido ou sala não encontrada.', 'danger');
            }
        });

        // ======== COPIAR CÓDIGO DA SALA ========
        const btnCopyCode = document.getElementById('btnCopyCode');

        btnCopyCode?.addEventListener('click', async () => {
            const code = btnCopyCode.dataset.code;

            if (!code) {
                showToast('Nenhum código disponível para copiar.', 'danger');
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(code);
                } else {
                    // 🔸 fallback para browsers sem suporte ou sem HTTPS
                    const tempInput = document.createElement('input');
                    tempInput.value = code;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                }

                showToast('Código copiado para a área de transferência!', 'success');
            } catch (err) {
                console.error(err);
                showToast('Falha ao copiar o código.', 'danger');
            }
        });
    });

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

