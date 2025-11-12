@extends('partials.app')
@section('title', 'Simulador de Rolagem de Dados')
@section('content')
<div class="container my-4">
  <h1>Simulador de Rolagem</h1>

  <input id="dice-notation" class="form-control" placeholder="1d20" />
  <button id="roll-btn" class="btn btn-primary">Rolar</button>
  <div id="scene-container" style="width: 400px; height: 400px;"></div>
</div>

@vite(['resources/js/dice.js'])
@endsection
