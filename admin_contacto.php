<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';


if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (function_exists('isAdmin') && !isAdmin()) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No tienes permisos para acceder a esta sección.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}


$motivoFiltro = $_GET['motivo'] ?? '';

$motivos = [
    'constitucion_sociedad'   => 'Constitución de sociedad',
    'contrato'                => 'Contrato',
    'idoneidades_jtia'        => 'Idoneidades JTIA',
    'aviso_operacion'         => 'Aviso de operación',
    'contrataciones_publicas' => 'Contrataciones públicas',
    'servicio_notarial'       => 'Servicio notarial',
    'consulta_general'        => 'Consulta general',
];

function labelMotivo($codigo, $motivos)
{
    return $motivos[$codigo] ?? $codigo;
}


$sql = "SELECT * FROM mensajes_contacto";
$params = [];

if ($motivoFiltro && isset($motivos[$motivoFiltro])) {
    $sql .= " WHERE motivo = ?";
    $params[] = $motivoFiltro;
}

$sql .= " ORDER BY fecha_envio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
  <h2 class="mb-4">Bandeja de contacto</h2>

  <p class="text-muted small mb-4">
    Aquí se muestran los mensajes enviados desde el formulario de contacto de LegalSmart. 
    Solo se listan con fines de seguimiento interno para el proyecto académico.
  </p>


  <form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-md-4">
      <label class="form-label">Filtrar por motivo</label>
      <select name="motivo" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($motivos as $valor => $texto): ?>
          <option value="<?= htmlspecialchars($valor); ?>" <?= $motivoFiltro === $valor ? 'selected' : ''; ?>>
            <?= htmlspecialchars($texto); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-outline-primary">
        Aplicar filtro
      </button>
      <?php if ($motivoFiltro): ?>
        <a href="admin_contacto.php" class="btn btn-link btn-sm">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if (empty($mensajes)): ?>
    <div class="alert alert-info">
      No se han recibido mensajes de contacto todavía.
    </div>
  <?php else: ?>
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 15%">Fecha</th>
                <th style="width: 20%">Nombre</th>
                <th style="width: 20%">Contacto</th>
                <th style="width: 15%">Motivo</th>
                <th>Mensaje</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($mensajes as $m): ?>
                <tr>
                  <td class="small text-muted">
                    <?= htmlspecialchars($m['fecha_envio']); ?>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($m['nombre']); ?></strong>
                  </td>
                  <td class="small">
                    <div><?= htmlspecialchars($m['email']); ?></div>
                    <?php if (!empty($m['telefono'])): ?>
                      <div class="text-muted">Tel: <?= htmlspecialchars($m['telefono']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      <?= htmlspecialchars(labelMotivo($m['motivo'], $motivos)); ?>
                    </span>
                  </td>
                  <td class="small">
                    <?= nl2br(htmlspecialchars($m['mensaje'])); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
