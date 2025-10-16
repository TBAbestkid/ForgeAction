@extends('layouts.app')

@section('content')
<div class="container text-light">
    <h2 class="mb-4">Redefinir Senha</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label>Nova Senha</label>
            <input type="password" name="password" class="form-control text-dark" required>
        </div>
        <div class="mb-3">
            <label>Confirme a Senha</label>
            <input type="password" name="password_confirmation" class="form-control text-dark" required>
        </div>

        <button type="submit" class="btn btn-success w-100">Salvar nova senha</button>
    </form>
</div>
@endsection
