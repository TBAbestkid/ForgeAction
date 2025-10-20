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

<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 600px;">
        <h2 class="text-center font-medieval text-white">Crie sua conta</h2>

        <div class="tab-content mt-3">
            <!-- Aba 1: Dados Cadastrais -->
            <form action="{{ route('register.post') }}" id="registerForm" method="post">
                @csrf
                <div class="" id="login">
                    <div class="form-floating mb-3">
                        <input type="text" name="email" class="form-control" placeholder="name@example.com" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="login" class="form-control" placeholder="Nome Usuario" required>
                        <label for="name">Nome usuario</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="senha" class="form-control" placeholder="Senha" required>
                        <label for="password">Senha</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" id="passwordConfirm" class="form-control" placeholder="Confirme a senha" required>
                        <label for="passwordConfirm">Confirme a senha</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Envie</button>
                </div>
            </form>
        </div>

    </div>
</div>

@include('partials.loading')
@include('partials.alerts')
<script src="{{ asset('js/loading.js') }}"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Fazer um js validando as infos do form --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('registerForm');
        const modalAlert = new bootstrap.Modal(document.getElementById('modalAlert'));
        const toastLive = new bootstrap.Toast(document.getElementById('liveToast'));

        // Função: Modal para avisos de erro/alerta
        function showModal(message) {
            document.getElementById('modalMessage').textContent = message;
            modalAlert.show();
        }

        // Função: Toast de sucesso
        function showToast(message = "Sucesso!") {
            document.getElementById('toastMessage').textContent = message;
            toastLive.show();
        }

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
                return showModal("Preencha todos os campos para continuar!");
            }

            if (senha.length < 6) {
                return showModal("A senha deve ter no mínimo 6 caracteres!");
            }

            if (senha !== senhaConfirm) {
                return showModal("As senhas não coincidem. Verifique e tente novamente.");
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
                    showModal(res.message || "Não foi possível criar a conta. Tente novamente.");
                }
            })
            .catch(err => {
                hideLoading();
                console.error(err);
                showModal("Erro inesperado. Verifique sua conexão ou tente mais tarde.");
            });
        });
    });
</script>


@endsection
