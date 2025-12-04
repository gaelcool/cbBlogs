# 📰 CbBlogs - Plataforma de Blogs Escolar

Una plataforma de blogs moderna y colaborativa diseñada para la comunidad escolar (CBTIS 03), con un sistema de usuarios, publicación de artículos con medios, democracia estudiantil y un diseño visual atractivo estilo "Frutiger Aero" / Pastel.

## 🚀 Características

### 📝 Sistema de Blogs
- **Escritura Avanzada**: Dos modos de escritura - básico y con medios (requiere 100+ puntos de contribución)
- **Publicación con Imágenes**: Soporte para subir y mostrar imágenes en los artículos
- **Lectura Interactiva**: Interfaz de lectura moderna con sistema de comentarios
- **Estilos Personalizables**: Los usuarios pueden personalizar el estilo visual de su blog

### 👥 Sistema de Usuarios y Comunidad
- **Gestión de Perfiles**: Registro, inicio de sesión y actualización de información personal
- **Sistema de Puntos**: Gana puntos por contribuciones (blogs, comentarios, recursos)
- **Estadísticas de Usuario**: Visualización de blogs totales, puntos y progreso
- **Badges Desbloqueables**: Sistema de rangos y logros basado en contribuciones

### 🎨 Diseño y Experiencia
- **Diseño Responsivo**: Optimizado para dispositivos móviles (768px y menores)
- **Glassmorphism**: Efectos modernos de vidrio esmerilado en toda la interfaz
- **Tipografía Premium**: Sistema de fuentes Fira Sans/Fira Sans Bold/Fira Code
- **Paleta de Colores Nostálgica**: Inspirada en Frutiger Aero con tonos pastel
- **Hub Central (LP.php)**: Portal principal con saludo personalizado según la hora del día

### 🗳️ Democracia Estudiantil
- **Sistema de Sugerencias**: Los estudiantes pueden proponer mejoras a la plataforma
- **Votación**: Sistema de apoyo para las sugerencias más populares
- **Implementación Transparente**: Registro de cambios implementados basados en sugerencias

### 📚 Recursos y Estudios
- **Biblioteca de Recursos**: Compartir archivos y enlaces de estudio
- **Comentarios en Recursos**: Discusión comunitaria sobre materiales educativos

### 🛡️ Reportes y Seguridad
- **Sistema de Reportes (ProblemasHH)**: Reportar acoso, seguridad u otros problemas
- **Panel Administrativo**: Gestión de reportes y comunicación con estudiantes
- **Protección de Datos**: PDO para prevenir inyecciones SQL, saneamiento contra XSS

## 📋 Requisitos

- **Servidor Web**: Apache (XAMPP recomendado).
- **PHP**: 7.4 o superior (con extensión PDO habilitada).
- **Base de Datos**: SQLite 3 (integrado en PHP).

## 🛠️ Instalación

1.  **Clonar/Descargar**: Coloca los archivos del proyecto en tu carpeta `htdocs` (ej. `C:\xampp\htdocs\CbBlogs`).
2.  **Inicializar Base de Datos**:
    - Abre tu navegador y visita: `http://localhost/CbBlogs/install.php`
    - Esto creará la base de datos SQLite y las tablas necesarias automáticamente.
3.  **Listo**: Ya puedes registrarte e iniciar sesión.

## 📂 Estructura de Archivos

```
CbBlogs/
├── data/
│   └── init.sql            # Esquema de la base de datos
├── lib/
│   └── common.php          # Funciones comunes y conexión a BD
├── LP.php                  # Panel Principal (Landing Page)
├── login.php               # Inicio de sesión
├── registrar.php           # Registro de nuevos usuarios
├── Write.php               # Editor de blogs
├── Read.php                # Lector de blogs
├── Account-info.php        # Información de cuenta
├── updateAcc.php           # Actualizar perfil
├── install.php             # Script de instalación
└── style.css / *.css       # Hojas de estilo
```

## 🗄️ Base de Datos (SQLite)****

El sistema utiliza SQLite. El esquema principal (`data/init.sql`) incluye:

### Tabla `user`
- `id_usr`: ID único del usuario
- `usuario`: Nombre de usuario (único)
- `nombre`: Nombre completo
- `email`: Correo electrónico
- `clave`: Contraseña (hasheada con password_hash)
- `grade`: Grado/Nivel escolar
- `genero_lit_fav`: Género literario favorito
- `user_contribution`: Puntos de contribución (blogs, comentarios, recursos)

### Tabla `user_blog_style`
- Personalización del blog (fondo, fuentes, tamaños de texto)

### Tabla `admin`
- Gestión de permisos y roles de administración (Nivel 1-3)

### Tabla `post`
- `id`: ID del post
- `title`: Título del artículo
- `subtitle`: Subtítulo
- `author_name`: Autor (vinculado a `user.usuario`)
- `content`: Contenido del blog en HTML
- `tag`: Etiqueta/categoría del post
- `created_at`: Fecha de publicación
- `file_path`: Ruta de imagen adjunta (opcional)

### Tabla `comment`
- Comentarios en los posts con tracking de autor y fecha

### Recursos de Estudio
- `study_resources`: Archivos y enlaces compartidos por la comunidad
- `resource_comments`: Comentarios en recursos educativos

### Democracia y Sugerencias
- `suggestions`: Propuestas de mejora de estudiantes
- `suggestion_supporters`: Votos y apoyo a sugerencias
- `implemented_changes`: Registro histórico de cambios implementados

### Problemas Humanos (Grievances)
- `problemasHH`: Reportes de problemas (acoso, seguridad, infraestructura)
- `grievance_communications`: Sistema de chat entre admin y reportante
- `problemasHH_acciones`: Log detallado de acciones administrativas

### Historial
- `user_contributions`: Registro de toda la actividad y contribuciones del usuario



## 🎯 Uso

1.  **Registro**: Crea una cuenta en `registrar.php`.
2.  **Login**: Inicia sesión en `login.php`.
3.  **Panel Principal**: Desde `LP.php` puedes ver tus estadísticas y navegar.
4.  **Escribir**: Ve a "Escribir Blog" para redactar un nuevo artículo.
5.  **Leer**: Explora los artículos de la comunidad en "Leer Blogs".
6.  **Perfil**: Actualiza tus datos en "Mi Cuenta".

## 🔧 Tecnologías

- **Backend**: PHP 7.4+ (PDO para consultas seguras, SQLite como motor de base de datos)
- **Frontend**: HTML5, CSS3 (Flexbox, Grid, Glassmorphism, Media Queries), JavaScript (ES6+)
- **Base de Datos**: SQLite 3 (sin necesidad de servidor externo)
- **Tipografía**: Google Fonts - Fira Sans, Fira Sans Bold, Fira Code
- **Gestión de Archivos**: Subida y almacenamiento de imágenes para blogs

## 📱 Diseño Responsivo

El sitio está optimizado para diferentes dispositivos:
- **Desktop**: Experiencia completa con hub central y navegación ampliada
- **Tablet/Mobile (≤768px)**: Diseño adaptado con navegación optimizada y elementos ajustados
- **Diseño Vertical-First**: Optimizado para uso en móviles sin sacrificar funcionalidad


---
*Desarrollado para la comunidad del CBTIS 03.*

**No siempre esta actualizado esta descripcion de la tabla
