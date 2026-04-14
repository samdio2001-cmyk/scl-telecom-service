<?php
// ============================================================
//  config/db.php — Conexión a MySQL para XAMPP
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP usa "root" sin contraseña por defecto
define('DB_PASS', '');            // Deja vacío en XAMPP local; pon tu clave en producción
define('DB_NAME', 'scl_telecom');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En producción nunca muestres el error real
    error_log("DB Error: " . $e->getMessage());
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// ── Función auxiliar: obtener valor de configuración ──
function config(PDO $pdo, string $clave, string $default = ''): string {
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->execute([$clave]);
    $row = $stmt->fetch();
    return $row ? $row['valor'] : $default;
}
