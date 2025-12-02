PRAGMA foreign_keys = ON;

-- Drop tables in reverse dependency order
-- Dependent tables must be dropped before the tables they reference
DROP TABLE IF EXISTS grievance_communications;
DROP TABLE IF EXISTS problemasHH_acciones;
DROP TABLE IF EXISTS problemasHH;
DROP TABLE IF EXISTS implemented_changes;
DROP TABLE IF EXISTS suggestion_supporters;
DROP TABLE IF EXISTS suggestions;
DROP TABLE IF EXISTS resource_comments;
DROP TABLE IF EXISTS study_resources;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS user_contributions;
DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS post;
DROP TABLE IF EXISTS user_blog_style;
DROP TABLE IF EXISTS user;

-- ================================================
-- CORE USER TABLES
-- ================================================

CREATE TABLE user (
    id_usr INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    usuario TEXT UNIQUE NOT NULL,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    clave TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade INTEGER DEFAULT 1,
    user_contributions INTEGER DEFAULT 0,
    genero_lit_fav TEXT DEFAULT 'General'
);

CREATE TABLE user_blog_style (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    template_name TEXT DEFAULT 'frutiger_aero',
    background_image TEXT,
    font_family TEXT DEFAULT 'Segoe UI, Arial, sans-serif',
    title_size TEXT DEFAULT '2.5rem',
    body_size TEXT DEFAULT '1.1rem',
    text_decoration TEXT DEFAULT 'none',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE
);

-- ================================================
-- ADMIN SYSTEM
-- ================================================

CREATE TABLE admin (
    id_admin INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nivel INTEGER DEFAULT 1 NOT NULL CHECK(nivel IN (1, 2, 3)),
    -- Nivel 1: Ayudante, Nivel 2: Moderador, Nivel 3: Admin
    usuario_id INTEGER UNIQUE NOT NULL,
    asignado_reportes TEXT DEFAULT "none",
    puntos_contribucion INTEGER DEFAULT 0, -- FUTURO: Sistema de puntos
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES user(id_usr) ON DELETE CASCADE
);


CREATE TABLE post (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    subtitle TEXT,
    author_name TEXT NOT NULL,
    content TEXT NOT NULL,
    tag TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path TEXT,
    FOREIGN KEY (author_name) REFERENCES user(usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE comment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id_C TEXT NOT NULL,
    grade INTEGER,
    text TEXT NOT NULL,
    post_id INTEGER,
    FOREIGN KEY (user_id_C) REFERENCES user(usuario) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE
);

-- ================================================
-- STUDY RESOURCES
-- ================================================

CREATE TABLE study_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    subject TEXT NOT NULL, -- Math, Science, History, etc.
    grade INTEGER, -- 1-6 for your grade system
    resource_type TEXT NOT NULL, -- 'text', 'pdf', 'link'
    
    -- Content storage (only one should be filled)
    text_content TEXT, -- For text-based resources
    file_path TEXT, -- For uploaded PDFs
    external_url TEXT, -- For external links
    
    -- Metadata
    uploader_id INTEGER NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Engagement metrics
    view_count INTEGER DEFAULT 0,
    download_count INTEGER DEFAULT 0,
    helpful_votes INTEGER DEFAULT 0,
    
    -- Moderation
    is_approved BOOLEAN DEFAULT 0, -- Needs admin approval
    approved_by INTEGER, -- Which admin approved it
    
    -- Flags for problematic content
    is_flagged BOOLEAN DEFAULT 0,
    flag_reason TEXT,
    
    FOREIGN KEY (uploader_id) REFERENCES user(id_usr) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES user(id_usr) ON DELETE SET NULL
);

CREATE TABLE resource_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resource_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT 0,
    FOREIGN KEY (resource_id) REFERENCES study_resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE
);

-- ================================================
-- DEMOCRATIC FEATURES - SUGGESTIONS
-- ================================================

CREATE TABLE suggestions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    category TEXT NOT NULL, -- 'feature', 'content', 'community', 'technical', 'other'
    
    -- Author info (can be anonymous)
    is_anonymous BOOLEAN DEFAULT 0,
    author_id INTEGER NOT NULL,
    
    -- Status tracking
    status TEXT DEFAULT 'pending', -- 'pending', 'under_review', 'in_progress', 'implemented', 'declined'
    priority TEXT, -- 'low', 'medium', 'high' (set by admin)
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Admin response
    admin_response TEXT,
    responded_by INTEGER, -- Which admin responded
    responded_at TIMESTAMP,
    
    -- Community engagement (visible to all students)
    support_count INTEGER DEFAULT 0, -- How many students support this
    
    FOREIGN KEY (author_id) REFERENCES user(id_usr) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES user(id_usr) ON DELETE SET NULL
);

CREATE TABLE suggestion_supporters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    suggestion_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    supported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (suggestion_id) REFERENCES suggestions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE,
    UNIQUE(suggestion_id, user_id)
);

CREATE TABLE implemented_changes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    suggestion_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    implemented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    implemented_by INTEGER NOT NULL,
    supporter_count INTEGER DEFAULT 0, -- How many students wanted this
    FOREIGN KEY (suggestion_id) REFERENCES suggestions(id) ON DELETE RESTRICT,
    FOREIGN KEY (implemented_by) REFERENCES user(id_usr) ON DELETE SET NULL
);

-- ================================================
-- PROBLEMAS HUMANOS (GRIEVANCE REPORTING SYSTEM)
-- ================================================

CREATE TABLE problemasHH (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Contenido del problema
    subject TEXT NULL,
    description TEXT NOT NULL,
    category TEXT NOT NULL CHECK(category IN ('harassment', 'discrimination', 'safety', 'academic', 'facility', 'other')),
    severity TEXT NOT NULL CHECK(severity IN ('low', 'medium', 'high', 'urgent')),
    
    -- Info del reportero (ANONIMIDAD REAL)
    is_anonimo BOOLEAN DEFAULT 0,
    reporter_id INTEGER NOT NULL,
    reporter_email TEXT,
    
    -- Partes involucradas (opcional - funcionalidad futura)
    involves_student BOOLEAN DEFAULT 0,
    involves_mod BOOLEAN DEFAULT 0,
    involves_infrastructure BOOLEAN DEFAULT 0,
    
    -- Seguimiento de status
    status TEXT DEFAULT 'submitted' CHECK(status IN ('submitted', 'acknowledged', 'investigating', 'resolved', 'closed')),
    is_resolved BOOLEAN DEFAULT 0,
    resumen_resolutorio TEXT,
    
    -- Timestamps
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP,
    resolved_at TIMESTAMP,
    
    -- Asignación de admin
    admin_asignado INTEGER,
    asignado_at TIMESTAMP,
    
    FOREIGN KEY (reporter_id) REFERENCES user(id_usr) ON DELETE CASCADE,
    FOREIGN KEY (admin_asignado) REFERENCES admin(id_admin) ON DELETE SET NULL
);

CREATE TABLE grievance_communications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    problemasHH_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    sender_role TEXT NOT NULL, -- 'student', 'admin'
    message_text TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT 0,
    read_at TIMESTAMP,
    is_archived BOOLEAN DEFAULT 0,
    archived_at TIMESTAMP,
    
    FOREIGN KEY (problemasHH_id) REFERENCES problemasHH(id) ON DELETE RESTRICT,
    FOREIGN KEY (sender_id) REFERENCES user(id_usr) ON DELETE RESTRICT
);

CREATE TABLE problemasHH_acciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    problemasHH_id INTEGER NOT NULL,
    admin_id INTEGER NOT NULL,
    tipo_accion TEXT NOT NULL DEFAULT 'asignado',
    detalles_accion TEXT DEFAULT 'se esta procesando',
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (problemasHH_id) REFERENCES problemasHH(id) ON DELETE RESTRICT,
    FOREIGN KEY (admin_id) REFERENCES admin(id_admin) ON DELETE RESTRICT
);

-- ================================================
-- SITE ACTIVITY TRACKING
-- ================================================

CREATE TABLE user_contributions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    contribution_type TEXT NOT NULL, -- 'blog', 'resource', 'suggestion', 'comment', 'helpful_vote'
    contribution_id INTEGER, -- ID of the thing they contributed
    contribution_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE
);

-- ================================================
-- INDEXES
-- ================================================

-- Admin indices
CREATE INDEX idx_admin_usuario ON admin(usuario_id);
CREATE INDEX idx_admin_nivel ON admin(nivel);

-- Study resources indices
CREATE INDEX idx_resources_subject ON study_resources(subject);
CREATE INDEX idx_resources_grade ON study_resources(grade);
CREATE INDEX idx_resources_approved ON study_resources(is_approved);
CREATE INDEX idx_resources_uploader ON study_resources(uploader_id);

-- Suggestions indices
CREATE INDEX idx_suggestions_status ON suggestions(status);
CREATE INDEX idx_suggestions_author ON suggestions(author_id);

-- ProblemasHH (Grievances) indices
CREATE INDEX idx_problemasHH_status ON problemasHH(status);
CREATE INDEX idx_problemasHH_reporter ON problemasHH(reporter_id);
CREATE INDEX idx_problemasHH_assigned ON problemasHH(admin_asignado);

-- ProblemasHH communications and actions indices
CREATE INDEX idx_communications_problemasHH ON grievance_communications(problemasHH_id);
CREATE INDEX idx_acciones_problemasHH ON problemasHH_acciones(problemasHH_id);
CREATE INDEX idx_acciones_admin ON problemasHH_acciones(admin_id);

-- ================================================
-- INITIAL DATA INSERTION
-- ================================================

-- 1. Insert Users
INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav)
VALUES
    ('Admin', 'Administrador Principal', 'admin@cbblogs.com', 'admin123', CURRENT_TIMESTAMP, 5, 'General'),
    ('TestUser', 'Usuario de Prueba', 'test@cbblogs.com', 'test123', CURRENT_TIMESTAMP, 1, 'Ficción');

INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav, user_contributions)
VALUES
    ('generico', 'Generico', 'generico@cbblogs.com', 'clave', CURRENT_TIMESTAMP, 3, 'General', 100 );

-- 2. Insert Admin (Depends on User)
INSERT INTO admin (usuario_id, nivel, puntos_contribucion)
VALUES (1, 3, 0);

-- 3. Insert Posts (Depends on User)
INSERT INTO post (title, subtitle, author_name, content, created_at)
VALUES (
    'Post de Ejemplo',
    'Bienvenidos al sistema',
    'Admin',
    'Este es un post de prueba del administrador del sistema.',
    CURRENT_TIMESTAMP
);

-- 4. Insert Comments (Depends on User)
INSERT INTO comment (created_at, user_id_C, text)
VALUES (
    CURRENT_TIMESTAMP,
    'Admin',
    'Este es un comentario de prueba del administrador.'
);

-- ================================================
-- ADDITIONAL EXAMPLE DATA - Demonstrating Features
-- ================================================

-- Example Users with Various Contribution Levels
INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav, user_contributions)
VALUES
    -- Beginner user with low contributions
    ('maria_escritora', 'MARÍA GONZÁLEZ', 'maria@cbblogs.com', 'maria123', datetime('2024-01-15 10:30:00'), 2, 'Romance', 25),
    
    -- Intermediate user approaching template unlock
    ('carlos_poeta', 'CARLOS RAMÍREZ', 'carlos@cbblogs.com', 'carlos123', datetime('2024-03-20 14:45:00'), 3, 'Poesía', 45),
    
    -- Premium user with 100+ points (can access WriteWitMedia)
    ('ana_blogger', 'ANA MARTÍNEZ', 'ana@cbblogs.com', 'ana123', datetime('2023-11-05 09:15:00'), 4, 'Ciencia Ficción', 120),
    
    -- Elite user with max contributions
    ('jorge_escritor', 'JORGE LÓPEZ', 'jorge@cbblogs.com', 'jorge123', datetime('2023-08-10 16:20:00'), 5, 'Fantasía', 165);

-- User Blog Style Customizations
INSERT INTO user_blog_style (user_id, template_name, background_image, font_family, title_size, body_size)
VALUES
    (3, 'pink_classic', 'see.jpg', 'Georgia, serif', '2.8rem', '1.2rem'),
    (6, 'frutiger_aero', NULL, 'Segoe UI, Arial, sans-serif', '2.5rem', '1.1rem');

-- Blog Posts from Various Users
INSERT INTO post (title, subtitle, author_name, content, tag, created_at, file_path)
VALUES
    ('El Poder de la Lectura', 
     'Cómo los libros transforman vidas',
     'maria_escritora',
     'La lectura nos transporta a mundos nuevos y nos permite vivir mil vidas diferentes. Cada libro es una aventura que espera ser descubierta...',
     'Romance',
     datetime('2024-11-15 10:00:00'),
     NULL),
    
    ('Versos del Alma', 
     'Poemas sobre la naturaleza humana',
     'carlos_poeta',
     'En el silencio de la noche,
las palabras encuentran su voz,
y el corazón late al ritmo
de versos que nacen del alma...',
     'Poesía',
     datetime('2024-11-20 15:30:00'),
     NULL),
    
    ('Viaje a las Estrellas', 
     'Una reflexión sobre la exploración espacial',
     'ana_blogger',
     'Mirando hacia el cosmos infinito, nos damos cuenta de lo pequeños que somos. La ciencia ficción nos prepara para un futuro entre las estrellas...',
     'Ciencia Ficción',
     datetime('2024-11-25 12:45:00'),
        'img/blog_media/stars_journey.jpg'),
    
    ('Mundos de Fantasía', 
     'La magia en la literatura moderna',
     'jorge_escritor',
     'Los mundos fantásticos nos enseñan más sobre nuestra realidad de lo que imaginamos. La magia existe en cada página que leemos...',
     'Fantasía',
     datetime('2024-12-01 09:20:00'),
     'img/blog_media/fantasy_world.jpg');

-- Comments showcasing community engagement
INSERT INTO comment (created_at, user_id_C, grade, text, post_id)
VALUES
    (datetime('2024-11-15 11:30:00'), 'carlos_poeta', 3, '¡Excelente reflexión! La lectura realmente cambia vidas.', 2),
    (datetime('2024-11-15 14:20:00'), 'ana_blogger', 4, 'Me encantó este artículo. Muy inspirador.', 2),
    (datetime('2024-11-20 16:45:00'), 'maria_escritora', 2, 'Tus poemas siempre me conmueven. ¡Bellísimo!', 3),
    (datetime('2024-11-25 13:30:00'), 'jorge_escritor', 5, 'Como amante de la ciencia ficción, aprecio mucho tu perspectiva.', 4),
    (datetime('2024-12-01 10:15:00'), 'ana_blogger', 4, '¡La fantasía es mi género favorito también! Gran post.', 5),
    (datetime('2024-12-01 18:45:00'), 'Admin', 5, 'Contenido de alta calidad. Sigue así.', 5);

-- Study Resources from different users
INSERT INTO study_resources (title, description, subject, grade, resource_type, text_content, uploader_id, is_approved, approved_by, view_count, helpful_votes)
VALUES
    ('Guía de Gramática Española',
     'Reglas esenciales de gramática para escritores',
     'Español',
     3,
     'text',
     'Esta guía cubre los aspectos fundamentales de la gramática española...',
     5,
     1,
     1,
     45,
     12),
    
    ('Técnicas de Escritura Creativa',
     'Métodos para mejorar tu escritura',
     'Literatura',
     4,
     'text',
     'La escritura creativa requiere práctica y técnica. Aquí aprenderás...',
     6,
     1,
     1,
     78,
     23);

-- Suggestions from the community
INSERT INTO suggestions (title, description, category, is_anonymous, author_id, status, support_count)
VALUES
    ('Agregar Editor de Markdown',
     'Sería útil tener un editor de markdown para formatear mejor los posts.',
     'feature',
     0,
     5,
     'under_review',
     15),
    
    ('Modo Oscuro',
     'Implementar un modo oscuro para la lectura nocturna.',
     'feature',
     0,
     6,
     'pending',
     28);

-- User Contributions tracking
INSERT INTO user_contributions (user_id, contribution_type, contribution_id)
VALUES
    (4, 'blog', 2),
    (5, 'blog', 3),
    (6, 'blog', 4),
    (7, 'blog', 5),
    (4, 'comment', 2),
    (5, 'comment', 3),
    (6, 'comment', 4),
    (7, 'resource', 1),
    (6, 'resource', 2);

