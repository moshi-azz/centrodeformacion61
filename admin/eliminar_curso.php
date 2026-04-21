<?php
// Guard y CSRF antes de cualquier output
require_once __DIR__ . '/includes/auth_check.php';

// Solo acepta POST (ya no GET) para proteger contra CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin');
    exit();
}

verify_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: admin');
    exit();
}

// Obtener nombre de imagen antes de eliminar (para borrarla del disco)
$stmt = $conn->prepare("SELECT imagen FROM cursos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($imagen);
$stmt->fetch();
$stmt->close();

// Eliminar el curso con prepared statement
$stmt = $conn->prepare("DELETE FROM cursos WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    // Si el curso tenía imagen, eliminarla del disco
    if (!empty($imagen)) {
        $imagePath = __DIR__ . '/../public/IMG/' . $imagen;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $stmt->close();
    header('Location: admin?ok=curso_eliminado');
} else {
    $stmt->close();
    header('Location: admin?error=no_eliminado');
}
exit();
