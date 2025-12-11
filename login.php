<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$errores = [];
$email = '';

$recordarEmail = $_COOKIE['recordar_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errores[] = "Correo y contraseña son obligatorios.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ?");
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['user_rol']     = $user['rol'] ?? 'usuario';
            $_SESSION['user_nombre']  = $user['nombre'] ?? 'Usuario';

            if (!empty($_POST['recordar'])) {
                setcookie('recordar_email', $email, time() + (7 * 24 * 60 * 60), "/");
            } else {
                setcookie('recordar_email', '', time() - 3600, "/");
            }

            if ($_SESSION['user_rol'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $errores[] = "Credenciales inválidas.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $recordarEmail !== '') {
    $email = $recordarEmail;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <h2 class="mb-4">Iniciar sesión</h2>

      <?php if ($errores): ?>
        <div class="alert alert-danger">
          <?php foreach ($errores as $e): ?>
            <div><?= htmlspecialchars($e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input
            type="email"
            name="email"
            id="email"
            class="form-control"
            required
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
          >
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3 form-check">
          <input
            type="checkbox"
            name="recordar"
            id="recordar"
            class="form-check-input"
            <?= $recordarEmail !== '' ? 'checked' : ''; ?>
          >
          <label class="form-check-label" for="recordar">
            Recordar mi correo
          </label>
        </div>

        <button type="submit" class="btn btn-primary">Entrar</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


