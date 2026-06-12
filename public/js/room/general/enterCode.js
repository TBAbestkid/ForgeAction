document.addEventListener('DOMContentLoaded', () => {
    const modalSalaCodigo = document.getElementById('modalSalabyCode');
    const btnEntrarSalaCodigo = modalSalaCodigo?.querySelector('#btnEntrarSalaCodigo');
    const inputCodigoSala = modalSalaCodigo?.querySelector('#inputCodigoSala');

    if (!modalSalaCodigo || !btnEntrarSalaCodigo || !inputCodigoSala) {
        return;
    }

    btnEntrarSalaCodigo.addEventListener('click', async () => {
        const codigo = inputCodigoSala.value.trim();

        if (!codigo) {
            showToast('Por favor, insira um codigo.', 'danger');
            inputCodigoSala.focus();
            return;
        }

        try {
            const codigoSeguro = encodeURIComponent(codigo);
            const response = await fetch(`/api/codigo/${codigoSeguro}`, {
                headers: { Accept: 'application/json' },
            });

            if (response.status === 404) {
                showToast('Codigo invalido ou sala nao encontrada.', 'danger');
                return;
            }

            if (!response.ok) {
                showToast('Erro inesperado ao verificar codigo.', 'danger');
                return;
            }

            window.location.href = `/salas/entrar/codigo?codigo=${codigoSeguro}`;
        } catch (e) {
            showToast('Erro ao validar codigo da sala.', 'danger');
        }
    });

    inputCodigoSala.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            btnEntrarSalaCodigo.click();
        }
    });

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-copy');
        if (!btn) return;

        const code = btn.dataset.code;
        if (!code) {
            showToast('Nenhum codigo disponivel para copiar.', 'danger');
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

            showToast('Codigo copiado para a area de transferencia!', 'success');
        } catch (err) {
            showToast('Falha ao copiar o codigo.', 'danger');
        }
    });
});
