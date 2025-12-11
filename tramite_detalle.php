<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$idTramite = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT t.*, s.nombre AS servicio_nombre
    FROM tramites t
    JOIN servicios s ON s.id = t.id_servicio
    WHERE t.id = ? AND t.id_usuario = ?
");
$stmt->execute([$idTramite, $_SESSION['user_id']]);
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    require_once __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/includes/navbar.php';
    echo "<div class='container mt-4'><div class='alert alert-danger'>Trámite no encontrado.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Historial
$stmtHist = $pdo->prepare("
    SELECT * FROM historial_estados
    WHERE id_tramite = ?
    ORDER BY fecha_cambio ASC
");
$stmtHist->execute([$idTramite]);
$historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

// Archivos
$stmtArch = $pdo->prepare("
    SELECT * FROM archivos_tramite WHERE id_tramite = ?
");
$stmtArch->execute([$idTramite]);
$archivos = $stmtArch->fetchAll(PDO::FETCH_ASSOC);

// Orden de estados para el "timeline"
$ordenEstados = ['recibido','en_revision','en_elaboracion','listo_entrega','completado'];
$estadoActual = $tramite['estado_actual'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-3">Detalle del trámite</h2>

  <div class="row">
    <div class="col-md-7">
      <div class="card mb-3">
        <div class="card-body">
          <p><strong>Código:</strong> <?= htmlspecialchars($tramite['codigo_tramite']); ?></p>
          <p><strong>Servicio:</strong> <?= htmlspecialchars($tramite['servicio_nombre']); ?></p>
          <p><strong>Monto:</strong> USD <?= number_format($tramite['monto'], 2); ?></p>
          <p><strong>Estado actual:</strong> <?= htmlspecialchars($estadoActual); ?></p>
          <p><strong>Creado:</strong> <?= htmlspecialchars($tramite['fecha_creacion']); ?></p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5>Descripción del caso</h5>
          <p><?= nl2br(htmlspecialchars($tramite['descripcion'])); ?></p>

          <?php if (!empty($tramite['observaciones_cliente'])): ?>
            <h6 class="mt-3">Observaciones del cliente</h6>
            <p><?= nl2br(htmlspecialchars($tramite['observaciones_cliente'])); ?></p>
          <?php endif; ?>

          <?php if (!empty($tramite['observaciones_internas'])): ?>
            <h6 class="mt-3">Notas internas</h6>
            <p class="small text-muted"><?= nl2br(htmlspecialchars($tramite['observaciones_internas'])); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($archivos): ?>
        <div class="card mb-3">
          <div class="card-body">
            <h5>Documentos adjuntos</h5>
            <ul>
              <?php foreach ($archivos as $a): ?>
                <li>
                  <a href="<?= htmlspecialchars($a['ruta_archivo']); ?>" target="_blank">
                    <?= htmlspecialchars($a['nombre_archivo']); ?>
                  </a>
                  <span class="text-muted small"> (<?= htmlspecialchars($a['fecha_subida']); ?>)</span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-md-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="mb-3">Seguimiento del trámite</h5>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($ordenEstados as $estado): 
                $completado = false;
                foreach ($historial as $h) {
                    if ($h['estado'] === $estado) {
                        $completado = true;
                        break;
                    }
                }
                $activo = $estado === $estadoActual;
            ?>
              <div class="d-flex align-items-center">
                <span class="timeline-step me-2
                    <?= $completado ? 'bg-success' : 'bg-secondary'; ?>
                    <?= $activo ? ' border border-2 border-dark' : ''; ?>">
                </span>
                <span class="<?= $activo ? 'fw-bold' : ''; ?>">
                  <?= htmlspecialchars($estado); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>

          <hr>

          <h6 class="mt-3">Historial de cambios</h6>
          <?php if ($historial): ?>
            <ul class="small">
              <?php foreach ($historial as $h): ?>
                <li>
                  <strong><?= htmlspecialchars($h['estado']); ?></strong>
                  – <?= htmlspecialchars($h['fecha_cambio']); ?>
                  <?php if (!empty($h['comentario'])): ?>
                    <br><span class="text-muted"><?= htmlspecialchars($h['comentario']); ?></span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="small text-muted">Aún no hay cambios registrados.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
