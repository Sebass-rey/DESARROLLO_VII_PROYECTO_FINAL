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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoEstado = $_POST['estado'] ?? $estadoActual;
    $comentario  = trim($_POST['comentario'] ?? '');

    if (!in_array($nuevoEstado, $ordenEstados)) {
        $errores[] = "Estado no válido.";
    } else {
        // Actualizar trámite
        $stmtUp = $pdo->prepare("
            UPDATE tramites 
            SET estado_actual = ?, fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        $stmtUp->execute([$nuevoEstado, $idTramite]);

        // Insertar historial
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
    SELECT * FROM historial_estados WHERE id_tramite = ? ORDER BY fecha_cambio ASC
");
$stmtHist2->execute([$idTramite]);
$historial = $stmtHist2->fetchAll(PDO::FETCH_ASSOC);

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
          <p><strong>Monto:</strong> USD <?= number_format($tramite['monto'], 2); ?></p>
          <p><strong>Estado actual:</strong> <?= htmlspecialchars($estadoActual); ?></p>
        </div>
      </div>

      <form method="post" class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Actualizar estado</h5>
          <div class="mb-3">
            <label class="form-label">Nuevo estado</label>
            <select name="estado" class="form-select">
              <?php foreach ($ordenEstados as $e): ?>
                <option value="<?= $e; ?>" <?= $e === $estadoActual ? 'selected' : ''; ?>>
                  <?= $e; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Comentario (opcional)</label>
            <textarea name="comentario" class="form-control" rows="3"></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-body">
          <h5>Historial de estados</h5>
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
            <p class="small text-muted">Sin historial registrado.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
