@extends('partials/app')
@section('title', 'Criar Sala - ForgeAction')

@section('content')
<div class="container mt-5">
    <div class="card mx-auto shadow border-0 rounded-3 p-4" style="max-width: 600px; background-color: #1c1c1c;">
        <h2 class="text-center font-medieval text-white mb-4">Criar Sala</h2>

        <form id="createSalaForm">
            @csrf

            <div class="form-floating mb-3">
                <input type="text" name="nome" class="form-control" placeholder="Nome da Sala" required>
                <label>Nome da Sala</label>
            </div>

            <div class="form-floating mb-3">
                <textarea name="descricao" class="form-control" placeholder="Descrição da Sala" style="height:100px;"></textarea>
                <label>Descrição</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="senha" class="form-control" placeholder="Senha (opcional)">
                <label>Senha (opcional)</label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" checked>
                <label class="form-check-label text-white" for="ativo">Sala Ativa</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-plus me-1"></i> Criar Sala
            </button>
        </form>

        <div id="createSalaAlert" class="mt-3"></div>
    </div>
</div>

@include('partials.alerts')
@include('partials.loading')

<script src="{{ asset('js/loading.js') }}"></script>
@endsection
@push('scripts')
<!-- jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
async function submitSala(e) {
    e.preventDefault();

    // Pega dados do formulário
    const form = document.getElementById('createSalaForm');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // Ajusta o checkbox (porque "on" não é legal de mandar)
    payload.ativo = formData.get("ativo") ? true : false;

    // Limpa mensagens antigas
    document.getElementById('createSalaAlert').innerHTML = '';

    // Mostra overlay/loading
    showLoading(3000);

    try {
        // Faz request para criar sala
        const res = await $.ajax({
            url: "{{ route('salas.store') }}",
            method: "POST",
            data: payload
        });

        // Esconde loading
        hideLoading();

        // Mostra mensagem de sucesso
        document.getElementById('createSalaAlert').innerHTML = `
            <div class="alert alert-success">Sala criada com sucesso!</div>
        `;

        // Redireciona após um tempo
        setTimeout(() => {
            goToPage("{{ route('salas.index') }}", 2000);
        }, 1500);

    } catch (err) {
        console.error(err);

        // Esconde loading
        hideLoading();

        // Mensagem padrão
        let msg = 'Erro ao criar sala.';

        // Caso a API retorne mensagem mais específica
        if (err.responseJSON && err.responseJSON.message) {
            msg = err.responseJSON.message;
        }

        // Mostra alerta de erro
        document.getElementById('createSalaAlert').innerHTML = `
            <div class="alert alert-danger">${msg}</div>
        `;
    }
}

// Bind no formulário
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('createSalaForm').addEventListener('submit', submitSala);
});
</script>
@endpush
