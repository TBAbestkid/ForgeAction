@extends('partials.app')

@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100 bg-dark">
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 450px; width: 100%; background-color: #1e1e1e;">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction" width="70" class="mb-3">
            <h2 class="font-medieval text-warning">Redefinir Senha</h2>
            <p class="text-secondary small">Escolha uma nova senha para continuar sua jornada.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label text-light fw-bold">Nova Senha</label>
                <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Digite sua nova senha" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-light fw-bold">Confirme a Senha</label>
                <input type="password" name="password_confirmation" class="form-control bg-dark text-light border-secondary" placeholder="Confirme sua nova senha" required>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold">
                <i class="fas fa-key me-2"></i> Salvar Nova Senha
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-info text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Voltar ao login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
