// ======== ENTRAR NA SALA PELO CÓDIGO ========
document.addEventListener('DOMContentLoaded', () => {
    console.log("[DEBUG] DOM totalmente carregado.");

    const btnEntrarSalaCodigo = document.getElementById('btnEntrarSalaCodigo');
    const inputCodigoSala = document.getElementById('inputCodigoSala');

    console.log("[DEBUG] Elemento btnEntrarSalaCodigo:", btnEntrarSalaCodigo);
    console.log("[DEBUG] Elemento inputCodigoSala:", inputCodigoSala);

    btnEntrarSalaCodigo?.addEventListener('click', async () => {
        console.log("[DEBUG] Botão 'Entrar Sala' clicado.");

        const codigo = inputCodigoSala.value.trim();
        console.log("[DEBUG] Código digitado:", codigo);

        if (!codigo) {
            console.warn("[DEBUG] Código vazio!");
            showToast('Por favor, insira um código.', 'danger');
            return;
        }

        try {
            console.log(`[DEBUG] Fazendo request GET /api/codigo/${codigo}`);

            const response = await fetch(`/api/codigo/${codigo}`, {
                headers: { 'Accept': 'application/json' }
            });

            console.log("[DEBUG] response.status =", response.status);
            console.log("[DEBUG] response.ok =", response.ok);

            if (response.status === 404) {
                console.warn("[DEBUG] API retornou 404 → Sala não encontrada.");
                showToast('Código inválido ou sala não encontrada.', 'danger');
                return;
            }

            if (!response.ok) {
                console.error("[DEBUG] response.ok = false → Erro inesperado:", response);
                showToast('Erro inesperado ao verificar código.', 'danger');
                return;
            }

            const sala = await response.json();
            console.log("[DEBUG] JSON recebido da API:", sala);

            console.log("[DEBUG] Redirecionando para rota Laravel...");
            window.location.href = `/salas/entrar/codigo?codigo=${codigo}`;

        } catch (e) {
            console.error("[DEBUG] Erro no try/catch:", e);
            showToast('Erro ao validar código da sala.', 'danger');
        }
    });

    // ======== COPIAR CÓDIGO DA SALA ========
    document.addEventListener('click', async (e) => {

        const btn = e.target.closest('.btn-copy');
        if (!btn) return;

        console.log("[DEBUG] Botão copiar clicado:", btn);

        const code = btn.dataset.code;
        console.log("[DEBUG] Código encontrado no data-code:", code);

        if (!code) {
            console.warn("[DEBUG] Nenhum código para copiar.");
            showToast('Nenhum código disponível para copiar.', 'danger');
            return;
        }

        try {
            console.log("[DEBUG] Tentando copiar código...");

            if (navigator.clipboard && navigator.clipboard.writeText) {
                console.log("[DEBUG] Copiando com navigator.clipboard.writeText()");
                await navigator.clipboard.writeText(code);
            } else {
                console.log("[DEBUG] Copiando via método fallback (input invisível)");
                const tempInput = document.createElement('input');
                tempInput.value = code;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }

            console.log("[DEBUG] Código copiado com sucesso!");
            showToast('Código copiado para a área de transferência!', 'success');

        } catch (err) {
            console.error("[DEBUG] Erro ao copiar código:", err);
            showToast('Falha ao copiar o código.', 'danger');
        }
    });
});
