<?php
// Guard y CSRF antes de cualquier output
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin');
    exit();
}

verify_csrf();

// ── Obtener y validar datos ──────────────────────────────────────────────────
$id          = (int)($_POST['id'] ?? 0);
$titulo      = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($id <= 0 || $titulo === '' || $descripcion === '') {
    header('Location: admin?error=datos_invalidos');
    exit();
}

// ── Obtener imagen actual de la BD ───────────────────────────────────────────
$stmtImg = $conn->prepare("SELECT imagen FROM cursos WHERE id = ?");
$stmtImg->bind_param('i', $id);
$stmtImg->execute();
$stmtImg->bind_result($currentImage);
$stmtImg->fetch();
$stmtImg->close();

$newFileName = $currentImage; // Por defecto conservar la imagen actual

// ── Procesar nueva imagen si se subió ────────────────────────────────────────
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size     = 5 * 1024 * 1024; // 5 MB

    // Tamaño
    if ($_FILES['foto']['size'] > $max_size) {
        header('Location: editar_curso?id=' . $id . '&error=archivo_grande');
        exit();
    }

    // Extensión
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        header('Location: editar_curso?id=' . $id . '&error=extension_invalida');
        exit();
    }

    // Verificar que sea una imagen válida leyendo sus magic bytes (sin extensión fileinfo)
    $imageInfo = @getimagesize($_FILES['foto']['tmp_name']);
    if ($imageInfo === false) {
        header('Location: editar_curso?id=' . $id . '&error=mime_invalido');
        exit();
    }
    $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    if (!in_array($imageInfo[2], $allowedImageTypes, true)) {
        header('Location: editar_curso?id=' . $id . '&error=tipo_invalido');
        exit();
    }

    // Nombre seguro y único
    $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir   = __DIR__ . '/../public/IMG/';
    $dest_path   = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest_path)) {
        // Eliminar imagen anterior si existía
        if (!empty($currentImage) && file_exists($uploadDir . $currentImage)) {
            unlink($uploadDir . $currentImage);
        }
    } else {
        header('Location: editar_curso?id=' . $id . '&error=upload_fallido');
        exit();
    }
}

// ── Actualizar en la base de datos ───────────────────────────────────────────
$stmt = $conn->prepare("UPDATE cursos SET titulo = ?, descripcion = ?, imagen = ? WHERE id = ?");
$stmt->bind_param('sssi', $titulo, $descripcion, $newFileName, $id);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: admin?ok=curso_actualizado');
} else {
    $stmt->close();
    header('Location: editar_curso?id=' . $id . '&error=db_error');
}
exit();
