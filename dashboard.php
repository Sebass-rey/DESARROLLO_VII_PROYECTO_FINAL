<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Datos básicos del usuario logueado
$usuarioId     = $_SESSION['user_id']      ?? null;
$usuarioNombre = $_SESSION['user_nombre']  ?? 'Usuario';
$usuarioRol    = $_SESSION['user_rol']     ?? 'usuario';

$esAdmin = ($usuarioRol === 'admin');

// ================== PANEL ADMIN: QUERIES ==================
$totalTramites    = 0;
$totalNuevos      = 0;
$totalPendientes  = 0;
$totalPorSubsanar = 0;
$ultimosTramites  = [];

if ($esAdmin) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tramites");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalTramites = (int)($row['total'] ?? 0);

        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tramites WHERE estado = 'recibido'");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalNuevos = (int)($row['total'] ?? 0);

        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tramites WHERE estado = 'en_proceso'");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPendientes = (int)($row['total'] ?? 0);

        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tramites WHERE estado = 'subsanar'");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPorSubsanar = (int)($row['total'] ?? 0);

        $stmt = $pdo->query("
            SELECT 
                t.id,
                u.nombre   AS usuario,
                s.nombre   AS servicio,
                t.estado,
                t.fecha_creacion
            FROM tramites t
            LEFT JOIN usuarios  u ON t.id_usuario  = u.id
            LEFT JOIN servicios s ON t.id_servicio = s.id
            ORDER BY t.fecha_creacion DESC
            LIMIT 10
        ");
        $ultimosTramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $totalTramites    = 0;
        $totalNuevos      = 0;
        $totalPendientes  = 0;
        $totalPorSubsanar = 0;
        $ultimosTramites  = [];
    }
}

// ================== PANEL USUARIO: ÚLTIMOS 3 TRÁMITES ==================
$tramitesUsuario = [];

if (!$esAdmin && $usuarioId !== null) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.id,
                s.nombre AS servicio,
                t.estado,
                t.fecha_creacion
            FROM tramites t
            INNER JOIN servicios s ON t.id_servicio = s.id
            WHERE t.id_usuario = ?
            ORDER BY t.fecha_creacion DESC
            LIMIT 3
        ");
        $stmt->execute([$usuarioId]);
        $tramitesUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $tramitesUsuario = [];
    }
}
?>

<div class="container my-5">

  <?php if ($esAdmin): ?>
    <!-- ================== PANEL ADMINISTRATIVO ================== -->
    <h1 class="mb-4">Panel administrativo</h1>
    <p class="mb-4">
      Hola,
      <?= htmlspecialchars($usuarioNombre ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?>.
      Aquí tienes un resumen rápido de lo que está pasando en LegalSmart.
    </p>

    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted small mb-1">Trámites totales</div>
            <div class="fs-3 fw-bold"><?= (int)$totalTramites; ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted small mb-1">Nuevos (recibido)</div>
            <div class="fs-3 fw-bold"><?= (int)$totalNuevos; ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted small mb-1">Pendientes de gestión</div>
            <div class="fs-3 fw-bold"><?= (int)$totalPendientes; ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <div class="text-muted small mb-1">Por subsanar</div>
            <div class="fs-3 fw-bold"><?= (int)$totalPorSubsanar; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla últimos trámites -->
    <div class="card">
      <div class="card-header">
        Últimos trámites registrados
      </div>
      <div class="card-body p-0">
        <?php if (!empty($ultimosTramites)): ?>
          <div class="table-responsive">
            <table class="table mb-0 table-striped align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Servicio</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ultimosTramites as $t): ?>
                  <tr>
                    <td><?= (int)$t['id']; ?></td>
                    <td><?= htmlspecialchars($t['usuario'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($t['servicio'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($t['estado'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($t['fecha_creacion'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-3 text-muted">
            No se encontraron trámites recientes.
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php else: ?>
    <!-- ================== PANEL DE USUARIO ================== -->
    <h2 class="mb-3">Mi panel de trámites</h2>
    <p class="text-muted mb-4">
      Hola,
      <?= htmlspecialchars($usuarioNombre ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?>.
      Aquí puedes ver el estado de los trámites que has solicitado.
    </p>

    <?php if ($usuarioId === null): ?>
      <div class="alert alert-warning">
        Ha ocurrido un problema con tu sesión. Vuelve a iniciar sesión para ver tus trámites.
      </div>
    <?php else: ?>

      <?php if (empty($tramitesUsuario)): ?>
        <div class="alert alert-info">
          Aún no tienes trámites registrados. Puedes iniciar uno desde el catálogo de servicios legales.
        </div>
        <a href="catalogo.php" class="btn btn-outline-primary btn-sm">Ir al catálogo</a>

      <?php else: ?>
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Últimos 3 trámites</span>
            <a href="mis_tramites.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table mb-0 table-striped align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tramitesUsuario as $t): ?>
                    <tr>
                      <td><?= (int)$t['id']; ?></td>
                      <td><?= htmlspecialchars($t['servicio'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?= htmlspecialchars($t['estado'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?= htmlspecialchars($t['fecha_creacion'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <a href="tramite_detalle.php?id=<?= (int)$t['id']; ?>" class="btn btn-sm btn-outline-secondary">
                          Ver
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



