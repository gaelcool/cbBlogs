#  CbBlogs - Plataforma de Blogs Escolar

Una plataforma de blogs moderna y colaborativa diseñada para la comunidad escolar (CBTIS 03), con un sistema de usuarios, publicación de artículos y un diseño visual atractivo estilo "Frutiger Aero" / Pastel.

##  Características

- **Sistema de Usuarios**: Registro, inicio de sesión y gestión de perfiles.
- **Blogs**: Crear, leer y explorar artículos de otros compañeros.
- **Comunidad**: Sistema de comentarios y perfiles de usuario.
- **Diseño**: Interfaz moderna con efectos de vidrio (Glassmorphism) y paleta de colores nostalgicos.
- **Estadísticas**: Visualización de blogs totales por usuario.
- **Seguridad**: Protección basica contra inyecciones SQL (PDO) y XSS.

##  Requisitos

- **Servidor Web**: Apache (XAMPP recomendado).
- **PHP**: 7.4 o superior (con extensión PDO habilitada).
- **Base de Datos**: SQLite 3 (integrado en PHP).

##  Instalación

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

## Database (SQLite)****

El sistema utiliza SQLite. El esquema principal (`data/init.sql`) incluye:

### Tabla `user`
- `id_usr`: ID único.
- `usuario`: Nombre de usuario (único).
- `nombre`: Nombre completo.
- `email`: Correo electrónico.
- `clave`: Contraseña (hasheada).
- `grade`: Grado/Nivel.
- `genero_lit_fav`: Género literario favorito.

### Tabla `post`
- `id`: ID del post.
- `title`: Título del artículo.
- `subtitle`: Subtítulo.
- `author_name`: Autor (vinculado a `user.usuario`).
- `content`: Contenido del blog.
- `created_at`: Fecha de publicación.

### Tabla `comment`
- `id`: ID del comentario.
- `text`: Contenido del comentario.
- `user_id_C`: Autor del comentario.


## Usage/uso

1.  **Registro**: Crea una cuenta en `registrar.php`.
2.  **Login**: Inicia sesión en `login.php`.
3.  **Panel Principal**: Desde `LP.php` puedes ver tus estadísticas y navegar.
4.  **Escribir**: Ve a "Escribir Blog" para redactar un nuevo artículo.
5.  **Leer**: Explora los artículos de la comunidad en "Leer Blogs".
6.  **Perfil**: Actualiza tus datos en "Mi Cuenta".

##  Tecnologías

- **Backend**: PHP (PDO, SQLite).
- **Frontend**: HTML5, CSS3 (Flexbox, Grid, Glassmorphism), JavaScript.
- **Base de Datos**: SQLite.

---
*Desarrollado para la comunidad del CBTIS 03.*

**No siempre esta actualizado esta descripcion de la tabla
