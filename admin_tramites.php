<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$stmt = $pdo->query("
    SELECT t.*, s.nombre AS servicio_nombre, u.nombre AS usuario_nombre
    FROM tramites t
    JOIN servicios s ON s.id = t.id_servicio
    JOIN usuarios u ON u.id = t.id_usuario
    ORDER BY t.fecha_creacion DESC
");
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-4">Panel de trámites (Admin)</h2>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Código</th>
          <th>Cliente</th>
          <th>Servicio</th>
          <th>Estado</th>
          <th>Creado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tramites as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['codigo_tramite']); ?></td>
            <td><?= htmlspecialchars($t['usuario_nombre']); ?></td>
            <td><?= htmlspecialchars($t['servicio_nombre']); ?></td>
            <td><?= htmlspecialchars($t['estado_actual']); ?></td>
            <td><?= htmlspecialchars($t['fecha_creacion']); ?></td>
            <td>
              <a href="admin_tramite_detalle.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-outline-primary">
                Ver / Actualizar
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
