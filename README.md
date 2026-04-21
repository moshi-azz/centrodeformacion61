# Centro de Formación Profesional 61 - La Criolla / Colonia Ayuí

Bienvenido al repositorio oficial del **Centro de Formación Profesional N° 61**. Este proyecto es una plataforma web desarrollada en **PHP y Bootstrap 5**, diseñada para facilitar la administración y exhibición de la oferta académica, trayectos formativos y eventos de la institución.

## Características Principales

- **Diseño Moderno y Responsivo:** Implementación de interfaces basadas en "Glassmorphism", un esquema corporativo riguroso y soporte total para visualización móvil y escritorio usando Bootstrap 5.
- **Catálogo de Trayectos Formativos:** Los usuarios pueden buscar y ver detalles de diferentes cursos usando filtros en tiempo real y componentes visuales amigables (Cards).
- **Gestión Administrativa:** Panel administrativo seguro y centralizado para gestionar (Crear, Editar, Eliminar):
  - Trayectos Formativos y Cursos.
  - Apertura y Cierre de Inscripciones.
  - Eventos institucionales.
  - Usuarios del panel.
- **Sistema de Rutas Limpias:** Configuración de Apache (`.htaccess`) para enrutamiento sin extensiones `.php`, mejorando la indexación y accesibilidad.
- **Gestión de Imágenes:** Sistema eficiente de carga, manejo y visualización de imágenes (WebP) nativas para los cursos y banners.

## Tecnologías Utilizadas

- **Frontend:** HTML5, CSS3 (Vanilla y variables), Bootstrap 5.3, Bootstrap Icons, JavaScript (ES6+).
- **Backend:** PHP 8.x con arquitectura estructurada.
- **Base de Datos:** MySQL / MariaDB (mediante la extensión `mysqli`).
- **Servidor:** Optimizado para servidores Apache con mod_rewrite habilitado.

## Instalación y Despliegue Rápidos

1. Clona el repositorio `git clone https://github.com/moshi-azz/centrodeformacion61.git` (asegúrate de configurar las claves si es privado).
2. Configura tu servidor local (XAMPP/MAMP) apuntando al directorio raíz.
3. El archivo de conexión `db.php` está **omisamente ignorado** por seguridad. Debes crear en la raíz un archivo `db.php` con la estructura base de conexión (ver `DOCUMENTACION.md` para más información).
4. Aplica el script SQL correspondiente en tu gestor de base de datos MySQL para preparar las tablas.

Para una guía más técnica de configuración, revisa la [Documentación Completa](DOCUMENTACION.md).

## Licencia y Derechos

Plataforma educativa para el Centro de Formación Profesional N° 61. Todos los derechos reservados.
