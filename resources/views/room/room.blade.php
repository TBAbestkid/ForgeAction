@extends('partials/app')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
<div class="container-fluid mt-4 d-flex flex-column gap-3">

    {{-- Mini Navbar da Sala --}}
    <nav class="navbar navbar-dark bg-dark rounded px-3 py-2 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="d-flex flex-column flex-md-row align-items-md-center">
            <div class="d-flex flex-column">
                <h2 class="font-medieval text-white mb-0">{{ $sala['nome'] }}</h2>
                <p class="text-light mb-0 small">{{ $sala['descricao'] }}</p>
            </div>
        </div>

        @if ($isDono)
            <!-- Botão de Convidar à direita -->
            <button class="btn btn-outline-light mt-2 mt-md-0 ms-md-auto px-3 py-2 d-flex align-items-center"
                    type="button" data-bs-toggle="modal" data-bs-target="#inviteModal"
                    aria-controls="offcanvasMembers">
                <i class="fa-solid fa-user-plus me-1"></i>
                <span class="d-none d-md-inline">Convidar</span>
            </button>

            <!-- Botão de Editar à direita -->
            <button class="btn btn-outline-light mt-2 mt-md-0 ms-md-auto px-3 py-2 d-flex align-items-center"
                    type="button" data-bs-toggle="modal" data-bs-target="#editSalaModal"
                    aria-controls="offcanvasMembers">
                <i class="fa-solid fa-pen-to-square me-1"></i>
                <span class="d-none d-md-inline">Editar Sala</span>
            </button>
        @endif

        <!-- Botão de membros à direita -->
        <button class="btn btn-outline-light mt-2 mt-md-0 ms-md-auto px-3 py-2 d-flex align-items-center"
                type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMembers"
                aria-controls="offcanvasMembers">
            <i class="fa-solid fa-users me-1"></i>
            <span class="d-none d-md-inline">Membros</span>
        </button>
    </nav>

    {{-- Estrutura principal com 3 colunas --}}
    <div class="d-flex flex-grow-1 gap-3">

        @if (!$isDono)
            {{-- Coluna 1: Ficha do Personagem --}}
            <div class="flex-shrink-0 d-flex flex-column align-items-center justify-content-center"
                style="width: 50px; min-width: 50px; background-color: transparent;">
                <button class="btn btn-warning rounded-circle"
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#personagemDrawer"
                        aria-controls="personagemDrawer" title="Abrir Ficha">
                    ☰
                </button>
            </div>

            {{-- 🔸 Drawer lateral da Ficha --}}
            <div class="offcanvas offcanvas-start bg-dark text-light" tabindex="-1" id="personagemDrawer"
                aria-labelledby="personagemDrawerLabel" style="width: 360px;">
                <div class="offcanvas-header">
                    <h5 id="personagemDrawerLabel" class="text-warning">Ficha do Personagem</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
                </div>

                <div class="offcanvas-body d-flex flex-column gap-3">

                    {{-- 🔹 Identidade --}}
                    <div class="card bg-secondary text-light">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseIdentidade" style="cursor:pointer;">
                            <h6 class="mb-0 text-warning">Identidade</h6>
                        </div>
                        <div id="collapseIdentidade" class="collapse show">
                            <div class="card-body">
                                <div><strong>Nome:</strong> {{ $personagem['nome'] ?? 'Desconhecido' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="flex-fill bg-dark rounded p-2">Raça: {{ $personagem['racaDescricao'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2">Classe: {{ $personagem['classeDescricao'] ?? '?' }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="flex-fill bg-dark rounded p-2">Idade: {{ $personagem['idade'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2">Gênero: {{ $personagem['genero'] ?? '?' }}</div>
                                </div>
                                <div class="text-center bg-dark rounded p-2 mt-2"><strong>Nível:</strong> {{ $personagem['level'] ?? 1 }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Atributos --}}
                    <div class="card bg-secondary text-light">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseAtributos" style="cursor:pointer;">
                            <h6 class="mb-0 text-warning">Atributos</h6>
                        </div>
                        <div id="collapseAtributos" class="collapse">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Força: {{ $personagem['forca'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Agilidade: {{ $personagem['agilidade'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Inteligência: {{ $personagem['inteligencia'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Destreza: {{ $personagem['destreza'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Vitalidade: {{ $personagem['vitalidade'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Percepção: {{ $personagem['percepcao'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Sabedoria: {{ $personagem['sabedoria'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Carisma: {{ $personagem['carisma'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Status de Combate --}}
                    <div class="card bg-secondary text-light">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseCombate" style="cursor:pointer;">
                            <h6 class="mb-0 text-warning">Status de Combate</h6>
                        </div>
                        <div id="collapseCombate" class="collapse">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">HP: {{ $personagem['vida'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Mana: {{ $personagem['mana'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Iniciativa: {{ $personagem['iniciativa'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Defesa: {{ $personagem['defesaPersonagem'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Esquiva: {{ $personagem['esquivaPersonagem'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Corpo: {{ $personagem['ataqueFisicoCorpo'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Distância: {{ $personagem['ataqueFisicoDistancia'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Mágico: {{ $personagem['ataqueMagico'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            {{-- Coluna 1: Ações de Mestre --}}
            <div class="flex-shrink-0 d-flex flex-column align-items-center justify-content-start gap-3 p-2 bg-dark rounded-4 shadow"
                style="width: 70px; min-width: 70px;">

                {{-- 🔹 Iniciar/Avançar Turno --}}
                <button id="btnIniciarTurno" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Iniciar/Avançar Turno"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-play fs-4"></i>
                </button>

                {{-- 🔹 Causar Dano --}}
                <button id="btn-dano" class="btn btn-outline-danger rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Causar Dano"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-burst fs-4"></i>
                </button>

                {{-- 🔹 Curar --}}
                <button id="btn-curar" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Curar"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-heart-pulse fs-4"></i>
                </button>

                {{-- 🔹 Upar Personagem --}}
                <button id="btn-upar" class="btn btn-outline-info rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Upar Personagem"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-arrow-up fs-4"></i>
                </button>

                {{-- 🔹 Lançar Dados --}}
                <button id="btn-lancar-mestre"
                    class="btn btn-outline-warning rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Lançar Dados (Mestre)"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-dice-d20 fs-4"></i>
                </button>

                {{-- 🔹 Permitir Dados --}}
                <button id="btn-permitir-jogada"
                    class="btn btn-outline-primary rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Permitir Jogada Extra"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-user-check fs-4"></i>
                </button>
            </div>
        @endif

        {{-- Coluna 2: Área de imagens + chat --}}
        <div class="d-flex flex-column flex-grow-1" style="min-height: 100vh; width: 100%;">

            {{-- Área principal de jogos (personagens + DiceBox) --}}
            <div id="games-section" class="d-flex flex-column flex-lg-row gap-3 align-items-stretch flex-grow-1" style="min-height: 50vh;">

                {{-- Coluna esquerda (personagens) --}}
                <div class="d-flex flex-column gap-2 overflow-auto" style="flex:1 1 auto; min-width:120px;">
                    @foreach ($membros->slice(0, ceil($membros->count() / 3)) as $m)
                        <div class="bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card"
                            data-id="{{ $m['personagemId'] }}"
                            data-vida-max="{{ $m['vida'] }}"
                            data-nome="{{ $m['nome'] }}"
                            data-raca="{{ $m['raca'] }}"
                            data-classe="{{ $m['classe'] }}"
                            data-level="{{ $m['level'] }}"
                            data-vida="{{ $m['vida'] }}"
                            data-mana="{{ $m['mana'] }}"
                            data-usuario-id="{{ $m['usuarioId'] }}"
                            data-forca="{{ $m['forca'] }}"
                            data-agilidade="{{ $m['agilidade'] }}"
                            data-inteligencia="{{ $m['inteligencia'] }}"
                            data-destreza="{{ $m['destreza'] }}"
                            data-vitalidade="{{ $m['vitalidade'] }}"
                            data-percepcao="{{ $m['percepcao'] }}"
                            data-sabedoria="{{ $m['sabedoria'] }}"
                            data-carisma="{{ $m['carisma'] }}"
                            data-ataque-magico="{{ $m['ataqueMagico'] }}"
                            data-ataque-corpo="{{ $m['ataqueFisicoCorpo'] }}"
                            data-ataque-distancia="{{ $m['ataqueFisicoDistancia'] }}"
                            data-defesa="{{ $m['defesaPersonagem'] }}"
                            data-esquiva="{{ $m['esquivaPersonagem'] }}"
                            data-iniciativa="{{ $m['iniciativa'] }}">
                            <strong class="small">{{ $m['nome'] }}</strong>
                            <div class="progress mt-1 w-100" style="height: 16px; font-size:0.7rem;">
                                <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                                    role="progressbar"
                                    style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;">
                                    {{ $m['vida'] }}/{{ $m['vida'] }} HP
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Coluna central (DiceBox) --}}
                <div id="dice-container" class="bg-dark rounded shadow-lg d-flex flex-column justify-content-center align-items-center"
                    style="flex:2 1 auto; min-height:150px; border:2px solid #555;">
                    <span id="dice-placeholder" class="text-white">🎲 Aguardando início do turno...</span>

                    <div id="turn-controls" class="d-none flex-column align-items-center gap-2 mt-2">
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <button id="btn-roll" class="btn btn-outline-light">🎲 Rodar Dado</button>
                            <button id="btn-skip" class="btn btn-outline-warning">⏭️ Pular</button>
                        </div>

                        <div id="dice-options" class="d-none mt-3 text-center">
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="4">D4</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="6">D6</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="10">D10</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="12">D12</button>
                            <button class="btn btn-outline-primary m-1 dice-btn" data-sides="20">D20</button>
                        </div>
                    </div>
                </div>

                {{-- Coluna direita (personagens) --}}
                <div class="d-flex flex-column gap-2 overflow-auto" style="flex:1 1 auto; min-width:120px;">
                    @foreach ($membros->slice(ceil($membros->count() / 3)) as $m)
                        <div class="bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card"
                            data-id="{{ $m['personagemId'] }}"
                            data-vida-max="{{ $m['vida'] }}"
                            data-nome="{{ $m['nome'] }}"
                            data-raca="{{ $m['raca'] }}"
                            data-classe="{{ $m['classe'] }}"
                            data-level="{{ $m['level'] }}"
                            data-vida="{{ $m['vida'] }}"
                            data-mana="{{ $m['mana'] }}"
                            data-usuario-id="{{ $m['usuarioId'] }}"
                            data-forca="{{ $m['forca'] }}"
                            data-agilidade="{{ $m['agilidade'] }}"
                            data-inteligencia="{{ $m['inteligencia'] }}"
                            data-destreza="{{ $m['destreza'] }}"
                            data-vitalidade="{{ $m['vitalidade'] }}"
                            data-percepcao="{{ $m['percepcao'] }}"
                            data-sabedoria="{{ $m['sabedoria'] }}"
                            data-carisma="{{ $m['carisma'] }}"
                            data-ataque-magico="{{ $m['ataqueMagico'] }}"
                            data-ataque-corpo="{{ $m['ataqueFisicoCorpo'] }}"
                            data-ataque-distancia="{{ $m['ataqueFisicoDistancia'] }}"
                            data-defesa="{{ $m['defesaPersonagem'] }}"
                            data-esquiva="{{ $m['esquivaPersonagem'] }}"
                            data-iniciativa="{{ $m['iniciativa'] }}">
                            <strong class="small">{{ $m['nome'] }}</strong>
                            <div class="progress mt-1 w-100" style="height: 16px; font-size:0.7rem;">
                                <div class="progress-bar bg-success d-flex justify-content-center align-items-center"
                                    role="progressbar"
                                    style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;">
                                    {{ $m['vida'] }}/{{ $m['vida'] }} HP
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            {{-- Chat --}}
            <button id="chat-toggle-btn" class="btn btn-sm btn-warning mt-2 d-flex align-items-center gap-1" type="button"
                    aria-expanded="true" aria-controls="chat-container">
                <i class="fa-solid fa-comment"></i> Chat
            </button>

            {{-- Chat com collapse --}}
            <div id="chat-container" class="d-flex flex-column bg-dark rounded p-3 text-white mt-2"
                style="flex-shrink:0; min-height:150px; max-height:40vh; overflow:hidden;">
                <strong>Chat da Sala:</strong>
                <div id="chat-messages" class="flex-grow-1 d-flex flex-column gap-2 overflow-auto">
                    <!-- Mensagens -->
                </div>
                <div class="d-flex mt-2">
                    <input type="text" class="form-control me-2" placeholder="Digite sua mensagem..." id="chat-input">
                    <button class="btn btn-primary" id="chat-send"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>

        </div>

        <!-- Offcanvas direita -->
        <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasMembers"
            aria-labelledby="offcanvasMembersLabel" style="background-color: #1c1c1c; min-width: 250px;">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasMembersLabel">Membros</h5>
                <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-0">
                <ul id="lista-membros" class="list-group list-group-flush overflow-auto" style="max-height: 500px;">

                    {{-- 🔹 Primeiro o Mestre (dono da sala) --}}
                    @if(isset($sala['mestre']))
                        @php
                            $mestre = collect($membros)->firstWhere('usuarioId', $sala['mestre']);
                        @endphp

                        <li class="list-group-item bg-dark text-warning d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fa-solid fa-crown text-warning me-2"></i>
                                <strong>{{ $mestre['usuarioLogin'] ?? 'Mestre' }}</strong>
                            </div>
                            <span><i class="fa-solid fa-circle text-success"></i></span>
                        </li>
                    @endif

                    {{-- 🔹 Depois os Players (exceto o mestre) --}}
                    @foreach($membros as $membro)
                        @continue(isset($sala['mestre']) && $membro['usuarioId'] == $sala['mestre']) {{-- Pula o mestre --}}

                        <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                            {{ $membro['usuarioLogin'] ?? 'Jogador Desconhecido' }}
                            <span><i class="fa-solid fa-circle text-success"></i></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>


<!-- Modal de Edição -->
<div class="modal fade" id="editSalaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sala</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditSala">
                    <input type="hidden" name="id" value="{{ $sala['id'] }}">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ $sala['nome'] }}">
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao">{{ $sala['descricao'] }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Salvar alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para input do valor -->
<div class="modal fade" id="modalValor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Insira valor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="inputValor">Insira valor:</label>
                <input type="number" id="inputValor" class="form-control" min="0">
            </div>
            <div class="modal-footer">
                <button type="button" id="btnConfirmarValor" class="btn btn-danger" data-bs-dismiss="modal">Aplicar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Convite -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalLabel">
                    <i class="fa-solid fa-user-plus me-2"></i> Convidar usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="userSearch" class="form-label">Pesquisar usuário:</label>
                <input type="text" id="userSearch" class="form-control mb-2" placeholder="Digite email ou login...">

                <select id="selectUser" class="form-select">
                    <option value="">Carregando...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSendInvite">Enviar Convite</button>
            </div>
        </div>
    </div>
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/loading.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/chat-room.js') }}"></script>
{{-- Ativar tooltips --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (window.bootstrap && window.bootstrap.Tooltip) {
            [...tooltipTriggerList].forEach(el => new window.bootstrap.Tooltip(el));
        } else {
            // bootstrap não carregado ainda; será criado pelo script do bundle quando disponível
            console.warn('[room] bootstrap.Tooltip não disponível no momento.');
        }
    });
</script>
{{-- Exporta variáveis PHP para JS --}}
<script>
    window.CHAT_CONFIG = {
        userId: {{ session('user_id') ?? 'null' }},
        userLogin: "{{ session('user_login') ?? 'Desconhecido' }}",
        salaId: {{ $sala['id'] }},
        wsUrl: "{{ env('EXTERNAL_API_URL') }}/ws",
        // Indica se o usuário é o mestre/dono da sala (usado pelo JS para habilitar controles)
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };
</script>

<script src="{{ asset('js/room-manager.js') }}"></script>

<!-- Inline script desativado: o mesmo código foi mantido aqui apenas como referência.
    Ele foi convertido para type="text/plain" para não ser executado pelo navegador.
    O arquivo `public/js/room-manager.js` agora contém a lógica ativa. -->
<script id="inline-room-script" type="text/plain" data-disabled="true">
document.addEventListener('DOMContentLoaded', () => {

    // ---------- CONFIGURAÇÃO / CONSTANTES ----------
    // Antes estava apontando para 'personagens-container' que não existe no DOM,
    // portanto a delegação de eventos falhava (personagensContainer === null).
    // Usamos o container existente 'games-section' para delegar cliques dos cards.
    const PERSONAGENS_CONTAINER_ID = 'games-section';
    const personagensContainer = document.getElementById(PERSONAGENS_CONTAINER_ID);

    // Chat e toasts
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatMessages = document.getElementById('chat-messages');
    const userLogin = '{{ session("user_login") ?? "Player" }}';
    const toastEl = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastBootstrap = bootstrap?.Toast?.getOrCreateInstance(toastEl);

    // Turnos e dados
    const placeholder = document.getElementById('dice-placeholder');
    const turnControls = document.getElementById('turn-controls');
    const diceOptions = document.getElementById('dice-options');
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');
    const btnIniciar = document.getElementById('btnIniciarTurno');
    const btnLancarMestre = document.getElementById('btn-lancar-mestre');

    // Botões de modo
    const btnDano = document.getElementById('btn-dano');
    const btnCurar = document.getElementById('btn-curar');
    const btnUpar = document.getElementById('btn-upar');

    // Modal unificado
    const modalValorEl = document.getElementById('modalValor');
    const modalValor = modalValorEl ? new bootstrap.Modal(modalValorEl) : null;
    const inputValor = document.getElementById('inputValor');
    const btnConfirmarValor = document.getElementById('btnConfirmarValor');

    // ---------- ESTADOS ----------
    let ordemTurnos = [];
    let turnoAtual = 0;
    let rodada = 1;
    let rodadaAtiva = false;
    let mestreModoSecreto = false;

    let modoDanoAtivo = false;
    let modoCurarAtivo = false;
    let modoUparAtivo = false;

    let personagemSelecionado = null;
    let tipoValor = null; // 'dano' ou 'cura'

    // ---------- FUNÇÕES AUXILIARES ----------
    function addMessage(text, sender = '🧠 Sistema') {
        if (!chatMessages) return;
        const messageDiv = document.createElement('div');
        messageDiv.className = 'd-flex align-items-start gap-2 mb-1';
        const icon = document.createElement('i');
        icon.className = sender === '🧠 Sistema'
            ? 'fa-solid fa-robot text-warning mt-1'
            : 'fa-solid fa-user text-primary mt-1';
        messageDiv.appendChild(icon);
        const msgBox = document.createElement('div');
        msgBox.className = sender === '🧠 Sistema'
            ? 'bg-dark text-warning rounded px-2 py-1 small'
            : 'bg-secondary rounded px-2 py-1';
        msgBox.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messageDiv.appendChild(msgBox);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function abrirModal(tipo) {
        const titulo = modalValorEl.querySelector('.modal-title');
        const btn = btnConfirmarValor;

        if (tipo === 'dano') {
            titulo.textContent = 'Aplicar Dano';
            btn.className = 'btn btn-danger';
        } else {
            titulo.textContent = 'Aplicar Cura';
            btn.className = 'btn btn-success';
        }

        modalValor?.show();
    }

    function showFeedback(text, tipo = 'primary', sender) {
        addMessage(text, sender ?? '🧠 Sistema');
        toastMessage.textContent = text;
        toastEl.className = `toast align-items-center text-bg-${tipo} border-0`;
        toastBootstrap?.show();
    }

    function getCardMaxHP(card) {
        const vidaMaxAttr = card.dataset.vidaMax || card.dataset['vida-max'];
        if (vidaMaxAttr) return parseInt(vidaMaxAttr, 10);
        const progress = card.querySelector('.progress-bar');
        if (progress) {
            const parts = (progress.textContent || '').split('/');
            if (parts[1]) return parseInt(parts[1], 10);
        }
        return parseInt(card.dataset.vida, 10) || 0;
    }

    function atualizarBarraVida(card, vidaAtual, vidaMax) {
        const progress = card.querySelector('.progress-bar');
        if (!progress) return;
        const pct = vidaMax > 0 ? Math.max(0, Math.min(100, (vidaAtual / vidaMax) * 100)) : 0;
        progress.style.width = `${pct}%`;
        progress.textContent = `${vidaAtual}/${vidaMax} HP`;
    }

    function aplicarVida(card, valor, tipo) {
        const vidaAtual = parseInt(card.dataset.vida ?? 0, 10);
        const vidaMax = getCardMaxHP(card);
        let novaVida = vidaAtual;

        if (tipo === 'cura') {
            novaVida = Math.min(vidaMax, vidaAtual + Math.abs(valor));
            showFeedback(`❤️ ${card.dataset.nome} recuperou ${valor} HP! (${novaVida}/${vidaMax})`, 'success');
        } else if (tipo === 'dano') {
            novaVida = Math.max(0, vidaAtual - Math.abs(valor));
            showFeedback(`💥 ${card.dataset.nome} sofreu ${valor} de dano! (${novaVida}/${vidaMax})`, 'danger');
        }

        card.dataset.vida = novaVida;
        atualizarBarraVida(card, novaVida, vidaMax);
    }

    function resetCardBorders() {
        document.querySelectorAll('.personagem-card').forEach(card => {
            card.classList.remove('border-danger', 'border-success', 'border-info', 'border-primary', 'border-3');
            card.style.cursor = '';
        });
    }

    function desativarTodosOsModos() {
        modoDanoAtivo = modoCurarAtivo = modoUparAtivo = false;
        resetCardBorders();
    }

    function destacarPersonagem(card) {
        document.querySelectorAll('.personagem-card').forEach(c => {
            c.classList.remove('border-warning', 'border-3');
        });
        card.classList.add('border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function ordenarIniciativas(personagens) {
        // Cria uma lista com nome e iniciativa
        let lista = personagens.map(card => ({
            nome: card.dataset.nome,
            iniciativa: parseInt(card.dataset.iniciativa || 0, 10),
            card
        }));

        // Ordena do maior para o menor
        lista.sort((a, b) => b.iniciativa - a.iniciativa);

        // Resolve empates sorteando entre empatados
        for (let i = 0; i < lista.length - 1; i++) {
            if (lista[i].iniciativa === lista[i + 1].iniciativa) {
                if (Math.random() < 0.5) {
                    [lista[i], lista[i + 1]] = [lista[i + 1], lista[i]];
                }
            }
        }

        return lista;
    }

    function iniciarRodada() {
        const personagens = Array.from(document.querySelectorAll('.personagem-card'));
        if (personagens.length === 0) {
            showFeedback('Nenhum personagem encontrado!', 'danger');
            return;
        }

        // Ordena e inicia
        ordemTurnos = ordenarIniciativas(personagens);
        turnoAtual = 0;
        rodadaAtiva = true;

        // Mostra quem começa
        const primeiro = ordemTurnos[turnoAtual];
        destacarPersonagem(primeiro.card);
        showFeedback(`🎯 Rodada ${rodada} iniciada! Primeiro: ${primeiro.nome}`, 'success');
        addMessage(`🕒 Ordem de turnos: ${ordemTurnos.map(p => p.nome).join(', ')}`);

        // Exibe controles e atualiza a UI
        document.getElementById('turn-controls')?.classList.remove('d-none');
        atualizarTurnoUI();
    }

    function atualizarTurnoUI() {
        const placeholder = document.getElementById('dice-placeholder');
        const btnRoll = document.getElementById('btn-roll');
        const btnSkip = document.getElementById('btn-skip');

        if (!rodadaAtiva) {
            placeholder.textContent = "🎲 Aguardando início do turno...";
            btnRoll.disabled = true;
            btnSkip.disabled = true;
            return;
        }

        const atual = ordemTurnos[turnoAtual];
        destacarPersonagem(atual.card);

        placeholder.textContent = `🕒 Turno de ${atual.nome}`;
        btnRoll.disabled = false;
        btnSkip.disabled = false;
    }

    function proximoTurno() {
        if (!rodadaAtiva) return;

        turnoAtual++;
        if (turnoAtual >= ordemTurnos.length) {
            // Todos jogaram
            showFeedback(`🏁 Fim da rodada ${rodada}. Clique em ▶️ para iniciar a próxima.`, 'primary');
            rodada++;
            rodadaAtiva = false;
            document.getElementById('btnIniciarTurno').disabled = false;

            // Esconde os controles até reiniciar
            document.getElementById('turn-controls')?.classList.add('d-none');
            document.getElementById('dice-placeholder').textContent = "🎲 Aguardando início do turno...";
            return;
        }

        const atual = ordemTurnos[turnoAtual];
        showFeedback(`👉 Turno de ${atual.nome}`, 'info');
        atualizarTurnoUI();
    }

    // ---------- EVENTOS DE MODO ----------
    if (btnDano) {
        btnDano.addEventListener('click', () => {
            const ativando = !modoDanoAtivo;
            desativarTodosOsModos();
            modoDanoAtivo = ativando;
            if (modoDanoAtivo) {
                document.querySelectorAll('.personagem-card').forEach(c => {
                    c.classList.add('border', 'border-danger');
                    c.style.cursor = 'pointer';
                });
                showFeedback('💥 Modo DANO ativado. Clique em um personagem para aplicar.', 'danger');
            } else showFeedback('🧠 Modo DANO desativado.', 'secondary');
        });
    }

    if (btnCurar) {
        btnCurar.addEventListener('click', () => {
            const ativando = !modoCurarAtivo;
            desativarTodosOsModos();
            modoCurarAtivo = ativando;
            if (modoCurarAtivo) {
                document.querySelectorAll('.personagem-card').forEach(c => {
                    c.classList.add('border', 'border-success');
                    c.style.cursor = 'pointer';
                });
                showFeedback('❤️ Modo CURA ativado. Clique em um personagem para curar.', 'success');
            } else showFeedback('🧠 Modo CURA desativado.', 'secondary');
        });
    }

    if (btnUpar) {
        btnUpar.addEventListener('click', () => {
            const ativando = !modoUparAtivo;
            desativarTodosOsModos();
            modoUparAtivo = ativando;
            if (modoUparAtivo) {
                document.querySelectorAll('.personagem-card').forEach(c => {
                    c.classList.add('border', 'border-info', 'border-3');
                    c.style.cursor = 'pointer';
                });
                showFeedback('✨ Modo UP ativo! Clique em um personagem para upar.', 'info');
            } else showFeedback('🧠 Modo UP desativado.', 'secondary');
        });
    }

    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            if (rodadaAtiva) {
                showFeedback('Já há uma rodada em andamento!', 'warning');
                return;
            }
            btnIniciar.disabled = true;
            iniciarRodada();
        });
    }

    if (btnRoll) {
        btnRoll.addEventListener('click', () => {
            if (!rodadaAtiva) return;

            const atual = ordemTurnos[turnoAtual];
            const lados = [4, 6, 8, 10, 12, 20];
            const dado = lados[Math.floor(Math.random() * lados.length)];
            const valor = Math.floor(Math.random() * dado) + 1;

            addMessage(`🎲 ${atual.nome} rolou um D${dado} e tirou **${valor}**`);
            proximoTurno();
        });
    }

    if (btnSkip) {
        btnSkip.addEventListener('click', () => {
            if (!rodadaAtiva) return;

            const atual = ordemTurnos[turnoAtual];
            addMessage(`⏭️ ${atual.nome} decidiu pular o turno.`);
            proximoTurno();
        });
    }

    // ---------- CLIQUES NOS CARDS ----------
    if (personagensContainer) {
        personagensContainer.addEventListener('click', (e) => {
            const card = e.target.closest('.personagem-card');
            if (!card) return;

            // Se estiver no modo DANO
            if (modoDanoAtivo) {
                personagemSelecionado = card;
                tipoValor = 'dano';
                abrirModal('dano'); // abre o modal
                return;
            }

            // Se estiver no modo CURA
            if (modoCurarAtivo) {
                personagemSelecionado = card;
                tipoValor = 'cura';
                abrirModal('cura'); // abre o modal
                return;
            }

            // Se estiver no modo UPAR
            if (modoUparAtivo) {
                let level = parseInt(card.dataset.level ?? 1, 10);
                level++;
                card.dataset.level = level;
                const levelText = card.querySelector('.level-text');
                if (levelText) levelText.textContent = `Lv. ${level}`;
                showFeedback(`⚡ ${card.dataset.nome} subiu para o nível ${level}!`, 'info');
            }
        });
    }

    // ---------- CONFIRMAR MODAL ----------
    if (btnConfirmarValor) {
        btnConfirmarValor.addEventListener('click', () => {
            const valor = parseInt(inputValor?.value ?? 0, 10);

            // Validação básica
            if (!valor || !personagemSelecionado || !tipoValor) return;

            aplicarVida(personagemSelecionado, valor, tipoValor);

            // Limpa e fecha o modal
            modalValor?.hide();
            inputValor.value = '';
            personagemSelecionado = null;
            tipoValor = null;
        });
    }

    // ---------- CHAT SIMPLES ----------
    if (chatSend && chatInput) {
        chatSend.addEventListener('click', () => {
            const text = chatInput.value.trim();
            if (!text) return;
            addMessage(text, userLogin);
            chatInput.value = '';
        });
    }

    // ---------- INICIALIZAÇÃO ----------
    document.querySelectorAll('.personagem-card').forEach(card => {
        const vidaAtual = parseInt(card.dataset.vida ?? 0, 10);
        const vidaMax = getCardMaxHP(card);
        atualizarBarraVida(card, vidaAtual, vidaMax);
    });
});



    $(document).ready(function() {
        const salaId = {{ $sala['id'] }};

        // Funções de alerta e toast
        function showModalAlert(message) {
            $('#modalMessage').text(message);
            const modal = new bootstrap.Modal(document.getElementById('modalAlert'));
            modal.show();
        }

        function showToast(message) {
            $('#toastMessage').text(message);
            const toast = new bootstrap.Toast(document.getElementById('liveToast'));
            toast.show();
        }

        // ======================
        // Editar Sala
        // ======================
        $('#btn-edit-sala').click(function() {
            const modal = new bootstrap.Modal(document.getElementById('editSalaModal'));
            modal.show();
        });

        // Submeter formulário de edição via AJAX
        $('#formEditSala').submit(function(e) {
            e.preventDefault();

            const data = {
                nome: $('#nome').val(),
                descricao: $('#descricao').val(),
                ativo: $('#ativo').is(':checked'),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: `/salas/${salaId}`,
                type: 'PUT',
                data: data,
                success: function() {
                    // Atualiza os dados no card sem recarregar
                    $('#salaNome').text(data.nome);
                    $('#salaDescricao').text(data.descricao);
                    $('#salaStatus').text(data.ativo ? 'Ativa' : 'Inativa');

                    // Fecha o modal
                    bootstrap.Modal.getInstance(document.getElementById('editSalaModal')).hide();

                    // Mostra toast de sucesso
                    showToast('Sala atualizada com sucesso!');
                },
                error: function() {
                    showModalAlert('Erro ao atualizar a sala.');
                }
            });
        });

        // ======================
        // Deletar sala
        // ======================
        $('#btn-delete').click(function() {
            if(!confirm("Tem certeza que deseja deletar esta sala?")) return;

            $.ajax({
                url: `/salas/${salaId}`,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    showToast('Sala deletada!');
                    setTimeout(() => window.location.href = "{{ route('salas.index') }}", 1000);
                },
                error: function() {
                    showModalAlert('Erro ao deletar a sala.');
                }
            });
        });

        // ======================
        // Convidar membro via modal
        // ======================
        let usuarios = [];

        $('#btn-invite').click(function() {
            const modalEl = document.getElementById('inviteModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // Puxar usuários
            $.ajax({
                url: '/usuarios', // sua rota de listar usuários
                type: 'GET',
                success: function(response) {
                    if(response.status !== 'success' || !Array.isArray(response.data)) {
                        showModalAlert('Erro ao carregar usuários.');
                        return;
                    }

                    const usuarios = response.data;
                    const select = $('#selectUser');
                    select.empty();
                    select.append('<option value="">Selecione um usuário</option>');

                    // Filtrar quem já está na sala
                    const membrosIds = $('.btn-remove-member').map((i, el) => $(el).data('id')).get();

                    usuarios.forEach(user => {
                        if(!membrosIds.includes(user.id)) {
                            select.append(`<option value="${user.email}">${user.login} (${user.email})</option>`);
                        }
                    });
                },
                error: function() {
                    showModalAlert('Erro ao carregar usuários.');
                }
            });
        });

        // Enviar convite
        $('#btnSendInvite').click(function() {
            const email = $('#selectUser').val();
            if(!email) {
                showModalAlert('Selecione um usuário para enviar o convite.');
                return;
            }

            // Pega o ID da sala direto do HTML
            const salaId = parseInt($('#salaId').text());

            $.ajax({
                url: '/enviar-invite',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    salaId: salaId,
                    email: email
                },
                success: function(res) {
                    showToast(res.message);
                    const modalEl = document.getElementById('inviteModal');
                    bootstrap.Modal.getInstance(modalEl).hide();
                },
                error: function(xhr) {
                    showModalAlert(xhr.responseJSON?.message || 'Erro ao enviar convite.');
                }
            });
        });

        // Filtrar usuários conforme digita
        $('#userSearch').on('input', function() {
            const query = $(this).val().toLowerCase();
            const filtered = usuarios.filter(u =>
                u.email.toLowerCase().includes(query) || u.login.toLowerCase().includes(query)
            );

            const select = $('#selectUser');
            select.empty();
            select.append('<option value="">Selecione um usuário</option>');
            filtered.forEach(user => {
                select.append(`<option value="${user.email}">${user.login} (${user.email})</option>`);
            });
        });


        // ======================
        // Remover membro
        // ======================
        $('.btn-remove-member').click(function() {
            const membroId = $(this).data('id');
            if(!confirm("Deseja remover este membro da sala?")) return;

            $.ajax({
                url: `/sala-personagem/sala/${salaId}/personagem/${membroId}`,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    showToast('Membro removido!');
                    setTimeout(() => location.reload(), 500);
                },
                error: function() {
                    showModalAlert('Erro ao remover membro.');
                }
            });
        });
    });
</script>

@endsection

