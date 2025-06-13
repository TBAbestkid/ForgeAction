@extends('partials.app')

@section('title', 'Sobre o ForgeAction')

@section('content')
<div class="container my-5">

    <!-- Imagem de Destaque com Título -->
    <div class="position-relative text-center mb-5">
        <img src="{{ asset('assets/images/forgewallpaper.jpg') }}" alt="ForgeAction" class="img-fluid" style="max-height: 600px; object-fit: cover; width: 100%;">
        <div class="position-absolute top-50 start-50 translate-middle text-white">
            <h1 class="display-4 font-medieval">ForgeAction</h1>
            <!-- <p class="lead opacity-75">Um RPG com sistema próprio para aventuras épicas!</p> -->
        </div>
    </div>

    <hr class="featurette-divider">

    <!-- Conteúdo "Sobre" -->
    <div class="row featurette">
        <div class="col-md-7">
            <h2 class="featurette-heading fw-normal lh-1 font-medieval">Sobre o ForgeAction</h2>
            <p class="lead">
                ForgeAction é um RPG inovador que traz um sistema próprio, 
                onde cada decisão e combate se tornam uma experiência única. 
                Desenvolvido com foco em mecânicas originais, nosso jogo propõe uma imersão completa, 
                desde a criação do personagem até batalhas épicas, utilizando regras personalizadas 
                para ataque, defesa e habilidades.
            </p>
        </div>
        <div class="col-md-5 order-md-1">
            <img class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" src="{{ asset('assets/images/aboutforge.png') }}" role="img" aria-label="Placeholder: 500x500" preserveAspectRatio="xMidYMid slice" focusable="false">
                <title>Placeholder</title>
                <rect width="100%" height="100%" fill="var(--bs-secondary-bg)"></rect>
            </img>
        </div>
    </div>

    <hr class="featurette-divider">

    <div class="row featurette">
        <div class="col-md-7 order-md-2 ">
            <p class="lead">
                A proposta do ForgeAction é oferecer aos jogadores um mundo repleto de desafios, 
                onde estratégia, criatividade e coragem se unem para construir uma jornada inesquecível. 
                Cada personagem possui atributos e poderes únicos, e o sistema de combate foi cuidadosamente 
                balanceado para proporcionar uma experiência dinâmica e envolvente.
            </p>
        </div>
        <div class="col-md-5 order-md-1">
            <img class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" src="{{ asset('assets/images/wallpaperaboutone.png') }}" role="img" aria-label="Placeholder: 500x500" preserveAspectRatio="xMidYMid slice" focusable="false">
                <title>Placeholder</title>
                <rect width="100%" height="100%" fill="var(--bs-secondary-bg)"></rect>
            </img>
        </div>
    </div>

    <hr class="featurette-divider">

    <!-- Seção Extra: Recursos do Sistema -->
    <div class="row">
        <div class="col-md-4 text-center ">
            <i class="fa-solid fa-dice fa-4x text-primary"></i>
            <h3 class="mt-3 font-medieval">Sistema Personalizado</h3>
            <p>Mecânicas exclusivas para ataques, defesas e habilidades, garantindo partidas desafiadoras.</p>
        </div>
        <div class="col-md-4 text-center">
            <i class="fa-solid fa-user-group fa-4x text-success"></i>
            <h3 class="mt-3 font-medieval">Diversidade de Personagens</h3>
            <p>Crie personagens únicos com atributos customizados e habilidades marcantes.</p>
        </div>
        <div class="col-md-4 text-center">
            <i class="fa-solid fa-book fa-4x text-warning"></i>
            <h3 class="mt-3 font-medieval">Lore Envolvente</h3>
            <p>Descubra histórias e cenários ricos que complementam a jogabilidade e inspiram aventuras.</p>
        </div>
    </div>

    <hr class="featurette-divider mt-5">

    <!-- Footer Simples -->
    <footer class="text-center">
        <p>© 2025 ForgeAction. Todos os direitos reservados.</p>
    </footer>
</div>
@endsection
