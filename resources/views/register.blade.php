@extends('partials/app')
@section('title', 'Cadastro de Usuário - ForgeAction')
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
    <div class="card form-signin w-100 bg-dark rounded-3 p-4 shadow-lg" style="max-width: 400px;">
        <form id="registerForm" method="POST" action="{{ route('register.post') }}">
            @csrf
            <img class="mb-4 mx-auto d-block" src="{{ asset('assets/images/forgeicon.png') }}" alt="" width="72" height="57">
            <h1 class="h3 mb-3 fw-normal text-center font-medieval text-white">Faça seu cadastro</h1>

            <div class="form-floating mb-3 text-light">
                <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                <label for="email"><i class="fa-solid fa-envelope me-1"></i> Email</label>
            </div>

            <div class="form-floating mb-3 text-light">
                <input type="text" name="login" class="form-control" id="login" placeholder="Nome de usuário" required>
                <label for="login"><i class="fa-solid fa-user me-1"></i> Nome de usuário</label>
            </div>

            <div class="form-floating mb-3 text-light">
                <input type="password" name="senha" class="form-control" id="senha" placeholder="Senha" required>
                <label for="senha"><i class="fa-solid fa-lock me-1"></i> Senha</label>
            </div>

            <div class="form-floating mb-4 text-light">
                <input type="password" id="passwordConfirm" class="form-control" placeholder="Confirme a senha" required>
                <label for="passwordConfirm"><i class="fa-solid fa-check-double me-1"></i> Confirmar senha</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fa-solid fa-paper-plane me-1"></i> Cadastrar
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-light text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Já tem uma conta? Faça login
                </a>
            </div>
        </form>
    </div>
</div>

@include('partials.loading')
@include('partials.alerts')
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Fazer um js validando as infos do form --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('registerForm');

        if (!form) {
            console.warn("Formulário de cadastro não encontrado!");
            return;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // -------------------
            // 1️⃣ Validação de campos
            // -------------------
            const email = form.email.value.trim();
            const login = form.login.value.trim();
            const senha = form.senha.value.trim();
            const senhaConfirm = document.getElementById('passwordConfirm').value.trim();

            if (!email || !login || !senha || !senhaConfirm) {
                return showAlert("Preencha todos os campos para continuar!");
            }

            if (senha.length < 6) {
                return showAlert("A senha deve ter no mínimo 6 caracteres!");
            }

            if (senha !== senhaConfirm) {
                return showAlert("As senhas não coincidem. Verifique e tente novamente.");
            }

            // -------------------
            // 2️⃣ Mostra overlay de loading
            // -------------------
            showLoading(5000);

            // -------------------
            // 3️⃣ Envio via AJAX para Laravel
            // -------------------
            const data = {
                email: email,
                login: login,
                senha: senha,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            fetch('{{ route("register.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                hideLoading();

                if (res.success) {
                    showToast(res.message || "Conta criada com sucesso! Redirecionando...");

                    setTimeout(() => {
                        goToPage(res.redirect || '/', 2000);
                    }, 1500);
                } else {
                    showAlert(res.message || "Não foi possível criar a conta. Tente novamente.");
                }
            })
            .catch(err => {
                hideLoading();
                console.error(err);
                showAlert("Erro inesperado. Verifique sua conexão ou tente mais tarde.");
            });
        });
    });
</script>


@endsection
