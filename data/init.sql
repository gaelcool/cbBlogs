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

-- Example Users with Various Contribution Levels & Authentic Teen Personalities
INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav, user_contributions)
VALUES
    -- Active creative writers
    ('maria_escritora', 'MARÍA GONZÁLEZ', 'maria@cbblogs.com', 'maria123', datetime('2024-01-15 10:30:00'), 2, 'Romance', 25),
    ('carlos_poeta', 'CARLOS RAMÍREZ', 'carlos@cbblogs.com', 'carlos123', datetime('2024-03-20 14:45:00'), 3, 'Poesía', 45),
    ('ana_blogger', 'ANA MARTÍNEZ', 'ana@cbblogs.com', 'ana123', datetime('2023-11-05 09:15:00'), 4, 'Ciencia Ficción', 120),
    ('jorge_escritor', 'JORGE LÓPEZ', 'jorge@cbblogs.com', 'jorge123', datetime('2023-08-10 16:20:00'), 5, 'Fantasía', 165),
    
    -- Additional diverse users
    ('valeria_tech', 'VALERIA TORRES', 'valeria@cbblogs.com', 'val123', datetime('2024-02-10 14:00:00'), 4, 'Ciencia Ficción', 88),
    ('diego_gamer', 'DIEGO HERNÁNDEZ', 'diego@cbblogs.com', 'diego123', datetime('2024-04-05 16:30:00'), 3, 'Fantasía', 52),
    ('sofia_artista', 'SOFÍA MENDOZA', 'sofia@cbblogs.com', 'sofia123', datetime('2024-01-20 11:15:00'), 5, 'General', 95),
    ('alejandro_code', 'ALEJANDRO ROJAS', 'alex@cbblogs.com', 'alex123', datetime('2023-12-01 09:45:00'), 5, 'No Ficción', 110),
    ('lucia_dreams', 'LUCÍA VARGAS', 'lucia@cbblogs.com', 'lucia123', datetime('2024-03-15 13:20:00'), 2, 'Romance', 38),
    ('ricardo_rebel', 'RICARDO CRUZ', 'ricardo@cbblogs.com', 'ricardo123', datetime('2024-02-28 15:50:00'), 4, 'Misterio', 67);

-- User Blog Style Customizations
INSERT INTO user_blog_style (user_id, template_name, background_image, font_family, title_size, body_size)
VALUES
    (3, 'pink_classic', 'see.jpg', 'Georgia, serif', '2.8rem', '1.2rem'),
    (6, 'frutiger_aero', NULL, 'Segoe UI, Arial, sans-serif', '2.5rem', '1.1rem'),
    (8, 'frutiger_aero', NULL, 'Fira Sans, sans-serif', '2.5rem', '1.1rem');

-- Blog Posts with Authentic Teen Voice & Topics
INSERT INTO post (title, subtitle, author_name, content, tag, created_at, file_path)
VALUES
    ('Sobreviviendo a los Exámenes Finales', 
     'Tips que realmente funcionan',
     'valeria_tech',
     'Todos decimos que esta vez sí vamos a estudiar con tiempo y luego terminamos a las 3 AM tomando café frío rogando que el tema 7 no venga. Pero tengo estrategias que me han salvado. La técnica Pomodoro funciona si eres honesto: 25 minutos de estudio y 5 de descanso. Los grupos de estudio ayudan si no se convierten en sesión de chisme. Una playlist de lo-fi hip hop es clave porque el reggaetón te distrae. Y lo más importante es dormir. En serio. Una vez hice un examen con 2 horas de sueño y confundí la independencia de México con la revolución francesa. Suerte a todos.',
     'General',
     datetime('2024-11-28 22:15:00'),
     NULL),

    ('Por Qué el Anime No Es "Solo Para Niños"',
     'Rant necesario',
     'diego_gamer',
     'Estoy harto de que la gente me diga que ya estoy grande para ver monitos. Primero que nada no son monitos, son obras de arte con narrativas complejas. Death Note es un thriller psicológico mejor escrito que el noventa por ciento de las series para adultos. Attack on Titan tiene crítica social y dilemas morales que te dejan pensando por días. Your Name es un romance que hace llorar más que cualquier película cursi. El problema es que la gente ve un episodio de algo infantil y cree que todo el anime es así. Anime es un medio, no un género. Hay de todo: terror, romance, deportes, cocina. La próxima vez que alguien diga que debería madurar le voy a recordar que el anime trata temas más profundos que sus realities favoritos.',
     'General',
     datetime('2024-11-30 19:45:00'),
     NULL),

    ('La Ansiedad Social Es Real',
     'Y está bien no estar bien',
     'lucia_dreams',
     'A veces siento que soy la única persona en la escuela que se pone nerviosa por todo. Pedir ketchup en la cafetería me da ansiedad. Participar en clase es ansiedad nivel mil. Mandarle mensaje a alguien primero mejor ni lo intento. Y lo peor es cuando la gente te dice que solo seas más segura como si fuera tan fácil. Pero he aprendido que no estoy sola, hay millones de personas que sienten lo mismo. Pequeños pasos cuentan. Hoy participé en clase y casi me muero pero lo hice. Tus amigos reales van a entender. Está bien pedir ayuda. Fui con la psicóloga de la escuela y cambió mi vida. Si te identificas con esto no eres el único weirdo del mundo.',
     'General',
     datetime('2024-11-27 16:30:00'),
     NULL),

    ('Cómo Empecé a Programar (Y Por Qué Deberías Intentarlo)',
     'No necesitas ser un genio',
     'alejandro_code',
     'Hace un año no sabía ni qué era Python y pensaba que era solo la serpiente. Hoy ya hice mi primera app y estoy aprendiendo desarrollo web. Y no soy un genio ni tengo diez en matemáticas. Programar está cool porque es literalmente magia moderna. Le dices a la computadora qué hacer y lo hace. Puedes hacer cualquier cosa: apps, juegos, páginas web, bots. No necesitas equipo caro, una laptop de hace cinco años funciona perfecto. La comunidad es increíble, todos ayudan a todos. Para empezar usa Codecademy o freeCodeCamp. Python primero porque es el más amigable. Haz proyectos tontos, mi primer programa sumaba dos números. No te compares con los niños prodigio de YouTube porque ellos editan cuarenta horas para hacer un video de diez minutos. Si estás aburrido o curioso solo inténtalo.',
     'General',
     datetime('2024-12-01 14:20:00'),
     NULL),

    ('Playlist Para Cuando Todo Es Demasiado',
     'Canciones que me salvan',
     'sofia_artista',
     'Todos tenemos esos días donde todo es demasiado. La escuela, la familia, los amigos, existir en general. Esta es mi playlistde emergencia. Breathe Me de Sia cuando necesito llorar. Holocene de Bon Iver para sentirme pequeña pero en el buen sentido. The Night We Met de Lord Huron debería ser más famosa. Saturn de Sleeping At Last es mi terapia musical. Youth de Daughter es el himno de las chicas tristes. Into The Mystic de Van Morrison es vieja pero me calma siempre. To Build A Home de The Cinematic Orchestra te hace llorar. Skinny Love de Bon Iver la versión original no la cover. Mad World de Gary Jules es triste pero aesthetic. The Night King de Ramin Djawadi es instrumental y te hace sentir epic. Pónganse audífonos y solo sientan. A veces eso es suficiente.',
     'General',
     datetime('2024-11-29 21:00:00'),
     NULL),

    ('El Poder de la Lectura', 
     'Cómo los libros transforman vidas',
     'maria_escritora',
     'La lectura nos transporta a mundos nuevos y nos permite vivir mil vidas diferentes. Cada libro es una aventura que espera ser descubierta. Cuando abrimos un libro no solo leemos palabras sino que vivimos experiencias y conocemos personas aunque sean ficticias. Aprendemos lecciones que ninguna clase podría enseñarnos. Mi libro favorito este año fue Rayuela de Cortázar. Sé que suena pretencioso pero la forma en que juega con la narrativa es genial. Puedes leer los capítulos en orden diferente y la historia cambia. También estoy obsesionada con La casa de los espíritus de Allende. Es realismo mágico increíble. Si no leen mucho empiecen con algo corto. El principito no es solo para niños, tiene filosofía profunda. Leer antes de dormir es mejor que scrollear en TikTok.',
     'Romance',
     datetime('2024-11-15 10:00:00'),
     NULL),

    ('Versos del Alma', 
     'Poemas sobre la naturaleza humana',
     'carlos_poeta',
     'En el silencio de la noche las palabras encuentran su voz y el corazón late al ritmo de versos que nacen del alma. Somos historias sin contar, páginas en blanco esperando que alguien se atreva a escribir la verdad que llevamos dentro. El amor llega en susurros, el dolor grita en silencio, y entre ambos navegamos este mar de sentimientos. Escribo porque callar duele más que las palabras. Porque a veces un poema dice lo que mil conversaciones no pueden. Si alguien más escribe cuando está triste o feliz o confundido déjame leer sus versos.',
     'Poesía',
     datetime('2024-11-20 15:30:00'),
     NULL),

    ('Viaje a las Estrellas', 
     'Una reflexión sobre la exploración espacial',
     'ana_blogger',
     'Mirando hacia el cosmos infinito nos damos cuenta de lo pequeños que somos. La ciencia ficción nos prepara para un futuro entre las estrellas. Estuve viendo videos de SpaceX y quedé obsesionada. Se imaginan ser la generación que va a colonizar Marte. Carl Sagan dijo que somos polvo de estrellas y es literal. Los átomos en nuestro cuerpo se formaron en estrellas que explotaron hace millones de años. Somos literalmente hechos de materia espacial. Mi teoría es que en cincuenta años vamos a tener escuelas en la Luna. The Expanse como serie, Project Hail Mary como libro y Cosmos el documental de Sagan son imprescindibles si les gusta el tema. El espacio es aterrador pero también la cosa más hermosa del universo.',
     'Ciencia Ficción',
     datetime('2024-11-25 12:45:00'),
     NULL),

    ('Mundos de Fantasía', 
     'La magia en la literatura moderna',
     'jorge_escritor',
     'Los mundos fantásticos nos enseñan más sobre nuestra realidad de lo que imaginamos. La magia existe en cada página que leemos. Tolkien, Rowling, Martin y Sanderson todos crearon universos enteros con sus propias reglas, idiomas e historias. Eso es poder puro. Mi objetivo este año es escribir mi propio mundo de fantasía. Ya tengo el mapa dibujado como el nerd que soy, tres sistemas de magia y una profecía vaga que voy a usar como plot device. Si alguien más tiene proyectos de escritura podemos hacer un grupo para compartir ideas y motivarnos.',
     'Fantasía',
     datetime('2024-12-01 09:20:00'),
     'img\council.jpg'),

    ('Hot Takes Que Nadie Pidió',
     'Opiniones impopulares del CBTis',
     'ricardo_rebel',
     'La pizza de la cafetería es mejor que la de cualquier lugar. Sí está grasosa pero eso es parte del encanto. Madrugar para clases no te hace más productivo solo más cansado. Los trabajos en equipo son una tortura social disfrazada de colaboración. Las tablets no reemplazan a los cuadernos porque escribir a mano ayuda a memorizar mejor y está comprobado científicamente. El wifi de la escuela es lento a propósito para que no nos distraigamos.',
     'General',
     datetime('2024-12-02 18:30:00'),
     NULL);

-- Comments showcasing authentic teen community engagement
INSERT INTO comment (created_at, user_id_C, grade, text, post_id)
VALUES
    -- Comments on exam survival post
    (datetime('2024-11-28 22:30:00'), 'diego_gamer', 3, 'BRO el tip del café frío me llamó personalmente 😭', 2),
    (datetime('2024-11-28 23:00:00'), 'lucia_dreams', 2, 'La parte de dormir >>> todo lo demás. Aprendí por las malas', 2),
    (datetime('2024-11-29 08:15:00'), 'alejandro_code', 5, 'Técnica Pomodoro funcionó para pasar Cálculo. 10/10 would recommend', 2),
    
    -- Comments on anime post
    (datetime('2024-11-30 20:00:00'), 'valeria_tech', 4, 'PREACH! Death Note es literalmente un master class de suspenso', 3),
    (datetime('2024-11-30 21:30:00'), 'sofia_artista', 5, 'Your Name me hizo llorar como bebé. No me arrepiento', 3),
    (datetime('2024-12-01 12:00:00'), 'jorge_escritor', 5, 'Attack on Titan > cualquier serie live action. Facts', 3),
    
    -- Comments on anxiety post
    (datetime('2024-11-27 17:00:00'), 'maria_escritora', 2, 'OMG yo pensaba que era la única!! Literal tienes mi vida en este post', 4),
    (datetime('2024-11-27 18:45:00'), 'carlos_poeta', 3, 'Lo de pedir ayuda cambió todo para mí también. No están solos ❤️', 4),
    (datetime('2024-11-28 10:20:00'), 'valeria_tech', 4, 'El sudor en las manos 24/7 es TAN real. Somos legión', 4),
    
    -- Comments on programming post
    (datetime('2024-12-01 15:00:00'), 'diego_gamer', 3, 'Python es GOD. Hice un bot de Discord y me sentí hackerman', 5),
    (datetime('2024-12-01 16:30:00'), 'ricardo_rebel', 4, 'Stack Overflow es mi mejor amigo ahora lol', 5),
    (datetime('2024-12-01 20:00:00'), 'sofia_artista', 5, 'Voy a intentar esto!!! Gracias por los recursos', 5),
    
    -- Comments on playlist post
    (datetime('2024-11-29 21:30:00'), 'lucia_dreams', 2, 'Bon Iver es mi safe place musical fr fr', 6),
    (datetime('2024-11-29 22:15:00'), 'maria_escritora', 2, 'Agregué todas a mi playlist. Gracias  por esto 🥺', 6),
    (datetime('2024-11-30 09:00:00'), 'carlos_poeta', 3, '"Mad World" >>> Mi alma en canción', 6),
    
    -- Original comments
    (datetime('2024-11-15 11:30:00'), 'carlos_poeta', 3, '¡Excelente reflexión! La lectura realmente cambia vidas', 7),
    (datetime('2024-11-15 14:20:00'), 'ana_blogger', 4, 'Me encantó este artículo. Muy inspirador', 7),
    (datetime('2024-11-20 16:45:00'), 'maria_escritora', 2, 'Tus poemas siempre me conmueven. ¡Bellísimo!', 8),
    (datetime('2024-11-25 13:30:00'), 'jorge_escritor', 5, 'Como amante de la ciencia ficción, aprecio mucho tu perspectiva', 9),
    (datetime('2024-12-01 10:15:00'), 'ana_blogger', 4, '¡La fantasía es mi género favorito también! Gran post', 10),
    (datetime('2024-12-01 18:45:00'), 'Admin', 5, 'Contenido de alta calidad. Sigue así', 10),
    
    -- Comments on hot takes
    (datetime('2024-12-02 19:00:00'), 'valeria_tech', 4, 'El #3 es VERDAD ABSOLUTA. Los trabajos en equipo son sufrimiento', 11),
    (datetime('2024-12-02 19:30:00'), 'alejandro_code', 5, 'Hot take: la pizza de la cafetería está sobrevalorada. Lo siento', 11),
    (datetime('2024-12-02 20:00:00'), 'diego_gamer', 3, 'Teoría del wifi lento es canon ahora', 11);

-- Study Resources with teen-relevant content
INSERT INTO study_resources (title, description, subject, grade, resource_type, text_content, external_url, uploader_id, is_approved, approved_by, view_count, helpful_votes)
VALUES
    ('Guía de Gramática Española',
     'Reglas esenciales de gramática para escritores',
     'Español',
     3,
     'text',
     'Esta guía cubre los aspectos fundamentales de la gramática española para que tus textos no parezcan escritos por autocorrector en crisis.

REGLA #1: Acentos existen por una razón
TÚ (pronombre) vs TU (posesivo)
MÁS (cantidad) vs MAS (pero)

REGLA #2: Comas salvan vidas
"Vamos a comer niños" vs "Vamos a comer, niños"

REGLA #3: Haber vs A ver
HABER = verbo ("debe haber comida")
A VER = mirar ("a ver qué pasa")

No confundir o los profes de español llorarán.',
     NULL,
     5,
     1,
     1,
     245,
     67),
    
    ('Técnicas de Escritura Creativa',
     'Métodos para mejorar tu escritura',
     'Literatura',
     4,
     'text',
     'La escritura creativa requiere práctica y técnica. Aquí aprenderás lo esencial:

SHOW, DON''T TELL
Malo: "Ana estaba triste"
Bueno: "Ana miraba la lluvia sin parpadear, sus dedos temblaban al sostener la taza vacía"

LEE MUCHO
No puedes escribir bien sin leer. Es como querer cocinar sin probar comida.

ESCRIBE BASURA PRIMERO
El primer borrador SIEMPRE es horrible. Eso es normal. La magia está en editar.

NO USES ADVERBIOS EN EXCESO
"Dijo rápidamente" < "escupió las palabras"

PRÁCTICA DIARIA
Aunque sean 100 palabras. La constancia > inspiración.',
     NULL,
     6,
     1,
     1,
     198,
     52),

    ('Cómo Estudiar Matemáticas (Sin Llorar)',
     'Estrategias que funcionan para enemigos de los números',
     'Matemáticas',
     3,
     'text',
     'De alguien que batalla con mate desde primaria:

ENTENDER > MEMORIZAR
No te aprendas las fórmulas como si fueran canciones. Entiende POR QUÉ funcionan.

PRÁCTICA, PRÁCTICA, PRÁCTICA  
Hacer 5 ejercicios > ver 10 tutoriales de YouTube

USA RECURSOS VISUALES
Khan Academy es tu amigo
Symbolab para cuando estás desesperado

HAZ EJERCICIOS SIMILARES
Si entendiste un problema, haz 3 más parecidos. Tu cerebro aprende por patrones.

PIDE AYUDA TEMPRANO
No esperes a estar 100% perdido. Si algo no tiene sentido, pregunta YA.',
     NULL,
     8,
     1,
     1,
     312,
     89),

    ('Guía de Supervivencia: Química',
     'No, no vas a explotar el laboratorio (probably)',
     'Química',
     4,
     'text',
     'Química tiene mala reputación pero la realmente no es TAN difícil.

TABLA PERIÓDICA: Memoriza los primeros 20. El resto Google existe.

BALANCEO DE ECUACIONES: Es literalmente álgebra disfrazada. Si puedes con álgebra, puedes con esto.

MOL: El concepto más confuso de la historia. Think of it como "docena" pero para átomos. 1 mol = 6.02 x 10^23 cosas.

ÁCIDOS Y BASES: pH bajo = ácido (limón), pH alto = base (jabón). Easy.

LABORATORIO: Lee las instrucciones DOS veces. Usa lentes SIEMPRE. No mezcles cosas random "para ver qué pasa".

Pro tip: La química orgánica es puro memorizar. Flashcards son tu salvador.',
     NULL,
     11,
     1,
     1,
     267,
     73),

    ('Biblioteca Digital Pirata (Shhh)',
     'Libros gratis porque somos estudiantes pobres',
     'Literatura',
     5,
     'link',
     NULL,
     'https://www.gutenberg.org',
     7,
     0,
     NULL,
     156,
     42);

-- Suggestions from the community with teen perspective
INSERT INTO suggestions (title, description, category, is_anonymous, author_id, status, support_count)
VALUES
    ('Agregar Editor de Markdown',
     'Sería útil tener un editor de markdown para formatear mejor los posts. Negrita, cursiva, listas, todo eso.',
     'feature',
     0,
     5,
     'under_review',
     15),
    
    ('Modo Oscuro',
     'Implementar un modo oscuro para la lectura nocturna. Mis ojos a las 2 AM lo agradecerían.',
     'feature',
     0,
     6,
     'pending',
     34),

    ('Sistema de Tags/Filtros Mejorado',
     'Poder filtrar por múltiples tags al mismo tiempo. Por ejemplo, "Ciencia Ficción" + "Romance".',
     'feature',
     0,
     9,
     'pending',
     22),

    ('Notificaciones de Nuevos Posts',
     'Que nos avise cuando alguien que seguimos sube contenido nuevo. Tipo Instagram pero para blogs.',
     'feature',
     0,
     10,
     'pending',
     28),

    ('Drafts/Borradores Automáticos',
     'Que guarde AUTOMÁTICAMENTE lo que estoy escribiendo cada 30 segundos. Ya perdí 3 posts por cerrar la pestaña sin querer.',
     'feature',
     0,
     12,
     'under_review',
     41),

    ('Reacciones Rápidas',
     'En vez de solo comentar, poder reaccionar con emojis ❤️😂😭 etc. Más rápido para mostrar que leíste algo.',
     'feature',
     0,
     8,
     'pending',
     19);

-- User Contributions tracking
INSERT INTO user_contributions (user_id, contribution_type, contribution_id)
VALUES
    (8, 'blog', 2),
    (9, 'blog', 3),
    (12, 'blog', 4),
    (11, 'blog', 5),
    (10, 'blog', 6),
    (4, 'blog', 7),
    (5, 'blog', 8),
    (6, 'blog', 9),
    (7, 'blog', 10),
    (13, 'blog', 11),
    (9, 'comment', 2),
    (12, 'comment', 3),
    (11, 'comment', 4),
    (5, 'resource', 1),
    (6, 'resource', 2),
    (8, 'resource', 3),
    (11, 'resource', 4),
    (7, 'resource', 5);


