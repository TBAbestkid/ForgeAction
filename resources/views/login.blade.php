@extends('partials/app')
@section('title', 'ForgeAction - Entrar')
@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="form-signin w-100" style="max-width: 400px;">
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

        <form id="myForm" method="POST" action="{{ route('login.post') }}">
            @csrf
            <img class="mb-4 mx-auto d-block" src="{{ asset('assets/images/forgeicon.png') }}" alt="" width="72" height="57">
            <h1 class="h3 mb-3 fw-normal text-center font-medieval text-white">Faça login</h1>

            <div class="form-floating mb-3 text-dark">
                <input type="text" name="login" class="form-control" id="floatingInput" placeholder="Insira usuario">
                <label for="floatingInput">Usuario</label>
            </div>
            <div class="form-floating mb-3 text-dark">
                <input type="password" name="senha" class="form-control" id="floatingPassword" placeholder="Insira a Senha">
                <label for="floatingPassword">Senha</label>
            </div>

            <div class="form-check text-start mb-3 text-white">
                <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    Lembre de mim!
                </label>
            </div>
            <button class="btn btn-primary w-100 py-2 btn-submit" type="submit">Entre</button>
        </form>
    </div>
</div>

<!-- Modal de Alerta -->
<div class="modal fade" id="modalAlert" tabindex="-1" aria-labelledby="modalAlertLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 d-flex align-items-center text-dark" id="modalAlertLabel">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> Alerta!
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

@include('partials/loading')

<script>
    document.getElementById('myForm').addEventListener('submit', function() {
        document.getElementById('loading-overlay').style.display = 'flex';
    });
</script>


@endsection