CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','empleado') DEFAULT 'empleado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE fichajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME NULL,
    hora_salida TIME NULL,

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
    ON DELETE CASCADE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','empleado') DEFAULT 'empleado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE fichajes;

CREATE TABLE fichajes (

    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL,

    fecha DATE NOT NULL,

    hora_entrada TIME NOT NULL,

    inicio_descanso TIME NOT NULL,

    fin_descanso TIME NOT NULL,

    hora_salida TIME NOT NULL,

    total_horas DECIMAL(4,2) DEFAULT 9.00,

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
    ON DELETE CASCADE
);

CREATE TABLE empresas (

    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(255) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

ALTER TABLE usuarios

ADD empresa_id INT NULL;

ALTER TABLE usuarios

ADD CONSTRAINT fk_empresa

FOREIGN KEY (empresa_id)

REFERENCES empresas(id)

ON DELETE SET NULL;

ALTER TABLE usuarios
MODIFY rol ENUM(
    'admin',
    'encargado',
    'empleado'
);
