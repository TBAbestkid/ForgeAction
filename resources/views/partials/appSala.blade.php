<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="ujLGJLcUsDPNpqlJDg1OtNICmpht1XP4428B_bWhUi8" />
    <meta name="description" content="Crie, gerencie e evolua personagens de RPG com o ForgeAction. Um sistema interativo de fichas e aventuras personalizadas.">
    <meta name="keywords" content="RPG, fichas de RPG, personagens, aventuras, sistema online, ForgeAction">
    <meta name="author" content="ForgeAction Team">
    <meta name="robots" content="index, follow">
    <meta name="language" content="pt-BR">
    <meta name="theme-color" content="#000000">
    <title>@yield('title', 'ForgeAction')</title>
    <link rel="icon" type="image/png" href="{{ secure_asset('assets/images/forgeicon.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/site.webmanifest') }}" />

    <!-- Google Fonts para temática RPG -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=MedievalSharp&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Stomp -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sockjs-client@1/dist/sockjs.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/forge-theme.css') }}" rel="stylesheet">
</head>
<body>

    <main class="py-4">
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0FGXCHJGQB"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-0FGXCHJGQB');
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                .then(registration => console.log('Service Worker registrado:', registration.scope))
                .catch(err => console.log('Falha ao registrar Service Worker:', err));
            });
        }
    </script>

</body>
</html>
