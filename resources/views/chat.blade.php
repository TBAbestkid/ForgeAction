@extends('partials/app')
@section('title', "TESTE PAGE")

@section('content')
<div class="container">
    <h1>Chat Page</h1>

    {{-- Modal de login --}}
    <div class="modal fade" id="modalLoginChat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Entrar no Chat</h5></div>
                <div class="modal-body">
                    <input type="text" id="chatName" class="form-control mb-2" placeholder="Seu nome">
                    <input type="text" id="chatChannel" class="form-control" placeholder="Canal">
                </div>
                <div class="modal-footer">
                    <button id="btnJoinChat" class="btn btn-primary">Entrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Chat --}}
    <div class="chat-container border p-3 mt-3">
        <div id="messages" style="height:200px; overflow-y:auto; border:1px solid #ccc; padding:5px;"></div>
        <div class="d-flex gap-2 mt-2">
            <input type="text" id="chatInput" class="form-control" placeholder="Digite sua mensagem">
            <button id="chatSend" class="btn btn-primary">Enviar</button>
        </div>
    </div>
</div>
<!-- Stomp -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalLogin = new bootstrap.Modal(document.getElementById('modalLoginChat'));
    modalLogin.show();

    const messages = document.getElementById('messages');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');

    let userName = null;
    let channel = null;
    let stompClient = null;

    document.getElementById('btnJoinChat').addEventListener('click', () => {
        userName = document.getElementById('chatName').value.trim() || '{{ session("user_login") ?? "Desconhecido" }}';
        channel = document.getElementById('chatChannel').value.trim() || 'geral';
        modalLogin.hide();

        addMessage(`🟢 Você entrou no canal "${channel}" como "${userName}"`);

        // Conectar com STOMP
        const socket = new SockJS('https://narrow-christan-rokaideveloper-806169ef.koyeb.app/ws');
        stompClient = Stomp.over(socket);

        stompClient.connect({}, () => {
            // Inscrever no tópico do canal
            stompClient.subscribe('/topic/' + channel, (message) => {
                const data = JSON.parse(message.body);
                addMessage(data.conteudo, data.autor);
            });
        });
    });

    function addMessage(text, sender = 'Sistema') {
        const div = document.createElement('div');
        div.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    chatSend.addEventListener('click', () => {
        const msg = chatInput.value.trim();
        if (!msg || !stompClient) return;

        // Enviar via STOMP
        const payload = {
            conteudo: msg,
            autor: userName
        };

        stompClient.send('/app/enviar/' + channel, {}, JSON.stringify(payload));
        chatInput.value = '';
        chatInput.focus();
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') chatSend.click();
    });
});
</script>
@endsection
