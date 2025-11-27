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
    FOREIGN KEY (author_name) REFERENCES user(usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE comment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id_C TEXT NOT NULL,
    grade INTEGER,
    text TEXT NOT NULL,
    FOREIGN KEY (user_id_C) REFERENCES user(usuario) ON DELETE CASCADE
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
    involves_staff BOOLEAN DEFAULT 0,
    involves_facility BOOLEAN DEFAULT 0,
    
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
    ('Admin', 'Administrador Principal', 'admin@cbblogs.com', 'admin123', CURRENT_TIMESTAMP, 3, 'General'),
    ('TestUser', 'Usuario de Prueba', 'test@cbblogs.com', 'test123', CURRENT_TIMESTAMP, 1, 'Ficción');

INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav)
VALUES
    ('generico', 'Generico', 'generico@cbblogs.com', 'clave', CURRENT_TIMESTAMP, 3, 'General');

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
