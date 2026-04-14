<?php
// ============================================================
//  admin/dashboard.php — Panel de Administración
// ============================================================
session_start();

// Proteger: solo usuarios autenticados
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

// ── Cambiar estado de cotización (acción rápida) ──
if (isset($_GET['accion'], $_GET['id'])) {
    $id     = (int)$_GET['id'];
    $accion = $_GET['accion'];
    $estados = ['pendiente', 'atendido', 'cerrado'];
    if (in_array($accion, $estados)) {
        $pdo->prepare("UPDATE cotizaciones SET estado = ? WHERE id = ?")
            ->execute([$accion, $id]);
    }
    header('Location: dashboard.php?pagina=' . ($_GET['pagina'] ?? 'cotizaciones'));
    exit;
}

// ── Eliminar cotización ──
if (isset($_GET['eliminar'])) {
    $pdo->prepare("DELETE FROM cotizaciones WHERE id = ?")
        ->execute([(int)$_GET['eliminar']]);
    header('Location: dashboard.php?pagina=cotizaciones');
    exit;
}

// ── Guardar configuración ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_config'])) {
    $campos = ['sitio_nombre','sitio_telefono','sitio_email','sitio_whatsapp'];
    foreach ($campos as $c) {
        if (isset($_POST[$c])) {
            $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?")
                ->execute([htmlspecialchars(trim($_POST[$c])), $c]);
        }
    }
    $msg_ok = 'Configuración guardada correctamente.';
}

// ── Estadísticas ──
$total_cotiz   = $pdo->query("SELECT COUNT(*) FROM cotizaciones")->fetchColumn();
$pendientes    = $pdo->query("SELECT COUNT(*) FROM cotizaciones WHERE estado='pendiente'")->fetchColumn();
$atendidos     = $pdo->query("SELECT COUNT(*) FROM cotizaciones WHERE estado='atendido'")->fetchColumn();
$total_serv    = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo=1")->fetchColumn();

// ── Cotizaciones recientes (10 más nuevas) ──
$cotizaciones  = $pdo->query("SELECT * FROM cotizaciones ORDER BY fecha DESC LIMIT 50")->fetchAll();

// ── Configuración ──
$configs = [];
foreach ($pdo->query("SELECT clave, valor FROM configuracion") as $row) {
    $configs[$row['clave']] = $row['valor'];
}

$pagina = $_GET['pagina'] ?? 'dashboard';
$nombre_admin = $_SESSION['admin_nombre'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | SCL Telecom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar:  #060d2a;
            --panel-bg: #0a1230;
            --card-bg:  rgba(255,255,255,.04);
            --border:   rgba(255,255,255,.07);
            --cyan:     #17a2b8;
            --gold:     #ffc107;
            --success:  #00c853;
            --danger:   #ff5252;
            --sw: 250px;
            --text:     #e0e6ff;
            --muted:    rgba(255,255,255,.4);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--panel-bg); color: var(--text); display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sw); background: var(--sidebar);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; height: 100vh; z-index: 200;
            transition: transform .3s;
        }
        .sidebar-header {
            padding: 1.4rem 1.2rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .sb-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg,#1f3a93,var(--cyan));
            display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
        }
        .sb-brand { font-family: 'Rajdhani',sans-serif; font-size: .95rem; font-weight: 700; letter-spacing: 1px; color:#fff; }
        .sb-sub   { font-size: .65rem; color: var(--cyan); letter-spacing: 2px; }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: .8rem 0; }
        .nav-section { padding: .5rem 1rem; font-size: .62rem; font-weight: 700; letter-spacing: 3px; color: rgba(255,255,255,.22); text-transform: uppercase; margin-top: .4rem; }

        .sb-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 1.2rem; color: rgba(255,255,255,.5);
            text-decoration: none; font-size: .88rem;
            border-left: 3px solid transparent; transition: all .2s;
        }
        .sb-link:hover, .sb-link.active {
            color: #fff; background: rgba(255,255,255,.05);
            border-left-color: var(--cyan);
        }
        .sb-link.active { color: var(--cyan); }
        .sb-link i { width: 20px; font-size: 1rem; }
        .sb-badge { margin-left: auto; background: var(--danger); color:#fff; font-size:.62rem; font-weight:700; padding:2px 7px; border-radius:10px; }

        .sidebar-footer {
            padding: 1rem 1.2rem; border-top: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .user-av { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,#1f3a93,var(--cyan)); display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0; }
        .user-name { font-size:.84rem; font-weight:600; color:#fff; }
        .user-role { font-size:.68rem; color:var(--cyan); }
        .logout-btn { margin-left:auto; background:none; border:none; color:var(--muted); cursor:pointer; font-size:1rem; transition:color .2s; padding:4px; }
        .logout-btn:hover { color:var(--danger); }

        /* ── MAIN ── */
        .main { margin-left: var(--sw); flex: 1; display: flex; flex-direction: column; }

        .topbar {
            height: 58px; background: rgba(6,13,42,.85);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
            padding: 0 1.5rem; position: sticky; top: 0; z-index: 100;
            backdrop-filter: blur(10px);
        }
        .topbar-toggle { display:none; background:none; border:1px solid var(--border); border-radius:8px; color:#fff; padding:5px 9px; cursor:pointer; }
        .topbar-title { font-family:'Rajdhani',sans-serif; font-size:1.15rem; font-weight:700; color:#fff; }
        .topbar-right { margin-left:auto; display:flex; align-items:center; gap:10px; }
        .topbar-time { font-size:.8rem; color:var(--muted); }

        /* ── CONTENIDO ── */
        .content { padding: 1.5rem; flex: 1; }

        /* Métricas */
        .metrics { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .metric {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.3rem; position: relative; overflow: hidden;
            transition: transform .2s;
        }
        .metric:hover { transform: translateY(-3px); }
        .metric::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
        .m-blue::before   { background: linear-gradient(90deg,#1f3a93,var(--cyan)); }
        .m-gold::before   { background: linear-gradient(90deg,#e65100,var(--gold)); }
        .m-green::before  { background: linear-gradient(90deg,#00695c,var(--success)); }
        .m-red::before    { background: linear-gradient(90deg,#b71c1c,var(--danger)); }

        .metric-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.8rem; }
        .metric-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
        .metric-val  { font-family:'Rajdhani',sans-serif; font-size:2rem; font-weight:700; color:#fff; line-height:1; }
        .metric-lbl  { font-size:.78rem; color:var(--muted); margin-top:4px; }

        /* Tarjetas de panel */
        .panel-card { background: var(--card-bg); border:1px solid var(--border); border-radius:14px; overflow:hidden; margin-bottom:1.5rem; }
        .panel-card-head {
            padding: .9rem 1.3rem; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .panel-card-head h3 { font-family:'Rajdhani',sans-serif; font-size:1rem; font-weight:700; color:#fff; margin:0; }

        /* Tabla */
        .data-table { width:100%; border-collapse:collapse; font-size:.85rem; }
        .data-table th { padding:9px 1.2rem; text-align:left; color:var(--muted); font-size:.72rem; letter-spacing:1px; text-transform:uppercase; border-bottom:1px solid var(--border); background:rgba(255,255,255,.02); }
        .data-table td { padding:11px 1.2rem; border-bottom:1px solid rgba(255,255,255,.03); color:rgba(255,255,255,.78); vertical-align:middle; }
        .data-table tr:last-child td { border-bottom:none; }
        .data-table tr:hover td { background:rgba(255,255,255,.025); }

        .badge-estado {
            display:inline-flex; align-items:center; gap:5px;
            font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:20px;
        }
        .b-pendiente { background:rgba(255,193,7,.15); color:var(--gold); }
        .b-atendido  { background:rgba(0,200,83,.15);  color:var(--success); }
        .b-cerrado   { background:rgba(255,82,82,.15);  color:var(--danger); }

        .action-btn {
            padding:3px 9px; border-radius:6px; border:none; font-size:.75rem;
            cursor:pointer; text-decoration:none; transition:all .2s;
            display:inline-flex; align-items:center; gap:4px;
        }
        .ab-cyan   { background:rgba(23,162,184,.15); color:var(--cyan); }
        .ab-green  { background:rgba(0,200,83,.15);  color:var(--success); }
        .ab-gold   { background:rgba(255,193,7,.15); color:var(--gold); }
        .ab-red    { background:rgba(255,82,82,.15); color:var(--danger); }
        .action-btn:hover { filter:brightness(1.3); }

        /* Formulario de config */
        .cfg-label { color:rgba(255,255,255,.6); font-size:.82rem; font-weight:600; letter-spacing:.4px; margin-bottom:5px; }
        .cfg-input {
            width:100%; background:rgba(255,255,255,.05); border:1.5px solid var(--border);
            border-radius:10px; color:#fff; padding:10px 14px; font-size:.9rem;
            transition:border-color .2s; outline:none;
        }
        .cfg-input:focus { border-color:var(--cyan); box-shadow:0 0 0 3px rgba(23,162,184,.12); }
        .btn-save {
            background:linear-gradient(135deg,#1f3a93,var(--cyan)); border:none; color:#fff;
            padding:10px 26px; border-radius:10px; font-family:'Rajdhani',sans-serif;
            font-size:1rem; font-weight:700; cursor:pointer; transition:all .3s;
        }
        .btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(23,162,184,.3); }

        .alert-ok { background:rgba(0,200,83,.1); border:1px solid rgba(0,200,83,.3); border-radius:10px; color:#00c853; padding:10px 16px; margin-bottom:1rem; font-size:.88rem; }

        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); box-shadow:10px 0 40px rgba(0,0,0,.5); }
            .main { margin-left:0; }
            .topbar-toggle { display:flex; }
            .metrics { grid-template-columns:1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sb-icon"><i class="bi bi-broadcast-pin text-white"></i></div>
        <div><div class="sb-brand">SCL TELECOM</div><div class="sb-sub">ADMIN PANEL</div></div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="?pagina=dashboard"     class="sb-link <?= $pagina==='dashboard'?'active':'' ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>

        <div class="nav-section">Gestión</div>
        <a href="?pagina=cotizaciones"  class="sb-link <?= $pagina==='cotizaciones'?'active':'' ?>">
            <i class="bi bi-file-earmark-text-fill"></i> Cotizaciones
            <?php if ($pendientes > 0): ?><span class="sb-badge"><?= $pendientes ?></span><?php endif; ?>
        </a>
        <a href="?pagina=servicios"     class="sb-link <?= $pagina==='servicios'?'active':'' ?>"><i class="bi bi-tools"></i> Servicios</a>

        <div class="nav-section">Sistema</div>
        <a href="?pagina=configuracion" class="sb-link <?= $pagina==='configuracion'?'active':'' ?>"><i class="bi bi-gear-fill"></i> Configuración</a>
        <a href="../index.php"          class="sb-link" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Sitio Web</a>
    </div>

    <div class="sidebar-footer">
        <div class="user-av"><i class="bi bi-person"></i></div>
        <div><div class="user-name"><?= htmlspecialchars($nombre_admin) ?></div><div class="user-role"><?= $_SESSION['admin_rol'] ?></div></div>
        <a href="logout.php" class="logout-btn" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>

<!-- ── MAIN ── -->
<div class="main">

    <!-- Topbar -->
    <header class="topbar">
        <button class="topbar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="bi bi-list" style="font-size:1.2rem"></i>
        </button>
        <div class="topbar-title" id="topbarTitle">
            <?php $titulos=['dashboard'=>'Dashboard','cotizaciones'=>'Cotizaciones','servicios'=>'Servicios','configuracion'=>'Configuración']; echo $titulos[$pagina] ?? 'Panel'; ?>
        </div>
        <div class="topbar-right">
            <span class="topbar-time" id="topbarClock"></span>
            <a href="logout.php" style="color:var(--muted); text-decoration:none; font-size:.85rem;"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
        </div>
    </header>

    <div class="content">

    <!-- ════════ DASHBOARD ════════ -->
    <?php if ($pagina === 'dashboard'): ?>

        <!-- Métricas -->
        <div class="metrics">
            <div class="metric m-blue">
                <div class="metric-head">
                    <div class="metric-icon" style="background:rgba(23,162,184,.15)"><i class="bi bi-file-earmark-text" style="color:var(--cyan)"></i></div>
                </div>
                <div class="metric-val"><?= $total_cotiz ?></div>
                <div class="metric-lbl">Total Cotizaciones</div>
            </div>
            <div class="metric m-gold">
                <div class="metric-head">
                    <div class="metric-icon" style="background:rgba(255,193,7,.15)"><i class="bi bi-hourglass-split" style="color:var(--gold)"></i></div>
                </div>
                <div class="metric-val"><?= $pendientes ?></div>
                <div class="metric-lbl">Pendientes</div>
            </div>
            <div class="metric m-green">
                <div class="metric-head">
                    <div class="metric-icon" style="background:rgba(0,200,83,.15)"><i class="bi bi-check-circle-fill" style="color:var(--success)"></i></div>
                </div>
                <div class="metric-val"><?= $atendidos ?></div>
                <div class="metric-lbl">Atendidos</div>
            </div>
            <div class="metric m-red">
                <div class="metric-head">
                    <div class="metric-icon" style="background:rgba(23,162,184,.1)"><i class="bi bi-tools" style="color:var(--cyan)"></i></div>
                </div>
                <div class="metric-val"><?= $total_serv ?></div>
                <div class="metric-lbl">Servicios Activos</div>
            </div>
        </div>

        <!-- Últimas cotizaciones -->
        <div class="panel-card">
            <div class="panel-card-head">
                <h3><i class="bi bi-clock-history me-2" style="color:var(--cyan)"></i>Últimas Cotizaciones</h3>
                <a href="?pagina=cotizaciones" class="action-btn ab-cyan">Ver todas</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Cliente</th><th>Servicio</th><th>Fecha</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($cotizaciones, 0, 6) as $c): ?>
                <tr>
                    <td><strong style="color:#fff"><?= htmlspecialchars($c['nombre']) ?></strong><br><small style="color:var(--muted)"><?= htmlspecialchars($c['email']) ?></small></td>
                    <td><?= htmlspecialchars($c['servicio'] ?: '—') ?></td>
                    <td style="color:var(--muted)"><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
                    <td>
                        <?php $cls=['pendiente'=>'b-pendiente','atendido'=>'b-atendido','cerrado'=>'b-cerrado']; ?>
                        <span class="badge-estado <?= $cls[$c['estado']] ?>"><?= ucfirst($c['estado']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <!-- ════════ COTIZACIONES ════════ -->
    <?php elseif ($pagina === 'cotizaciones'): ?>
        <div class="panel-card">
            <div class="panel-card-head">
                <h3><i class="bi bi-file-earmark-text-fill me-2" style="color:var(--cyan)"></i>Todas las Cotizaciones <small style="color:var(--muted);font-size:.8rem">(<?= count($cotizaciones) ?> registros)</small></h3>
            </div>
            <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Cliente</th><th>Teléfono</th><th>Servicio</th><th>Mensaje</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($cotizaciones as $c): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $c['id'] ?></td>
                    <td>
                        <strong style="color:#fff"><?= htmlspecialchars($c['nombre']) ?></strong><br>
                        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="color:var(--cyan);font-size:.78rem"><?= htmlspecialchars($c['email']) ?></a>
                    </td>
                    <td><?= htmlspecialchars($c['telefono'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($c['servicio'] ?: '—') ?></td>
                    <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= htmlspecialchars($c['mensaje']) ?>"><?= htmlspecialchars(substr($c['mensaje'],0,60)) ?>...</td>
                    <td style="color:var(--muted);white-space:nowrap"><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
                    <td>
                        <?php $cls=['pendiente'=>'b-pendiente','atendido'=>'b-atendido','cerrado'=>'b-cerrado']; ?>
                        <span class="badge-estado <?= $cls[$c['estado']] ?>"><?= ucfirst($c['estado']) ?></span>
                    </td>
                    <td style="white-space:nowrap">
                        <?php if ($c['estado'] !== 'atendido'): ?>
                        <a href="?pagina=cotizaciones&accion=atendido&id=<?= $c['id'] ?>" class="action-btn ab-green" title="Marcar como atendido"><i class="bi bi-check-lg"></i></a>
                        <?php endif; ?>
                        <?php if ($c['estado'] !== 'cerrado'): ?>
                        <a href="?pagina=cotizaciones&accion=cerrado&id=<?= $c['id'] ?>" class="action-btn ab-gold" title="Cerrar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                        <a href="?pagina=cotizaciones&eliminar=<?= $c['id'] ?>" class="action-btn ab-red"
                            onclick="return confirm('¿Eliminar esta cotización?')" title="Eliminar"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

    <!-- ════════ SERVICIOS ════════ -->
    <?php elseif ($pagina === 'servicios'): ?>
        <?php $servicios_admin = $pdo->query("SELECT * FROM servicios ORDER BY orden")->fetchAll(); ?>
        <div class="panel-card">
            <div class="panel-card-head">
                <h3><i class="bi bi-tools me-2" style="color:var(--cyan)"></i>Catálogo de Servicios</h3>
            </div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Servicio</th><th>Ícono</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($servicios_admin as $s): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $s['orden'] ?></td>
                    <td>
                        <strong style="color:#fff"><?= htmlspecialchars($s['titulo']) ?></strong><br>
                        <small style="color:var(--muted)"><?= htmlspecialchars(substr($s['descripcion'],0,70)) ?>...</small>
                    </td>
                    <td><i class="bi <?= htmlspecialchars($s['icono']) ?>" style="font-size:1.4rem; color:var(--cyan)"></i></td>
                    <td>
                        <?php if ($s['activo']): ?>
                            <span class="badge-estado b-atendido"><i class="bi bi-circle-fill" style="font-size:.4rem"></i>Activo</span>
                        <?php else: ?>
                            <span class="badge-estado b-cerrado"><i class="bi bi-circle-fill" style="font-size:.4rem"></i>Oculto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <!-- ════════ CONFIGURACIÓN ════════ -->
    <?php elseif ($pagina === 'configuracion'): ?>
        <?php if (isset($msg_ok)): ?>
            <div class="alert-ok"><i class="bi bi-check-circle me-2"></i><?= $msg_ok ?></div>
        <?php endif; ?>
        <div class="panel-card" style="max-width:600px">
            <div class="panel-card-head">
                <h3><i class="bi bi-gear-fill me-2" style="color:var(--cyan)"></i>Configuración del Sitio</h3>
            </div>
            <form method="POST" style="padding:1.5rem">
                <div class="mb-3">
                    <label class="cfg-label">Nombre del sitio</label>
                    <input type="text" name="sitio_nombre" class="cfg-input" value="<?= htmlspecialchars($configs['sitio_nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="cfg-label">Teléfono principal</label>
                    <input type="text" name="sitio_telefono" class="cfg-input" value="<?= htmlspecialchars($configs['sitio_telefono'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="cfg-label">Email de contacto</label>
                    <input type="email" name="sitio_email" class="cfg-input" value="<?= htmlspecialchars($configs['sitio_email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="cfg-label">Número de WhatsApp <small style="color:var(--muted)">(sin +, ej: 5077589716)</small></label>
                    <input type="text" name="sitio_whatsapp" class="cfg-input" value="<?= htmlspecialchars($configs['sitio_whatsapp'] ?? '') ?>">
                </div>
                <button type="submit" name="guardar_config" class="btn-save"><i class="bi bi-check-lg me-2"></i>Guardar Cambios</button>
            </form>
        </div>

    <?php endif; ?>

    </div><!-- /content -->
</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Reloj en el topbar
    function updateClock() {
        const now = new Date();
        document.getElementById('topbarClock').textContent =
            now.toLocaleDateString('es-PA', {weekday:'short', day:'numeric', month:'short'}) +
            ' ' + now.toLocaleTimeString('es-PA', {hour:'2-digit', minute:'2-digit'});
    }
    updateClock(); setInterval(updateClock, 1000);
</script>
</body>
</html>
