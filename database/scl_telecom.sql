-- ============================================================
--  SCL TELECOM SERVICES — Base de Datos Completa
--  Compatible con MySQL 5.7+ / MariaDB
--  Importar desde phpMyAdmin o terminal de XAMPP
-- ============================================================

CREATE DATABASE IF NOT EXISTS scl_telecom
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE scl_telecom;

-- ─────────────────────────────────────────
--  TABLA: usuarios (panel admin)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,          -- password_hash()
    nombre     VARCHAR(100) NOT NULL,
    email      VARCHAR(100),
    rol        ENUM('admin','editor') DEFAULT 'editor',
    activo     TINYINT(1) DEFAULT 1,
    ultimo_login DATETIME NULL,
    creado     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuario admin por defecto: admin / scl2026
INSERT INTO usuarios (username, password, nombre, email, rol) VALUES
('admin',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- scl2026
 'Administrador SCL',
 'admin@scltelecomunicaciones.com',
 'admin');

-- ─────────────────────────────────────────
--  TABLA: cotizaciones (formulario contacto)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cotizaciones (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    telefono   VARCHAR(25)  DEFAULT '',
    servicio   VARCHAR(100) DEFAULT '',
    mensaje    TEXT         NOT NULL,
    estado     ENUM('pendiente','atendido','cerrado') DEFAULT 'pendiente',
    ip_origen  VARCHAR(45)  DEFAULT '',
    fecha      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Datos de ejemplo
INSERT INTO cotizaciones (nombre, email, telefono, servicio, mensaje, estado) VALUES
('Empresa XYZ',    'xyz@empresa.com',   '+507 6123-4567', 'Redes y Fibra Óptica', 'Necesitamos instalar fibra óptica en nuestras 3 sucursales.', 'atendido'),
('Pyme Local S.A.','pyme@local.com',    '+507 6234-5678', 'Soporte Tecnológico',  'Tenemos 10 computadoras que necesitan mantenimiento preventivo.', 'pendiente'),
('Negocio Local',  'negocio@local.com', '+507 6345-6789', 'Diseño Gráfico',       'Queremos diseño de valla publicitaria y logo renovado.', 'pendiente'),
('Tech Corp',      'tech@corp.com',     '+507 6456-7890', 'Cámaras CCTV',         'Instalación de 8 cámaras IP en nuestras instalaciones.', 'cerrado'),
('Retail Store',   'retail@store.com',  '+507 6567-8901', 'Soporte 24/7',         'Necesitamos soporte remoto mensual para nuestros sistemas.', 'atendido');

-- ─────────────────────────────────────────
--  TABLA: servicios (catálogo del sitio)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS servicios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150) NOT NULL,
    descripcion TEXT,
    icono       VARCHAR(60)  DEFAULT 'bi-gear',
    color       VARCHAR(30)  DEFAULT 'info',
    activo      TINYINT(1)   DEFAULT 1,
    orden       INT          DEFAULT 0,
    creado      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO servicios (titulo, descripcion, icono, color, activo, orden) VALUES
('Soporte Tecnológico',    'Venta, reparación y mantenimiento de computadoras, impresoras y periféricos con garantía.',    'bi-laptop',        'info',    1, 1),
('Redes y Fibra Óptica',   'Instalación de redes de alta velocidad, cableado estructurado y conectividad empresarial.',    'bi-diagram-3',     'success', 1, 2),
('Diseño Gráfico',         'Vallas publicitarias, banners, logotipos y branding profesional para tu marca.',               'bi-palette',       'danger',  1, 3),
('Cámaras de Seguridad',   'Instalación de sistemas CCTV e IP con acceso remoto y monitoreo 24/7.',                       'bi-camera-video',  'warning', 1, 4),
('Nube y Backup',          'Configuración de Google Workspace, Microsoft 365 y soluciones de respaldo automático.',       'bi-cloud-arrow-up','primary', 1, 5),
('Mantenimiento Impresoras','Limpieza, cambio de consumibles y reparación de impresoras láser e inyección de tinta.',     'bi-printer',       'secondary',1,6);

-- ─────────────────────────────────────────
--  TABLA: testimonios
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    cliente    VARCHAR(100) NOT NULL,
    empresa    VARCHAR(100) DEFAULT '',
    rol        VARCHAR(100) DEFAULT '',
    comentario TEXT         NOT NULL,
    estrellas  TINYINT      DEFAULT 5,
    activo     TINYINT(1)   DEFAULT 1,
    creado     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO testimonios (cliente, empresa, rol, comentario, estrellas) VALUES
('Carlos M.',   'Empresa XYZ',    'Gerente de TI',       'Excelente servicio. Instalaron nuestra red de fibra óptica sin inconvenientes y en tiempo récord.', 5),
('Ana R.',      'Pyme Local S.A.','Administradora',      'Rápidos, confiables y con precios justos. Resolvieron nuestro problema de conectividad en horas.',   5),
('Pedro G.',    'Negocio Local',  'Dueño',               'El diseño gráfico superó todas nuestras expectativas. Las vallas publicitarias quedaron increíbles.', 5);

-- ─────────────────────────────────────────
--  TABLA: configuracion (ajustes del sitio)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS configuracion (
    clave      VARCHAR(80) PRIMARY KEY,
    valor      TEXT,
    descripcion VARCHAR(200)
) ENGINE=InnoDB;

INSERT INTO configuracion (clave, valor, descripcion) VALUES
('sitio_nombre',    'SCL Telecom Services',                    'Nombre del sitio'),
('sitio_telefono',  '+507 7589716',                            'Teléfono principal'),
('sitio_email',     'info@scltelecomunicaciones.com',           'Email de contacto'),
('sitio_instagram', 'https://www.instagram.com/alci_lopez_',   'URL de Instagram'),
('sitio_whatsapp',  '5077589716',                              'Número de WhatsApp (sin +)'),
('email_notif',     'admin@scltelecomunicaciones.com',          'Email para recibir notificaciones'),
('mantenimiento',   '0',                                        '1 = sitio en mantenimiento');
