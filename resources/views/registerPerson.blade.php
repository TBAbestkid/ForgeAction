@extends('partials/app')
@section('title', 'Cadastro de Personagem - ForgeAction')
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
    <div class="card mx-auto p-4" style="max-width: 700px;">
        <h2 class="text-center font-medieval text-white mb-3">Crie seu personagem</h2>

        <form id="form-personagem" method="POST" action="{{ route('personagem.store') }}">
            @csrf

            <!-- Etapa 1: Informações Básicas -->
            <div class="wizard-step">
                <div class="form-floating mb-3">
                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" required>
                    <label for="nome" class="text-light">Nome do Personagem</label>
                </div>
                <div class="form-floating mb-3">
                    <select id="classe" name="classe" class="form-control" required></select>
                    <label for="classe" class="text-light">Classe</label>
                </div>
                <div class="form-floating mb-3">
                    <select id="raca" name="raca" class="form-control" required></select>
                    <label for="raca" class="text-light">Raça</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" name="idade" id="idade" class="form-control" placeholder="Idade" required>
                    <label for="idade" class="text-light">Idade</label>
                </div>
                <div class="form-floating mb-3">
                    <select id="genero" name="genero" class="form-control" required>
                        <option value="" disabled selected>Selecione seu gênero</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Geladeira Electrolux Frost Free">Geladeira Electrolux Frost Free</option>
                        <option value="Boeing AH-64 Apache">Boeing AH-64 Apache</option>
                        <option value="Outro">Outro</option>
                    </select>
                    <label for="genero" class="text-light">Identificação</label>
                </div>

                <div class="d-flex justify-content-end">
                    <button id="btn-next" type="button" class="btn btn-primary" disabled>Próximo</button>
                </div>
            </div>

            <!-- Etapa 2: Atributos -->
            <div class="wizard-step d-none">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-light mb-0">Distribuição de Atributos</h5>
                    <button type="button"
                        class="btn btn-sm btn-outline-info"
                        data-bs-toggle="popover"
                        data-bs-placement="left"
                        data-bs-trigger="focus"
                        title="Como distribuir pontos"
                        data-bs-content="Você tem 23 pontos para distribuir entre 8 atributos: Força, Agilidade, Inteligência, Sabedoria, Destreza, Vitalidade, Percepção e Carisma. Cada atributo deve ter pelo menos 1 ponto e todos os 23 pontos devem ser utilizados.">
                        <i class="fa-solid fa-circle-question"></i>
                    </button>
                </div>

                <div class="row g-3">
                    @foreach(['forca','agilidade','inteligencia','sabedoria','destreza','vitalidade','percepcao','carisma'] as $attr)
                        <div class="col-md-6 form-floating">
                            <input type="number" name="{{ $attr }}" class="form-control" placeholder="{{ ucfirst($attr) }}" required min="1" value="1">
                            <label class="text-light">{{ ucfirst($attr) }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button id="btn-prev" type="button" class="btn btn-secondary">Voltar</button>
                    <button id="btn-submit" type="submit" class="btn btn-primary" disabled>Finalizar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('partials.loading')
@include('partials.alerts')
<script src="{{ asset('js/utils/loading.js') }}"></script>
<script src="{{ asset('js/utils/alerts.js') }}"></script>

<!-- jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(el => new bootstrap.Popover(el));
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const TOTAL_POINTS = 23;
        const ATTRS = [
            'forca','agilidade','inteligencia','sabedoria','destreza','vitalidade','percepcao','carisma'
        ];

        const form = document.getElementById('form-personagem');
        const nextBtn = document.getElementById('btn-next');
        const prevBtn = document.getElementById('btn-prev');
        const submitBtn = document.getElementById('btn-submit');

        const stepInfo = document.getElementById('step-info');
        const stepAttrs = document.getElementById('step-attrs');
        const attrsMsg = document.getElementById('attrs-message');

        const infoFields = ['nome','classe','raca','idade','genero'].map(id => document.getElementById(id));

        // Helpers
        const isPositiveInt = v => Number.isInteger(Number(v)) && Number(v) >= 1;

        const setInvalid = (el,msg) => {
            el.classList.add('is-invalid');
            let feed = el.nextElementSibling;
            if (!feed || !feed.classList.contains('invalid-feedback')) {
                feed = document.createElement('div');
                feed.className = 'invalid-feedback';
                el.parentNode.insertBefore(feed, el.nextSibling);
            }
            feed.textContent = msg;
        };

        const clearInvalid = el => {
            el.classList.remove('is-invalid');
            const feed = el.nextElementSibling;
            if(feed?.classList.contains('invalid-feedback')) feed.remove();
        };

        // Valida campos básicos
        function validateInfo() {
            let valid = true;
            infoFields.forEach(f => {
                clearInvalid(f);
                const val = f.value.trim();
                if (!val) {
                    setInvalid(f,'Campo obrigatório');
                    valid = false; return;
                }
                if ((f.id==='classe'||f.id==='raca') && f.selectedIndex===0) {
                    setInvalid(f,'Selecione uma opção');
                    valid = false;
                }
                if (f.id === 'nome' && val.length>50) {
                    setInvalid(f,'Nome muito longo');
                    valid = false;
                }
                if (f.id === 'idade') {
                    const n = parseInt(val,10);
                    if(!isPositiveInt(n) || n < 1 || n > 999999) {
                        setInvalid(f, 'Idade inválida');
                        valid=false;
                    }
                }
            });
            return valid;
        }

        // Valida atributos
        function validateAttrs() {
            let sum = 0, valid = true;

            ATTRS.forEach(a => {
                const el = form.querySelector(`[name="${a}"]`);
                clearInvalid(el);
                if(!el || !isPositiveInt(el.value)) {
                    setInvalid(el,'Atributo inválido');
                    valid=false; return;
                }
                sum += parseInt(el.value,10);
            });

            if(sum < TOTAL_POINTS){
                attrsMsg.textContent=`Distribua mais ${TOTAL_POINTS-sum} pontos`;
                valid=false;
            } else if(sum > TOTAL_POINTS){
                attrsMsg.textContent=`Ultrapassou total de ${TOTAL_POINTS}`;
                valid=false;
            } else {
                attrsMsg.textContent='';
            }

            submitBtn.disabled = !valid;
            return valid;
        }

        // Toggle steps
        function goToAttrs() {
            if(validateInfo()){
                stepInfo.classList.add('d-none');
                stepAttrs.classList.remove('d-none');
                validateAttrs();
            }
        }
        function goToInfo() {
            stepAttrs.classList.add('d-none');
            stepInfo.classList.remove('d-none');
            validateInfo();
            nextBtn.disabled=!validateInfo();
        }

        nextBtn.addEventListener('click', goToAttrs);
        prevBtn.addEventListener('click', goToInfo);

        // Input listeners
        infoFields.forEach(f => {
            const ev=f.tagName==='SELECT'?'change':'input';
            f.addEventListener(ev,()=>{ nextBtn.disabled=!validateInfo(); });
        });

        ATTRS.forEach(a=>{
            const el = form.querySelector(`[name="${a}"]`);
            if(!el) return;
            el.addEventListener('input', validateAttrs);
        });

        // Submit
        form.addEventListener('submit', e => {
            if(!validateInfo() || !validateAttrs()){
                e.preventDefault(); return;
            }
            if(typeof showLoading==='function') {
                showLoading(5000);
            }
        });
    });
</script>

@endsection
