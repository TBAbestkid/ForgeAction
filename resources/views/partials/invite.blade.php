
<!-- Modal de Convite -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalLabel">
                <i class="fa-solid fa-user-plus me-2"></i> Convidar usuários
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <label for="selectUser" class="form-label">Pesquisar e selecionar usuários:</label>

                <select multiple id="selectUser" class="form-select select2-multi w-auto w-75" >
                    <option value="">Selecione um usuário</option>
                </select>

                <div id="selectedUsers" class="d-flex flex-wrap gap-2 mt-2">
                    <!-- Tags de usuários selecionados aparecerão aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSendInvite">Enviar Convite</button>
            </div>
        </div>
    </div>
</div>
