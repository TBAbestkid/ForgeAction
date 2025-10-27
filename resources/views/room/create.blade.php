@extends('partials/app')
@section('title', 'Criar Sala - ForgeAction')

@section('content')
<div class="container mt-5">
    <div class="text-dark mx-auto border-0 rounded-3 p-4" style="max-width: 600px;">
        <img class="mb-4 mx-auto d-block" src="{{ asset('assets/images/forgeicon.png') }}" alt="" width="72" height="57">
        <h2 class="text-center font-medieval text-white mb-4">Criar Sala</h2>

        <form id="createSalaForm" onsubmit="return false;">
            @csrf

            <div class="form-floating mb-3">
                <input type="text" name="nome" class="form-control" placeholder="Nome da Sala" required>
                <label><i class="fa-solid fa-door-open"></i> Nome da Sala</label>
            </div>

            <div class="form-floating mb-3">
                <textarea name="descricao" class="form-control" placeholder="Descrição da Sala" style="height:100px;"></textarea>
                <label><i class="fa-solid fa-align-left mt-2"></i> Descrição</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="senha" class="form-control" placeholder="Senha (opcional)">
                <label><i class="fa-solid fa-key"></i> Senha (opcional)</label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" checked>
                <label class="form-check-label text-white" for="ativo"> Sala Ativa</label>
            </div>

            <button type="button" id="btnCreateSala" class="btn btn-primary w-100">
                <i class="fa-solid fa-plus me-1"></i> Criar Sala
            </button>
        </form>

        <div id="createSalaAlert" class="mt-3"></div>
    </div>
</div>

@include('partials.alerts')
@include('partials.loading')

<script src="{{ asset('js/loading.js') }}"></script>
<!-- jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.getElementById('btnCreateSala').addEventListener('click', async function () {
        // console.log('🟡 Botão "Criar Sala" clicado.');

        const form = document.getElementById('createSalaForm');
        const formData = new FormData(form);

        // Validação básica
        const nome = formData.get('nome')?.trim();
        if (!nome) {
            showModal('O nome da sala é obrigatório.');
            return;
        }

        // Converte checkbox para boolean
        const ativo = form.querySelector('#ativo')?.checked || false;

        // Monta o payload seguro
        const payload = new FormData();
        payload.append('nome', nome);
        payload.append('descricao', formData.get('descricao') || '');
        payload.append('senha', formData.get('senha') || '');
        payload.append('ativo', ativo ? 'true' : 'false');
        payload.append('usuario_id', '{{ session("user_id") }}');

        // console.log('📦 Dados do formulário preparados:');
        // for (let [key, value] of payload.entries()) {
        //     console.log(`   ${key}:`, value);
        // }

        // Função de modal
        function showModal(message) {
            const modalEl = document.getElementById('modalAlert');
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // Função de toast
        function showToast(message) {
            const toastEl = document.getElementById('liveToast');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = message;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // Exibe loading até resposta
        showLoading();

        try {
            const response = await fetch('{{ route("salas.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: payload
            });

            console.log('📩 Resposta recebida. Status:', response.status);

            let result = {};
            try {
                result = await response.json();
                console.log('📦 Corpo da resposta (JSON):', result);
            } catch {
                console.warn('⚠️ Resposta não era JSON.');
            }

            hideLoading();

            if (result.status === 'success' || response.ok) {
                showToast(result.message || 'Sala criada com sucesso!');

                const salaId = result.data?.id;
                if (salaId) {
                    setTimeout(() => {
                        goToPage(`/salas/${salaId}`, 1500); // redireciona direto para a sala criada
                    }, 1000);
                } else {
                    setTimeout(() => {
                        goToPage('{{ route("salas.index") }}', 1500); // fallback
                    }, 1000);
                }

            } else {
                // Mostra erro detalhado do backend
                showModal(result.message || 'Erro ao criar sala.');
            }

        } catch (error) {
            console.error('💥 Erro inesperado ao criar sala:', error);
            hideLoading();
            showModal('Erro inesperado ao criar sala.');
        }
    });
</script>
@endsection
