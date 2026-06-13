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
            <div id="step-info" class="wizard-step">
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
                        {{-- <option value="Geladeira Electrolux Frost Free">Geladeira Electrolux Frost Free</option>
                        <option value="Boeing AH-64 Apache">Boeing AH-64 Apache</option> --}}
                        <option value="Outro">Outro</option>
                    </select>
                    <label for="genero" class="text-light">Identificação</label>
                </div>

                <div class="d-flex justify-content-end">
                    <button id="btn-next" type="button" class="btn btn-primary" disabled>Próximo</button>
                </div>
            </div>

            <!-- Etapa 2: Atributos -->
            <div id="step-attrs" class="wizard-step d-none">
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

                <div class="alert alert-info">
                    <strong>Pontos utilizados:</strong> <span id="attrs-used">8</span>/23<br>
                    <strong>Pontos para distribuir:</strong> <span id="attrs-remaining" class="text-warning">15</span>
                </div>

                <div class="mb-3">
                    <h6 class="text-info mb-3">
                        <i class="fa-solid fa-dumbbell"></i> Atributos
                    </h6>

                    <div class="row g-3">
                        @php
                            $atributos = [
                                ['key' => 'forca', 'label' => 'Força', 'icon' => 'fa-hand-fist'],
                                ['key' => 'agilidade', 'label' => 'Agilidade', 'icon' => 'fa-bolt'],
                                ['key' => 'inteligencia', 'label' => 'Inteligência', 'icon' => 'fa-brain'],
                                ['key' => 'sabedoria', 'label' => 'Sabedoria', 'icon' => 'fa-book'],
                                ['key' => 'destreza', 'label' => 'Destreza', 'icon' => 'fa-bullseye'],
                                ['key' => 'vitalidade', 'label' => 'Vitalidade', 'icon' => 'fa-shield-heart'],
                                ['key' => 'percepcao', 'label' => 'Percepção', 'icon' => 'fa-eye'],
                                ['key' => 'carisma', 'label' => 'Carisma', 'icon' => 'fa-comments'],
                            ];
                        @endphp

                        @foreach($atributos as $attr)
                            <div class="col-md-6">
                                <div class="mb-1 p-2 bg-dark rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="attr-{{ $attr['key'] }}" class="mb-0 text-light">
                                            <strong>
                                                <i class="fa-solid {{ $attr['icon'] }} me-1"></i>{{ $attr['label'] }}
                                            </strong>
                                        </label>
                                        <span class="badge bg-info" id="badge-{{ $attr['key'] }}">1</span>
                                    </div>

                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" type="button"
                                            data-attr-action="decrease" data-attr="{{ $attr['key'] }}"
                                            aria-label="Diminuir {{ $attr['label'] }}">
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                        <input type="number" name="{{ $attr['key'] }}" id="attr-{{ $attr['key'] }}"
                                            class="form-control text-center" required min="1" value="1" readonly
                                            style="max-width: 70px;">
                                        <button class="btn btn-outline-success" type="button"
                                            data-attr-action="increase" data-attr="{{ $attr['key'] }}"
                                            aria-label="Aumentar {{ $attr['label'] }}">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="attrs-message" class="text-warning fw-bold mt-3"></div>

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
    document.addEventListener('DOMContentLoaded', async () => {
        const classeSelect = document.getElementById('classe');
        const racaSelect = document.getElementById('raca');

        async function carregarSelect(url, select, labelPadrao) {
            try {
                const res = await fetch(url);
                const json = await res.json();

                console.log('🔍 Resposta de', url, json); // <-- veja no console

                // Verifica se há uma propriedade data e se ela é um array
                let data = [];
                if (Array.isArray(json)) {
                    data = json;
                } else if (Array.isArray(json.data)) {
                    data = json.data;
                } else if (json.status === 'success' && typeof json.data === 'object') {
                    // Se data não for array mas for um objeto, tenta extrair valores
                    data = Object.values(json.data);
                } else {
                    console.warn('⚠️ Formato inesperado em', url, json);
                }

                // Limpa e adiciona opção padrão
                select.innerHTML = `<option value="" disabled selected>${labelPadrao}</option>`;

                // Popula opções se houver
                if (Array.isArray(data)) {
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.constante ?? item.id ?? item.nome ?? '';
                        opt.textContent = item.descricao ?? item.nome ?? item.toString();
                        select.appendChild(opt);
                    });
                }

            } catch (err) {
                console.error('❌ Erro ao carregar opções de', url, err);
                select.innerHTML = `<option disabled>Erro ao carregar</option>`;
            }
        }

        await carregarSelect('/enums/classes', classeSelect, 'Selecione a classe');
        await carregarSelect('/enums/racas', racaSelect, 'Selecione a raça');
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
        const attrsUsed = document.getElementById('attrs-used');
        const attrsRemaining = document.getElementById('attrs-remaining');

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

        function getAttrsSum() {
            return ATTRS.reduce((sum, a) => {
                const el = form.querySelector(`[name="${a}"]`);
                return sum + (parseInt(el?.value, 10) || 0);
            }, 0);
        }

        function updateAttrControls(sum) {
            ATTRS.forEach(a => {
                const el = form.querySelector(`[name="${a}"]`);
                const value = parseInt(el?.value, 10) || 0;
                const badge = document.getElementById(`badge-${a}`);
                const decreaseBtn = form.querySelector(`[data-attr-action="decrease"][data-attr="${a}"]`);
                const increaseBtn = form.querySelector(`[data-attr-action="increase"][data-attr="${a}"]`);

                if (badge) badge.textContent = value;
                if (decreaseBtn) decreaseBtn.disabled = value <= 1;
                if (increaseBtn) increaseBtn.disabled = sum >= TOTAL_POINTS;
            });
        }

        function updateAttrsSummary(sum) {
            const remaining = TOTAL_POINTS - sum;

            if (attrsUsed) attrsUsed.textContent = sum;
            if (attrsRemaining) {
                attrsRemaining.textContent = Math.max(0, remaining);
                attrsRemaining.className = remaining === 0 ? 'text-success' : 'text-warning';
            }

            return remaining;
        }

        function adjustAttr(attr, delta) {
            const el = form.querySelector(`[name="${attr}"]`);
            if (!el) return;

            const currentValue = parseInt(el.value, 10) || 1;
            const currentSum = getAttrsSum();

            if (delta > 0 && currentSum >= TOTAL_POINTS) return;
            if (delta < 0 && currentValue <= 1) return;

            el.value = currentValue + delta;
            validateAttrs();
        }

        // Valida atributos em tempo real
        function validateAttrs() {
            let sum = 0, valid = true;

            ATTRS.forEach(a => {
                const el = form.querySelector(`[name="${a}"]`);
                clearInvalid(el);

                if(!el || !isPositiveInt(el.value)){
                    setInvalid(el,'Atributo inválido');
                    valid = false;
                } else {
                    sum += parseInt(el.value,10);
                }
            });

            const remaining = updateAttrsSummary(sum);
            updateAttrControls(sum);

            if(remaining > 0){
                attrsMsg.textContent = `Você ainda tem ${remaining} ponto(s) para distribuir`;
                valid = false;
            } else if(remaining < 0){
                attrsMsg.textContent = `Você ultrapassou o total de ${TOTAL_POINTS} pontos em ${-remaining}`;
                valid = false;
            } else {
                attrsMsg.textContent = `Todos os pontos foram distribuídos!`;
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

        form.querySelectorAll('[data-attr-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const attr = btn.dataset.attr;
                const delta = btn.dataset.attrAction === 'increase' ? 1 : -1;
                adjustAttr(attr, delta);
            });
        });

        // chama uma vez no load para atualizar mensagem
        validateAttrs();

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
