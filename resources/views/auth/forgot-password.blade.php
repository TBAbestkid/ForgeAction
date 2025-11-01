@extends('partials.app')
@section('title', 'Esqueci senha - ForgeAction')
@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="text-center text-light" style="max-width: 400px; width: 100%;">
        <img src="{{ asset('assets/images/forgeicon.png') }}" alt="ForgeAction" width="80" class="mb-3">
        <h2 class="mb-3">Esqueceu sua senha?</h2>
        <p class="text-secondary mb-4">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>

       @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('forgot-password.send') }}" class="text-start">
            @csrf
            <div class="mb-3">
                <label class="form-label text-light fw-bold">E-mail</label>
                <input type="email" name="email" class="form-control border-secondary" placeholder="Digite seu e-mail" required>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold">
                <i class="fas fa-envelope me-2"></i> Enviar Link de Redefinição
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-info text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Voltar ao login
            </a>
        </div>
    </div>
</div>
@endsection
