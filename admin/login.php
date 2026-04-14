<?php
// ============================================================
//  admin/login.php — Inicio de sesión del panel
// ============================================================
session_start();

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = 1");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // ✅ Login correcto
            $_SESSION['admin_id']     = $usuario['id'];
            $_SESSION['admin_nombre'] = $usuario['nombre'];
            $_SESSION['admin_rol']    = $usuario['rol'];

            // Actualizar último login
            $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")
                ->execute([$usuario['id']]);

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SCL Telecom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #030a1e 0%, #0d1b4b 50%, #17293a 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif; overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(23,162,184,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(23,162,184,.06) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid-move 25s linear infinite;
        }
        @keyframes grid-move { to { background-position: 50px 50px; } }

        .login-card {
            position: relative; z-index: 10;
            width: 100%; max-width: 420px;
            background: rgba(8,18,55,.88);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(23,162,184,.2);
            border-radius: 24px; padding: 2.5rem;
            box-shadow: 0 40px 80px rgba(0,0,0,.5);
        }
        .brand-icon {
            width: 68px; height: 68px; border-radius: 18px;
            background: linear-gradient(135deg, #1f3a93, #17a2b8);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1rem; color: #fff;
            box-shadow: 0 12px 30px rgba(23,162,184,.3);
        }
        .brand-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.6rem; font-weight: 700; color: #fff;
            letter-spacing: 2px; text-align: center; margin-bottom: 4px;
        }
        .brand-sub { text-align: center; color: rgba(255,255,255,.4); font-size: .82rem; margin-bottom: 2rem; }

        .form-label { color: rgba(255,255,255,.65); font-size: .82rem; font-weight: 600; letter-spacing: .5px; }

        .input-group-custom { position: relative; margin-bottom: 1.2rem; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,.3); font-size: 1rem; z-index: 2;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,.05);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 10px; color: #fff;
            padding: 12px 14px 12px 42px;
            font-size: .95rem; font-family: inherit;
            transition: border-color .2s, box-shadow .2s; outline: none;
        }
        .form-input:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 3px rgba(23,162,184,.15);
            background: rgba(255,255,255,.08);
        }
        .form-input::placeholder { color: rgba(255,255,255,.22); }

        .toggle-pass {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,.35);
            cursor: pointer; font-size: 1rem; transition: color .2s;
        }
        .toggle-pass:hover { color: #17a2b8; }

        .alert-error {
            background: rgba(220,53,69,.12); border: 1px solid rgba(220,53,69,.35);
            border-radius: 10px; color: #ff8a80; font-size: .85rem;
            padding: 10px 14px; margin-bottom: 1.2rem;
            display: flex; align-items: center; gap: 8px;
        }

        .btn-login {
            width: 100%; padding: 13px; border: none; border-radius: 10px;
            background: linear-gradient(135deg, #1f3a93, #17a2b8);
            color: #fff; font-size: 1rem; font-weight: 700;
            font-family: 'Rajdhani', sans-serif; letter-spacing: 2px;
            text-transform: uppercase; cursor: pointer; transition: all .3s;
            position: relative; overflow: hidden;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(23,162,184,.4); }

        .demo-box {
            background: rgba(23,162,184,.08); border: 1px dashed rgba(23,162,184,.3);
            border-radius: 10px; padding: 12px 16px; margin-top: 1.5rem;
            font-size: .8rem; color: rgba(255,255,255,.4); text-align: center;
        }
        .demo-box strong { color: #17a2b8; }
        .back-link { display: block; text-align: center; margin-top: 1.2rem; font-size: .82rem; }
        .back-link a { color: rgba(255,255,255,.4); text-decoration: none; transition: color .2s; }
        .back-link a:hover { color: #ffc107; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-icon"><i class="bi bi-broadcast-pin"></i></div>
    <div class="brand-title">SCL TELECOM</div>
    <div class="brand-sub">Panel de Administración</div>

    <?php if ($error): ?>
        <div class="alert-error"><i class="bi bi-exclamation-triangle-fill"></i><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label class="form-label">Usuario</label>
        <div class="input-group-custom">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="username" class="form-input" placeholder="Tu usuario"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
        </div>

        <label class="form-label">Contraseña</label>
        <div class="input-group-custom">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" id="passInput" class="form-input" placeholder="••••••••" required>
            <button type="button" class="toggle-pass" onclick="togglePass()">
                <i class="bi bi-eye" id="passEye"></i>
            </button>
        </div>

        <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right me-2"></i>Ingresar</button>
    </form>

    <div class="demo-box">
        Demo: <strong>admin</strong> / <strong>scl2026</strong>
    </div>
    <div class="back-link"><a href="../index.php"><i class="bi bi-arrow-left me-1"></i>Volver al sitio</a></div>
</div>
<script>
function togglePass() {
    const inp = document.getElementById('passInput');
    const eye = document.getElementById('passEye');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
