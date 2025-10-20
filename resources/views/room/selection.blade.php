@extends('partials/app')

@section('title', "Seleção de Personagem - {$sala['nome']}")

@section('content')
<div class="container mt-5">

    <!-- Header da Sala -->
    <div class="text-center mb-4">
        <img src="{{ asset('assets/images/forgeicon.png') }}" alt="Logo" style="width:80px;">
        <h1 style="font-family:'MedievalSharp', serif; color:#d4af37;">{{ $sala['nome'] }}</h1>
        <p style="font-size:1.1rem;">Selecione o personagem que você deseja usar nesta aventura</p>
    </div>

    <!-- Pesquisa de Personagem -->
    <div class="mb-4 text-center">
        <input type="text" id="searchCharacter" class="form-control w-50 mx-auto" placeholder="Buscar personagem por nome, raça ou classe">
    </div>

    <!-- Lista de Personagens -->
    <div class="row g-4" id="characterList">
        @forelse($personagens as $personagem)
        <div class="col-md-4 personagem-item"
             data-nome="{{ strtolower($personagem['nome']) }}"
             data-raca="{{ strtolower($personagem['raca'] ?? '') }}"
             data-classe="{{ strtolower($personagem['classe'] ?? '') }}">
            <div class="card bg-dark text-light border-2 border-purple shadow-sm h-100">
                <img src="{{ $personagem['avatar'] ?? asset('assets/images/default-character.png') }}" class="card-img-top" alt="{{ $personagem['nome'] }}">
                <div class="card-body text-center">
                    <h5 class="card-title" style="font-family:'MedievalSharp', serif; color:#d4af37;">{{ $personagem['nome'] }}</h5>
                    <p class="card-text">
                        <strong>Raça:</strong> {{ $personagem['raca'] ?? 'Desconhecida' }}<br>
                        <strong>Classe:</strong> {{ $personagem['classe'] ?? 'Desconhecida' }}<br>
                        <strong>Nível:</strong> {{ $personagem['nivel'] ?? 1 }}
                    </p>
                    <button class="btn btn-join select-btn mt-2"
                        data-character='@json($personagem)'>
                        Selecionar
                    </button>
                </div>
            </div>
        </div>
        @empty
        <p class="text-center w-100">Você ainda não possui personagens disponíveis.</p>
        @endforelse
    </div>
</div>
@endsection

@section('styles')
<style>
    body { background-color: #1a1a1a; color: #f8f9fa; }
    .card { border-radius: 12px; border-color: #6f42c1; }
    .btn-join { background-color: #6f42c1; color: #fff; font-weight: bold; border-radius: 10px; padding: 10px 20px; }
    .btn-join:hover { background-color: #5a379f; text-decoration: none; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pesquisa de personagens
    const searchInput = document.getElementById('searchCharacter');
    const listItems = document.querySelectorAll('.personagem-item');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        listItems.forEach(item => {
            const nome = item.dataset.nome;
            const raca = item.dataset.raca;
            const classe = item.dataset.classe;
            item.style.display = (nome.includes(query) || raca.includes(query) || classe.includes(query)) ? 'flex' : 'none';
        });
    });

    // Selecionar personagem via AJAX
    const selectButtons = document.querySelectorAll('.select-btn');
    selectButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const character = JSON.parse(this.dataset.character);

            fetch("{{ route('room.join') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    salaId: {{ $sala['id'] }},
                    personagemId: character.id
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    location.href = "{{ route('room.view', ['id' => $sala['id']]) }}";
                } else {
                    alert(data.message || 'Erro ao selecionar personagem.');
                }
            });
        });
    });
});
</script>
@endsection
