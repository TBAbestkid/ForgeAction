@extends('partials/app')
@section('title', 'ForgeAction - Dashboard')
@section('content')

<div class="container mt-4">
    <h1 class="font-medieval text-center mb-4">Seus Personagens</h1>

    <div class="d-flex flex-column gap-3">
        @forelse($personagens as $p)
            @php
                $info = $p['infoPersonagem'];
                $atributos = $p['atributos'];
                $bonus = $p['bonus'];
                $status = $p['status'];
                $ataque = $p['ataque'];
                $danoBase = $p['danoBase'];
            @endphp

            <div class="card p-3 bg-dark text-white personagem-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2>{{ $info['nome'] }}</h2>
                        <p>Classe: {{ $info['classe'] }} | Raça: {{ $info['raca'] }} | Idade: {{ $info['idade'] }} | Genero: {{ $info['genero'] }}</p>
                    </div>
                    <div class="btn-group-vertical">
                        <button class="btn btn-primary btn-sm mb-1">Selecionar</button>
                        <button class="btn btn-warning btn-sm mb-1">Editar</button>
                        <!-- Botão de excluir -->
                        <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $p['id'] }}">Excluir</button>
                    </div>
                </div>

                <hr class="bg-white">

                <!-- Botão Ler Mais -->
                <button class="btn btn-outline-light btn-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#detalhes-{{ $p['id'] }}" aria-expanded="false" aria-controls="detalhes-{{ $p['id'] }}">
                    Ler mais
                </button>

                <!-- Conteúdo expansível -->
                <div class="collapse" id="detalhes-{{ $p['id'] }}">
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <h5 class="font-medieval">Atributos</h5>
                            <ul class="mb-0">
                                <li>Força: {{ $atributos['forca'] }}</li>
                                <li>Agilidade: {{ $atributos['agilidade'] }}</li>
                                <li>Inteligência: {{ $atributos['inteligencia'] }}</li>
                                <li>Sabedoria: {{ $atributos['sabedoria'] }}</li>
                                <li>Destreza: {{ $atributos['destreza'] }}</li>
                                <li>Vitalidade: {{ $atributos['vitalidade'] }}</li>
                                <li>Percepção: {{ $atributos['percepcao'] }}</li>
                                <li>Carisma: {{ $atributos['carisma'] }}</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h5 class="font-medieval">Bônus & Status</h5>
                            <p>Bônus Vida: {{ $bonus['bonupVida'] }} | Bônus Mana: {{ $bonus['bonupMana'] }}</p>
                            <p>Vida Total: {{ $status['vida'] }} | Mana Total: {{ $status['mana'] }} | Iniciativa: {{ $status['iniciativa'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5 class="font-medieval">Ataque & Dano</h5>
                            <p>Físico Corpo: {{ $ataque['ataqueFisicoCorpo'] }} | Físico Distância: {{ $ataque['ataqueFisicoDistancia'] }} | Mágico: {{ $ataque['ataqueMagico'] }}</p>
                            <p>Dano Físico: {{ $danoBase['fisico'] }} | Dano Mágico: {{ $danoBase['magico'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <p class="text-white">Nenhum personagem encontrado.</p>
        @endforelse
    </div>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este personagem?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

<style>
    .personagem-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .personagem-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,255,255,0.2);
    }
    .btn-group-vertical button {
        min-width: 80px;
    }
</style>
<!-- No head ou antes do fechamento do body -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    let personagemToDelete = null;

    // Quando clicar em "Excluir"
    $('.delete-btn').click(function() {
        personagemToDelete = $(this).data('id');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });

    // Confirma exclusão
    $('#confirmDelete').click(function() {
        if(!personagemToDelete) return;

        $.ajax({
            url: `/personagem/${personagemToDelete}`,
            type: 'DELETE',
            success: function(res) {
                // Remove o card da tela de forma confiável
                $(`.delete-btn[data-id='${personagemToDelete}']`).closest('.personagem-card').remove();
                personagemToDelete = null;

                // Fecha modal
                const modalEl = document.getElementById('deleteModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            },
            error: function(xhr) {
                if(xhr.status === 419){
                    alert('Sessão expirada. Atualize a página e tente novamente.');
                } else {
                    alert('Erro ao deletar personagem: ' + xhr.status);
                }
            }
        });
    });
</script>
@endsection
