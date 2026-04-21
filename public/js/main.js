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
    const eventoDiv = document.getElementById('evento');
    const contadorDiv = document.getElementById('contador');

    if (eventoDiv && contadorDiv) {
        const fechaStr = eventoDiv.getAttribute('data-fecha');
        if (fechaStr) {
            const eventoFecha = new Date(fechaStr).getTime();

            const intervalo = setInterval(function () {
                const ahora = new Date().getTime();
                const distancia = eventoFecha - ahora;

                const dias    = Math.floor(distancia / (1000 * 60 * 60 * 24));
                const horas   = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
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

    // ── 3. Navbar shrink al hacer scroll ─────────────────────────────────────
    const navbar = document.querySelector('.glass-nav');
    if (navbar) {
        const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 60);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // estado inicial
    }

    // ── 4. Parallax en el hero ────────────────────────────────────────────────
    const hero = document.getElementById('heroContainer');
    if (hero) {
        window.addEventListener('scroll', function () {
            // El fondo se mueve al 35% de la velocidad del scroll → efecto profundidad
            hero.style.backgroundPositionY = 'calc(50% + ' + (window.scrollY * 0.35) + 'px)';
        }, { passive: true });
    }

    // ── 5. Offcanvas de Inscripción ───────────────────────────────────────────

    // 5a. Pre-seleccionar el curso al hacer click en "Inscribirse" de una tarjeta
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-inscribirse-card');
        if (!btn) return;
        const cursoId = btn.dataset.cursoId;
        const select  = document.getElementById('oc_curso');
        if (select && cursoId) {
            select.value = cursoId;
        }
        // Bootstrap abre el offcanvas automáticamente por data-bs-toggle
    });

    // 5b. Auto-abrir el offcanvas si PHP puso un $mensaje (respuesta POST)
    (function () {
        const ocEl = document.getElementById('offcanvasInscripcion');
        if (ocEl && ocEl.querySelector('.alert')) {
            bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
        }
    })();

});
