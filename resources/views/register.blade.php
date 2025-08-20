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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Fazer um js validando as infos do form --}}
<script>
    const form = document.querySelector('#registerForm');
    const loading = document.querySelector('#loading-overlay');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Verifica se senha e confirmação batem
        const senha = form.senha.value;
        const senhaConfirm = document.querySelector('#passwordConfirm').value;
        if(senha !== senhaConfirm){
            alert('As senhas não coincidem!');
            return;
        }

        const data = {
            login: form.login.value,
            senha: senha,
            email: form.email.value
        };

        // Mostra o overlay de loading
        loading.style.display = 'flex';

        fetch('/ajax/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            // Esconde o loading
            loading.style.display = 'none';

            if(res.success){
                alert(res.message);
                window.location.href = '/dashboard'; // redireciona após sucesso
            } else {
                alert(res.message);
            }
        })
        .catch(err => {
            loading.style.display = 'none';
            console.error(err);
            alert('Ocorreu um erro inesperado.');
        });
    });
</script>
@endsection
