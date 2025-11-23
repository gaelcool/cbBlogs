# 📰 CbNoticias - Blog Platform

Una plataforma de blogs moderna con sistema de usuarios, administración y tema pastel rosa.

## 🚀 Características

- **Sistema de Usuarios**: Registro, login y perfiles personalizados
- **Blogs**: Crear, leer y gestionar artículos
- **Administración**: Panel admin para gestionar usuarios y contenido
- **Validaciones**: 5 expresiones regulares para validación de datos
- **Cálculos**: Estadísticas automáticas de palabras, tiempo de lectura y métricas
- **Diseño**: Tema pastel rosa con efectos modernos

## 📋 Requisitos

- PHP 7.4+ con extensión mysqli
- MySQL/MariaDB
- Servidor web (Apache/Nginx) o XAMPP

## 🛠️ Instalación

### 1. Configurar Base de Datos

```sql
-- Ejecutar en MySQL/MariaDB
CREATE DATABASE bd_noticias;
USE bd_noticias;

-- Crear tablas
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo_usuario ENUM('normal', 'admin') DEFAULT 'normal',
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(11) NULL,
    clave VARCHAR(300) NOT NULL,
    genero_lit_fav VARCHAR(50) NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    subtitulo VARCHAR(300) NULL,
    contenido TEXT NOT NULL,
    palabra_count INT NOT NULL,
    tiempo_lectura INT NOT NULL,
    tag VARCHAR(50) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar usuario admin por defecto
INSERT INTO usuarios (usuario, nombre, tipo_usuario, correo, clave, genero_lit_fav) 
VALUES ('admin', 'Administrador', 'admin', 'admin@cbnoticias.com', 'admin123', 'General');
```

### 2. Configurar Archivos

1. Asegúrate de que `conexion.php` apunte a la base de datos correcta
2. Sube todos los archivos al directorio web
3. Asegúrate de que PHP tenga permisos de escritura

### 3. Verificar Instalación

Visita `test-setup.php` para verificar que todo esté configurado correctamente.

## 🎯 Uso

### Para Usuarios Normales
1. **Registro**: `register.html` - Crear cuenta con validaciones
2. **Login**: `login.html` - Iniciar sesión
3. **Panel**: `LP.html` - Acceso a funciones principales
4. **Escribir**: `Write.html` - Crear blogs con estadísticas automáticas
5. **Leer**: `Read.html` - Explorar blogs de otros usuarios
6. **Cuenta**: `Account-info.html` - Gestionar perfil y ver estadísticas

### Para Administradores
1. **Login**: Usar credenciales de admin
2. **Panel Admin**: `admin/crud-users.php` - Gestionar usuarios
3. **Blogs Admin**: `admin/crud-blogs.php` - Moderar contenido

## 🔧 Validaciones Implementadas

### 5 Expresiones Regulares
1. **Nombre**: `/^[A-ZÁÉÍÓÚÑ\s]+$/` - Solo mayúsculas y espacios
2. **Correo**: `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/` - Formato email válido
3. **Usuario**: `/^[a-zA-Z0-9_]{3,20}$/` - 3-20 caracteres alfanuméricos
4. **Contraseña**: `/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/` - Mínimo 6 caracteres con letras y números
5. **Teléfono**: `/^[0-9]{10}$/` - Exactamente 10 dígitos

## 📊 Cálculos Automáticos

- **Conteo de palabras** en blogs
- **Tiempo de lectura** estimado (200 palabras/minuto)
- **Estadísticas de usuario** (días registrado, blogs publicados, promedio)
- **Nivel de escritor** (1-6 basado en actividad)
- **Métricas de contenido** (longitud promedio, frecuencia de publicación)

## 🎨 Diseño

- **Tema**: Pastel rosa con efectos modernos
- **Responsive**: Adaptable a diferentes pantallas
- **Animaciones**: Transiciones suaves y efectos hover
- **UX**: Interfaz intuitiva y fácil de usar

## 📁 Estructura de Archivos

```
CbNoticias/
├── index.html              # Página de bienvenida
├── login.html              # Formulario de login
├── register.html           # Formulario de registro
├── LP.html                 # Panel principal
├── Write.html              # Crear blogs
├── Read.html               # Leer blogs
├── Account-info.html       # Gestión de cuenta
├── admin/
│   ├── crud-users.php      # Administración de usuarios
│   └── crud-blogs.php      # Administración de blogs
├── php/
│   ├── conexion.php        # Conexión a BD
│   ├── registrar.php       # Procesar registro
│   ├── login-process.php   # Procesar login
│   ├── save-blog.php       # Guardar blogs
│   └── update-account.php  # Actualizar cuenta
├── style.css               # Estilos principales
├── setup_database.sql      # Script de BD
├── test-setup.php          # Verificación de instalación
└── README.md              # Este archivo
```

## 🔒 Seguridad

- **Prepared Statements**: Protección contra SQL injection
- **Validación**: Cliente y servidor
- **Sesiones**: Control de acceso por tipo de usuario
- **Sanitización**: Escape de datos de salida

## 🐛 Solución de Problemas

### Error de Conexión a BD
- Verificar credenciales en `conexion.php`
- Asegurar que MySQL esté ejecutándose
- Verificar que la base de datos existe

### Problemas de Validación
- Revisar que JavaScript esté habilitado
- Verificar que los campos cumplan los patrones regex
- Comprobar que no hay caracteres especiales no permitidos

### Errores de Permisos
- Verificar permisos de escritura en directorio
- Asegurar que PHP puede crear sesiones
- Comprobar configuración de servidor web

## 📞 Soporte

Para problemas técnicos, revisa:
1. `test-setup.php` - Verificación automática
2. Logs de error de PHP
3. Consola del navegador para errores JavaScript

## 🎓 Propósito Educativo

Este proyecto demuestra:
- Desarrollo web con PHP puro
- Manejo de bases de datos MySQL
- Validación de formularios
- Diseño responsivo con CSS
- Programación del lado del cliente (JavaScript)
- Arquitectura MVC básica
- Seguridad web básica

---

*Desarrollado con ❤️ para aprendizaje y práctica de desarrollo web*
