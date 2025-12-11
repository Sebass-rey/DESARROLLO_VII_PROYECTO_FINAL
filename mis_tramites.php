<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$stmt = $pdo->prepare("
    SELECT t.*, s.nombre AS servicio_nombre
    FROM tramites t
    JOIN servicios s ON s.id = t.id_servicio
    WHERE t.id_usuario = ?
    ORDER BY t.fecha_creacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-4">Mis trámites</h2>

  <?php if (!$tramites): ?>
    <div class="alert alert-info">Aún no has creado ningún trámite.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Código</th>
            <th>Servicio</th>
            <th>Estado</th>
            <th>Monto (USD)</th>
            <th>Creado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tramites as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['codigo_tramite']); ?></td>
              <td><?= htmlspecialchars($t['servicio_nombre']); ?></td>
              <td><?= htmlspecialchars($t['estado_actual']); ?></td>
              <td><?= number_format($t['monto'], 2); ?></td>
              <td><?= htmlspecialchars($t['fecha_creacion']); ?></td>
              <td>
                <a href="tramite_detalle.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-outline-primary">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
