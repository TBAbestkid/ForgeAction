@extends('partials/app')
@section('title', 'ForgeAction - Cadastro de Personagem')
@section('content')
{{-- Aqui exibe os alerts --}}
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
    <div class="card mx-auto p-4" style="max-width: 600px;">
        <h2 class="text-center font-medieval text-white">Crie seu personagem</h2>
        <div class="tab-content mt-3">
            <!-- Aba 2: Dados do Personagem -->
            <form action="" id="myForm" method="post">
                @csrf
                <div class="form-floating mb-3">
                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do Personagem" required>
                    <label for="nome">Nome do Personagem</label>
                </div>

                <div class="form-floating mb-3">
                    <select id="classe" name="classe" class="form-control">
                        <option value="" selected disabled>Selecione uma Classe</option>
                    </select>
                    <label for="classe">Classe</label>
                </div>

                <div class="form-floating mb-3">
                    <select id="raca" name="raca" class="form-control">
                        <option value="" selected disabled>Selecione uma Raça</option>
                    </select>
                    <label for="raca">Raça</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="number" id="idade" name="idade" class="form-control" placeholder="Idade">
                    <label for="idade">Idade</label>
                </div>

                <div class="form-floating mb-3">
                    <select id="sexualidade" name="sexualidade" class="form-control">
                        <option value="" selected disabled>Selecione</option>
                        <option value="homem">Homem</option>
                        <option value="mulher">Mulher</option>
                        <option value="indefinido">Indefinido</option>
                    </select>
                    <label for="sexualidade">Identificação</label>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevTab(0)">Voltar</button>
                    <button type="submit" class="btn btn-primary">Próximo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inclua jQuery e Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Carrega raças
$.ajax({
    url: '/enums/personagem',
    method: 'GET',
    success: function(data) {
        // popula raças
        data.racas.forEach(raca => {
            $('#raca').append(new Option(raca.descricao, raca.constante));
        });
        $('#raca').select2({ placeholder: 'Selecione uma Raça' });

        // popula classes
        data.classes.forEach(classe => {
            $('#classe').append(new Option(classe.descricao, classe.constante));
        });
        $('#classe').select2({ placeholder: 'Selecione uma Classe' });
    }
});

</script>

@endsection
