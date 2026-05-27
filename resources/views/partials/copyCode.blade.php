<!-- Modal de Copiar Código da Sala -->
<div class="modal fade" id="modalCopiarCodigo" tabindex="-1" aria-labelledby="modalCopiarCodigoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCopiarCodigoLabel">
                    <i class="fa-solid fa-copy me-2"></i> Código da Sala
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-secondary mb-3">Compartilhe este código com seus amigos:</p>
                <div class="input-group">
                    <input type="text" class="form-control bg-secondary text-white border-0" id="inputCodigoSala" readonly>
                    <button class="btn btn-primary" type="button" id="btnCopiarCodigoSala">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
                <small class="text-success mt-2" id="mensagemCopiaoCodigo" style="display: none;">
                    <i class="fa-solid fa-check"></i> Código copiado!
                </small>
            </div>
        </div>
    </div>
</div>
