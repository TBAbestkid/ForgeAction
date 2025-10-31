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

                {{-- 🔹 Iniciar Turno --}}
                <button class="btn btn-outline-success rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Iniciar Turno"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-play fs-4"></i>
                </button>

                {{-- 🔹 Causar Dano --}}
                <button class="btn btn-outline-danger rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Causar Dano"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-burst fs-4"></i>
                </button>

                {{-- 🔹 Upar Personagem --}}
                <button class="btn btn-outline-info rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Upar Personagem"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-arrow-up fs-4"></i>
                </button>

                {{-- 🔹 Lançar Dados --}}
                <button class="btn btn-outline-warning rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Lançar Dados"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-dice-d20 fs-4"></i>
                </button>

                {{-- 🔹 Permitir Dados --}}
                <button class="btn btn-outline-primary rounded-circle d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="tooltip" title="Permitir Dados"
                    style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-user-check fs-4"></i>
                </button>
            </div>
        @endif

        {{-- Coluna 2: Área de imagens + chat --}}
        <div class="flex-grow-1 d-flex flex-column h-100">

            {{-- Botão toggle de imagens/jogos --}}
            <button id="toggle-images-btn" class="btn btn-sm btn-warning mb-2"
                    data-bs-toggle="collapse" data-bs-target="#games-section" aria-expanded="true" aria-controls="games-section">
                <i class="fa-solid fa-chevron-up"></i> Área de Jogos
            </button>

            {{-- Área principal de jogos (personagens + DiceBox) --}}
            <div id="games-section" class="collapse show flex-grow-1 d-flex gap-4 align-items-stretch" style="height: 50vh;">

                {{-- Coluna esquerda (personagens) --}}
                <div class="d-flex flex-column gap-3 flex-shrink-0 overflow-auto" style="min-width: 200px; max-height: 100%;">
                    @foreach ($membros->slice(0, ceil($membros->count() / 3)) as $m)
                        <div class="bg-dark rounded p-2 text-end d-flex flex-column align-items-end"
                            data-id="{{ $m['personagemId'] }}"
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
                            <strong>{{ $m['nome'] }}</strong>
                            <div class="progress mt-1 w-100" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;"
                                    aria-valuenow="{{ $m['vida'] }}" aria-valuemin="0" aria-valuemax="{{ $m['vida'] }}">
                                </div>
                            </div>
                            <small class="text-light">{{ $m['vida'] }}/{{ $m['vida'] }} HP</small>
                        </div>
                    @endforeach
                </div>

                {{-- Coluna central (DiceBox) --}}
                <div id="dice-container" class="bg-dark rounded shadow-lg d-flex flex-column justify-content-center align-items-center w-100"
                    style="height: 100%; border: 2px solid #555;">
                    <span id="dice-placeholder" class="text-white">🎲 Aguardando início do turno...</span>

                    <div id="turn-controls" class="d-none flex-column align-items-center gap-2">
                        <div class="d-flex gap-3">
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
                <div class="d-flex flex-column gap-3 flex-shrink-0 overflow-auto" style="min-width: 200px; max-height: 100%;">
                    @foreach ($membros->slice(ceil($membros->count() / 3)) as $m)
                        <div class="bg-dark rounded p-2 text-start d-flex flex-column align-items-start"
                                data-id="{{ $m['personagemId'] }}"
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
                            <strong>{{ $m['nome'] }}</strong>
                            <div class="progress mt-1 w-100" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ ($m['vida'] / $m['vida']) * 100 }}%;"
                                    aria-valuenow="{{ $m['vida'] }}" aria-valuemin="0" aria-valuemax="{{ $m['vida'] }}">
                                </div>
                            </div>
                            <small class="text-light">{{ $m['vida'] }}/{{ $m['vida'] }} HP</small>
                        </div>
                    @endforeach
                </div>

            </div>

            {{-- Botão toggle do Chat --}}
            <button id="chat-toggle-btn" class="btn btn-sm btn-warning mt-2" type="button"
                    aria-expanded="true" aria-controls="chat-container">
                <i class="fa-solid fa-comment"></i> Chat
            </button>

            {{-- Chat --}}
            <div id="chat-container" class="collapse show d-flex flex-column bg-dark rounded p-3 text-white mt-2"
                style="height: 400px;">
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
{{-- Ativar tooltips --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ===== CHAT =====
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatMessages = document.getElementById('chat-messages');
    const userLogin = '{{ session("user_login") ?? "Player" }}';

    window.addMessage = function (text, sender = '🧠 Sistema') {
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
    };

    chatSend.addEventListener('click', () => {
        const text = chatInput.value.trim();
        if (!text) return;
        addMessage(text, userLogin);
        chatInput.value = '';
        chatInput.focus();
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            chatSend.click();
        }
    });

    // ===== COLLAPSE CUSTOM =====
    function setupCollapse(toggleBtnId, collapseElId, iconUp = 'fa-chevron-up', iconDown = 'fa-chevron-down') {
        const toggleBtn = document.getElementById(toggleBtnId);
        const collapseEl = document.getElementById(collapseElId);
        const icon = toggleBtn?.querySelector('i');

        if (!toggleBtn || !collapseEl) return;

        toggleBtn.addEventListener('click', () => {
            collapseEl.classList.toggle('collapsed');
            if (collapseEl.classList.contains('collapsed')) {
                collapseEl.style.height = '0';
                collapseEl.style.overflow = 'hidden';
                if (icon) icon.classList.replace(iconUp, iconDown);
            } else {
                collapseEl.style.height = '';
                collapseEl.style.overflow = '';
                if (icon) icon.classList.replace(iconDown, iconUp);
            }
        });
    }

    setupCollapse('toggle-images-btn', 'games-section');
    setupCollapse('chat-toggle-btn', 'chat-container', 'fa-comment', 'fa-comment-dots');

    // ===== AÇÕES DE JOGO =====
    const toastEl = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastEl);

    const placeholder = document.getElementById('dice-placeholder');
    const turnControls = document.getElementById('turn-controls');
    const diceOptions = document.getElementById('dice-options');
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');
    const btnIniciar = document.querySelector('.btn.btn-outline-success');

    let ordemTurnos = [];
    let turnoAtual = 0;
    let rodada = 1;

    btnIniciar.addEventListener('click', iniciarTurno);

    function iniciarTurno() {
        const personagens = [...document.querySelectorAll('.personagem-card, [data-iniciativa]')];
        if (!personagens.length) return showModal('Nenhum personagem encontrado.');

        personagens.forEach(p => {
            p.style.boxShadow = 'none';
            p.dataset.jogou = 'false';
        });

        ordemTurnos = personagens.sort((a, b) => b.dataset.iniciativa - a.dataset.iniciativa);

        for (let i = 0; i < ordemTurnos.length - 1; i++) {
            if (ordemTurnos[i].dataset.iniciativa === ordemTurnos[i + 1].dataset.iniciativa) {
                if (Math.random() > 0.5) [ordemTurnos[i], ordemTurnos[i + 1]] = [ordemTurnos[i + 1], ordemTurnos[i]];
            }
        }

        turnoAtual = 0;
        addMessage(`🧠 Rodada ${rodada} começou!`);
        ativarTurno(ordemTurnos[turnoAtual]);
    }

    function ativarTurno(personagem) {
        document.querySelectorAll('[data-iniciativa]').forEach(p => p.style.boxShadow = 'none');
        personagem.style.boxShadow = '0 0 20px 4px rgba(0, 255, 0, 0.8)';

        placeholder.classList.add('d-none');
        turnControls.classList.remove('d-none');
        diceOptions.classList.add('d-none');
        placeholder.textContent = `🎯 Turno de ${personagem.dataset.nome}`;

        toastMessage.textContent = `É o turno de ${personagem.dataset.nome}!`;
        toastEl.className = 'toast align-items-center text-bg-success border-0';
        toastBootstrap.show();

        addMessage(`É o turno de ${personagem.dataset.nome}!`);

        btnRoll.onclick = () => diceOptions.classList.toggle('d-none');
        btnSkip.onclick = () => proximoTurno(personagem);

        document.querySelectorAll('.dice-btn').forEach(btn => {
            btn.onclick = () => {
                const lados = parseInt(btn.dataset.sides);
                const resultado = Math.floor(Math.random() * lados) + 1;
                showDiceResult(personagem.dataset.nome, lados, resultado);
                personagem.dataset.jogou = 'true';
                diceOptions.classList.add('d-none');
            };
        });
    }

    function proximoTurno(personagem) {
        personagem.dataset.jogou = 'true';

        const todosJogaram = ordemTurnos.every(p => p.dataset.jogou === 'true');

        if (todosJogaram) {
            addMessage(`🧠 Turno ${rodada} encerrado!`);
            rodada++;
            ordemTurnos.forEach(p => p.dataset.jogou = 'false');
            addMessage(`🧠 Rodada ${rodada} começou!`);
            turnoAtual = 0;
        } else {
            do {
                turnoAtual = (turnoAtual + 1) % ordemTurnos.length;
            } while (ordemTurnos[turnoAtual].dataset.jogou === 'true');
        }

        ativarTurno(ordemTurnos[turnoAtual]);
    }

    function showDiceResult(nome, lados, resultado) {
        placeholder.classList.remove('d-none');
        placeholder.innerHTML = `🎲 <strong>${nome}</strong> rolou um <strong>D${lados}</strong> e tirou <strong>${resultado}</strong>!`;
        turnControls.classList.add('d-none');

        toastMessage.textContent = `${nome} rolou D${lados} → ${resultado}`;
        toastEl.className = 'toast align-items-center text-bg-primary border-0';
        toastBootstrap.show();

        addMessage(`${nome} rolou um D${lados} e tirou ${resultado}.`);

        setTimeout(() => {
            turnControls.classList.remove('d-none');
            placeholder.classList.add('d-none');
        }, 2500);
    }

    function showModal(message) {
        document.getElementById('modalMessage').textContent = message;
        const modal = new bootstrap.Modal(document.getElementById('modalAlert'));
        modal.show();
    }
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

