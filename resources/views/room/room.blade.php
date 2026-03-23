@extends('partials/appSala')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')

<div id="roomBackground"
     style="background-image: url('{{ $sala['urlBackground'] ? $sala['urlBackground'] : asset('images/default-bg.jpg') }}');">
</div>

<div class="position-absolute top-0 end-0 d-flex align-items-center gap-2 m-3">

    <div class="dropdown">
        <button class="btn outline-btn-light text-dark dropdown-toggle" id="optionsMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu">
            {{-- Tanto para mestre quanto para player --}}
            <li>
                <button class="dropdown-item" type="button" id="fullscreen" onclick="entrarEmFullscreen()">
                    <i class="fa-solid fa-expand"></i>
                    Tela Cheia
                </button>
            </li>
            <li>
                <a href="#" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMembers">
                    <i class="fa-solid fa-users"></i>
                    Membros
                </a>
            </li>
            @if($isDono)
                <li><hr class="dropdown-divider text-white"></li>
                <li>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#configModal">
                        <i class="fa-solid fa-user-plus"></i>
                        Convidar Membros
                    </a>
                </li>
                <li>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#copiarcodigoSala">
                        <i class="fa-solid fa-copy"></i>
                        Copiar Código da Sala
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('home') }}" class="dropdown-item">
                    <i class="fa-solid fa-door-open"></i>
                    Sair da Sala
                </a>
            </li>
        </ul>
    </div>
    @if (!$isDono)
        <button class="btn outline-btn-light text-dark ms-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFicha" aria-controls="offcanvasFicha">
            <i class="fa-solid fa-scroll me-2"></i>
            Ficha
        </button>
    @endif
</div>

<div class="position-absolute bottom-0 start-0 m-3">

    <!-- Botão -->
    <button class="btn btn-dark mb-2"
            data-bs-toggle="collapse"
            data-bs-target="#chatCollapse">
        💬 Chat
    </button>

    <!-- Collapse -->
    <div class="collapse" id="chatCollapse">
        <div class="chat-box rounded-4 shadow p-3">

            <div id="chatMessages" class="mb-3">
                {{-- Mensagens --}}
            </div>

            <div class="input-group">
                <input type="text" class="form-control" placeholder="Digite sua mensagem...">
                <button class="btn btn-primary"> <i class="fa-solid fa-paper-plane"></i></button>
            </div>

        </div>
    </div>

</div>

{{-- Botões de Ações Mestre/Player --}}
{{-- Usando de base a ideia de HUID --}}
{{-- Botões de ação de mestre como linha abaixo de área --}}
<div class="position-fixed bottom-0 start-50 translate-middle-x mb-3 z-3">
    <div class="d-flex gap-3 px-3 py-2 rounded-4 shadow hud-bg">

        @if($isDono)

            <button id="btnIniciarTurno"
                class="btn btn-success btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Turno">
                <i class="fa-solid fa-play"></i>
            </button>

            <button id="btnLancarMestre"
                class="btn btn-warning btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Dados" disabled>
                <i class="fa-solid fa-dice-d20"></i>
            </button>

            <button id="btnPermitirJogadaExtra"
                class="btn btn-primary btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Extra" disabled>
                <i class="fa-solid fa-user-check"></i>
            </button>

            <button id="btnDano"
                class="btn btn-danger btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Dano" disabled>
                <i class="fa-solid fa-burst"></i>
            </button>

            <button id="btnCurar"
                class="btn btn-success btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Curar" disabled>
                <i class="fa-solid fa-heart-pulse"></i>
            </button>

            <button id="btnUpar"
                class="btn btn-info btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Upar" disabled>
                <i class="fa-solid fa-arrow-up"></i>
            </button>
        @else
            {{-- 🎲 Rodar Dado --}}
            <button id="btn-roll"
                class="btn btn-outline-light btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Rodar Dado" disabled>
                <i class="fa-solid fa-dice-d20"></i>
            </button>

            {{-- ⏭️ Pular Turno --}}
            <button id="btn-skip"
                class="btn btn-outline-warning btn-lg rounded-3 d-flex align-items-center justify-content-center hud-btn"
                title="Pular Turno" disabled>
                <i class="fa-solid fa-forward"></i>
            </button>
        @endif
    </div>
</div>

@if ($isDono)
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
@else
    <div class="offcanvas offcanvas-end text-light" tabindex="-1" id="offcanvasFicha"
        aria-labelledby="offcanvasFichaLabel" style="background-color: #1c1c1c; max-width: 280px;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFichaLabel">
                <i class="fa-solid fa-scroll me-2"></i>Ficha de {{ $personagemJogador['nome'] ?? 'Personagem' }}
            </h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-3 overflow-auto" style="max-height: calc(100vh - 60px);">
            <div class="row g-2">
                <!-- Header do Personagem -->
                <div class="col-12 mb-3 border-bottom border-secondary pb-2">
                    <div><small><strong><i class="fa-solid fa-user-shield"></i> Raça:</strong> {{ $personagemJogador['raca'] ?? 'N/A' }}</small></div>
                    <div><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Classe:</strong> {{ $personagemJogador['classe'] ?? 'N/A' }}</small></div>
                    <div><small><strong><i class="fa-solid fa-signal"></i> Nível:</strong> {{ $personagemJogador['level'] ?? 1 }}</small></div>
                </div>

                <!-- Atributos Principais -->
                <div class="col-12 mb-2">
                    <small class="text-warning"><strong>⚔️ Atributos Principais</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-dumbbell"></i> Força:</strong> {{ $personagemJogador['forca'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-bolt"></i> Agilidade:</strong> {{ $personagemJogador['agilidade'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-brain"></i> Inteligência:</strong> {{ $personagemJogador['inteligencia'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand"></i> Destreza:</strong> {{ $personagemJogador['destreza'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-heart"></i> Vitalidade:</strong> {{ $personagemJogador['vitalidade'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-eye"></i> Percepção:</strong> {{ $personagemJogador['percepcao'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-book"></i> Sabedoria:</strong> {{ $personagemJogador['sabedoria'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-comments"></i> Carisma:</strong> {{ $personagemJogador['carisma'] ?? 0 }}</small></div>

                <!-- Recursos de Vida/Mana -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-info"><strong>❤️ Recursos</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-heart"></i> Vida:</strong> {{ $personagemJogador['vida'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-droplet"></i> Mana:</strong> {{ $personagemJogador['mana'] ?? 0 }}</small></div>

                <!-- Iniciativa e Bônus -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-success"><strong>⚡ Ações</strong></small>
                </div>
                <div class="col-12"><small><strong><i class="fa-solid fa-forward"></i> Iniciativa:</strong> {{ $personagemJogador['iniciativa'] ?? 0 }}</small></div>

                <!-- Ataques -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-danger"><strong>⚔️ Ataques</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-wand-magic-sparkles"></i> Atk Mágico:</strong> {{ $personagemJogador['ataqueMagico'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-hand-fist"></i> Atk Corpo:</strong> {{ $personagemJogador['ataqueFisicoCorpo'] ?? 0 }}</small></div>
                <div class="col-12 mt-2"><small><strong><i class="fa-solid fa-bullseye"></i> Atk Distância:</strong> {{ $personagemJogador['ataqueFisicoDistancia'] ?? 0 }}</small></div>

                <!-- Defesa -->
                <div class="col-12 mb-2 mt-2 border-top border-secondary pt-2">
                    <small class="text-secondary"><strong>🛡️ Defesa</strong></small>
                </div>
                <div class="col-6"><small><strong><i class="fa-solid fa-shield-halved"></i> Defesa:</strong> {{ $personagemJogador['defesaPersonagem'] ?? 0 }}</small></div>
                <div class="col-6"><small><strong><i class="fa-solid fa-feather"></i> Esquiva:</strong> {{ $personagemJogador['esquivaPersonagem'] ?? 0 }}</small></div>
            </div>
        </div>
    </div>
@endif

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
            @php
                $mestreId = $sala['mestre'];
            @endphp

            <li class="list-group-item bg-dark text-warning d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa-solid fa-crown text-warning me-2"></i>
                    <strong>{{ $membros[$mestreId] ?? 'Mestre' }}</strong>
                </div>
                <span><span class="members-list-dot offline"></span></span>
            </li>

            {{-- 🔹 Depois os Players (exceto o mestre) --}}
            @foreach($membros as $uid => $login)
                @continue($uid == $mestreId)

                <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center"
                    data-user-id="{{ $uid }}">
                    {{ $login }}
                    <span><span class="members-list-dot offline"></span></span>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div id="aviso-fullscreen" class="alert alert-info" style="display: none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1050; cursor: pointer;">
    <i class="fa-solid fa-info-circle"></i>
    Pressione <strong>F11</strong> ou <strong>Clique aqui</strong> para entrar em tela cheia
</div>

<script>
    window.onload = function() {
        const aviso = document.getElementById('aviso-fullscreen');
        setTimeout(() => {
            aviso.style.display = 'block';
            aviso.onclick = function() {
                entrarEmFullscreen();
                aviso.style.display = 'none';
            };
        }, 1000);
    };

    function entrarEmFullscreen() {
        const elem = document.documentElement;
        const fullscreenButton = document.getElementById('fullscreen');

        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari e Opera */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE/Edge */
            elem.msRequestFullscreen();
        }

        if (fullscreenButton) {
            fullscreenButton.innerHTML = '<i class="fa-solid fa-compress"></i> Minimizar';
            fullscreenButton.onclick = sairDoFullscreen;
        }
    }

    function sairDoFullscreen() {
        const fullscreenButton = document.getElementById('fullscreen'); // Ajustado para o ID correto

        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }

        // Volta o botão ao estado original
        if (fullscreenButton) {
            fullscreenButton.innerHTML = '<i class="fa-solid fa-expand"></i> Tela Cheia';
            fullscreenButton.onclick = entrarEmFullscreen;
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'F11') {
            e.preventDefault(); // impede comportamento padrão
            toggleFullscreen();
        }
    });
</script>
@endsection
