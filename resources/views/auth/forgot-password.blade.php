@extends('layouts.app')

@section('content')
<div class="container text-light">
    <h2 class="mb-4">Esqueceu sua senha?</h2>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control text-dark" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Enviar link</button>
    </form>
</div>
@endsection
