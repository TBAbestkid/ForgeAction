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
            const response = await fetch(`/api/codigo/${codigo}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error();
            const data = await response.json();

            if (data?.status === 'success' && data?.data?.id) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalSalabyCode'));
                modal?.hide();

                // 🔹 Redireciona o usuário para o fluxo controlado por Laravel
                window.location.href = `/salas/entrar/codigo?codigo=${codigo}`;
            } else {
                showToast('Código inválido ou sala não encontrada.', 'danger');
            }
        } catch (e) {
            showToast('Código inválido ou sala não encontrada.', 'danger');
        }
    });

    // ======== COPIAR CÓDIGO DA SALA ========
    const btnCopyCode = document.getElementById('btnCopyCode');

    btnCopyCode?.addEventListener('click', async () => {
        const code = btnCopyCode.dataset.code;

        if (!code) {
            showToast('Nenhum código disponível para copiar.', 'danger');
            return;
        }

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(code);
            } else {
                // 🔸 fallback para browsers sem suporte ou sem HTTPS
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
