@extends('partials/app')
@section('title', "{$sala['nome']} - ForgeAction")

@section('content')
<div class="container mt-4">

    {{-- Detalhes da sala --}}
    <div class="card bg-dark text-light mb-3" id="cardSala">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 id="salaNome">{{ $sala['nome'] }}</h3>
                <p><strong>ID:</strong> <span id="salaId">{{ $sala['id'] }}</span></p>
                <p><strong>Descrição:</strong> <span id="salaDescricao">{{ $sala['descricao'] }}</span></p>
                <p><strong>Status:</strong> <span id="salaStatus">{{ $sala['ativo'] ? 'Ativa' : 'Inativa' }}</span></p>
            </div>

            {{-- Botões de ação apenas para dono --}}
            @if($isDono)
            <div class="btn-group-vertical">
                {{-- Editar --}}
                <button class="btn btn-sm btn-outline-warning mb-1" id="btn-edit-sala">
                    <i class="fa-solid fa-pen me-1"></i> Editar
                </button>

                {{-- Convidar --}}
                <button class="btn btn-sm btn-outline-success mb-1" id="btn-invite">
                    <i class="fa-solid fa-user-plus me-1"></i> Convidar
                </button>

                {{-- Deletar --}}
                <button class="btn btn-sm btn-outline-danger" id="btn-delete">
                    <i class="fa-solid fa-trash me-1"></i> Deletar
                </button>
            </div>
            @endif
        </div>
    </div>

    <hr>

    {{-- Lista de membros --}}
    <h4>Membros da sala</h4>
    <ul class="list-group mb-3" id="members-list">
        {{-- @forelse($membros as $m)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $m['nome'] ?? 'Desconhecido' }}
                @if($isDono)
                    <button class="btn btn-sm btn-outline-danger btn-remove-member" data-id="{{ $m['id'] ?? 0 }}">
                        <i class="fa-solid fa-user-minus"></i>
                    </button>
                @endif
            </li>
        @empty
            <li class="list-group-item">Nenhum membro na sala.</li>
        @endforelse --}}
    </ul>
</div>



<!-- Modal de Edição -->
<div class="modal fade" id="editSalaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sala</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditSala">
                    <input type="hidden" name="id" value="{{ $sala['id'] }}">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ $sala['nome'] }}">
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao">{{ $sala['descricao'] }}</textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="ativo" name="ativo" {{ $sala['ativo'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="ativo">Ativa</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Salvar alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal de Convite -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalLabel">
                    <i class="fa-solid fa-user-plus me-2"></i> Convidar usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="userSearch" class="form-label">Pesquisar usuário:</label>
                <input type="text" id="userSearch" class="form-control mb-2" placeholder="Digite email ou login...">

                <select id="selectUser" class="form-select">
                    <option value="">Carregando...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSendInvite">Enviar Convite</button>
            </div>
        </div>
    </div>
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/loading.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    const salaId = {{ $sala['id'] }};

    // Funções de alerta e toast
    function showModalAlert(message) {
        $('#modalMessage').text(message);
        const modal = new bootstrap.Modal(document.getElementById('modalAlert'));
        modal.show();
    }

    function showToast(message) {
        $('#toastMessage').text(message);
        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
        toast.show();
    }

    // ======================
    // Editar Sala
    // ======================
    $('#btn-edit-sala').click(function() {
        const modal = new bootstrap.Modal(document.getElementById('editSalaModal'));
        modal.show();
    });

    // Submeter formulário de edição via AJAX
    $('#formEditSala').submit(function(e) {
        e.preventDefault();

        const data = {
            nome: $('#nome').val(),
            descricao: $('#descricao').val(),
            ativo: $('#ativo').is(':checked'),
            _token: "{{ csrf_token() }}"
        };

        $.ajax({
            url: `/salas/${salaId}`,
            type: 'PUT',
            data: data,
            success: function() {
                // Atualiza os dados no card sem recarregar
                $('#salaNome').text(data.nome);
                $('#salaDescricao').text(data.descricao);
                $('#salaStatus').text(data.ativo ? 'Ativa' : 'Inativa');

                // Fecha o modal
                bootstrap.Modal.getInstance(document.getElementById('editSalaModal')).hide();

                // Mostra toast de sucesso
                showToast('Sala atualizada com sucesso!');
            },
            error: function() {
                showModalAlert('Erro ao atualizar a sala.');
            }
        });
    });

    // ======================
    // Deletar sala
    // ======================
    $('#btn-delete').click(function() {
        if(!confirm("Tem certeza que deseja deletar esta sala?")) return;

        $.ajax({
            url: `/salas/${salaId}`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                showToast('Sala deletada!');
                setTimeout(() => window.location.href = "{{ route('salas.index') }}", 1000);
            },
            error: function() {
                showModalAlert('Erro ao deletar a sala.');
            }
        });
    });

    // ======================
    // Convidar membro via modal
    // ======================
    let usuarios = [];

    $('#btn-invite').click(function() {
        const modalEl = document.getElementById('inviteModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Puxar usuários
        $.ajax({
            url: '/usuarios', // sua rota de listar usuários
            type: 'GET',
            success: function(users) {
                usuarios = users;
                const select = $('#selectUser');
                select.empty();
                select.append('<option value="">Selecione um usuário</option>');

                // Filtrar quem já está na sala
                const membrosIds = $('.btn-remove-member').map((i, el) => $(el).data('id')).get();
                usuarios.forEach(user => {
                    if(!membrosIds.includes(user.id)) {
                        select.append(`<option value="${user.email}">${user.login} (${user.email})</option>`);
                    }
                });
            },
            error: function() {
                showModalAlert('Erro ao carregar usuários.');
            }
        });
    });

    // Filtrar usuários conforme digita
    $('#userSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        const filtered = usuarios.filter(u =>
            u.email.toLowerCase().includes(query) || u.login.toLowerCase().includes(query)
        );

        const select = $('#selectUser');
        select.empty();
        select.append('<option value="">Selecione um usuário</option>');
        filtered.forEach(user => {
            select.append(`<option value="${user.email}">${user.login} (${user.email})</option>`);
        });
    });

    // Enviar convite
    $('#btnSendInvite').click(function() {
        const email = $('#selectUser').val();
        if(!email) {
            showModalAlert('Selecione um usuário para enviar o convite.');
            return;
        }

        $.ajax({
            url: '/enviar-invite', // nova rota
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                salaId: salaId,
                email: email
            },
            success: function(res) {
                showToast(res.message);
                const modalEl = document.getElementById('inviteModal');
                bootstrap.Modal.getInstance(modalEl).hide();
            },
            error: function(xhr) {
                showModalAlert(xhr.responseJSON?.message || 'Erro ao enviar convite.');
            }
        });
    });


    // ======================
    // Remover membro
    // ======================
    $('.btn-remove-member').click(function() {
        const membroId = $(this).data('id');
        if(!confirm("Deseja remover este membro da sala?")) return;

        $.ajax({
            url: `/sala-personagem/sala/${salaId}/personagem/${membroId}`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                showToast('Membro removido!');
                setTimeout(() => location.reload(), 500);
            },
            error: function() {
                showModalAlert('Erro ao remover membro.');
            }
        });
    });
});
</script>

@endsection

