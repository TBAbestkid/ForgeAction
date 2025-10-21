@extends('partials.app')

@section('title', 'Perfil de ' . $user['login'] . ' - ForgeAction')

@section('content')
<div class="container my-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 800px;">
        <div class="row g-4 align-items-center">
            {{-- Coluna esquerda: ícone + login --}}
            <div class="col-md-4 text-center border-end">
                <i class="fa-solid fa-user-circle text-light" style="font-size: 8rem;"></i>
                <h3 class="mt-3 mb-0 text-light">{{ $user['login'] }}</h3>
                <p class="text-light mb-0">{{ ucfirst(strtolower($user['role'])) }}</p>

                {{-- Toggle para mudar o papel --}}
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="toggleRole"
                        {{ $user['role'] === 'MASTER' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="toggleRole">
                        {{ $user['role'] === 'MASTER' ? 'Mestre Ativo' : 'Player Padrão' }}
                    </label>
                </div>
            </div>

            {{-- Coluna direita: informações editáveis --}}
            <div class="col-md-8">
                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-light">Email</label>
                    <div class="input-group">
                        <input type="email" id="email" class="form-control" value="{{ $user['email'] ?? '' }}">
                        <button class="btn btn-primary" id="btnUpdateEmail">
                            <i class="fa-solid fa-envelope"></i> Atualizar
                        </button>
                    </div>
                </div>

                {{-- Nova senha --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-light">Nova senha</label>
                    <input type="password" id="senha" class="form-control" placeholder="Digite a nova senha">
                </div>

                {{-- Confirmar senha --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-light">Confirmar senha</label>
                    <div class="input-group">
                        <input type="password" id="senhaConfirm" class="form-control" placeholder="Confirme a nova senha">
                        <button class="btn btn-primary" id="btnUpdatePassword">
                            <i class="fa-solid fa-key"></i> Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials/loading')
@include('partials/alerts')
<script src="{{ asset('js/loading.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const btnUpdateEmail = document.getElementById('btnUpdateEmail');

        const senhaInput = document.getElementById('senha');
        const senhaConfirmInput = document.getElementById('senhaConfirm');
        const btnUpdatePassword = document.getElementById('btnUpdatePassword');

        const toggleRole = document.getElementById('toggleRole');
        const roleLabel = toggleRole.nextElementSibling;

        // --- Função para mostrar Toast existente ---
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('liveToast');
            const toastMessage = document.getElementById('toastMessage');

            toastMessage.textContent = message;

            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning');
            if(type === 'success') toastEl.classList.add('bg-success');
            else if(type === 'danger') toastEl.classList.add('bg-danger');
            else if(type === 'warning') toastEl.classList.add('bg-warning');

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // --- Função para mostrar Modal existente ---
        function showModal(message) {
            const modalEl = document.getElementById('modalAlert');
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // --- Atualizar Email --- Funciona!
        btnUpdateEmail.addEventListener('click', function(e) {
            e.preventDefault();
            fetch("{{ route('profile.updateEmail') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: emailInput.value,
                    role: toggleRole.checked ? 'MASTER' : 'PLAYER'
                })
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') showToast(res.message, 'success');
                else showModal(res.message || 'Erro ao atualizar email');
            })
            .catch(err => {
                console.error(err);
                showModal('Erro na requisição');
            });
        });

         // --- Atualizar Senha ---
        btnUpdatePassword.addEventListener('click', function(e) {
            e.preventDefault();
            const senha = senhaInput.value.trim();
            const senhaConfirm = senhaConfirmInput.value.trim();

            if(!senha || !senhaConfirm) return showModal('Preencha os dois campos de senha');
            if(senha !== senhaConfirm) return showModal('As senhas não coincidem');

            fetch("{{ route('profile.updatePassword') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ senha })
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    showToast(res.message, 'success');
                    senhaInput.value = '';
                    senhaConfirmInput.value = '';
                } else showModal(res.message || 'Erro ao atualizar senha');
            })
            .catch(err => {
                console.error(err);
                showModal('Erro na requisição');
            });
        });

        // --- Alternar Role ---
        toggleRole.addEventListener('change', function() {
            const newRole = toggleRole.checked ? 'MASTER' : 'PLAYER';

            fetch("{{ route('profile.updateRole') }}", {
                method: 'PUT', // use PUT direto
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ role: newRole })
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    roleLabel.textContent = newRole === 'MASTER' ? 'Mestre Ativo' : 'Player Padrão';
                    roleLabel.classList.add('text-light');
                    showToast(`Role alterado para ${newRole}`, 'success');
                } else {
                    showModal(res.message || 'Erro ao atualizar role');
                }
            })
            .catch(err => {
                console.error('Erro no fetch:', err);
                showModal('Erro na requisição');
            });
        });
    });
</script>
@endsection
