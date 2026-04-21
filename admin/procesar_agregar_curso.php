<?php
// Guard y CSRF antes de cualquier output
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: agregar_curso');
    exit();
}

verify_csrf();

// ── Validar datos del formulario ─────────────────────────────────────────────
$titulo      = trim($_POST['nombre_curso'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($titulo === '' || $descripcion === '') {
    header('Location: agregar_curso?error=campos_vacios');
    exit();
}

// ── Validar y procesar la imagen ─────────────────────────────────────────────
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header('Location: agregar_curso?error=imagen_requerida');
    exit();
}

$allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size     = 5 * 1024 * 1024; // 5 MB

// Tamaño
if ($_FILES['foto']['size'] > $max_size) {
    header('Location: agregar_curso?error=archivo_grande');
    exit();
}

// Extensión
$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext, true)) {
    header('Location: agregar_curso?error=extension_invalida');
    exit();
}

// Verificar que sea una imagen válida leyendo sus magic bytes (sin extensión fileinfo)
$imageInfo = @getimagesize($_FILES['foto']['tmp_name']);
if ($imageInfo === false) {
    header('Location: agregar_curso?error=mime_invalido');
    exit();
}
$allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
if (!in_array($imageInfo[2], $allowedImageTypes, true)) {
    header('Location: agregar_curso?error=tipo_invalido');
    exit();
}

// Nombre seguro y único
$newFileName  = bin2hex(random_bytes(16)) . '.' . $ext;
$uploadDir    = __DIR__ . '/../public/IMG/';
$dest_path    = $uploadDir . $newFileName;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dest_path)) {
    header('Location: agregar_curso?error=upload_fallido');
    exit();
}

// ── Insertar en la base de datos con prepared statement ──────────────────────
$stmt = $conn->prepare("INSERT INTO cursos (titulo, descripcion, imagen) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $titulo, $descripcion, $newFileName);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: admin?ok=curso_agregado');
} else {
    // Si falla la BD, eliminar la imagen ya subida
    if (file_exists($dest_path)) {
        unlink($dest_path);
    }
    $stmt->close();
    header('Location: agregar_curso?error=db_error');
}
exit();
