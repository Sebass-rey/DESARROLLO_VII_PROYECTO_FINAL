<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (!isset($_SESSION['user_nombre'])) {
    $_SESSION['user_nombre'] = 'Usuario';
}

$usuarioNombre = $_SESSION['user_nombre'];
?>

<div class="container my-5">

<?php if ($esAdmin): ?>

  <h2 class="mb-3">Panel administrativo</h2>
 <p>
  Hola, 
  <?= htmlspecialchars($usuarioNombre ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?>.
  Aquí tienes un resumen rápido de lo que está pasando en LegalSmart.
</p>

  <?php
  // Resúmenes simples
  $totTramites = (int)$pdo->query("SELECT COUNT(*) FROM tramites")->fetchColumn();
  $nuevos      = (int)$pdo->query("SELECT COUNT(*) FROM tramites WHERE estado_actual = 'recibido'")->fetchColumn();
  $subsanar    = (int)$pdo->query("
                    SELECT COUNT(*) 
                    FROM tramites 
                    WHERE observaciones_internas IS NOT NULL 
                      AND observaciones_internas <> ''
                      AND estado_actual IN ('en_revision','en_elaboracion')
                 ")->fetchColumn();

  // Últimos 5 trámites
  $stmt = $pdo->query("
      SELECT t.codigo_tramite, t.estado_actual, t.fecha_creacion,
             u.nombre AS cliente, s.nombre AS servicio
      FROM tramites t
      JOIN usuarios u ON t.id_usuario = u.id
      JOIN servicios s ON t.id_servicio = s.id
      ORDER BY t.fecha_creacion DESC
      LIMIT 5
  ");
  $ultimosTramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!-- Tarjetas pequeñas -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted small">Trámites totales</div>
          <div class="fs-3 fw-bold"><?= $totTramites; ?></div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted small">Nuevos (recibido)</div>
          <div class="fs-3 fw-bold"><?= $nuevos; ?></div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted small">Pendientes de subsanar</div>
          <div class="fs-3 fw-bold"><?= $subsanar; ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Últimos trámites -->
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="card-title mb-3">Últimas solicitudes de trámite</h5>

      <?php if (empty($ultimosTramites)): ?>
        <p class="text-muted small mb-0">Todavía no hay trámites registrados.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultimosTramites as $t): ?>
                <tr>
                  <td class="small"><?= htmlspecialchars($t['codigo_tramite']); ?></td>
                  <td class="small"><?= htmlspecialchars($t['cliente']); ?></td>
                  <td class="small"><?= htmlspecialchars($t['servicio']); ?></td>
                  <td class="small">
                    <span class="badge bg-light text-dark border">
                      <?= htmlspecialchars($t['estado_actual']); ?>
                    </span>
                  </td>
                  <td class="small text-muted"><?= htmlspecialchars($t['fecha_creacion']); ?></td>
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
  <p class="text-muted mb-4">
    Hola, <?= htmlspecialchars($usuario['nombre']); ?>. Aquí puedes ver el estado de los trámites que has solicitado.
  </p>

  <?php
  $stmt = $pdo->prepare("
      SELECT t.codigo_tramite, t.estado_actual, t.fecha_creacion,
             t.monto, s.nombre AS servicio
      FROM tramites t
      JOIN servicios s ON t.id_servicio = s.id
      WHERE t.id_usuario = ?
      ORDER BY t.fecha_creacion DESC
  ");
  $stmt->execute([$idUsuario]);
  $misTramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <?php if (empty($misTramites)): ?>
    <div class="alert alert-info">
      Aún no tienes trámites registrados. Puedes iniciar uno desde el catálogo de servicios legales.
    </div>
  <?php else: ?>
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title mb-3">Mis trámites</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Servicio</th>
                <th>Estado</th>
                <th>Monto</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($misTramites as $t): ?>
                <tr>
                  <td class="small"><?= htmlspecialchars($t['codigo_tramite']); ?></td>
                  <td class="small"><?= htmlspecialchars($t['servicio']); ?></td>
                  <td class="small">
                    <span class="badge bg-light text-dark border">
                      <?= htmlspecialchars($t['estado_actual']); ?>
                    </span>
                  </td>
                  <td class="small">USD <?= number_format($t['monto'], 2); ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($t['fecha_creacion']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

