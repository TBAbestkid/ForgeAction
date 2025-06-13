@extends('partials/app')
@section('title', 'ForgeAction - Cadastro2.0')
@section('content')
<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 600px;">
        <h2 class="text-center font-medieval text-white">Crie sua conta</h2>
        <ul class="nav nav-tabs" id="registerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-login" data-bs-toggle="tab" data-bs-target="#login" type="button">Dados Cadastrais</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-personagem" data-bs-toggle="tab" data-bs-target="#personagem" type="button">Dados do Personagem</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-atributos" data-bs-toggle="tab" data-bs-target="#atributos" type="button">Atributos</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Aba 1: Dados Cadastrais -->
            <form action="{{ route('registertwo') }}" id="myForm" method="post">
                @csrf
                <div class="tab-pane fade show active" id="login">
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" placeholder="name@example.com" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Senha" required>
                        <label for="password">Senha</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" id="passwordConfirm" class="form-control" placeholder="Confirme a senha" required>
                        <label for="passwordConfirm">Confirme a senha</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Envie</button>
                </div>
            </form>
            <!-- Aba 2: Dados do Personagem -->
            <div class="tab-pane fade" id="personagem">
                <div class="form-floating mb-3">
                    <input type="text" id="nome" class="form-control" placeholder="Nome do Personagem" required>
                    <label for="nome">Nome do Personagem</label>
                </div>
                <div class="form-floating mb-3">
                    <select id="classe" class="form-control">
                        <option value="" selected disabled>Selecione uma Classe</option>
                    </select>
                    <label for="classe">Classe</label>
                </div>
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

            <!-- Aba 3: Atributos -->
            <div class="tab-pane fade" id="atributos">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="number" id="forca" class="form-control" placeholder="Força">
                            <label for="forca">Força</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="agilidade" class="form-control" placeholder="Agilidade">
                            <label for="agilidade">Agilidade</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="inteligencia" class="form-control" placeholder="Inteligência">
                            <label for="inteligencia">Inteligência</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="destreza" class="form-control" placeholder="Destreza">
                            <label for="destreza">Destreza</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="number" id="vitalidade" class="form-control" placeholder="Vitalidade">
                            <label for="vitalidade">Vitalidade</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="percepcao" class="form-control" placeholder="Percepção">
                            <label for="percepcao">Percepção</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="sabedoria" class="form-control" placeholder="Sabedoria">
                            <label for="sabedoria">Sabedoria</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" id="carisma" class="form-control" placeholder="Carisma">
                            <label for="carisma">Carisma</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-secondary" onclick="prevTab(1)">Voltar</button>
                    <button type="submit" class="btn btn-success" >Finalizar Cadastro</button>
                </div>
                <div class="form-check text-start mb-3 text-white">
                    <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault">
                        <a href="">Termos</a>
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#myForm').on('submit', function(e){
        e.preventDefault();

        var formData = {
            chapLogin: $('input[name="username"]').val(),
            chapSenha: $('input[name="password"]').val()
        };

        $.ajax({
            url: "/proxy/chave_personagem", // Agora chama o Laravel
            method: "POST",
            data: JSON.stringify(formData),
            contentType: "application/json",
            dataType: "json",
            success: function(response) {
                alert(response.message);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                alert('Ocorreu um erro.');
            }
        });
    });
});

</script>

@endsection