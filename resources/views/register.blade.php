@extends('partials/app')
@section('title', 'ForgeAction - Cadastro de Usuário')
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
        <!-- <ul class="nav nav-tabs" id="registerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-login" data-bs-toggle="tab" type="button">Dados Cadastrais</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-personagem" data-bs-toggle="tab" type="button">Dados do Personagem</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-atributos" data-bs-toggle="tab" type="button">Atributos</button>
            </li>
        </ul> -->

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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const overlay = document.getElementById('loading-overlay');

        if (!form || !overlay) {
            console.warn("Form ou overlay não encontrados!");
            return;
        }

        form.addEventListener('submit', function(e) {
            // Validações front-end
            const senha = form.senha.value;
            const senhaConfirm = document.querySelector('#passwordConfirm').value;

            if(senha !== senhaConfirm){
                e.preventDefault(); // impede envio
                showModal("As senhas não coincidem!");
                return;
            }

            // Mostra overlay enquanto a página carrega
            overlay.style.display = 'flex';
            if(typeof showLoading === "function") {
                showLoading(3000); // opcional, para animação
            }

            // e.preventDefault(); // descomente se for usar fetch
            /*
            e.preventDefault();
            const data = {
                login: form.login.value,
                senha: senha,
                email: form.email.value
            };

            fetch('{{ route("register.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                overlay.style.display = 'none';
                if(res.success){
                    showToast(res.message, 'success');
                    setTimeout(() => window.location.href = '/dashboard', 1500);
                } else {
                    showModal(res.message);
                }
            })
            .catch(err => {
                overlay.style.display = 'none';
                console.error(err);
                showModal('Ocorreu um erro inesperado.');
            });
            */
            // Se for envio tradicional (reload da página), não precisa do fetch
        });
    });
</script>


@endsection
