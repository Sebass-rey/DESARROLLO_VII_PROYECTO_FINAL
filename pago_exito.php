<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$idTramite = isset($_GET['id_tramite']) ? (int)$_GET['id_tramite'] : 0;

// Validar que el trámite sea del usuario
$stmt = $pdo->prepare("
    SELECT t.*, s.nombre AS servicio_nombre
    FROM tramites t
    JOIN servicios s ON s.id = t.id_servicio
    WHERE t.id = ? AND t.id_usuario = ?
");
$stmt->execute([$idTramite, $_SESSION['user_id']]);
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    require_once __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/includes/navbar.php';
    echo "<div class='container mt-4'><div class='alert alert-danger'>Trámite no válido.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Verificar si ya hay un pago registrado
$stmtCheck = $pdo->prepare("SELECT id FROM pagos WHERE id_tramite = ?");
$stmtCheck->execute([$idTramite]);
$pago = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    // Registrar pago como pagado (con Stripe en modo prueba)
    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (id_tramite, monto, metodo, referencia, estado)
        VALUES (?, ?, ?, ?, 'pagado')
    ");
    $stmtPago->execute([
        $tramite['id'],
        $tramite['monto'],
        'Stripe (test)',
        'checkout_session'
    ]);
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <div class="alert alert-success mt-4">
    <h4 class="alert-heading">Pago registrado</h4>
    <p>Tu pago ha sido confirmado correctamente mediante Stripe (modo prueba).</p>
    <hr>
    <p class="mb-0">
      Puedes revisar el estado de tu trámite en 
      <a href="mis_tramites.php" class="alert-link">Mis trámites</a>.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

