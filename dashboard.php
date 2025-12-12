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
$usuarioId     = $_SESSION['user_id']     ?? null;
$usuarioNombre = $_SESSION['user_nombre'] ?? 'Usuario';
$usuarioRol    = $_SESSION['user_rol']    ?? 'usuario';

$esAdmin = ($usuarioRol === 'admin');

function etiquetaEstado(string $estado): string {
    $map = [
        'recibido'       => 'Recibido',
        'en_revision'    => 'En revisión',
        'en_elaboracion' => 'En elaboración',
        'listo_entrega'  => 'Listo para entrega',
        'completado'     => 'Completado',
        'cancelado'      => 'Cancelado',
    ];
    return $map[$estado] ?? $estado;
}

// ================== PANEL ADMIN: QUERIES ==================
$totalTramites    = 0;
$totalNuevos      = 0;
$totalPendientes  = 0;
$totalPorSubsanar = 0;
$ultimosTramites  = [];

if ($esAdmin) {
    try {
        $totalTramites = (int)$pdo->query("SELECT COUNT(*) FROM tramites")->fetchColumn();

        $totalNuevos = (int)$pdo->query("
            SELECT COUNT(*) 
            FROM tramites 
            WHERE estado_actual = 'recibido'
        ")->fetchColumn();

        $totalPendientes = (int)$pdo->query("
            SELECT COUNT(*)
            FROM tramites
            WHERE estado_actual IN ('en_revision','en_elaboracion','listo_entrega')
        ")->fetchColumn();

        $totalPorSubsanar = (int)$pdo->query("
            SELECT COUNT(*)
            FROM tramites
            WHERE observaciones_internas IS NOT NULL
              AND observaciones_internas <> ''
              AND estado_actual IN ('en_revision','en_elaboracion')
        ")->fetchColumn();

        $stmt = $pdo->query("
            SELECT 
                t.id,
                u.nombre AS usuario,
                s.nombre AS servicio,
                t.estado_actual,
                t.fecha_creacion
            FROM tramites t
            LEFT JOIN usuarios  u ON t.id_usuario  = u.id
            LEFT JOIN servicios s ON t.id_servicio = s.id
            ORDER BY t.fecha_creacion DESC
            LIMIT 10
        ");
        $ultimosTramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $totalTramites = $totalNuevos = $totalPendientes = $totalPorSubsanar = 0;
        $ultimosTramites = [];
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
                t.estado_actual,
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

  <h2 class="mb-3">Panel administrativo</h2>
  <p class="text-muted mb-4">
    Hola, <?= htmlspecialchars($usuarioNombre); ?>. Aquí tienes un resumen rápido de lo que está pasando en LegalSmart.
  </p>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <div class="text-muted small">Trámites totales</div>
          <div class="fs-2 fw-bold"><?= (int)$totalTramites; ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <div class="text-muted small">Nuevos (recibido)</div>
          <div class="fs-2 fw-bold"><?= (int)$totalNuevos; ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <div class="text-muted small">Pendientes de gestión</div>
          <div class="fs-2 fw-bold"><?= (int)$totalPendientes; ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <div class="text-muted small">Por subsanar</div>
          <div class="fs-2 fw-bold"><?= (int)$totalPorSubsanar; ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white">
      <strong>Últimos trámites registrados</strong>
    </div>
    <div class="card-body">
      <?php if (empty($ultimosTramites)): ?>
        <div class="alert alert-info mb-0">No se encontraron trámites recientes.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Estado</th>
                <th>Creado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultimosTramites as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['usuario'] ?? '—'); ?></td>
                  <td><?= htmlspecialchars($t['servicio'] ?? '—'); ?></td>
                  <td><?= htmlspecialchars(etiquetaEstado($t['estado_actual'] ?? '')); ?></td>
                  <td><?= htmlspecialchars($t['fecha_creacion'] ?? ''); ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="admin_tramite_detalle.php?id=<?= (int)$t['id']; ?>">
                      Ver detalle
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>

  <h2 class="mb-3">Mi panel de trámites</h2>
  <p class="text-muted">
    Hola, <strong><?= htmlspecialchars($usuarioNombre); ?></strong>. Aquí puedes ver el estado de los trámites que has solicitado.
  </p>

  <?php if (empty($tramitesUsuario)): ?>
    <div class="alert alert-info">
      Aún no tienes trámites registrados. Puedes iniciar uno desde el catálogo de servicios legales.
    </div>
  <?php else: ?>
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white">
        <strong>Últimos 3 trámites</strong>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Servicio</th>
                <th>Estado</th>
                <th>Creado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tramitesUsuario as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['servicio']); ?></td>
                  <td><?= htmlspecialchars(etiquetaEstado($t['estado_actual'])); ?></td>
                  <td><?= htmlspecialchars($t['fecha_creacion']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="tramite_detalle.php?id=<?= (int)$t['id']; ?>">
                      Ver detalle
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          <a href="mis_tramites.php" class="btn btn-primary btn-sm">Ver todos mis trámites</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>




