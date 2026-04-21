<?php
// Guard y CSRF antes de cualquier output
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin');
    exit();
}

verify_csrf();

$cursoId = (int)($_POST['id'] ?? 0);

if ($cursoId <= 0) {
    header('Location: admin?error=id_invalido');
    exit();
}

$stmt = $conn->prepare("UPDATE cursos SET inscripciones_cerradas = 0, fecha_apertura = NULL WHERE id = ?");
$stmt->bind_param('i', $cursoId);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: admin?ok=inscripcion_abierta');
} else {
    $stmt->close();
    header('Location: admin?error=no_abierto');
}
exit();
