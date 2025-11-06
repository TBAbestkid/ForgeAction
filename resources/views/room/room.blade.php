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
            <button class="btn btn-outline-light mt-2 mt-md-0 ms-md-auto px-3 py-2 d-flex align-items-center btn-invite"
                data-id="{{ $sala['id'] }}">
                <i class="fa-solid fa-user-plus me-1"></i>
                <span class="d-none d-md-inline">Convidar</span>
            </button>

            <!-- Botão de Editar à direita -->
            <button class="btn btn-outline-light mt-2 mt-md-0 ms-md-auto px-3 py-2 d-flex align-items-center"
                    type="button" data-bs-toggle="modal" data-bs-target="#editSalaModal">
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
        @if(!$isDono)
            <button class="btn btn-sm btn-outline-danger btn-leave mt-2 mt-md-0 ms-md-3 px-3 py-2 d-flex align-items-center"
                    data-id="{{ $sala['id'] }}">
                <i class="fa-solid fa-door-open me-1"></i> Sair da Sala
            </button>
        @endif
    </nav>

    {{-- Estrutura principal com 3 colunas --}}
    <div class="d-flex flex-grow-1 gap-3">

        {{-- @if (!$isDono) --}}
            {{-- Coluna 1: Ficha do Personagem --}}
            {{-- <div class="flex-shrink-0 d-flex flex-column align-items-center justify-content-center"
                style="width: 50px; min-width: 50px; background-color: transparent;">
                <button class="btn btn-warning rounded-circle"
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#personagemDrawer"
                        aria-controls="personagemDrawer" title="Abrir Ficha">
                    ☰
                </button>
            </div> --}}

            {{-- 🔸 Drawer lateral da Ficha --}}
            {{-- <div class="offcanvas offcanvas-start bg-dark text-light" tabindex="-1" id="personagemDrawer"
                aria-labelledby="personagemDrawerLabel" style="width: 360px;">
                <div class="offcanvas-header">
                    <h5 id="personagemDrawerLabel" class="text-warning">Ficha do Personagem</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
                </div>

                <div class="offcanvas-body d-flex flex-column gap-3"> --}}

                    {{-- 🔹 Identidade --}}
                    {{-- <div class="card bg-secondary text-light">
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
                    </div> --}}

                    {{-- 🔹 Atributos --}}
                    {{-- <div class="card bg-secondary text-light">
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
                    </div> --}}

                    {{-- 🔹 Status de Combate --}}
                    {{-- <div class="card bg-secondary text-light">
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
            </div> --}}
        {{-- @else --}}
            {{-- Coluna 1: Ações de Mestre --}}
            {{-- <div class="flex-shrink-0 d-flex flex-column align-items-center justify-content-start gap-3 p-2 bg-dark rounded-4 shadow"
                style="width: 70px; min-width: 70px;"> --}}

                {{-- 🔹 Iniciar/Avançar Turno --}}
                {{-- <button id="btnIniciarTurno" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Iniciar/Avançar Turno"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-play fs-4"></i>
                </button> --}}

                {{-- 🔹 Causar Dano --}}
                {{-- <button id="btn-dano" class="btn btn-outline-danger rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Causar Dano"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-burst fs-4"></i>
                </button> --}}

                {{-- 🔹 Curar --}}
                {{-- <button id="btn-curar" class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Curar"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-heart-pulse fs-4"></i>
                </button> --}}

                {{-- 🔹 Upar Personagem --}}
                {{-- <button id="btn-upar" class="btn btn-outline-info rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Upar Personagem"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-arrow-up fs-4"></i>
                </button> --}}

                {{-- 🔹 Lançar Dados --}}
                {{-- <button id="btn-lancar-mestre"
                    class="btn btn-outline-warning rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Lançar Dados (Mestre)"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-dice-d20 fs-4"></i>
                </button> --}}

                {{-- 🔹 Permitir Dados --}}
                {{-- <button id="btn-permitir-jogada"
                    class="btn btn-outline-primary rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Permitir Jogada Extra"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-user-check fs-4"></i>
                </button>
            </div> --}}
        {{-- @endif --}}

        {{-- Coluna 2: Área de imagens + chat --}}
        <div class="d-flex flex-column flex-grow-1" style="min-height: 100vh; width: 100%;">

            {{-- Área principal de jogos (personagens + DiceBox) --}}
            <div id="games-section" class="d-flex flex-column flex-lg-row gap-3 align-items-stretch flex-grow-1" style="min-height: 50vh;">

                {{-- Coluna esquerda (personagens) --}}
                <div class="d-flex flex-column gap-2 overflow-auto" style="flex:1 1 auto; min-width:120px;">
                    @foreach ($membros->slice(0, ceil($membros->count() / 3)) as $m)
                        <div id="info-personagem-{{ $m['personagemId'] }}" class="bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card"
                            data-bs-toggle="collapse"
                            data-bs-target="#info-personagem-{{ $m['personagemId'] }}"
                            aria-expanded="false"
                            aria-controls="info-personagem-{{ $m['personagemId'] }}"
                            style="houver-cursor: pointer;"
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
                            <div id="info-personagem-{{ $m['personagemId'] }}" class="collapse mt-2"
                                style="min-height:150px; max-height:40vh; overflow:hidden;">
                                <div class="bg-dark rounded p-2 text-start text-light small">
                                    <strong>Raça:</strong> {{ $m['raca'] }}<br>
                                    <strong>Classe:</strong> {{ $m['classe'] }}<br>
                                    <strong>Nível:</strong> {{ $m['level'] }}<br>
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
                            @if ($isDono)
                                {{-- Apenas caso mestre quiser ocultar os dados --}}
                                <input type="checkbox" name="ocultarDados" id="ocultarDados" class="form-check-input mt-1" >
                                <label for="ocultarDados" class="form-check-label text-white">Ocultar Dados aos Jogadores</label>
                            @endif
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
                        <div id="info-personagem-{{ $m['personagemId'] }}" class="bg-dark rounded p-1 text-center d-flex flex-column align-items-center personagem-card"
                            data-bs-toggle="collapse"
                            data-bs-target="#info-personagem-{{ $m['personagemId'] }}"
                            aria-expanded="false"
                            aria-controls="info-personagem-{{ $m['personagemId'] }}"
                            style="houver-cursor: pointer;"
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
                            <div id="info-personagem-{{ $m['personagemId'] }}" class="collapse mt-2"
                                style="min-height:150px; max-height:40vh; overflow:hidden;">
                                <div class="bg-dark rounded p-2 text-start text-light small">
                                    <strong>Raça:</strong> {{ $m['raca'] }}<br>
                                    <strong>Classe:</strong> {{ $m['classe'] }}<br>
                                    <strong>Nível:</strong> {{ $m['level'] }}<br>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
            @if(!$isDono)
                {{-- View de infos personagem --}}
                <div class="flex-shrink-0 d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 gap-md-4 p-2 bg-dark rounded-4 shadow mt-3">

                    {{-- 🔹 Identidade --}}
                    <div class="card bg-warning text-dark">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseIdentidade" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Identidade</h6>
                        </div>
                        <div id="collapseIdentidade" class="collapse">
                            <div class="card-body">
                                <div><strong>Nome:</strong> {{ $personagem['nome'] ?? 'Desconhecido' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="flex-fill bg-dark rounded p-2">Raça: {{ $personagem['racaDescricao'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2">Classe: {{ $personagem['classeDescricao'] ?? '?' }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="flex-fill bg-dark rounded p-2">Idade: {{ $personagem['idade'] ?? '?' }}</div>
                                    <div class="flex-fill bg-dark rounded p-2">Gênero: {{ $personagem['genero'] ?? '?' }}</div>
                                </div>
                                <div class="text-center bg-dark rounded p-2 mt-2 text-white"><strong>Nível:</strong> {{ $personagem['level'] ?? 1 }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Atributos --}}
                    <div class="card bg-warning text-dark">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseAtributos" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Atributos</h6>
                        </div>
                        <div id="collapseAtributos" class="collapse">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Força: {{ $personagem['forca'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Agilidade: {{ $personagem['agilidade'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Inteligência: {{ $personagem['inteligencia'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Destreza: {{ $personagem['destreza'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Vitalidade: {{ $personagem['vitalidade'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Percepção: {{ $personagem['percepcao'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Sabedoria: {{ $personagem['sabedoria'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Carisma: {{ $personagem['carisma'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 🔹 Status de Combate --}}
                    <div class="card bg-warning text-dark">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#collapseCombate" style="cursor:pointer;">
                            <h6 class="mb-0 text-dark">Status de Combate</h6>
                        </div>
                        <div id="collapseCombate" class="collapse">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">HP: {{ $personagem['vida'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Mana: {{ $personagem['mana'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Iniciativa: {{ $personagem['iniciativa'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Defesa: {{ $personagem['defesaPersonagem'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Esquiva: {{ $personagem['esquivaPersonagem'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Corpo: {{ $personagem['ataqueFisicoCorpo'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2 text-white">
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Distância: {{ $personagem['ataqueFisicoDistancia'] ?? 0 }}</div>
                                    <div class="p-2 bg-dark rounded flex-fill text-center">Atk Mágico: {{ $personagem['ataqueMagico'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @else
                {{-- Botões de ação de mestre como linha abaixo de área --}}
                <div class="flex-shrink-0 d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 gap-md-4 p-2 bg-dark rounded-4 shadow mt-3">
                    {{-- Linha pra ter três elementos --}}
                    <div class="row mb-2 gap-2 justify-content-center">
                        {{-- 🔹 Iniciar/Avançar Turno --}}
                        <button id="btnIniciarTurno" class="btn btn-outline-success rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Iniciar/Avançar Turno"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-play fs-4"></i>
                        </button>
                        {{-- 🔹 Lançar Dados --}}
                        <button id="btn-lancar-mestre"
                            class="btn btn-outline-warning rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Lançar Dados (Mestre)"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-dice-d20 fs-4"></i>
                        </button>

                        {{-- 🔹 Permitir Dados --}}
                        <button id="btn-permitir-jogada"
                            class="btn btn-outline-primary rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Permitir Jogada Extra"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-user-check fs-4"></i>
                        </button>
                    </div>

                    {{-- Linha pra ter três elementos --}}
                    <div class="row mb-2 gap-2 justify-content-center">
                        {{-- 🔹 Causar Dano --}}
                        <button id="btn-dano" class="btn btn-outline-danger rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Causar Dano"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-burst fs-4"></i>
                        </button>

                        {{-- 🔹 Curar --}}
                        <button id="btn-curar" class="btn btn-outline-success rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Curar"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-heart-pulse fs-4"></i>
                        </button>
                        {{-- 🔹 Upar Personagem --}}
                        <button id="btn-upar" class="btn btn-outline-info rounded-circle mx-2 d-flex flex-column align-items-center justify-content-center"
                            data-bs-toggle="tooltip" title="Upar Personagem"
                            style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-arrow-up fs-4"></i>
                        </button>


                    </div>
                </div>
            @endif

            {{-- Botão para abrir/fechar chat --}}
            <button id="chat-toggle-btn"
                    class="btn btn-sm btn-warning mt-2 d-flex align-items-center gap-1"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#chat-container"
                    aria-expanded="false"
                    aria-controls="chat-container">
                <i class="fa-solid fa-comment"></i> Chat
            </button>

            {{-- Chat com collapse --}}
            <div id="chat-container" class="collapse mt-2">
                <div class="d-flex flex-column bg-dark rounded p-3 text-white"
                    style="min-height:150px; max-height:40vh; overflow:hidden;">
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


@include('partials/loading')
@include('partials/alerts')
@include('partials/invite')
{{-- @include('partials/invite') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/room/dice-manager.js') }}"></script>
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
    const newWsUrl = "/ws";

    console.log("WebSocket URL:", newWsUrl);


    window.CHAT_CONFIG = {
        userId: {{ session('user_id') ?? 'null' }},
        userLogin: "{{ session('user_login') ?? 'Desconhecido' }}",
        salaId: {{ $sala['id'] }},
        wsUrl: newWsUrl, // "{{ env('EXTERNAL_API_URL') }}/ws",
        isMestre: {{ $isDono ? 'true' : 'false' }}
    };

    window.csrfToken = "{{ csrf_token() }}";
    const csrfToken = "{{ csrf_token() }}";
    const routeSalasIndex = "{{ route('salas.index') }}";
</script>

<script src="{{ asset('js/room/exit.js') }}"></script>
<script src="{{ asset('js/room/invite.js') }}"></script>
<script src="{{ asset('js/room/delete.js') }}"></script>
<script src="{{ asset('js/utils/webSocketService.js') }}"></script>
<script src="{{ asset('js/room/chat-room.js') }}"></script>
<script src="{{ asset('js/room/room-manager.js') }}"></script>

@endsection

