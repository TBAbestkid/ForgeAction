<!-- Modal: Inserir Código -->
<div class="modal fade" id="modalSalabyCode" tabindex="-1" aria-labelledby="modalCodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 d-flex align-items-center text-white" id="modalCodeLabel">
                    <i class="fa-solid fa-key text-white me-2"></i> Entrar na sala com código
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3 text-white">Digite o código da sala que você recebeu:</p>
                <input type="text" id="inputCodigoSala" class="form-control form-control-lg text-center text-white" placeholder="Ex: ABC123" maxlength="10">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEntrarSalaCodigo">
                    <i class="fa-solid fa-door-open me-1"></i> Entrar
                </button>
            </div>
        </div>
    </div>
</div>
