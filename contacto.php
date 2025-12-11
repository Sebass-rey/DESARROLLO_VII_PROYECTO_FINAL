<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Crear tabla de mensajes si no existe (para que sea 100% funcional)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS mensajes_contacto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telefono VARCHAR(50) NULL,
        motivo VARCHAR(50) NOT NULL,
        mensaje TEXT NOT NULL,
        fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Motivo inicial según ?servicio= de la URL (cuando vienes de 'Solicitar')
$servicioParam = isset($_GET['servicio']) ? strtolower($_GET['servicio']) : '';
$motivoInicial = 'consulta_general';

if ($servicioParam) {
    if (str_contains($servicioParam, 'sociedad')) {
        $motivoInicial = 'constitucion_sociedad';
    } elseif (str_contains($servicioParam, 'contrato')) {
        $motivoInicial = 'contrato';
    } elseif (str_contains($servicioParam, 'jtia') || str_contains($servicioParam, 'idoneidad')) {
        $motivoInicial = 'idoneidades_jtia';
    } elseif (str_contains($servicioParam, 'aviso')) {
        $motivoInicial = 'aviso_operacion';
    } elseif (str_contains($servicioParam, 'contrataciones') || str_contains($servicioParam, 'panamacompra')) {
        $motivoInicial = 'contrataciones_publicas';
    } elseif (str_contains($servicioParam, 'notarial') || str_contains($servicioParam, 'notariales')) {
        $motivoInicial = 'servicio_notarial';
    }
}

$errors = [];
$success = false;

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $motivo  = $_POST['motivo'] ?? 'consulta_general';
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Validaciones
    if ($nombre === '') {
        $errors[] = 'El nombre completo es obligatorio.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Debe indicar un correo electrónico válido.';
    }

    $motivosPermitidos = [
        'constitucion_sociedad',
        'contrato',
        'idoneidades_jtia',
        'aviso_operacion',
        'contrataciones_publicas',
        'servicio_notarial',
        'consulta_general'
    ];
    if (!in_array($motivo, $motivosPermitidos, true)) {
        $errors[] = 'Motivo de mensaje no válido.';
    }

    if ($mensaje === '') {
        $errors[] = 'El mensaje no puede estar vacío.';
    }

    // Si todo ok, guardar en la BD
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO mensajes_contacto (nombre, email, telefono, motivo, mensaje)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $email, $telefono ?: null, $motivo, $mensaje]);
        $success = true;

        // Limpiar campos del formulario
        $nombre = $email = $telefono = $mensaje = '';
        $motivoInicial = 'consulta_general';
    } else {
        // Mantener motivo seleccionado
        $motivoInicial = $motivo;
    }
}
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <h2 class="text-center mb-4">Contacto</h2>

      <p class="text-muted text-center mb-4">
        Completa el formulario y cuéntanos brevemente qué necesitas. 
        Utilizamos esta información para organizar tu trámite y responderte de forma clara y puntual.
      </p>

      <?php if ($success): ?>
        <div class="alert alert-success">
          Tu mensaje ha sido enviado correctamente. Te contactaremos en un plazo de 24 a 48 horas hábiles.
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <form method="post" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($nombre ?? ''); ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($email ?? ''); ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Teléfono (opcional)</label>
                <input type="text" name="telefono" class="form-control"
                       value="<?= htmlspecialchars($telefono ?? ''); ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Motivo del mensaje</label>
                <select name="motivo" class="form-select">
                  <option value="constitucion_sociedad" <?= ($motivoInicial === 'constitucion_sociedad') ? 'selected' : ''; ?>>
                    Constitución de sociedad
                  </option>
                  <option value="contrato" <?= ($motivoInicial === 'contrato') ? 'selected' : ''; ?>>
                    Contrato
                  </option>
                  <option value="idoneidades_jtia" <?= ($motivoInicial === 'idoneidades_jtia') ? 'selected' : ''; ?>>
                    Idoneidades JTIA
                  </option>
                  <option value="aviso_operacion" <?= ($motivoInicial === 'aviso_operacion') ? 'selected' : ''; ?>>
                    Aviso de operación
                  </option>
                  <option value="contrataciones_publicas" <?= ($motivoInicial === 'contrataciones_publicas') ? 'selected' : ''; ?>>
                    Contrataciones públicas
                  </option>
                  <option value="servicio_notarial" <?= ($motivoInicial === 'servicio_notarial') ? 'selected' : ''; ?>>
                    Servicio notarial
                  </option>
                  <option value="consulta_general" <?= ($motivoInicial === 'consulta_general') ? 'selected' : ''; ?>>
                    Consulta general
                  </option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Mensaje</label>
                <textarea name="mensaje" class="form-control" rows="4" required><?= htmlspecialchars($mensaje ?? ''); ?></textarea>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary px-4">
                Enviar mensaje
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="mt-4 text-muted small">
        Respondemos dentro de <strong>24 a 48 horas hábiles</strong>. <br>
        También puedes escribirnos a <strong>tramitacionesyservicios507@gmail.com</strong> 
        o vía WhatsApp al <strong>+507 6280-1611</strong>.
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
