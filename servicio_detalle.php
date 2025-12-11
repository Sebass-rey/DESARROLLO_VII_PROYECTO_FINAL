<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/classes/Servicio.php';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicioModel = new Servicio($pdo);
$servicio      = $servicioModel->obtenerPorId($id);


if (!$servicio) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Servicio no encontrado.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="container my-5">
  <div class="row">
    <div class="col-md-8">
      <h2 class="mb-3"><?= htmlspecialchars($servicio['nombre']); ?></h2>
      <p><?= nl2br(htmlspecialchars($servicio['descripcion'])); ?></p>

      <?php if (!empty($servicio['tiempo_estimado'])): ?>
        <p class="text-muted">
          <strong>Tiempo estimado:</strong> <?= htmlspecialchars($servicio['tiempo_estimado']); ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <p class="fw-bold fs-4">USD <?= number_format($servicio['precio'], 2); ?></p>
          <p class="small text-muted mb-3">
            Precio referencial para el trámite estándar. El detalle final se confirma con el profesional.
          </p>

          <?php if (isLoggedIn()): ?>
            <a href="crear_tramite.php?id_servicio=<?= $servicio['id']; ?>" class="btn btn-primary w-100 mb-2">
              Iniciar trámite
            </a>
          <?php else: ?>
            <p class="small mb-2">Debes iniciar sesión para solicitar este servicio.</p>
            <a href="login.php" class="btn btn-outline-primary w-100 mb-2">Iniciar sesión</a>
            <a href="registro.php" class="btn btn-warning w-100">Crear cuenta</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



