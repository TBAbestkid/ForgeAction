// Função padrão para requisições
export function apiRequest(url, method = 'GET', data = null) {
    return $.ajax({
        url: url,
        method: method,
        data: data ? JSON.stringify(data) : null,
        contentType: "application/json",
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

// Exibir modal de alerta já do Bootstrap
export function showModal(message, title = "Alerta!") {
    $("#modalAlertLabel").html(`<i class="bi bi-info-circle-fill text-primary me-2"></i> ${title}`);
    $("#modalMessage").text(message);

    let modal = new bootstrap.Modal(document.getElementById('modalAlert'));
    modal.show();
}

// Exibir toast Bootstrap
export function showToast(message, type = 'success') {
    let bgClass = {
        success: "bg-success",
        error: "bg-danger",
        info: "bg-info",
        warning: "bg-warning"
    }[type] || "bg-secondary";

    let toastEl = document.getElementById('liveToast');
    let toastBody = document.getElementById('toastMessage');

    toastEl.className = `toast align-items-center text-white border-0 ${bgClass}`;
    toastBody.textContent = message;

    let toast = new bootstrap.Toast(toastEl);
    toast.show();
}
