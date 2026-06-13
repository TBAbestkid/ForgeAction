/**
 * =========================
 * 📌 SISTEMA DE PIN (FIXAR FICHA)
 * =========================
 * Permite fixar a ficha aberta para visualizar enquanto joga
 */
class PinManager {
    constructor(offcanvasId, pinButtonId) {
        this.offcanvasId = offcanvasId;
        this.offcanvas = document.getElementById(offcanvasId);
        this.pinBtn = document.getElementById(pinButtonId);
        this.isFixed = false;
        this.bsOffcanvas = null;
        this.allowHideOnce = false;

        if (!this.offcanvas || !this.pinBtn) {
            console.warn(`PinManager: Elementos não encontrados (${offcanvasId}, ${pinButtonId})`);
            return;
        }

        this.init();
    }

    init() {
        this.offcanvas.addEventListener('show.bs.offcanvas', () => {
            this.bsOffcanvas = bootstrap.Offcanvas.getInstance(this.offcanvas);

            if (this.isFixed) {
                this.unpin();
            }
        });

        this.offcanvas.addEventListener('hidden.bs.offcanvas', () => {
            if (this.isFixed) {
                this.unpin();
            }

            document.body.classList.remove('offcanvas-interactive');
            this.allowHideOnce = false;
        });

        this.offcanvas.addEventListener('hide.bs.offcanvas', (e) => {
            if (!this.isFixed || this.allowHideOnce) {
                this.allowHideOnce = false;
                return;
            }

            e.preventDefault();
            this.removeBackdrops();
        });

        this.offcanvas.querySelectorAll('[data-bs-dismiss="offcanvas"]').forEach((closeBtn) => {
            closeBtn.addEventListener('click', () => {
                if (!this.isFixed) return;

                this.allowHideOnce = true;
                this.unpin();
            });
        });

        this.pinBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isFixed) {
                this.unpin();
            }
        });
    }

    toggle() {
        if (this.isFixed) {
            this.unpin();
        } else {
            this.pin();
        }
    }

    syncBootstrapConfig(backdrop, scroll) {
        this.bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(this.offcanvas);

        if (!this.bsOffcanvas?._config) return;

        this.bsOffcanvas._config.backdrop = backdrop;
        this.bsOffcanvas._config.scroll = scroll;
    }

    removeBackdrops() {
        document.body.classList.add('offcanvas-interactive');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        document.querySelectorAll('.offcanvas-backdrop').forEach((backdrop) => {
            backdrop.remove();
        });
    }

    pin() {
        this.offcanvas.setAttribute('data-bs-scroll', 'true');
        this.offcanvas.setAttribute('data-bs-backdrop', 'false');

        this.syncBootstrapConfig(false, true);
        this.removeBackdrops();

        this.pinBtn.classList.add('active');
        this.pinBtn.title = 'Desfixar Ficha (pressione ESC)';

        this.offcanvas.classList.add('offcanvas-pinned');

        this.isFixed = true;

        if (typeof showToast === 'function') {
            showToast('✓ Ficha fixada! Você pode interagir com o mapa.');
        }

        console.log(`✓ Ficha ${this.offcanvasId} fixada`);
    }

    unpin() {
        this.offcanvas.setAttribute('data-bs-scroll', 'false');
        this.offcanvas.setAttribute('data-bs-backdrop', 'true');

        this.syncBootstrapConfig(true, false);

        document.body.classList.remove('offcanvas-interactive');

        this.pinBtn.classList.remove('active');
        this.pinBtn.title = 'Fixar Ficha (você pode interagir enquanto ela fica aberta)';

        this.offcanvas.classList.remove('offcanvas-pinned');

        this.isFixed = false;

        if (typeof showToast === 'function') {
            showToast('✓ Ficha desfixada');
        }

        console.log(`✓ Ficha ${this.offcanvasId} desfixada`);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('fixarFichaMestre')) {
        window.pinMestre = new PinManager('offcanvasFichaPersonagem', 'fixarFichaMestre');
    }

    if (document.getElementById('fixarFichaJogador')) {
        window.pinJogador = new PinManager('offcanvasFicha', 'fixarFichaJogador');
    }
});
