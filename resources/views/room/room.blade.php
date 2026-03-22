@extends('partials/appSala')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')

<div id="roomBackground"
    style="background-image: url('https://media.discordapp.net/attachments/1073740510352650300/1483935134762930287/from-PixAI-1991114275611497399-Imagem_RPG.png?ex=69bc65ca&is=69bb144a&hm=2eb3dcf9da663a46034e1f25c3b34ff64103a114438117f3b1f9dfb311e86798&=&format=webp&quality=lossless&width=1534&height=920')">

</div>
<button class="btn btn-primary" id="fullscreen" onclick="entrarEmFullscreen()">Fullscreen</button>
<div id="minhaSala" class="container">
    sala
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

<script>
    function entrarEmFullscreen() {
        const elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) { /* Firefox */
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE/Edge */
            elem.msRequestFullscreen();
        }
    }

    function sairDoFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) { /* Firefox */
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) { /* Chrome, Safari and Opera */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) { /* IE/Edge */
            document.msExitFullscreen();
        }
    }
</script>
@endsection
