document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Buscador de cursos en tiempo real ─────────────────────────────────
    const buscador = document.getElementById('buscadorCursos');
    const tarjetas = document.querySelectorAll('.curso-card-container');

    if (buscador) {
        buscador.addEventListener('keyup', function (e) {
            const termino = e.target.value.toLowerCase();
            tarjetas.forEach(function (tarjeta) {
                const titulo = tarjeta.querySelector('.card-title').textContent.toLowerCase();
                tarjeta.style.display = titulo.includes(termino) ? 'block' : 'none';
            });
        });
    }

    // ── 2. Contador del próximo evento ───────────────────────────────────────
    const eventoDiv  = document.getElementById('evento');
    const contadorDiv = document.getElementById('contador');

    if (eventoDiv && contadorDiv) {
        const fechaStr = eventoDiv.getAttribute('data-fecha');
        if (fechaStr) {
            const eventoFecha = new Date(fechaStr).getTime();

            const intervalo = setInterval(function () {
                const ahora     = new Date().getTime();
                const distancia = eventoFecha - ahora;

                const dias     = Math.floor(distancia / (1000 * 60 * 60 * 24));
                const horas    = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutos  = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
                const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

                contadorDiv.innerHTML =
                    "<div class='d-flex justify-content-center gap-3 mt-3'>" +
                    "<div class='timer-box'><span class='timer-num'>" + dias     + "</span><span class='timer-label'>Días</span></div>" +
                    "<div class='timer-box'><span class='timer-num'>" + horas    + "</span><span class='timer-label'>Horas</span></div>" +
                    "<div class='timer-box'><span class='timer-num'>" + minutos  + "</span><span class='timer-label'>Minutos</span></div>" +
                    "<div class='timer-box'><span class='timer-num'>" + segundos + "</span><span class='timer-label'>Seg</span></div>" +
                    "</div>";

                if (distancia < 0) {
                    clearInterval(intervalo);
                    contadorDiv.innerHTML = "<div class='alert alert-success mt-3'>¡El evento ha comenzado!</div>";
                }
            }, 1000);
        }
    }

    // ── 3. Navbar: shrink al scroll + padding dinámico ───────────────────────
    const navbar   = document.querySelector('.glass-nav');
    const mainEl   = document.querySelector('main');
    const headerEl = document.querySelector('body > header');

    function adjustMainPadding() {
        if (!navbar) return;
        const h = navbar.offsetHeight + 'px';
        if (mainEl)   mainEl.style.paddingTop   = h;
        if (headerEl) headerEl.style.paddingTop = h;
    }

    if (navbar) {
        const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 60);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    adjustMainPadding();
    window.addEventListener('resize', adjustMainPadding, { passive: true });

    // ── 4. Parallax en el hero ────────────────────────────────────────────────
    const hero = document.getElementById('heroContainer');
    if (hero) {
        window.addEventListener('scroll', function () {
            hero.style.backgroundPositionY = 'calc(50% + ' + (window.scrollY * 0.35) + 'px)';
        }, { passive: true });
    }

    // ── 5. Offcanvas de Inscripción ───────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-inscribirse-card');
        if (!btn) return;
        const cursoId = btn.dataset.cursoId;
        const select  = document.getElementById('oc_curso');
        if (select && cursoId) select.value = cursoId;
    });

    (function () {
        const ocEl = document.getElementById('offcanvasInscripcion');
        if (ocEl && ocEl.querySelector('.alert')) {
            bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
        }
    })();

    // ── 6. Píldora deslizante + sección activa ───────────────────────────────
    const pill = document.getElementById('navPill');

    const navSections = [
        { sectionEl: document.getElementById('heroContainer'), linkEl: document.getElementById('nav-inicio') },
        { sectionEl: document.getElementById('trayectos'),     linkEl: document.getElementById('nav-trayectos') },
        { sectionEl: document.getElementById('nosotros'),      linkEl: document.getElementById('nav-nosotros') },
        { sectionEl: document.querySelector('footer'),         linkEl: document.getElementById('nav-contacto') },
    ].filter(s => s.sectionEl && s.linkEl);

    function movePill(linkEl) {
        if (!pill || !linkEl || !navbar) return;
        if (window.innerWidth < 992) {
            pill.style.opacity = '0';
            return;
        }
        const navRect  = navbar.getBoundingClientRect();
        const linkRect = linkEl.getBoundingClientRect();
        pill.style.left    = (linkRect.left - navRect.left) + 'px';
        pill.style.width   = linkRect.width + 'px';
        pill.style.opacity = '1';
    }

    function updateActiveNav() {
        if (!navSections.length) return;
        const offset  = (navbar ? navbar.offsetHeight : 70) + 20;
        const scrollY = window.scrollY + offset;

        let active = navSections[0];
        for (const s of navSections) {
            if (s.sectionEl.offsetTop <= scrollY) active = s;
        }

        navSections.forEach(s => s.linkEl.classList.remove('nav-active'));
        active.linkEl.classList.add('nav-active');
        movePill(active.linkEl);
    }

    window.addEventListener('scroll', updateActiveNav, { passive: true });
    window.addEventListener('resize', function () {
        adjustMainPadding();
        updateActiveNav();
    }, { passive: true });

    // 100ms garantiza layout completo del nav fijo antes de leer getBoundingClientRect
    adjustMainPadding();
    setTimeout(updateActiveNav, 100);

    // ── 7. Modal Custom: Detalle de Trayecto (animación de origen) ───────────
    const overlay    = document.getElementById('cursoModalOverlay');
    const modalCard  = document.getElementById('cursoModalCard');
    const modalImg   = document.getElementById('cursoModalImg');
    const modalTit   = document.getElementById('cursoModalTitulo');
    const modalDesc  = document.getElementById('cursoModalDesc');
    const modalInsc  = document.getElementById('cursoModalInscripcionArea');
    const modalClose = document.getElementById('cursoModalClose');
    let lastBtn = null;

    function computeOrigin(btn) {
        if (!btn || !modalCard) return;
        const btnRect  = btn.getBoundingClientRect();
        const cardRect = modalCard.getBoundingClientRect();
        const ox = (btnRect.left + btnRect.width  / 2) - cardRect.left;
        const oy = (btnRect.top  + btnRect.height / 2) - cardRect.top;
        modalCard.style.transformOrigin = ox + 'px ' + oy + 'px';
    }

    function openModal(btn) {
        const cerradas = btn.dataset.inscripcionesCerradas === 'true';

        modalImg.src           = btn.dataset.imagen       || '';
        modalImg.alt           = btn.dataset.titulo        || '';
        modalTit.textContent   = btn.dataset.titulo        || '';
        modalDesc.textContent  = btn.dataset.descripcion   || '';

        if (cerradas) {
            const apertura = btn.dataset.fechaApertura || '';
            modalInsc.innerHTML =
                '<div class="inscripcion-cerrada">Inscripciones cerradas</div>' +
                (apertura ? '<p class="mt-2 mb-0">Fecha de apertura: ' + apertura + '</p>' : '');
        } else {
            modalInsc.innerHTML =
                '<button type="button" class="btn btn-primary btn-sm mt-2 btn-inscribirse-modal"' +
                ' data-bs-toggle="offcanvas" data-bs-target="#offcanvasInscripcion"' +
                ' aria-controls="offcanvasInscripcion"' +
                ' data-curso-id="' + btn.dataset.cursoid + '">' +
                '<i class="bi bi-telephone-fill me-1"></i>Quiero anotarme</button>';
        }

        lastBtn = btn;
        overlay.classList.add('visible');

        // 1er rAF: el display:flex se aplica y el card tiene dimensiones reales
        requestAnimationFrame(function () {
            computeOrigin(btn);
            // 2do rAF: dispara las transiciones CSS desde la posición medida
            requestAnimationFrame(function () {
                overlay.classList.add('open');
            });
        });

        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!overlay) return;
        if (lastBtn) computeOrigin(lastBtn);
        overlay.classList.remove('open');
        overlay.addEventListener('transitionend', function handler() {
            overlay.removeEventListener('transitionend', handler);
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
            if (modalCard) modalCard.style.transformOrigin = '';
        });
    }

    // Abrir desde "Ver trayecto"
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ver-curso');
        if (btn && overlay) openModal(btn);
    });

    // "Quiero anotarme" dentro del modal → pre-seleccionar curso y cerrar modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-inscribirse-modal');
        if (!btn) return;
        const select = document.getElementById('oc_curso');
        if (select) select.value = btn.dataset.cursoId;
        closeModal();
    });

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (overlay)    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('open')) closeModal();
    });

});
