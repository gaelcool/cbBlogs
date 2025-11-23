PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS post;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    id_usr INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario TEXT UNIQUE NOT NULL,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    clave TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade INTEGER DEFAULT 1,
    genero_lit_fav TEXT DEFAULT 'General'
);

CREATE TABLE post (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    subtitle TEXT,
    author_name TEXT,
    content TEXT NOT NULL,
    tag TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_name) REFERENCES user(usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav)
VALUES
    ('Mechy', 'Gael', 'hello@gmail.com', 'password', datetime('2023-04-22'), 3, 'Ficción'),
    ('Jimmy', 'James Rodriguez', 'jimmy@gmail.com', 'password123', datetime('2024-01-15'), 2, 'Ciencia Ficción');


CREATE TABLE comment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id_C TEXT NOT NULL,
    grade INTEGER,
    text TEXT NOT NULL,
    FOREIGN KEY (user_id_C) REFERENCES user(usuario) ON DELETE CASCADE
);

INSERT INTO post (title, subtitle, author_name, content, created_at)
VALUES (
    'TitulO',
    'SubTItuLo',
    'Mechy',
    'Lorem ipsum dolor YMD sit amet, consectetur adipiscing elit...',
    datetime('2023-04-22')
);

INSERT INTO comment (created_at, user_id_C, text)
VALUES (
    datetime('2025-11-25'),
    'Mechy',
    'This is Mechy''s GREAT contribution!!!'
);
