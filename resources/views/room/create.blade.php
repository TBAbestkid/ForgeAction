@extends('partials/app')
@section('title', 'Criar Sala - ForgeAction')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container mt-5">
    <div class="text-light bg-dark mx-auto border-0 rounded-3 p-4" style="max-width: 600px;">
        <img class="mb-4 mx-auto d-block" src="{{ asset('assets/images/forgeicon.png') }}" alt="" width="72" height="57">
        <h2 class="text-center font-medieval text-white mb-4">Criar Sala</h2>

        <form id="createSalaForm" action="{{ route('api.salas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-floating mb-3">
                <input type="text" name="nome" class="form-control" placeholder="Nome da Sala" required>
                <label class="text-ligh"><i class="fa-solid fa-door-open"></i> Nome da Sala</label>
            </div>

            <div class="form-floating mb-3">
                <textarea name="descricao" class="form-control" placeholder="Descrição da Sala" style="height:100px;"></textarea>
                <label class="text-ligh"><i class="fa-solid fa-align-left mt-2"></i> Descrição</label>
            </div>

            <div class="form-floating mb-3">
                <label for="backgroundInput" class="form-label text-light mb-2 text-center">
                    <i class="fa-solid fa-image me-1"></i> Background da sala
                </label>
                <div id="dropzone" class="bg-secondary p-4 rounded dropzone text-center">
                    <p class="mb-1">Arraste uma imagem aqui</p>
                    <small>ou clique para selecionar</small>

                    <input type="file" name="background" id="backgroundInput" accept="image/*" hidden>
                </div>
            </div>

            <button type="button" id="btnCreateSala" class="btn btn-primary w-100">
                <i class="fa-solid fa-plus me-1"></i> Criar Sala
            </button>
        </form>

        <div id="createSalaAlert" class="mt-3"></div>
    </div>
</div>

@include('partials.alerts')
@include('partials.loading')

<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>
<!-- jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('backgroundInput');

    // clicar abre o seletor
    dropzone.addEventListener('click', () => input.click());

    // highlight ao arrastar
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    // remover highlight
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    // drop do arquivo
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');

        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            previewImage(file);
        }
    });

    // quando selecionar manualmente
    input.addEventListener('change', () => {
        previewImage(input.files[0]);
    });

    function previewImage(file){
        if(!file) return;

        const reader = new FileReader();

        reader.onload = (e) => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '100%';
            img.style.borderRadius = '8px';

            dropzone.appendChild(img);
        };

        reader.readAsDataURL(file);
    }

    const form = document.getElementById('createSalaForm');

    document.getElementById('btnCreateSala').addEventListener('click', function () {

        const nome = form.querySelector('[name="nome"]').value.trim();

        // ❌ validação
        if (!nome) {
            showAlert('O nome da sala é obrigatório.');
            return;
        }

        // (opcional) valida imagem
        const file = input.files[0];
        if (file && file.size > 2 * 1024 * 1024) {
            showAlert('Imagem muito grande (máx: 2MB)');
            return;
        }

        console.log('🚀 Enviando formulário...');
        showLoading();

        // ✅ envia o form REAL
        form.submit();
    });
</script>
@endsection
