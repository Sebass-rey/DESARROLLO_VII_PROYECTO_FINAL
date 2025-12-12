<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

//servicos mas solicitados
$stmt = $pdo->query("SELECT id, nombre, descripcion, precio FROM servicios WHERE activo = 1 LIMIT 3");
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
  <section class="hero-home text-center">
    <img
      src="/PROYECTO/legalsmart/assets/img/logo_legalsmart.png"
      alt="LegalSmart"
      class="hero-logo mb-3"
    >
    <h1 class="mb-3 hero-title">LegalSmart</h1>
    <p class="lead mb-4 hero-subtitle">
      Plataforma de trámites legales digitales para Panamá. 
      Contrata servicios estandarizados, paga en línea y da seguimiento a tu trámite desde un mismo lugar.
    </p>
    <a href="catalogo.php" class="btn btn-primary btn-lg">Ver servicios legales</a>
  </section>

  <section class="mb-5">
    <h2 class="h4 mb-3">Servicios destacados</h2>
    <div class="row">
      <?php foreach ($servicios as $servicio): ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($servicio['nombre']); ?></h5>
              <p class="card-text small mb-3">
                <?= nl2br(htmlspecialchars(substr($servicio['descripcion'], 0, 140))); ?>.
              </p>
              <p class="fw-bold mb-3">
                USD <?= number_format($servicio['precio'], 2); ?>
              </p>
              <a href="servicio_detalle.php?id=<?= $servicio['id']; ?>" class="btn btn-outline-primary mt-auto">
                Ver detalles
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

