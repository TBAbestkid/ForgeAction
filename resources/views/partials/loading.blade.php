<!-- Elemento de loading -->
<div id="loading-overlay" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999; ">
    
    <div style="display: flex; align-items: center; gap: 20px;">
        <img src="{{ asset('assets/images/forjando.gif') }}" alt="Carregando..." style="width: 200px; height: auto;">
        <h1 class="font-medieval text-white" style="font-size: 3rem; margin: 0;">Forjando...</h1>
    </div>
</div>
