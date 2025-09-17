@extends('partials/app')
@section('title', 'Salas - ForgeAction')

@section('content')
<div class="container mt-4">
    <h1 class="font-medieval text-start mb-4">Salas</h1>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow border-0 flex-fill">
                <div class="card-body text-white rounded-3 p-4" id="salas-container">
                    <p>Carregando salas...</p>
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

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    const container = $("#salas-container");

    $.ajax({
        url: "/salas/usuario/{{ session('user_id') }}",
        method: "GET",
        success: function (response) {
            container.empty();

            if (response.length > 0) {
                const list = $("<ul class='list-group'></ul>");
                response.forEach(sala => {
                    list.append(
                        `<li class="list-group-item d-flex justify-content-between align-items-center">
                            ${sala.nome}
                            <span class="badge bg-primary rounded-pill">ID: ${sala.id}</span>
                        </li>`
                    );
                });
                container.append(list);
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
@endpush
