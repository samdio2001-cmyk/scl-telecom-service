<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fix Contraseña Admin | SCL Telecom</title>
    <style>
        body { font-family: monospace; background: #0d1b4b; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: rgba(255,255,255,.06); border: 1px solid rgba(23,162,184,.3); border-radius: 16px; padding: 2.5rem; max-width: 520px; width: 100%; }
        h2  { color: #ffc107; font-size: 1.3rem; margin-bottom: 1.5rem; }
        p   { color: rgba(255,255,255,.75); font-size: .9rem; line-height: 1.6; margin-bottom: 1rem; }
        .hash { background: rgba(0,0,0,.4); border-radius: 8px; padding: 10px 14px; font-size: .78rem; color: #69f0ae; word-break: break-all; margin: 1rem 0; }
        .ok   { background: rgba(0,200,83,.1); border: 1px solid rgba(0,200,83,.3); border-radius: 10px; padding: 12px 16px; color: #00c853; font-size: .9rem; }
        .err  { background: rgba(255,82,82,.1); border: 1px solid rgba(255,82,82,.3); border-radius: 10px; padding: 12px 16px; color: #ff5252; font-size: .9rem; }
        .btn  { display: inline-block; margin-top: 1.5rem; background: linear-gradient(135deg,#1f3a93,#17a2b8); color:#fff; text-decoration:none; padding: 10px 24px; border-radius: 8px; font-size: .9rem; font-weight: 700; }
        label { display: block; color: rgba(255,255,255,.6); font-size: .82rem; margin-bottom: 6px; }
        input { width: 100%; background: rgba(255,255,255,.06); border: 1.5px solid rgba(255,255,255,.15); border-radius: 8px; color: #fff; padding: 10px 14px; font-size: .95rem; outline: none; box-sizing: border-box; }
        input:focus { border-color: #17a2b8; }
        .submit { margin-top: 1rem; background: #17a2b8; color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-size: .95rem; font-weight: 700; cursor: pointer; width: 100%; }
        .submit:hover { background: #138f9e; }
        hr { border-color: rgba(255,255,255,.08); margin: 1.5rem 0; }
        .warning { font-size: .78rem; color: rgba(255,193,7,.7); margin-top: 1.2rem; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔑 Reparar Contraseña del Admin</h2>

<?php
require_once 'config/db.php';

// ── CASO 1: Formulario para cambiar a cualquier contraseña ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva'])) {
    $nueva    = $_POST['nueva'];
    $confirma = $_POST['confirma'];
    $usuario  = trim($_POST['usuario'] ?? 'admin');

    if (strlen($nueva) < 6) {
        echo '<div class="err">❌ La contraseña debe tener al menos 6 caracteres.</div>';
    } elseif ($nueva !== $confirma) {
        echo '<div class="err">❌ Las contraseñas no coinciden.</div>';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);

        // Verificar si el usuario existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$usuario]);
        $existe = $stmt->fetch();

        if ($existe) {
            // Actualizar hash
            $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = ?")
                ->execute([$hash, $usuario]);
            echo '<div class="ok">✅ ¡Contraseña actualizada correctamente!<br><br>
                  Usuario: <strong>' . htmlspecialchars($usuario) . '</strong><br>
                  Nueva contraseña: <strong>' . htmlspecialchars($nueva) . '</strong>
                  </div>';
            echo '<p class="warning">⚠️ <strong>Elimina este archivo (fix-password.php) del servidor después de usarlo.</strong></p>';
            echo '<a href="admin/login.php" class="btn">Ir al Login →</a>';
        } else {
            // El usuario no existe, crearlo
            $pdo->prepare("INSERT INTO usuarios (username, password, nombre, rol) VALUES (?, ?, 'Administrador', 'admin')")
                ->execute([$usuario, $hash]);
            echo '<div class="ok">✅ Usuario creado exitosamente.<br><br>
                  Usuario: <strong>' . htmlspecialchars($usuario) . '</strong><br>
                  Contraseña: <strong>' . htmlspecialchars($nueva) . '</strong>
                  </div>';
            echo '<a href="admin/login.php" class="btn">Ir al Login →</a>';
        }
    }
    echo '</div></body></html>';
    exit;
}

// ── CASO 2: Verificar el estado actual ──
$stmt = $pdo->query("SELECT id, username, nombre, rol FROM usuarios LIMIT 5");
$users = $stmt->fetchAll();
?>

    <p>Este script corrige el hash de contraseña en la base de datos. Completa el formulario para establecer una nueva contraseña.</p>

    <?php if (!empty($users)): ?>
    <p>Usuarios existentes en el sistema:</p>
    <div class="hash">
        <?php foreach ($users as $u): ?>
        👤 <strong><?= htmlspecialchars($u['username']) ?></strong> — <?= htmlspecialchars($u['nombre']) ?> (<?= $u['rol'] ?>)<br>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="err">⚠️ No hay usuarios en la tabla. Se creará uno nuevo.</div>
    <?php endif; ?>

    <hr>

    <form method="POST" action="fix-password.php">
        <div style="margin-bottom:1rem">
            <label>Nombre de usuario</label>
            <input type="text" name="usuario" value="admin" required>
        </div>
        <div style="margin-bottom:1rem">
            <label>Nueva contraseña <span style="color:rgba(255,255,255,.4)">(mín. 6 caracteres)</span></label>
            <input type="password" name="nueva" placeholder="Tu nueva contraseña" required>
        </div>
        <div style="margin-bottom:1rem">
            <label>Confirmar contraseña</label>
            <input type="password" name="confirma" placeholder="Repite la contraseña" required>
        </div>
        <button type="submit" class="submit">🔐 Guardar Nueva Contraseña</button>
    </form>

    <p class="warning">⚠️ Después de usarlo, elimina este archivo del servidor.</p>
</div>
</body>
</html>
