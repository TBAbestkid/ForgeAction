@extends('partials/app')
@section('title', 'ForgeAction - Dashboard')
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="container mt-4 ">
    <div class="card p-3 text-center text-white">
        <h1 class="font-medieval">Ficha Personagem</h1>
        <h2>{{ $personagem['nome'] ?? 'Desconhecido' }}</h2>
        <p>
            Classe: {{ $personagem['classe'] ?? '-' }} | 
            Raça: {{ $personagem['raca'] ?? '-' }} | 
            Idade: {{ $personagem['idade'] ?? '-' }} | 
            Identificação: {{ $personagem['identificacao'] ?? '-' }}
        </p>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card p-3 text-white">
                <h4 class="font-medieval">Atributos</h4>
                <ul class="">
                    <li class="">Força: X</li>
                    <li class="">Agilidade: X</li>
                    <li class="">Inteligência: X</li>
                    <li class="">Sabedoria: X</li>
                    <li class="">Destreza: X</li>
                    <li class="">Vitalidade: X</li>
                    <li class="">Percepção: X</li>
                    <li class="">Carisma: X</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 mb-3 text-white">
                <h4 class="font-medieval">Bônus</h4>
                <pclass="text-white">Vida: X</p>
                <p>Mana: X</p>
            </div>
            <div class="card p-3 mb-3 text-white">
                <h4 class="font-medieval">Status</h4>
                <p>Vida Total: X</p>
                <p>Mana Total: X</p>
                <p>Iniciativa: X</p>
            </div>
            <div class="card p-3 text-white">
                <h4 class="font-medieval">Dano Base</h4>
                <p>Físico: X</p>
                <p>Mágico: X</p>
            </div>
        </div>
    </div>
</div>
@endsection