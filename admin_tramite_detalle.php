<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$idTramite = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT t.*, s.nombre AS servicio_nombre, u.nombre AS usuario_nombre, u.email AS usuario_email
    FROM tramites t
    JOIN servicios s ON s.id = t.id_servicio
    JOIN usuarios u ON u.id = t.id_usuario
    WHERE t.id = ?
");
$stmt->execute([$idTramite]);
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    require_once __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/includes/navbar.php';
    echo "<div class='container mt-4'><div class='alert alert-danger'>Trámite no encontrado.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$estadoActual = $tramite['estado_actual'];
$mensaje = '';
$errores = [];
$ordenEstados = ['recibido','en_revision','en_elaboracion','listo_entrega','completado','cancelado'];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoEstado = $_POST['estado'] ?? $estadoActual;
    $comentario  = trim($_POST['comentario'] ?? '');

    if (!in_array($nuevoEstado, $ordenEstados, true)) {
        $errores[] = "Estado no válido.";
    } else {
        $stmtUp = $pdo->prepare("
            UPDATE tramites 
            SET estado_actual = ?, fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        $stmtUp->execute([$nuevoEstado, $idTramite]);

        $stmtHist = $pdo->prepare("
            INSERT INTO historial_estados (id_tramite, estado, comentario)
            VALUES (?, ?, ?)
        ");
        $stmtHist->execute([$idTramite, $nuevoEstado, $comentario ?: 'Actualización de estado']);

        $mensaje = "Estado actualizado correctamente.";
        $estadoActual = $nuevoEstado;
    }
}

// Historial
$stmtHist2 = $pdo->prepare("
    SELECT * FROM historial_estados 
    WHERE id_tramite = ? 
    ORDER BY fecha_cambio ASC
");
$stmtHist2->execute([$idTramite]);
$historial = $stmtHist2->fetchAll(PDO::FETCH_ASSOC);

$stmtArch = $pdo->prepare("
    SELECT * 
    FROM archivos_tramite
    WHERE id_tramite = ?
    ORDER BY fecha_subida ASC
");
$stmtArch->execute([$idTramite]);
$archivos = $stmtArch->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-3">Trámite (Admin)</h2>

  <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <?php if ($errores): ?>
    <div class="alert alert-danger">
      <?php foreach ($errores as $e): ?>
        <div><?= htmlspecialchars($e); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-7">

      <div class="card mb-3">
        <div class="card-body">
          <h5><?= htmlspecialchars($tramite['codigo_tramite']); ?></h5>
          <p><strong>Cliente:</strong> <?= htmlspecialchars($tramite['usuario_nombre']); ?> (<?= htmlspecialchars($tramite['usuario_email']); ?>)</p>
          <p><strong>Servicio:</strong> <?= htmlspecialchars($tramite['servicio_nombre']); ?></p>
          <p><strong>Monto:</strong> USD <?= number_format((float)$tramite['monto'], 2); ?></p>
          <p><strong>Estado actual:</strong> <?= htmlspecialchars(etiquetaEstado($estadoActual)); ?></p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="mb-3">Documentos adjuntos</h5>

          <?php if (empty($archivos)): ?>
            <div class="alert alert-info mb-0">Este trámite no tiene documentos adjuntos.</div>
          <?php else: ?>
            <ul class="list-group">
              <?php foreach ($archivos as $a): ?>
                <?php
                  $nombre = $a['nombre_archivo'] ?? 'Documento';
                  $ruta   = $a['ruta_archivo'] ?? '';
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="me-3">
                    <div class="fw-semibold"><?= htmlspecialchars($nombre); ?></div>
                    <?php if (!empty($a['fecha_subida'])): ?>
                      <div class="text-muted small"><?= htmlspecialchars($a['fecha_subida']); ?></div>
                    <?php endif; ?>
                  </div>

                  <?php if ($ruta): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($ruta); ?>" target="_blank" rel="noopener">
                      Ver / Descargar
                    </a>
                  <?php else: ?>
                    <span class="badge bg-secondary">Sin ruta</span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5>Actualizar estado</h5>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Nuevo estado</label>
              <select name="estado" class="form-select">
                <?php foreach ($ordenEstados as $e): ?>
                  <option value="<?= htmlspecialchars($e); ?>" <?= $e === $estadoActual ? 'selected' : ''; ?>>
                    <?= htmlspecialchars(etiquetaEstado($e)); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Comentario (opcional)</label>
              <textarea name="comentario" class="form-control" rows="3"></textarea>
            </div>

            <button class="btn btn-primary">Guardar cambios</button>
          </form>
        </div>
      </div>

    </div>

    <div class="col-md-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5>Historial de estados</h5>

          <?php if (empty($historial)): ?>
            <p class="text-muted small mb-0">No hay historial de cambios.</p>
          <?php else: ?>
            <ul class="mb-0">
              <?php foreach ($historial as $h): ?>
                <li class="mb-2">
                  <strong><?= htmlspecialchars(etiquetaEstado($h['estado'] ?? '')); ?></strong>
                  – <?= htmlspecialchars($h['fecha_cambio'] ?? ''); ?><br>
                  <span class="text-muted small"><?= htmlspecialchars($h['comentario'] ?? ''); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

