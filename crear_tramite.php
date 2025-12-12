<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$idServicio = isset($_GET['id_servicio']) ? (int)$_GET['id_servicio'] : 0;

// Cargar servicio (ahora incluye detalle + documentos_json)
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

// Lista de documentos requeridos (desde la BD)
$documentosReq = [];
if (!empty($servicio['documentos_json'])) {
    $tmp = json_decode($servicio['documentos_json'], true);
    if (is_array($tmp)) $documentosReq = $tmp;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($descripcion === '') {
        $errores[] = "Por favor describe brevemente tu caso o necesidad.";
    }

    // Validar uploads según documentos requeridos
    // Si hay documentosReq, cada uno es requerido (si quieres alguno opcional, me lo dices y lo marcamos)
    if (!empty($documentosReq)) {
        for ($i = 0; $i < count($documentosReq); $i++) {
            if (empty($_FILES['docs']['name'][$i])) {
                $errores[] = "Falta adjuntar: " . $documentosReq[$i];
            }
        }
    }

    if (empty($errores)) {
        // Generar código de trámite
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

        // Guardar archivos (uno por documento requerido)
        if (!empty($documentosReq)) {
            // Asegurar carpeta
            $uploadsDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0777, true);
            }

            for ($i = 0; $i < count($documentosReq); $i++) {
                if (empty($_FILES['docs']['name'][$i])) continue;

                $nombreOriginal = $_FILES['docs']['name'][$i];
                $tmp            = $_FILES['docs']['tmp_name'][$i];
                $error          = $_FILES['docs']['error'][$i];

                if ($error !== UPLOAD_ERR_OK) {
                    // Si falla, no rompe todo el trámite, pero lo registra como error
                    $errores[] = "No se pudo subir el archivo: " . $documentosReq[$i];
                    continue;
                }

                $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                $permitidos = ['pdf','jpg','jpeg','png'];
                if (!in_array($ext, $permitidos, true)) {
                    $errores[] = "Formato no permitido en: " . $documentosReq[$i] . " (usa PDF/JPG/PNG).";
                    continue;
                }

                $nombreSeguro = 'doc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $destino = $uploadsDir . $nombreSeguro;

                if (move_uploaded_file($tmp, $destino)) {
                    $nombreArchivoBD = $documentosReq[$i] . ' - ' . $nombreOriginal;
                    $rutaArchivoBD   = 'uploads/' . $nombreSeguro;

                    $stmtArch = $pdo->prepare("
                        INSERT INTO archivos_tramite (id_tramite, nombre_archivo, ruta_archivo)
                        VALUES (?, ?, ?)
                    ");
                    $stmtArch->execute([$idTramite, $nombreArchivoBD, $rutaArchivoBD]);
                } else {
                    $errores[] = "No se pudo guardar el archivo adjunto: " . $documentosReq[$i];
                }
            }
        }

        // Si hubo errores de upload, los mostramos (sin cancelar el trámite)
        if (!empty($errores)) {
            // NO redirige a pago hasta que el usuario vea qué falló
        } else {
            // Ir a pantalla de pago
            header("Location: pago.php?id_tramite=" . $idTramite);
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-3">Iniciar trámite: <?= htmlspecialchars($servicio['nombre']); ?></h2>

  <div class="row">
    <div class="col-md-7">

     <?php if (!empty($servicio['detalle'])): ?>
  <div class="alert alert-secondary">
    
    <div class="mb-2">
      <?= nl2br(htmlspecialchars($servicio['detalle'], ENT_QUOTES, 'UTF-8')); ?>
    </div>


    <?php if (!empty($documentosReq)): ?>
      <hr class="my-2">
      <strong>Documentos requeridos:</strong>
      <ul class="mb-0 mt-1">
        <?php foreach ($documentosReq as $doc): ?>
          <li><?= htmlspecialchars($doc, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  </div>
<?php endif; ?>

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
          <div class="form-text">Explica brevemente el contexto para el abogado (sin datos demasiado sensibles).</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Observaciones adicionales (opcional)</label>
          <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
        </div>

        <?php if (!empty($documentosReq)): ?>
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="mb-3">Documentos requeridos</h5>

              <?php foreach ($documentosReq as $i => $doc): ?>
                <div class="mb-3">
                  <label class="form-label"><?= ($i+1) . '. ' . htmlspecialchars($doc); ?></label>
                  <input type="file" name="docs[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
              <?php endforeach; ?>

              <div class="form-text">Formatos permitidos: PDF, JPG, PNG.</div>
            </div>
          </div>
        <?php else: ?>
          <div class="mb-3">
            <label class="form-label">Adjuntar documento (opcional)</label>
            <input type="file" name="docs[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <div class="form-text">PDF, JPG o PNG.</div>
          </div>
        <?php endif; ?>

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

