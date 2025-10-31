@extends('partials/app')
@section('title', 'Salas - ForgeAction')

@section('content')
<div class="container mt-4 d-flex flex-column flex-lg-row gap-4">

    <!-- Coluna principal -->
    <div class="flex-fill d-flex flex-column gap-5">

        <!-- Botão de Voltar -->
        <div class="text-start">
            <a href="{{ url('/') }}" class="btn btn-outline-light mb-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Home
            </a>
        </div>

        <!-- Minhas Salas -->
        @if(session('user_role') === 'MASTER')
            <div>
                <h2 class="font-medieval mb-3">Minhas Salas</h2>
                <div id="minhas-salas" class="d-flex flex-column gap-3">
                    @forelse($minhasSalas as $sala)
                        <div class="sala-card p-3 rounded d-flex justify-content-between align-items-center bg-dark text-white">
                            <div>
                                <strong>{{ $sala['nome'] }}</strong><br>
                                <small class="text-light">{{ $sala['descricao'] }}</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">{{ $sala['total_jogadores'] ?? 0 }} jogador(es)</span>
                                <a href="/salas/{{ $sala['id'] }}" class="btn btn-sm btn-primary">Entrar</a>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info"><i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala criada.</div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- Salas que participo -->
        <div>
            <h2 class="font-medieval mb-3">Salas que participo</h2>
            <div id="salas-participando" class="d-flex flex-column gap-3">
                @forelse($outrasSalas as $sala)
                    @if(is_array($sala))
                        <div class="sala-card p-3 rounded d-flex justify-content-between align-items-center bg-dark text-white">
                            <div>
                                <strong>{{ $sala['nome'] ?? '—' }}</strong><br>
                                <small class="text-light">{{ $sala['descricao'] ?? '' }}</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">{{ $sala['total_jogadores'] ?? 0 }} jogador(es)</span>
                                <a href="/salas/{{ $sala['id'] ?? '#' }}" class="btn btn-sm btn-primary">Entrar</a>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="alert alert-info"><i class="fa-solid fa-circle-exclamation"></i> Nenhuma sala participando.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Coluna lateral: apenas para MASTER -->
    @if(session('user_role') === 'MASTER')
    <div class="flex-grow-0 flex-shrink-1" style="flex-basis: 300px; min-width: 200px;">
        <div class="card shadow border-0 h-100 bg-dark text-white">
            <div class="card-body rounded-3 p-4 d-flex flex-column gap-2">
                <a href="{{ route('salas.index') }}" class="btn btn-outline-success w-100">
                    <i class="fa-solid fa-user-plus me-2"></i> Todas as salas
                </a>
                <a href="{{ route('salas.create') }}" class="btn btn-outline-primary w-100">
                    <i class="fa-solid fa-user-group me-2"></i> Criar Sala
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-danger w-100">
                    <i class="fa-solid fa-trash me-1"></i> Remover Sala
                </a>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

