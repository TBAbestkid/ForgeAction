@extends('partials/app')
@section('title', 'Entrar - ForgeAction')
@section('content')
{{-- Aqui exibe os alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="form-signin w-100 bg-dark rounded-3 p-4 shadow-lg" style="max-width: 400px;">
        <form id="myForm" method="POST" action="{{ route('login.post') }}">
            @csrf
            <img class="mb-4 mx-auto d-block" src="{{ asset('assets/images/forgeicon.png') }}" alt="" width="72" height="57">
            <h1 class="h3 mb-3 fw-normal text-center font-medieval text-white">Faça login</h1>

            <div class="form-floating mb-3 text-light">
                <input type="text" name="login" class="form-control" id="floatingInput" placeholder="Insira usuario">
                <label for="floatingInput"><i class="fa-solid fa-user me-1"></i> Usuario</label>
            </div>
            <div class="form-floating mb-3 text-light">
                <input type="password" name="senha" class="form-control" id="floatingPassword" placeholder="Insira a Senha">
                <label for="floatingPassword"><i class="fa-solid fa-lock me-1"></i>Senha</label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check text-start text-white mb-0">
                    <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault">
                        Lembre de mim!
                    </label>
                </div>

                <div>
                    <a href="{{ route('forgot-password') }}" class="text-light text-decoration-none">
                        <i class="fas fa-key me-1"></i> Esqueceu a senha?
                    </a>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2 btn-submit" type="submit">
                <i class="fa-solid fa-paper-plane me-1"></i> Entre
            </button>
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-light text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Não tem uma conta? Faça cadastro
                </a>
            </div>
        </form>
    </div>
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('myForm');
        const overlay = document.getElementById('loading-overlay');

        // console.log("DOM carregado. Form:", form, "Overlay:", overlay);

        if (form && overlay) {
            form.addEventListener('submit', function(e) {
                // e.preventDefault(); // remova se quiser que envie de verdade
                overlay.style.display = 'flex';
                console.log("Overlay ativado pelo submit do formulário");

                if (typeof showLoading === "function") {
                    showLoading(3000);
                }
            });
        } else {
            console.warn("Form ou overlay não encontrados!");
        }
    });
</script>

@endsection
