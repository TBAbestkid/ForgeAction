@extends('partials/app')
@section('title', 'ForgeAction - Cadastro de Atributos')
@section('content')
<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 600px;">
        <h2 class="text-center font-medieval text-white">Defina os atributos</h2>
        <div class="tab-content mt-3">
            <!-- Aba 3: Atributos -->
            <form action="{{ route('registerthree.post') }}" id="myForm" method="post">
                @csrf
                <div class="" id="atributos">
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
            </form>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- realizar validação com js --}}

@endsection
