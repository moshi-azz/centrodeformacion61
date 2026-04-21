<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$dbPath = __DIR__ . '/../db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usuario'], $_POST['contrasena'])) {

    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    try {
        $db = new Database();
        $conexion = $db->getConnection();
        
        $query = "SELECT * FROM usuarios WHERE usuario = ?";
        
        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $filas = $resultado->fetch_assoc();
                
                if (password_verify($contrasena, $filas['contrasena'])) {
                    $id_cargo = isset($filas['id_cargo']) ? $filas['id_cargo'] : 1;
                    
                    $_SESSION['id_cargo'] = $id_cargo;
                    
                    if ($id_cargo == 1) { 
                        $_SESSION['isAdmin'] = true;
                        header("Location: ../admin/admin");
                    } else { 
                        header("Location: cliente");
                    }
                    exit();
                } else {
                    echo "<h3>Usuario o contraseña incorrectos.</h3> <a href='javascript:history.back()'>Volver</a>";
                    exit();
                }
            } else {
                echo "<h3>Usuario o contraseña incorrectos.</h3> <a href='javascript:history.back()'>Volver</a>";
                exit();
            }
            $stmt->close();
        }
        $db->closeConnection();
        
    } catch (Exception $e) {
        die("Error general en la plataforma: " . $e->getMessage());
    }
}
// IMPORTANTE: NO HAY ELSE AQUÍ PARA NO ROMPER LA PÁGINA PÚBLICA CUANDO SE INCLUYE ESTE ARCHIVO
?>
