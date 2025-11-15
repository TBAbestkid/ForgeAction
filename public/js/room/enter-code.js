// ======== ENTRAR NA SALA PELO CÓDIGO ========
document.addEventListener('DOMContentLoaded', () => {
    const btnEntrarSalaCodigo = document.getElementById('btnEntrarSalaCodigo');
    const inputCodigoSala = document.getElementById('inputCodigoSala');

    btnEntrarSalaCodigo?.addEventListener('click', async () => {
        const codigo = inputCodigoSala.value.trim();
        if (!codigo) {
            showToast('Por favor, insira um código.', 'danger');
            return;
        }

        try {
            const response = await fetch(`/api/codigo/${codigo}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (response.status === 404) {
                showToast('Código inválido ou sala não encontrada.', 'danger');
                return;
            }

            if (!response.ok) {
                showToast('Erro inesperado ao verificar código.', 'danger');
                return;
            }

            const sala = await response.json();

            // Sala válida → redireciona
            window.location.href = `/salas/entrar/codigo?codigo=${codigo}`;

        } catch (e) {
            showToast('Erro ao validar código da sala.', 'danger');
        }
    });

    // ======== COPIAR CÓDIGO DA SALA ========
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-copy');
        if (!btn) return;

        const code = btn.dataset.code;
        if (!code) {
            showToast('Nenhum código disponível para copiar.', 'danger');
            return;
        }

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(code);
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = code;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }

            showToast('Código copiado para a área de transferência!', 'success');
        } catch (err) {
            console.error(err);
            showToast('Falha ao copiar o código.', 'danger');
        }
    });
});
