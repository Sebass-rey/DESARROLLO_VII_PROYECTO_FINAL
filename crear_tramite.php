<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$idServicio = isset($_GET['id_servicio']) ? (int)$_GET['id_servicio'] : 0;

// Cargar servicio
$stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ? AND activo = 1");
$stmt->execute([$idServicio]);
$servicio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$servicio) {
    require_once __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/includes/navbar.php';
    echo "<div class='container mt-4'><div class='alert alert-danger'>Servicio no válido.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($descripcion === '') {
        $errores[] = "Por favor describe brevemente tu caso o necesidad.";
    }

    // Manejo de archivo (opcional)
    $nombreArchivoBD = null;
    $rutaArchivoBD = null;

    if (!empty($_FILES['archivo']['name'])) {
        $nombreOriginal = $_FILES['archivo']['name'];
        $tmp = $_FILES['archivo']['tmp_name'];

        $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $nombreSeguro = 'doc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

        $destino = __DIR__ . '/uploads/' . $nombreSeguro;

        if (move_uploaded_file($tmp, $destino)) {
            $nombreArchivoBD = $nombreOriginal;
            $rutaArchivoBD = 'uploads/' . $nombreSeguro;
        } else {
            $errores[] = "No se pudo guardar el archivo adjunto.";
        }
    }

    if (empty($errores)) {
        // Generar código de trámite simple
        $codigo = 'LS-' . date('YmdHis') . '-' . rand(100, 999);

        // Insertar trámite
        $stmt = $pdo->prepare("
            INSERT INTO tramites 
            (id_usuario, id_servicio, codigo_tramite, descripcion, estado_actual, monto, observaciones_cliente) 
            VALUES (?, ?, ?, ?, 'recibido', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $servicio['id'],
            $codigo,
            $descripcion,
            $servicio['precio'],
            $observaciones
        ]);

        $idTramite = $pdo->lastInsertId();

        // Insertar historial de estado inicial
        $stmtHist = $pdo->prepare("
            INSERT INTO historial_estados (id_tramite, estado, comentario) 
            VALUES (?, 'recibido', 'Trámite creado por el cliente')
        ");
        $stmtHist->execute([$idTramite]);

        // Guardar archivo si existe
        if ($rutaArchivoBD) {
            $stmtArch = $pdo->prepare("
                INSERT INTO archivos_tramite (id_tramite, nombre_archivo, ruta_archivo)
                VALUES (?, ?, ?)
            ");
            $stmtArch->execute([$idTramite, $nombreArchivoBD, $rutaArchivoBD]);
        }

        // Ir a pantalla de pago
        header("Location: pago.php?id_tramite=" . $idTramite);
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-3">Iniciar trámite: <?= htmlspecialchars($servicio['nombre']); ?></h2>

  <div class="row">
    <div class="col-md-7">
      <?php if ($errores): ?>
        <div class="alert alert-danger">
          <?php foreach ($errores as $e): ?>
            <div><?= htmlspecialchars($e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Describe tu caso</label>
          <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
          <div class="form-text">
            Explica brevemente el contexto para el abogado (sin datos demasiado sensibles).
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Observaciones adicionales (opcional)</label>
          <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Adjuntar documento (opcional)</label>
          <input type="file" name="archivo" class="form-control">
          <div class="form-text">PDF, JPG, PNG u otro documento relacionado.</div>
        </div>

        <button type="submit" class="btn btn-primary">Continuar al pago</button>
      </form>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-body">
          <h5>Resumen del servicio</h5>
          <p class="mb-1"><strong>Servicio:</strong> <?= htmlspecialchars($servicio['nombre']); ?></p>
          <p class="mb-1"><strong>Precio:</strong> USD <?= number_format($servicio['precio'], 2); ?></p>
          <?php if (!empty($servicio['tiempo_estimado'])): ?>
            <p class="mb-1"><strong>Tiempo estimado:</strong> <?= htmlspecialchars($servicio['tiempo_estimado']); ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
