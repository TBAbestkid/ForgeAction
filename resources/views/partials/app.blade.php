<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ForgeAction')</title>
    <link rel="icon" type="image/png" href="{{ secure_asset('assets/images/forgeicon.png') }}" />
    <!-- Google Fonts para temática RPG -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=MedievalSharp&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @vite(['resources/js/app.js', 'resources/css/app.css', 'resources/css/style.css'])

</head>
<body>
    <!-- Navbar simples -->
    <nav class="navbar navbar-expand-lg navbar-dark font-medieval">
        <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fa-brands fa-d-and-d"></i> ForgeAction
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
            @if(session('user_login'))
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa-solid fa-user-cog"></i> {{ session('user_login') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}"><i class="fa-solid fa-user"></i> Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}"><i class="fa-solid fa-user-cog"></i> Cadastro</a>
                </li>
            @endif
            </ul>
        </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>
    <!-- Scripts do Bootstrap -->
    <!-- <script src="{{ asset('js/app.js') }}"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>
</html>
