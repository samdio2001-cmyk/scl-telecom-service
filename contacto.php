<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto | SCL TELECOM SERVICES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root { --navy: #0d1b4b; --cyan: #17a2b8; --gold: #ffc107; }

        body { padding-top: 70px; background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }

        .page-hero {
            background: linear-gradient(135deg, #0d1b4b 0%, #1f3a93 60%, #17a2b8 100%);
            padding: 4rem 0 3rem; color: #fff; text-align: center;
        }
        .page-hero h1 { font-size: 2.6rem; font-weight: 700; margin-bottom: .5rem; }
        .page-hero p  { color: rgba(255,255,255,.75); font-size: 1.05rem; }

        .contact-section { padding: 4rem 0; }

        /* Tarjeta del formulario */
        .form-card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 8px 40px rgba(13,27,75,.1);
            padding: 2.5rem;
        }
        .form-card h2 { font-size: 1.5rem; font-weight: 700; color: var(--navy); margin-bottom: 1.5rem; }

        .form-label { font-weight: 600; font-size: .85rem; color: #444; }
        .form-control, .form-select {
            border: 1.5px solid #e0e6f0; border-radius: 10px;
            padding: .75rem 1rem; font-size: .95rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(23,162,184,.12);
        }
        .form-control.is-invalid { border-color: #dc3545; }

        .char-counter { font-size: .75rem; color: #999; text-align: right; margin-top: 4px; }

        .btn-enviar {
            background: linear-gradient(135deg, var(--navy), var(--cyan));
            color: #fff; border: none; border-radius: 50px;
            padding: 13px 40px; font-size: 1rem; font-weight: 700;
            transition: all .3s; width: 100%;
        }
        .btn-enviar:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(23,162,184,.35); color: #fff; }
        .btn-enviar:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* Panel de información de contacto */
        .info-panel {
            background: linear-gradient(135deg, #0d1b4b, #1f3a93);
            border-radius: 20px; padding: 2.5rem; color: #fff; height: 100%;
        }
        .info-panel h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 1.5rem; }
        .info-item {
            display: flex; align-items: flex-start; gap: 14px;
            margin-bottom: 1.5rem; padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .info-item:last-of-type { border-bottom: none; }
        .info-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(255,255,255,.1); display: flex;
            align-items: center; justify-content: center;
            font-size: 1.2rem; color: var(--gold); flex-shrink: 0;
        }
        .info-text strong { display: block; font-size: .9rem; margin-bottom: 2px; }
        .info-text a, .info-text span { font-size: .85rem; color: rgba(255,255,255,.7); text-decoration: none; }
        .info-text a:hover { color: var(--gold); }

        .wa-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: #25d366; color: #fff; border-radius: 50px;
            padding: 12px 24px; font-weight: 700; text-decoration: none;
            transition: all .3s; margin-top: 1.5rem; font-size: .95rem;
        }
        .wa-btn:hover { background: #1ebe57; transform: translateY(-2px); color: #fff;
            box-shadow: 0 8px 20px rgba(37,211,102,.4); }

        /* Alertas */
        .alert-success-custom {
            background: rgba(0,200,83,.08); border: 1.5px solid rgba(0,200,83,.3);
            border-radius: 12px; padding: 1.2rem 1.5rem; color: #00695c;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .alert-error-custom {
            background: rgba(220,53,69,.08); border: 1.5px solid rgba(220,53,69,.3);
            border-radius: 12px; padding: 1.2rem 1.5rem; color: #721c24;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .alert-icon { font-size: 1.3rem; flex-shrink: 0; margin-top: 2px; }

        /* Spinner del botón */
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: 2px; }
    </style>
</head>
<body>

<?php
// ── Incluir conexión DB ──
require_once 'config/db.php';

$exito  = '';
$errores = [];
$form   = ['nombre'=>'','email'=>'','telefono'=>'','servicio'=>'','mensaje'=>''];

// ── Obtener datos de configuración ──
$tel       = config($pdo, 'sitio_telefono', '+507 7589716');
$emailSitio = config($pdo, 'sitio_email',   'info@scltelecomunicaciones.com');
$waNum     = config($pdo, 'sitio_whatsapp', '5077589716');

// ── Lista de servicios para el select ──
$servicios_db = $pdo->query("SELECT titulo FROM servicios WHERE activo = 1 ORDER BY orden")->fetchAll();

// ════════════════════════════════════════
//  PROCESAR FORMULARIO
// ════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Leer y sanear entradas
    $form['nombre']   = htmlspecialchars(trim($_POST['nombre']   ?? ''));
    $form['email']    = htmlspecialchars(trim($_POST['email']    ?? ''));
    $form['telefono'] = htmlspecialchars(trim($_POST['telefono'] ?? ''));
    $form['servicio'] = htmlspecialchars(trim($_POST['servicio'] ?? ''));
    $form['mensaje']  = htmlspecialchars(trim($_POST['mensaje']  ?? ''));

    // 2. Validaciones
    if (strlen($form['nombre']) < 2)
        $errores['nombre'] = 'El nombre debe tener al menos 2 caracteres.';

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL))
        $errores['email'] = 'Ingresa un correo electrónico válido.';

    if (strlen($form['mensaje']) < 10)
        $errores['mensaje'] = 'El mensaje debe tener al menos 10 caracteres.';

    // 3. Guardar en DB si no hay errores
    if (empty($errores)) {
        $stmt = $pdo->prepare("
            INSERT INTO cotizaciones (nombre, email, telefono, servicio, mensaje, ip_origen)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form['nombre'],
            $form['email'],
            $form['telefono'],
            $form['servicio'],
            $form['mensaje'],
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        $exito = "¡Gracias, <strong>{$form['nombre']}</strong>! Tu mensaje fue enviado correctamente. Te contactaremos pronto.";
        $form  = ['nombre'=>'','email'=>'','telefono'=>'','servicio'=>'','mensaje'=>'']; // limpiar
    }
}
?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="SCL.webp" alt="SCL Logo" class="navbar-logo">
            <span class="ms-3">SCL TELECOM SERVICES</span>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="nosotros.php">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="servicios.php">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="diseno-grafico.php">Diseño Gráfico</a></li>
                <li class="nav-item"><a class="nav-link active btn btn-outline-info ms-lg-3 px-3" href="contacto.php">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-envelope-fill me-2" style="color:var(--gold)"></i> Contáctanos</h1>
        <p>Solicita tu cotización gratuita. Te respondemos en menos de 1 hora.</p>
    </div>
</div>

<!-- SECCIÓN PRINCIPAL -->
<section class="contact-section">
    <div class="container">
        <div class="row g-4 align-items-start">

            <!-- ── FORMULARIO ── -->
            <div class="col-lg-7">
                <div class="form-card">
                    <h2><i class="bi bi-send-fill me-2" style="color:var(--cyan)"></i>Enviar Mensaje</h2>

                    <?php if ($exito): ?>
                        <div class="alert-success-custom mb-4">
                            <span class="alert-icon">✅</span>
                            <div><?= $exito ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errores)): ?>
                        <div class="alert-error-custom mb-4">
                            <span class="alert-icon">⚠️</span>
                            <div>
                                <strong>Por favor corrige los siguientes errores:</strong>
                                <ul class="mb-0 mt-1">
                                    <?php foreach ($errores as $e): ?>
                                        <li><?= $e ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contacto.php" id="contactForm" novalidate>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                                    placeholder="Tu nombre" value="<?= $form['nombre'] ?>" required>
                                <?php if (isset($errores['nombre'])): ?>
                                    <div class="invalid-feedback"><?= $errores['nombre'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>"
                                    placeholder="tu@correo.com" value="<?= $form['email'] ?>" required>
                                <?php if (isset($errores['email'])): ?>
                                    <div class="invalid-feedback"><?= $errores['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control"
                                    placeholder="+507 6000-0000" value="<?= $form['telefono'] ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Servicio de interés</label>
                                <select name="servicio" class="form-select">
                                    <option value="">— Selecciona un servicio —</option>
                                    <?php foreach ($servicios_db as $srv): ?>
                                        <option value="<?= htmlspecialchars($srv['titulo']) ?>"
                                            <?= $form['servicio'] === $srv['titulo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($srv['titulo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                                <textarea name="mensaje" id="mensajeTA" rows="5"
                                    class="form-control <?= isset($errores['mensaje']) ? 'is-invalid' : '' ?>"
                                    placeholder="Describe brevemente lo que necesitas..." maxlength="1000"><?= $form['mensaje'] ?></textarea>
                                <div class="char-counter"><span id="charCount">0</span> / 1000</div>
                                <?php if (isset($errores['mensaje'])): ?>
                                    <div class="invalid-feedback"><?= $errores['mensaje'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn-enviar" id="btnEnviar">
                                    <i class="bi bi-send-fill me-2"></i> Enviar Mensaje
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- ── PANEL DE CONTACTO ── -->
            <div class="col-lg-5">
                <div class="info-panel">
                    <h3><i class="bi bi-headset me-2" style="color:var(--gold)"></i>Información de Contacto</h3>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div class="info-text">
                            <strong>Teléfono</strong>
                            <a href="tel:<?= $tel ?>"><?= $tel ?></a>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div class="info-text">
                            <strong>Correo Electrónico</strong>
                            <a href="mailto:<?= $emailSitio ?>"><?= $emailSitio ?></a>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-clock-fill"></i></div>
                        <div class="info-text">
                            <strong>Horario de Atención</strong>
                            <span>Lun–Vie: 8:00 AM – 6:00 PM</span><br>
                            <span>Sáb: 9:00 AM – 2:00 PM</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="info-text">
                            <strong>Ubicación</strong>
                            <span>Panamá, República de Panamá</span>
                        </div>
                    </div>

                    <a href="https://wa.me/<?= $waNum ?>?text=Hola!%20Quisiera%20una%20cotización." target="_blank" class="wa-btn">
                        <i class="bi bi-whatsapp fs-5"></i>
                        Escribir por WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-dark text-white py-4 text-center">
    <p class="mb-1">© 2026 SCL Telecom Service — Todos los derechos reservados</p>
    <small>
        <a href="privacidad.php" class="text-white-50 text-decoration-none me-2">Privacidad</a>
        <a href="terminos.php"   class="text-white-50 text-decoration-none me-2">Términos</a>
        <a href="aviso-legal.php" class="text-white-50 text-decoration-none">Aviso Legal</a>
    </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Contador de caracteres del textarea
    const ta = document.getElementById('mensajeTA');
    const cc = document.getElementById('charCount');
    function updateCount() { cc.textContent = ta.value.length; }
    ta.addEventListener('input', updateCount);
    updateCount();

    // Deshabilitar botón mientras envía (evita doble submit)
    document.getElementById('contactForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    });
</script>
</body>
</html>
