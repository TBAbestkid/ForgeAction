@extends('partials.app')
@section('title', 'Simulador de Rolagem de Dados')
@section('content')
<div class="container my-4">
    <h1>Simulador de Rolagem</h1>

    <input id="dice-notation" class="form-control mb-2" placeholder="Notação de dados (ex: 1d20)" />
    <input id="force-value" type="number" class="form-control mb-2" placeholder="Forçar valor (opcional)" />
    <button id="roll-btn" class="btn btn-primary mb-4">Rolar</button>
    <div id="scene-container" style="width: 400px; height: 400px;"></div>
</div>

@vite('resources/js/dados.js')

@endsection
