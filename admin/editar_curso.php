<?php
$pageTitle  = 'Editar Trayecto — CFP 61';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/auth_check.php';

// Obtener ID del curso a editar con prepared statement (fix SQL injection)
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: admin');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$curso  = $result->fetch_assoc();
$stmt->close();

if (!$curso) {
    header('Location: admin');
    exit();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <!-- Encabezado de página -->
    <div class="admin-page-header">
        <h2><i class="bi bi-pencil-square me-2"></i>Editar Trayecto</h2>
        <a href="admin" class="btn-admin-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="admin-card">
                <div class="card-section-title">
                    <i class="bi bi-book-fill me-1"></i>
                    Editando: <strong><?= htmlspecialchars($curso['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <form action="procesar_editar_curso" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$curso['id'] ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            Título <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               value="<?= htmlspecialchars($curso['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                               required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion"
                                  rows="4" required maxlength="1000"><?= htmlspecialchars($curso['descripcion'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label">Nueva Imagen (opcional)</label>
                        <input type="file" class="form-control" id="foto" name="foto"
                               accept=".jpg,.jpeg,.png,.gif,.webp">
                        <div class="form-text">
                            Dejá este campo vacío para conservar la imagen actual.<br>
                            Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 5 MB.
                        </div>

                        <?php if (!empty($curso['imagen'])): ?>
                        <div class="mt-2">
                            <p class="fw-bold mb-1 small">Imagen actual:</p>
                            <img src="../public/IMG/<?= htmlspecialchars($curso['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Imagen actual del trayecto" class="img-preview">
                        </div>
                        <?php endif; ?>

                        <!-- Vista previa de la nueva imagen -->
                        <img id="previewImg" src="#" alt="Vista previa" class="img-preview d-none mt-2">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-admin-primary">
                            <i class="bi bi-save-fill me-1"></i>Actualizar Trayecto
                        </button>
                        <a href="admin" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div><!-- /.container-fluid -->

<?php
$extraScripts = <<<'JS'
<script>
// Vista previa de nueva imagen antes de subir
document.getElementById('foto').addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('previewImg');
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.classList.add('d-none');
    }
});
</script>
JS;

require_once __DIR__ . '/includes/footer.php';
