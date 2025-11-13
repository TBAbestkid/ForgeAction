@extends('partials.app')
@section('title', 'Redefinir senha - ForgeAction')
@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg bg-dark border-0 rounded-4 p-4" style="max-width: 450px; width: 100%; background-color: #1e1e1e;">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction" width="70" class="mb-3">
            <h2 class="font-medieval text-warning">Redefinir Senha</h2>
            <p class="text-secondary small">Escolha uma nova senha para continuar sua jornada.</p>
        </div>

        <form id="resetForm" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label text-light fw-bold">Nova Senha</label>
                <input type="password" id="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Digite sua nova senha" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-light fw-bold">Confirme a Senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control bg-dark text-light border-secondary" placeholder="Confirme sua nova senha" required>
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
@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<script>
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;

        if(password !== confirm) {
            e.preventDefault(); // impede o envio do formulário
            showAlert('As senhas não coincidem!'); // função para mostrar modal
            return false;
        }

        if(password.length < 6) {
            e.preventDefault();
            showAlert('A senha deve ter no mínimo 6 caracteres!');
            return false;
        }
    });

</script>
@endsection
