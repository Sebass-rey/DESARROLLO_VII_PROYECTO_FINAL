<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/classes/Servicio.php';

$slugs = [
    'constitucion-sociedad-basica',
    'contrato-prestacion-servicios',
    'idoneidad-jtia-profesional',
    'aviso-operaciones',
    'registro-panamacompra',
    'servicios-notariales'
];


$servicioModel   = new Servicio($pdo);
$serviciosPorSlug = $servicioModel->obtenerPorSlugs($slugs);

function getServicio($slug, $serviciosPorSlug) {
    return $serviciosPorSlug[$slug] ?? null;
}
?>

<div class="container my-5">
  <h2 class="text-center mb-4">Servicios legales</h2>

  <p class="text-center text-muted mb-5">
    LegalSmart organiza trámites legales básicos en paquetes claros y digitales para el usuario panameño. 
    Selecciona la categoría que mejor se ajuste a tu necesidad y podrás iniciar el trámite en línea.
  </p>

  <div class="row g-4">

    <!-- 1. Constitución de Sociedades -->
    <?php $serv = getServicio('constitucion-sociedad-basica', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_constitucion_sociedades.jpg"
             alt="Constitución de sociedades"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_default.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Constitución de Sociedades</h5>
          <p class="small text-muted mb-2">
            Creación de sociedad anónima bajo Ley 32, con inscripción en Registro Público y entrega de documentos digitales.
          </p>
          <ul class="small mb-3">
            <li>Pacto social básico.</li>
            <li>Designación de directores y dignatarios.</li>
            <li>Inscripción en el Registro Público.</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Redacción de Contratos -->
    <?php $serv = getServicio('contrato-prestacion-servicios', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_contratos.jpg"
             alt="Redacción de contratos"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_default.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Redacción de Contratos</h5>
          <p class="small text-muted mb-2">
            Contratos claros y adaptados a tu relación comercial o laboral, explicados en lenguaje sencillo.
          </p>
          <ul class="small mb-3">
            <li>Contrato privado de arrendamiento.</li>
            <li>Contrato de prestación de servicios (C2C, C2B, B2C, B2B).</li>
            <li>Contrato laboral (indefinido, definido, por obra).</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Idoneidades Profesionales – JTIA -->
    <?php $serv = getServicio('idoneidad-jtia-profesional', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_idoneidades_jtia.jpg"
             alt="Idoneidades Profesionales JTIA"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_default.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Idoneidades Profesionales – JTIA</h5>
          <p class="small text-muted mb-2">
            Preparación y revisión de expedientes para idoneidad ante la Junta Técnica de Ingeniería y Arquitectura.
          </p>
          <ul class="small mb-3">
            <li>Revisión de requisitos y documentos.</li>
            <li>Llenado de formularios.</li>
            <li>Acompañamiento en el proceso de presentación.</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Aviso de Operaciones -->
    <?php $serv = getServicio('aviso-operaciones', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_aviso_operaciones.jpg"
             alt="Aviso de operaciones"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_default.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Aviso de Operaciones</h5>
          <p class="small text-muted mb-2">
            Trámite ante el Ministerio de Comercio e Industrias para formalizar tu actividad económica.
          </p>
          <ul class="small mb-3">
            <li>Revisión de actividad económica.</li>
            <li>Gestión en línea del aviso.</li>
            <li>Entrega del documento digital.</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Registro en PanamaCompra -->
    <?php $serv = getServicio('registro-panamacompra', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_panamacompra.jpg"
             alt="Registro en PanamaCompra"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_contrataciones_publicas.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Registro en PanamaCompra</h5>
          <p class="small text-muted mb-2">
            Apoyo para registrar tu empresa como proveedor del Estado panameño.
          </p>
          <ul class="small mb-3">
            <li>Revisión de requisitos.</li>
            <li>Registro en la plataforma.</li>
            <li>Orientación básica sobre el uso del sistema.</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Servicios notariales -->
    <?php $serv = getServicio('servicios-notariales', $serviciosPorSlug); ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 services-card d-flex flex-column">
        <img src="/PROYECTO/legalsmart/assets/img/serv_notorios.jpg"
             alt="Servicios notariales"
             class="services-img"
             onerror="this.onerror=null;this.src='/PROYECTO/legalsmart/assets/img/serv_servicios_notariales.jpg';">
        <div class="card-body d-flex flex-column">
          <h5 class="services-card-title mb-2">Servicios notariales básicos</h5>
          <p class="small text-muted mb-2">
            Coordinación de firmas y autenticaciones notariales relacionadas con tus trámites.
          </p>
          <ul class="small mb-3">
            <li>Poderes especiales simples.</li>
            <li>Autenticación de firmas.</li>
            <li>Reconocimiento de firmas en documentos básicos.</li>
          </ul>
          <div class="mt-auto">
            <?php if ($serv): ?>
              <a href="servicio_detalle.php?id=<?= $serv['id']; ?>" class="btn btn-primary btn-sm w-100">
                Solicitar
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="text-center mt-5">
    <p class="text-muted small mb-2">
      Otros servicios adicionales, como la consulta legal en línea, pueden gestionarse según el caso concreto.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>






