# Documentación Técnica - CFP 61

## Requisitos de Entorno
1. PHP 8.0 o superior.
2. Servidor Apache (Mod_Rewrite habilitado imprescindible para rutas amigables).
3. MySQL o MariaDB.
4. Extensión `mysqli` habilitada en `php.ini`.

## Configuración de Entorno y Base de Datos
El proyecto requiere que exista un archivo `db.php` **en la raíz del proyecto**. Este archivo NO es parte del repositorio por medidas de seguridad.

### Estructura Requerida para `db.php`
```php
<?php
define('DB_SERVER', 'localhost o tu remoto');
define('DB_USERNAME', 'usuario_db');
define('DB_PASSWORD', 'contraseña_db');
define('DB_DATABASE', 'nombre_db');

class Database
{
    private $conexion;
    // ... implementar patrón singleton o instanciación para proveer $conexion
    // la cual debera devolver una instancia de 'new mysqli(...)' accesible desde
    // el método getConnection() y otro llamado closeConnection().
}
?>
```

## Sistema de Enrutamiento (URLs Limpias)
A través de `.htaccess`, la web remueve en su totalidad las extensiones `.php`, tanto en el frontend (`public/`) como en el backend (`admin/`). Esto es gestionado por una reescritura que oculta los directorios y presenta un árbol virtual de navegación (por ejemplo, buscar `/admin/agregar_curso` redirige silenciosamente a `/admin/agregar_curso.php`).

## Estructura de Directorios Principal
- `/admin`: Toda la gestión del backend administrativo (protegido por sesión).
- `/public`: Archivos expuestos públicamente al estudiante/visitante de la web.
- `/public/IMG`: Directorio principal de activos visuales. Para rendimiento y peso, gran parte de las imágenes generadas del lado administrativo se formatean como `WEBP`.

## Seguridad Integrada
- El login de administrador requiere protección contra inyecciones SQL que utilicen `stmt->bind_param`.
- La información sobre alumnos que deciden "inscribirse" está respaldada para evitar que la plataforma sufra ataques XSS (se utiliza escape HTML) desde el formulario nativo hacia la base de datos en caso de recolectar data a futuro.
