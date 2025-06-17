@extends('partials/app')
@section('title', 'ForgeAction - Cadastro2.0')
@section('content')
<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 600px;">
        <h2 class="text-center font-medieval text-white">Crie sua conta</h2>
        <ul class="nav nav-tabs" id="registerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-login" data-bs-toggle="tab" type="button">Dados Cadastrais</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-personagem" data-bs-toggle="tab" type="button">Dados do Personagem</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-atributos" data-bs-toggle="tab" type="button">Atributos</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Aba 1: Dados Cadastrais -->
            <form action="{{ route('register.post') }}" id="myForm" method="post">
                @csrf
                <div class="" id="login">
                    <div class="form-floating mb-3">
                        <input type="text" name="email" class="form-control" placeholder="name@example.com" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="chapLogin" class="form-control" placeholder="Nome Usuario" required>
                        <label for="name">Nome usuario</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="chapSenha" class="form-control" placeholder="Senha" required>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Fazer um js validando as infos do form --}}

@endsection
