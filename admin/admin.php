<?php
$pageTitle  = 'Dashboard — CFP 61';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/auth_check.php';

$result = $conn->query("SELECT * FROM cursos ORDER BY id DESC");
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <!-- Encabezado de página -->
    <div class="admin-page-header">
        <h2><i class="bi bi-grid-fill me-2"></i>Panel de Administración — CFP 61</h2>
        <a href="agregar_curso" class="btn-admin-primary">
            <i class="bi bi-plus-lg me-1"></i>Agregar Trayecto
        </a>
    </div>

    <h4 class="fw-bold mb-3">Trayectos Registrados</h4>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Inscripciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($row['inscripciones_cerradas']): ?>
                                    <span class="badge-closed"><i class="bi bi-lock-fill me-1"></i>Cerradas</span>
                                <?php else: ?>
                                    <span class="badge-open"><i class="bi bi-unlock-fill me-1"></i>Abiertas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <!-- Editar -->
                                    <a href="editar_curso?id=<?= (int)$row['id'] ?>"
                                       class="btn btn-sm btn-warning fw-bold">
                                        <i class="bi bi-pencil-fill"></i> Editar
                                    </a>

                                    <!-- Eliminar (POST + CSRF, sin GET) -->
                                    <form method="POST" action="eliminar_curso"
                                          onsubmit="return confirm('¿Eliminar el trayecto «<?= htmlspecialchars(addslashes($row['titulo']), ENT_QUOTES, 'UTF-8') ?>»? Esta acción no se puede deshacer.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger fw-bold">
                                            <i class="bi bi-trash-fill"></i> Eliminar
                                        </button>
                                    </form>

                                    <!-- Abrir / Cerrar inscripciones -->
                                    <?php if ($row['inscripciones_cerradas']): ?>
                                        <form method="POST" action="abrir_inscripcion">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                <i class="bi bi-unlock-fill"></i> Abrir
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-info fw-bold"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#offcanvasFecha"
                                                onclick="setCursoId(<?= (int)$row['id'] ?>)">
                                            <i class="bi bi-lock-fill"></i> Cerrar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No hay trayectos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enlace a gestión de eventos -->
    <div class="d-flex gap-2 mb-4">
        <a href="eventos" class="btn-admin-secondary">
            <i class="bi bi-calendar-event-fill me-1"></i>Gestionar Eventos
        </a>
        <a href="../public/index" class="btn-admin-secondary" target="_blank">
            <i class="bi bi-eye-fill me-1"></i>Ver Sitio Público
        </a>
    </div>

</div><!-- /.container-fluid -->

<!-- Offcanvas: Cerrar Inscripción -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFecha" aria-labelledby="offcanvasFechaLabel">
    <div class="offcanvas-header brand-header">
        <h5 class="offcanvas-title" id="offcanvasFechaLabel">
            <i class="bi bi-lock-fill me-2"></i>Cerrar Inscripción
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-muted mb-3">Establecé la fecha en la que se reabrirán las inscripciones para este trayecto.</p>
        <form id="fechaForm" action="cerrar_inscripcion" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="cursoId" value="">
            <div class="mb-3">
                <label for="fecha_apertura" class="form-label">Fecha de Reapertura</label>
                <input type="date" class="form-control" id="fecha_apertura"
                       name="fecha_apertura" required
                       min="<?= date('Y-m-d') ?>">
                <div class="form-text">Se mostrará en la tarjeta del trayecto en el sitio público.</div>
            </div>
            <button type="submit" class="btn-admin-primary w-100">
                <i class="bi bi-lock-fill me-1"></i>Cerrar Inscripción
            </button>
        </form>
    </div>
</div>

<?php
$extraScripts = <<<'JS'
<script>
function setCursoId(id) {
    document.getElementById('cursoId').value = id;
}
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
