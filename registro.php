<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    if ($nombre === '' || $email === '' || $password === '' || $password2 === '') {
        $errores[] = "Todos los campos son obligatorios.";
    } elseif ($password !== $password2) {
        $errores[] = "Las contraseñas no coinciden.";
    } else {
        // Verificar si ya existe el correo
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe una cuenta con ese correo.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
            $stmt->execute([$nombre, $email, $password]);
            $exito = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h2 class="mb-4">Crear cuenta</h2>

      <?php if ($exito): ?>
        <div class="alert alert-success">
          Cuenta creada. Ahora puedes <a href="login.php" class="alert-link">iniciar sesión</a>.
        </div>
      <?php endif; ?>

      <?php if ($errores): ?>
        <div class="alert alert-danger">
          <?php foreach ($errores as $e): ?>
            <div><?= htmlspecialchars($e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre completo</label>
          <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="password2" class="form-label">Repetir contraseña</label>
          <input type="password" name="password2" id="password2" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-warning">Registrarme</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
