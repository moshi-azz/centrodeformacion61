<?php
// Guard y CSRF antes de cualquier output
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin');
    exit();
}

verify_csrf();

$cursoId      = (int)($_POST['id'] ?? 0);
$fechaApertura = trim($_POST['fecha_apertura'] ?? '');

if ($cursoId <= 0 || $fechaApertura === '') {
    header('Location: admin?error=datos_invalidos');
    exit();
}

$stmt = $conn->prepare("UPDATE cursos SET fecha_apertura = ?, inscripciones_cerradas = 1 WHERE id = ?");
$stmt->bind_param('si', $fechaApertura, $cursoId);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: admin?ok=inscripcion_cerrada');
} else {
    $stmt->close();
    header('Location: admin?error=no_cerrado');
}
exit();
