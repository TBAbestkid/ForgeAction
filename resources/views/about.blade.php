@extends('partials.app')

@section('title', 'Sobre - ForgeAction')

@section('content')
<div class="container my-5">

    <!-- Imagem de Destaque com Título -->
    <div class="position-relative text-center mb-5">
        <img src="{{ asset('assets/images/forgewallpaper.jpg') }}" alt="ForgeAction" class="img-fluid" style="max-height: 600px; object-fit: cover; width: 100%;">
        <div class="position-absolute top-50 start-50 translate-middle text-white">
            <h1 class="display-4 font-medieval">ForgeAction</h1>
            <p class="lead opacity-75">O seu aliado para aventuras de RPG, organizado e divertido!</p>
        </div>
    </div>

    <hr class="featurette-divider">

    <!-- Sobre o ForgeAction -->
    <div class="row featurette">
        <div class="col-md-7">
            <h2 class="featurette-heading fw-normal lh-1 font-medieval">Sobre o ForgeAction</h2>
            <p class="lead">
                ForgeAction é um <strong>helper completo para mestres e jogadores de RPG</strong>, oferecendo ferramentas
                para organizar aventuras, criar salas e compartilhar itens com outros participantes.
                Ele possui um sistema próprio que facilita a gestão de personagens, combates e narrativas,
                garantindo partidas mais dinâmicas e envolventes.
            </p>
            <p class="lead">
                <strong>Atualização do sistema:</strong> aqui será atualizado mais pra frente, adicionando mais informações sobre mecânicas,
                regras ou funcionalidades que estiverem sendo implementadas, mantendo todos os usuários informados.
            </p>
        </div>
        <div class="col-md-5 order-md-1">
            <img class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" src="{{ asset('assets/images/aboutforge.png') }}" role="img" alt="Sobre ForgeAction">
        </div>
    </div>

    <hr class="featurette-divider">

    <!-- Funcionalidades -->
    <div class="row featurette">
        <div class="col-md-7 order-md-2">
            <p class="lead">
                Com ForgeAction, você pode criar <strong>salas de RPG online</strong>, convidar outros jogadores,
                gerenciar inventários compartilhados e acompanhar a evolução de cada personagem.
                Tudo isso em uma interface intuitiva, focada em agilizar a organização das aventuras e permitir
                que mestres e jogadores se concentrem na história e na diversão, sem se perder em planilhas ou regras complicadas.
            </p>
        </div>
        <div class="col-md-5 order-md-1">
            <img class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" src="{{ asset('assets/images/wallpaperaboutone.png') }}" role="img" alt="Funcionalidades ForgeAction">
        </div>
    </div>

    <hr class="featurette-divider">

    <!-- Seção Extra: Recursos do Sistema -->
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <i class="fa-solid fa-dice fa-4x text-primary"></i>
            <h3 class="mt-3 font-medieval">Sistema Personalizado</h3>
            <p>Gerencie combates, habilidades e regras próprias com mecânicas flexíveis que se adaptam a cada aventura.</p>
        </div>
        <div class="col-md-4 mb-4">
            <i class="fa-solid fa-user-group fa-4x text-success"></i>
            <h3 class="mt-3 font-medieval">Salas e Interações</h3>
            <p>Crie salas de RPG, convide jogadores e compartilhe itens e informações de forma prática e organizada.</p>
        </div>
        <div class="col-md-4 mb-4">
            <i class="fa-solid fa-book fa-4x text-warning"></i>
            <h3 class="mt-3 font-medieval">Lore e Narrativa</h3>
            <p>Construa histórias imersivas, com cenários ricos e ferramentas que facilitam o desenvolvimento de aventuras épicas.</p>
        </div>
    </div>

    <hr class="featurette-divider mt-5">

    <!-- Call to Action -->
    <div class="text-center mb-5">
        <h2 class="font-medieval">Comece sua aventura!</h2>
        <p class="lead">Junte-se a outros jogadores e mestres, organize suas aventuras e descubra todo o potencial do ForgeAction.</p>
        <a href="/register" class="btn btn-primary btn-lg"><i class="fa-solid fa-play me-2"></i> Criar Conta</a>
    </div>

    <!-- Footer Simples -->
    <footer class="text-center py-4">
        <p>© 2025 ForgeAction. Todos os direitos reservados.</p>
    </footer>
</div>
@endsection
