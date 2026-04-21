<?php
$pageTitle  = 'Gestión de Eventos — CFP 61';
$activePage = 'eventos';
require_once __DIR__ . '/includes/auth_check.php';

// ── Procesar POST antes de cualquier output ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Agregar evento
    if (isset($_POST['agregar_evento'])) {
        $titulo      = trim($_POST['titulo'] ?? '');
        $fecha_hora  = trim($_POST['fecha_hora'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($titulo !== '' && $fecha_hora !== '' && $descripcion !== '') {
            $stmt = $conn->prepare("INSERT INTO eventos (titulo, fecha_hora, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $titulo, $fecha_hora, $descripcion);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Editar evento
    if (isset($_POST['editar_evento'])) {
        $id          = (int)($_POST['id'] ?? 0);
        $titulo      = trim($_POST['titulo'] ?? '');
        $fecha_hora  = trim($_POST['fecha_hora'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id > 0 && $titulo !== '' && $fecha_hora !== '' && $descripcion !== '') {
            $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, fecha_hora = ?, descripcion = ? WHERE id = ?");
            $stmt->bind_param('sssi', $titulo, $fecha_hora, $descripcion, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Eliminar evento (POST, ya no GET)
    if (isset($_POST['eliminar_evento'])) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: eventos');
    exit();
}

// ── Obtener eventos ──────────────────────────────────────────────────────────
$result = $conn->query("SELECT * FROM eventos ORDER BY fecha_hora DESC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <!-- Encabezado de página -->
    <div class="admin-page-header">
        <h2><i class="bi bi-calendar-event-fill me-2"></i>Gestión de Eventos</h2>
        <a href="admin" class="btn-admin-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
        </a>
    </div>

    <!-- Formulario: Agregar evento -->
    <div class="admin-card mb-4">
        <div class="card-section-title">
            <i class="bi bi-plus-circle me-1"></i>Agregar Nuevo Evento
        </div>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="titulo" class="form-label">Título del Evento</label>
                    <input type="text" name="titulo" id="titulo"
                           class="form-control" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label for="fecha_hora" class="form-label">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha_hora" id="fecha_hora"
                           class="form-control" required>
                </div>
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion"
                              class="form-control" rows="3" required maxlength="1000"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" name="agregar_evento" class="btn-admin-primary">
                        <i class="bi bi-plus-lg me-1"></i>Agregar Evento
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de eventos existentes -->
    <h4 class="fw-bold mb-3">Eventos Existentes</h4>

    <div class="admin-card">
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha y Hora</th>
                        <th>Descripción</th>
                        <th style="min-width:300px;">Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($evento = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($evento['fecha_hora'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($evento['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <!-- Formulario de edición en línea -->
                            <form method="POST" class="border rounded p-2 bg-light">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$evento['id'] ?>">
                                <div class="mb-2">
                                    <input type="text" name="titulo" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                           required maxlength="255">
                                </div>
                                <div class="mb-2">
                                    <input type="datetime-local" name="fecha_hora" class="form-control form-control-sm"
                                           value="<?= date('Y-m-d\TH:i', strtotime($evento['fecha_hora'])) ?>"
                                           required>
                                </div>
                                <div class="mb-2">
                                    <textarea name="descripcion" class="form-control form-control-sm"
                                              rows="2" required maxlength="1000"><?= htmlspecialchars($evento['descripcion'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <button type="submit" name="editar_evento" class="btn btn-sm btn-warning fw-bold">
                                    <i class="bi bi-check-lg"></i> Actualizar
                                </button>
                            </form>
                        </td>
                        <td>
                            <!-- Eliminar via POST + CSRF -->
                            <form method="POST"
                                  onsubmit="return confirm('¿Eliminar el evento «<?= htmlspecialchars(addslashes($evento['titulo']), ENT_QUOTES, 'UTF-8') ?>»?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$evento['id'] ?>">
                                <button type="submit" name="eliminar_evento" class="btn btn-sm btn-danger fw-bold">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-center text-muted py-4">
                <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                No hay eventos registrados.
            </p>
        <?php endif; ?>
    </div>

</div><!-- /.container-fluid -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
