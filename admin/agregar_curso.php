<?php
$pageTitle  = 'Agregar Trayecto — CFP 61';
$activePage = 'agregar';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <!-- Encabezado de página -->
    <div class="admin-page-header">
        <h2><i class="bi bi-plus-circle-fill me-2"></i>Agregar Nuevo Trayecto</h2>
        <a href="admin" class="btn-admin-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="admin-card">
                <div class="card-section-title">
                    <i class="bi bi-book-fill me-1"></i>Datos del Trayecto
                </div>

                <form action="procesar_agregar_curso" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre_curso" class="form-label">
                            Nombre del Trayecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nombre_curso"
                               name="nombre_curso" required maxlength="255"
                               placeholder="Ej: Electricidad Domiciliaria">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion"
                                  rows="4" required maxlength="1000"
                                  placeholder="Describí brevemente el contenido del trayecto..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label">
                            Imagen del Trayecto <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="foto" name="foto"
                               accept=".jpg,.jpeg,.png,.gif,.webp" required>
                        <div class="form-text">
                            Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 5 MB.
                        </div>
                        <!-- Vista previa de imagen -->
                        <img id="previewImg" src="#" alt="Vista previa" class="img-preview d-none mt-2">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-admin-primary">
                            <i class="bi bi-save-fill me-1"></i>Guardar Trayecto
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
// Vista previa de imagen antes de subir
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
