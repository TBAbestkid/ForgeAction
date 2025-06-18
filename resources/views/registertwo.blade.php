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
            <form action="{{ route('registertwo.post') }}" id="myForm" method="post">
                @csrf
                <div class="" id="personagem">
                    <div class="form-floating mb-3">
                        <input type="text" id="nome" class="form-control" placeholder="Nome do Personagem" required>
                        <label for="nome">Nome do Personagem</label>
                    </div>

                    {{-- Utilizar o select2 --}}
                    <div class="form-floating mb-3">
                        <select id="classe" class="form-control">
                            <option value="" selected disabled>Selecione uma Classe</option>
                        </select>
                        <label for="classe">Classe</label>
                    </div>

                    {{-- Utilizar o select2 --}}
                    <div class="form-floating mb-3">
                        <select id="raca" class="form-control">
                            <option value="" selected disabled>Selecione uma Raça</option>
                        </select>
                        <label for="raca">Raça</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" id="idade" class="form-control" placeholder="Idade">
                        <label for="idade">Idade</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select id="sexualidade" class="form-control">
                            <option value="" selected disabled>Selecione</option>
                            <option value="homem">Homem</option>
                            <option value="mulher">Mulher</option>
                            <option value="indefinido">Indefinido</option>
                        </select>
                        <label for="sexualidade">Identificação</label>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary" onclick="prevTab(0)">Voltar</button>
                        <button class="btn btn-primary" onclick="nextTab(2)" onclick="submitDadosPersonagem()">Próximo</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
