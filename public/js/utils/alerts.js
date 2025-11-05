/* =========================================================
🧱 ALERTS GLOBAIS — showAlert, showToast, showConfirm
========================================================= */

function showAlert(message) {
    const modalMessage = document.getElementById('modalMessage');
    modalMessage.textContent = message;

    const modalEl = document.getElementById('modalAlert');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function showToast(message, tipo = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toastMessage');

    toastMessage.textContent = message;
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toastEl.classList.add(`bg-${tipo}`);

    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function showConfirm(message, onConfirm) {
    const messageEl = document.getElementById('modalConfirmMessage');
    messageEl.textContent = message;

    const modalEl = document.getElementById('modalConfirm');
    const modal = new bootstrap.Modal(modalEl);

    const confirmBtn = document.getElementById('btnConfirmAction');
    confirmBtn.replaceWith(confirmBtn.cloneNode(true));
    const newConfirmBtn = document.getElementById('btnConfirmAction');

    newConfirmBtn.addEventListener('click', function () {
        modal.hide();
        onConfirm();
    });

    modal.show();
}
