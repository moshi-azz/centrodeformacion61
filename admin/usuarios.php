<?php
$pageTitle  = 'Gestión de Usuarios — CFP 61';
$activePage = 'usuarios';
require_once __DIR__ . '/includes/auth_check.php';

// ── Procesar POST antes de cualquier output ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Agregar nuevo usuario
    if (isset($_POST['agregar_usuario'])) {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave_plana = trim($_POST['contrasena'] ?? '');
        $id_cargo = (int)($_POST['id_cargo'] ?? 1);

        if ($usuario !== '' && $clave_plana !== '') {
            // Utilizar la función de hash recomendada de PHP (muy seguro)
            $hash = password_hash($clave_plana, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena, id_cargo) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $usuario, $hash, $id_cargo);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Cambiar contraseña de un usuario existente
    if (isset($_POST['cambiar_clave'])) {
        $id = (int)($_POST['id'] ?? 0);
        $nueva_clave = trim($_POST['nueva_contrasena'] ?? '');

        if ($id > 0 && $nueva_clave !== '') {
            // Hasheamos la nueva contraseña antes de actualizar
            $hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Eliminar un usuario
    if (isset($_POST['eliminar_usuario'])) {
        $id = (int)($_POST['id'] ?? 0);
        // Evitaremos que se borre a si mismo por error asumiendo que $_SESSION almacena su ID si quisieras, 
        // pero por ahora dejaremos el delete base para administrar.
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: usuarios');
    exit();
}

// ── Obtener todos los usuarios ────────────────────────────────────────────────
$result = $conn->query("SELECT * FROM usuarios ORDER BY id ASC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <!-- Encabezado de página -->
    <div class="admin-page-header">
        <h2><i class="bi bi-people-fill me-2"></i>Gestión de Usuarios</h2>
        <a href="admin" class="btn-admin-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
        </a>
    </div>

    <!-- Formulario: Agregar Usuario -->
    <div class="admin-card mb-4">
        <div class="card-section-title">
            <i class="bi bi-person-plus-fill me-1"></i>Crear Nuevo Usuario
        </div>
        <div class="alert alert-info">
            <small><i class="bi bi-info-circle me-1"></i> Las contraseñas creadas aquí se guardarán de forma <strong>segura (Hasheadas)</strong> en la base de datos.</small>
        </div>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="usuario" class="form-label">Nombre de Usuario (Login)</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required maxlength="100">
                </div>
                <div class="col-md-4">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" id="contrasena" class="form-control" minlength="4" required>
                </div>
                <div class="col-md-4">
                    <label for="id_cargo" class="form-label">Nivel de Acceso</label>
                    <select name="id_cargo" id="id_cargo" class="form-select" required>
                        <option value="1">Administrador (Control Total)</option>
                        <option value="2">Cliente / Restringido</option>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" name="agregar_usuario" class="btn-admin-primary">
                        <i class="bi bi-plus-lg me-1"></i>Crear Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Usuarios existentes -->
    <h4 class="fw-bold mb-3">Usuarios Registrados en el Sistema</h4>

    <div class="admin-card">
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 30%;">Nombre de Usuario</th>
                        <th style="width: 25%;">Rol</th>
                        <th style="min-width:300px;">Cambiar Contraseña</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= (int)$usuario['id'] ?></span></td>
                        <td class="fw-bold"><?= htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if($usuario['id_cargo'] == 1): ?>
                                <span class="badge bg-primary">Administrador</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark">Cliente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Formulario para cambiar clave a este usuario -->
                            <form method="POST" class="d-flex align-items-center gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                                <input type="password" name="nueva_contrasena" class="form-control form-control-sm" placeholder="Nueva Contraseña..." required minlength="4">
                                <button type="submit" name="cambiar_clave" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap">
                                    <i class="bi bi-key-fill"></i> Actualizar
                                </button>
                            </form>
                        </td>
                        <td>
                            <!-- Formulario Eliminar -->
                            <form method="POST" onsubmit="return confirm('¿Bajo tu propia responsabilidad, deseas eliminar al usuario <?= htmlspecialchars(addslashes($usuario['usuario']), ENT_QUOTES, 'UTF-8') ?>?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                                <button type="submit" name="eliminar_usuario" class="btn btn-sm btn-danger fw-bold">
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
                No hay usuarios registrados.
            </p>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
