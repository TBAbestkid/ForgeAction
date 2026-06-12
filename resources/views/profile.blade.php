@extends('partials.app')

@section('title', 'Perfil de ' . $user['login'] . ' - ForgeAction')

@section('content')
<div class="container my-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 800px;">
        <div class="text-start">
            <a href="{{ url('/') }}" class="btn btn-outline-light mb-4 px-4 py-2 shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar para Home
            </a>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-md-4 text-center border-end">
                <i class="fa-solid fa-user-circle text-light" style="font-size: 8rem;"></i>
                <h3 class="mt-3 mb-0 text-light">{{ $user['login'] }}</h3>
                <p class="text-light mb-0"><i class="fa-solid fa-id-badge me-1"></i>{{ ucfirst(strtolower($user['role'])) }}</p>

                <button id="btnEditProfile" class="btn btn-outline-light mt-4 w-100">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Editar Perfil
                </button>
            </div>

            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label fw-bold text-light">
                        <i class="fa-solid fa-envelope me-1"></i> Email
                    </label>
                    <div class="input-group">
                        <input type="email" id="email" class="form-control"
                            value="{{ $user['email'] ?? '' }}" disabled>
                        <button class="btn btn-primary" id="btnUpdateEmail" disabled>
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Atualizar
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-light">
                        <i class="fa-solid fa-lock me-1"></i> Senha Atual
                    </label>
                    <input type="password" id="senhaAtual" class="form-control"
                        placeholder="Digite a senha atual" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-light">
                        <i class="fa-solid fa-key me-1"></i> Nova Senha
                    </label>
                    <input type="password" id="senha" class="form-control"
                        placeholder="Digite a nova senha" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-light">
                        <i class="fa-solid fa-key me-1"></i> Confirmar Senha
                    </label>
                    <div class="input-group">
                        <input type="password" id="senhaConfirm" class="form-control"
                            placeholder="Confirme a nova senha" disabled>
                        <button class="btn btn-primary" id="btnUpdatePassword" disabled>
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editBtn = document.getElementById("btnEditProfile");
        const inputs = document.querySelectorAll("#email, #senha, #senhaAtual, #senhaConfirm");
        const buttons = document.querySelectorAll("#btnUpdateEmail, #btnUpdatePassword");

        const emailInput = document.getElementById('email');
        const btnUpdateEmail = document.getElementById('btnUpdateEmail');

        const senhaInput = document.getElementById('senha');
        const senhaAtualInput = document.getElementById('senhaAtual');
        const senhaConfirmInput = document.getElementById('senhaConfirm');
        const btnUpdatePassword = document.getElementById('btnUpdatePassword');

        let editMode = false;

        btnUpdateEmail.addEventListener('click', function(e) {
            e.preventDefault();
            fetch("{{ route('profile.updateEmail') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: emailInput.value })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') showToast(res.message, 'success');
                else showAlert(res.message || 'Erro ao atualizar email');
            })
            .catch(() => showAlert('Erro na requisicao'));
        });

        btnUpdatePassword.addEventListener('click', function(e) {
            e.preventDefault();
            const senha = senhaInput.value.trim();
            const senhaAtual = senhaAtualInput.value.trim();
            const senhaConfirm = senhaConfirmInput.value.trim();

            if (!senha || !senhaConfirm || !senhaAtual) return showAlert('Preencha os tres campos de senha');
            if (senha !== senhaConfirm) return showAlert('As senhas nao coincidem');

            fetch("{{ route('profile.updatePassword') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ senhaAtual, senha })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    senhaInput.value = '';
                    senhaAtualInput.value = '';
                    senhaConfirmInput.value = '';
                } else {
                    showAlert(res.message || 'Erro ao atualizar senha');
                }
            })
            .catch(() => showAlert('Erro na requisicao'));
        });

        editBtn.addEventListener("click", () => {
            editMode = !editMode;

            inputs.forEach(input => input.disabled = !editMode);
            buttons.forEach(btn => btn.disabled = !editMode);

            if (editMode) {
                editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Salvar Alteracoes';
                editBtn.classList.remove("btn-outline-light");
                editBtn.classList.add("btn-success");
            } else {
                editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Perfil';
                editBtn.classList.remove("btn-success");
                editBtn.classList.add("btn-outline-light");
            }
        });
    });
</script>
@endsection
