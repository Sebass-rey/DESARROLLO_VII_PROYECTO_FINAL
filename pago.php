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

// pasarela de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $stripeSecretKey = 'sk_test_TU_LLAVE_AQUI';

    // Monto en centavos
    $amountCents = (int) round($tramite['monto'] * 100);

    // URL de éxito y cancelación
    $successUrl = 'http://localhost/PROYECTO/legalsmart/pago_exito.php?id_tramite=' . $tramite['id'];
    $cancelUrl  = 'http://localhost/PROYECTO/legalsmart/pago.php?id_tramite=' . $tramite['id'] . '&cancel=1';

    $data = [
        'payment_method_types[]' => 'card',
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url'  => $cancelUrl,
        'line_items[0][price_data][currency]' => 'usd',
        'line_items[0][price_data][product_data][name]' => $tramite['servicio_nombre'],
        'line_items[0][price_data][unit_amount]' => $amountCents,
        'line_items[0][quantity]' => 1,
    ];

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $stripeSecretKey,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $httpCode >= 400) {
        $errorMsg = curl_error($ch) ?: $response;
        curl_close($ch);
        die("Error al crear la sesión de pago con Stripe: " . htmlspecialchars($errorMsg));
    }

    curl_close($ch);

    $session = json_decode($response, true);

    if (!isset($session['url'])) {
        die("No se pudo obtener la URL de pago de Stripe.");
    }

    // Redirigir al Checkout de Stripe
    header("Location: " . $session['url']);
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
  <h2 class="mb-3">Pago del trámite con Stripe</h2>

  <?php if (isset($_GET['cancel'])): ?>
    <div class="alert alert-warning">
      El pago fue cancelado. Puedes intentar nuevamente cuando estés listo.
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-6">
      <form method="post">
        <p>Serás redirigido a una página segura de Stripe para completar el pago.</p>
        <button type="submit" class="btn btn-success">
          Pagar con tarjeta (Stripe - modo prueba)
        </button>
      </form>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5>Resumen del trámite</h5>
          <p><strong>Código:</strong> <?= htmlspecialchars($tramite['codigo_tramite']); ?></p>
          <p><strong>Servicio:</strong> <?= htmlspecialchars($tramite['servicio_nombre']); ?></p>
          <p><strong>Monto:</strong> USD <?= number_format($tramite['monto'], 2); ?></p>
          <p class="text-muted small">
            Este pago se realiza en modo de pruebas de Stripe. No es un cobro real.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
