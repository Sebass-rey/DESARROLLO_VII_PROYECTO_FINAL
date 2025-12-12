<?php
$loggedIn = !empty($_SESSION['user_id']);
$esAdmin  = $loggedIn && (($_SESSION['user_rol'] ?? '') === 'admin');

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

function navActive(string $file, string $currentPage): string {
  return ($currentPage === $file) ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">

    <!-- Marca con logo -->
    <a class="navbar-brand d-flex align-items-center" href="/PROYECTO/legalsmart/index.php">
      <img
        src="/PROYECTO/legalsmart/assets/img/logo_legalsmart.png"
        alt="LegalSmart"
        class="logo-navbar me-2"
      >
      <span>LegalSmart</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLegalSmart">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarLegalSmart">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <?php if (!$loggedIn): ?>
          <!-- Visitante -->
          <li class="nav-item">
            <a class="nav-link <?= navActive('index.php', $currentPage); ?>" href="/PROYECTO/legalsmart/index.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('quienes_somos.php', $currentPage); ?>" href="/PROYECTO/legalsmart/quienes_somos.php">¿Quiénes Somos?</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('catalogo.php', $currentPage); ?>" href="/PROYECTO/legalsmart/catalogo.php">Servicios Legales</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('contacto.php', $currentPage); ?>" href="/PROYECTO/legalsmart/contacto.php">Contacto</a>
          </li>

        <?php elseif ($esAdmin): ?>
          <!-- Admin -->
          <li class="nav-item">
            <a class="nav-link <?= navActive('dashboard.php', $currentPage); ?>" href="/PROYECTO/legalsmart/dashboard.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('admin_tramites.php', $currentPage); ?>" href="/PROYECTO/legalsmart/admin_tramites.php">Trámites en curso</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('admin_contacto.php', $currentPage); ?>" href="/PROYECTO/legalsmart/admin_contacto.php">Bandeja de Contacto</a>
          </li>

        <?php else: ?>
          <!-- Usuario normal -->
          <li class="nav-item">
            <a class="nav-link <?= navActive('dashboard.php', $currentPage); ?>" href="/PROYECTO/legalsmart/dashboard.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('quienes_somos.php', $currentPage); ?>" href="/PROYECTO/legalsmart/quienes_somos.php">¿Quiénes Somos?</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('catalogo.php', $currentPage); ?>" href="/PROYECTO/legalsmart/catalogo.php">Servicios Legales</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('mis_tramites.php', $currentPage); ?>" href="/PROYECTO/legalsmart/mis_tramites.php">Mis Trámites</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= navActive('contacto.php', $currentPage); ?>" href="/PROYECTO/legalsmart/contacto.php">Contacto</a>
          </li>
        <?php endif; ?>

      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($loggedIn): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">
              Hola, <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="/PROYECTO/legalsmart/logout.php">Cerrar sesión</a>
          </li>
        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-outline-light btn-sm" href="/PROYECTO/legalsmart/login.php">Iniciar sesión</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-warning btn-sm" href="/PROYECTO/legalsmart/registro.php">Registrarme</a>
          </li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>



