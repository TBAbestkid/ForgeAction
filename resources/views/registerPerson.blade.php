@extends('partials/app')
@section('title', 'ForgeAction - Cadastro de Personagem')
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

        <div id="wizard">
            <!-- Etapa 1: Informações Básicas -->
            <div class="wizard-step">
                <form id="form-basico">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" required>
                        <label for="nome">Nome do Personagem</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select id="classe" name="classe" class="form-control" required></select>
                        <label for="classe">Classe</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select id="raca" name="raca" class="form-control" required></select>
                        <label for="raca">Raça</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" name="idade" id="idade" class="form-control" placeholder="Idade" required>
                        <label for="idade">Idade</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select id="identificacao" name="identificacao" class="form-control" required>
                            <option value="" disabled selected>Selecione seu gênero</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Geladeira Electrolux Frost Free">Geladeira Electrolux Frost Free</option>
                            <option value="Boeing AH-64 Apache">Boeing AH-64 Apache</option>
                            <option value="Outro">Outro</option>
                        </select>
                        <label for="identificacao">Identificação</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button id="btn-next" type="button" class="btn btn-primary" disabled>Próximo</button>
                    </div>
                </form>
            </div>

            <!-- Etapa 2: Atributos -->
            <div class="wizard-step d-none">
                <form id="form-atributos">
                    @csrf
                    <div class="row g-3">
                        @foreach(['forca','agilidade','inteligencia','sabedoria','destreza','vitalidade','percepcao','carisma'] as $attr)
                            <div class="col-md-6 form-floating">
                                <input type="number" name="{{ $attr }}" class="form-control" placeholder="{{ ucfirst($attr) }}" required min="1" value="1">
                                <label>{{ ucfirst($attr) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button id="btn-prev" type="button" class="btn btn-secondary">Voltar</button>
                        <button id="btn-submit" type="button" class="btn btn-primary" disabled>Finalizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.loading')
@include('partials.alerts')
<script src="{{ asset('js/loading.js') }}"></script>

<!-- jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        function showModal(message) {
            // Coloca a mensagem no modal
            const modalMessage = document.getElementById("modalMessage");
            if (modalMessage) modalMessage.textContent = message;

            // Inicializa e mostra o modal usando Bootstrap 5
            const modalEl = document.getElementById("modalAlert");
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        /* ---------- CONFIG ---------- */
        const TOTAL_POINTS = 23;
        const ATTRS = ['forca','agilidade','inteligencia','sabedoria','destreza','vitalidade','percepcao','carisma'];
        const userId = {{ session('user_id') ?? 0 }};

        /* ---------- HELPERS ---------- */
        const getCsrf = ()=>document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value || '';

        const isPositiveInt = v => {
            if(v==null) return false;
            const n = Number(v);
            return Number.isInteger(n) && n>=1;
        };

        const setInvalid = (el,msg)=>{
            if(!el) return;
            el.classList.add('is-invalid');
            let feed = el.nextElementSibling;
            if(!feed || !feed.classList.contains('invalid-feedback')){
                feed = document.createElement('div');
                feed.className='invalid-feedback';
                el.parentNode.insertBefore(feed, el.nextSibling);
            }
            feed.textContent=msg;
        };

        const clearInvalid = el=>{
            if(!el) return;
            el.classList.remove('is-invalid');
            const feed = el.nextElementSibling;
            if(feed?.classList.contains('invalid-feedback')) feed.remove();
        };

        /* ---------- API ---------- */
        async function api(path,method='GET',payload=null){
            const headers = { 'Accept':'application/json' };
            if(payload) { headers['Content-Type']='application/json'; headers['X-CSRF-TOKEN']=getCsrf(); }
            const resp = await fetch(path,{ method, credentials:'same-origin', headers, body: payload?JSON.stringify(payload):null });
            let data=null; try{ data=await resp.json(); }catch(e){}
            return { ok: resp.ok, status: resp.status, data, resp };
        }

        /* ---------- LOAD ENUMS ---------- */
        async function loadEnum(id,path,placeholder){
            const select = document.getElementById(id);
            if(!select) return;
            select.innerHTML=`<option disabled selected>${placeholder}</option>`;
            try{
                const r = await api(path);
                r.data?.forEach(e=> select.add(new Option(e.descricao,e.constante)));
            }catch(e){ console.error('Erro ao carregar', id, e); }
        }
        loadEnum('classe','/enums/classes','Selecione uma Classe');
        loadEnum('raca','/enums/racas','Selecione uma Raça');

        /* ---------- INFO VALIDATION ---------- */
        const infoFields = ['nome','classe','raca','idade','genero'].map(id=>document.getElementById(id)).filter(Boolean);
        const nextBtn=document.getElementById('btn-next');
        const submitBtn=document.getElementById('btn-submit');

        function validateFields(fields){
            let allValid=true;
            fields.forEach(f=>{
                clearInvalid(f);
                if(!f.value.trim()){ setInvalid(f,'Campo obrigatório'); allValid=false; }
                else if(f.id==='idade' && !isPositiveInt(f.value)){ setInvalid(f,'Informe inteiro >=1'); allValid=false; }
            });
            return allValid;
        }

        infoFields.forEach(f=>{
            const ev=f.tagName==='SELECT'||f.type==='checkbox'?'change':'input';
            f.addEventListener(ev,()=>{ validateFields(infoFields); toggleNext(); });
        });

        function toggleNext(){ if(nextBtn) nextBtn.disabled = !validateFields(infoFields); }
        toggleNext();

        /* ---------- ATTRIBUTES ---------- */
        function updatePoints(){
            let sum=0, invalid=false;
            ATTRS.forEach(a=>{
                const el=document.querySelector(`input[name="${a}"]`);
                if(!el || !isPositiveInt(el.value)) invalid=true;
                sum += isPositiveInt(el.value)?parseInt(el.value,10):0;
            });
            const remaining = TOTAL_POINTS - sum;
            let msgBox=document.getElementById('attrs-message');
            if(!msgBox){
                const step=document.querySelectorAll('#wizard .wizard-step')[1];
                msgBox=document.createElement('div'); msgBox.id='attrs-message'; msgBox.className='mt-2 text-warning small';
                step?.querySelector('form')?.appendChild(msgBox);
            }
            if(invalid) { msgBox.textContent='Todos atributos devem ser inteiros >=1'; if(submitBtn) submitBtn.disabled=true; return; }
            if(sum>TOTAL_POINTS){ msgBox.textContent='Ultrapassou total de '+TOTAL_POINTS; if(submitBtn) submitBtn.disabled=true; return; }
            if(sum<TOTAL_POINTS){ msgBox.textContent='Distribua mais '+(TOTAL_POINTS-sum)+' pontos'; if(submitBtn) submitBtn.disabled=true; return; }
            msgBox.textContent=''; if(submitBtn) submitBtn.disabled=false;
        }

        ATTRS.forEach(a=>{
            const el=document.querySelector(`input[name="${a}"]`);
            if(!el) return;
            el.setAttribute('min','1'); el.setAttribute('step','1'); if(!el.value) el.value='1';
            el.addEventListener('input', updatePoints);
        });
        updatePoints();

        /* ---------- GATHER DATA ---------- */
        function getInfo(){
            const info={};
            ['nome','classe','raca','idade','genero','infoPersonagem'].forEach(k=>{
                const el=document.getElementById(k) || document.querySelector(`[name="${k}"]`);
                if(!el) return;
                info[k]=el.type==='number'?parseInt(el.value,10):el.value.trim();
            });
            return info;
        }
        function getAttrs(){
            const out={}; ATTRS.forEach(a=>{ const el=document.querySelector(`input[name="${a}"]`); out[a]=el?parseInt(el.value,10):1; });
            return out;
        }

        /* ---------- SUBMIT ---------- */
        async function tryDeletePersonagem(id){ if(!id) return; await api('/personagem/'+id,'DELETE'); }

        async function submitCharacter() {
            if (!userId || userId === 0) {
                showModal("Usuário não logado");
                return;
            }
            if (!validateFields(infoFields)) {
                goToInfo();
                return;
            }

            const info = getInfo(), attrs = getAttrs();
            const payload = {
                usuario: { id: userId },
                nome: info.nome,
                raca: info.raca,
                classe: info.classe,
                idade: info.idade,
                genero: info.genero
            };

            nextBtn.disabled = true;
            submitBtn.disabled = true;

            showLoading(5000); // mostra o loading enquanto cria

            try {
                // Cria personagem
                const resP = await api('/personagem', 'POST', payload);
                if (!resP.ok) throw new Error('Erro ao criar personagem');

                // Pega o último personagem criado do usuário
                const resUser = await api(`/personagem/usuario/${userId}`);
                if (!resUser.ok || !Array.isArray(resUser.data) || !resUser.data.length)
                    throw new Error('Não foi possível recuperar os personagens do usuário');

                const lastChar = resUser.data[resUser.data.length - 1];
                const id = lastChar.id;

                // Cria atributos e info
                await Promise.all([
                    api('/atributos-personagem', 'POST', { personagem: { id }, ...attrs }),
                    api('/info-personagem', 'POST', { personagem: { id }, ...info })
                ]);

                hideLoading(); // esconde loading
                showModal("Personagem criado com sucesso!"); // mostra modal
                setTimeout(() => window.location.href = '/dashboard', 2000);

            } catch (err) {
                console.error(err);
                hideLoading(); // esconde loading mesmo no erro
                showModal(err.message || "Erro desconhecido"); // mostra modal de erro
            } finally {
                nextBtn.disabled = false;
                submitBtn.disabled = false;
            }
        }

        /* ---------- WIZARD ---------- */
        function goToAttributes(){ if(!validateFields(infoFields)) return; document.querySelectorAll('#wizard .wizard-step').forEach((s,i)=>i===0?s.classList.add('d-none'):s.classList.remove('d-none')); document.querySelector(`input[name="${ATTRS[0]}"]`)?.focus(); }
        function goToInfo(){ document.querySelectorAll('#wizard .wizard-step').forEach((s,i)=>i===0?s.classList.remove('d-none'):s.classList.add('d-none')); }

        nextBtn?.addEventListener('click',goToAttributes);
        document.getElementById('btn-prev')?.addEventListener('click',goToInfo);
        submitBtn?.addEventListener('click',submitCharacter);

    });
</script>

@endsection
