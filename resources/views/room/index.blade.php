@extends('partials/app')
@section('title', 'Salas - ForgeAction')

@section('content')
<div class="container mt-4">
    <h1 class="font-medieval text-start mb-4">Salas</h1>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow border-0 flex-fill">
                <div class="card-body text-white rounded-3 p-4" id="salas-container">
                    <li class="list-group-item bg-dark text-white text-center" id="loadingRoom">
                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando salas...
                    </li>
                </div>
            </div>
        </div>

        @if(session('user_role') === 'MASTER')
        <div class="col-md-4">
            <div class="card shadow border-0 flex-fill">
                <div class="card-body text-white rounded-3 p-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('salas.create') }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-user-group"></i> Criar Sala
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        const container = $("#salas-container");

        $.ajax({
            url: "/salas/usuario/{{ session('user_id') }}",
            method: "GET",
            success: function (response) {
                container.empty();

                if (response.status === "success" && response.data.length > 0) {
                    response.data.forEach(sala => {
                        const numPlayers = sala.salaPersonagens ? sala.salaPersonagens.length : 0;

                        const salaDiv = $(`
                            <div class="sala-card p-3 mb-3 rounded d-flex justify-content-between align-items-center" style="background-color: #1a1a1a; color: #f5f5f5;">
                                <div>
                                    <strong>${sala.nome}</strong><br>
                                    <small class="text-lights">${sala.descricao}</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">${numPlayers} jogador(es)</span>
                                    <a href="/salas/${sala.id}" class="btn btn-sm btn-primary">Entrar</a>
                                </div>
                            </div>
                        `);

                        container.append(salaDiv);
                    });
                } else {
                    container.append(
                        `<div class="alert alert-info">
                            <i class="fa-solid fa-circle-exclamation"></i> Não há salas!
                        </div>`
                    );
                }
            },
            error: function () {
                container.html("<p class='text-danger'>Erro ao carregar salas.</p>");
            }
        });
    });
</script>
