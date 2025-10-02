<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema_tortillas.php';
require_once __DIR__ . '/schema_rellenitos.php';

$deliveredOrders = 0;
$satisfiedClients = 0;
$statsError = null;

try {
  $conn = db();

  if (!($conn instanceof mysqli)) {
    throw new RuntimeException('No se pudo obtener una conexión a la base de datos.');
  }

  ensure_tortilla_schema($conn);
  ensure_rellenitos_schema($conn);

  $tortillaDelivered = 0;
  $tortillaClients = 0;
  $rellenitoDelivered = 0;
  $rellenitoClients = 0;

  if ($result = $conn->query("SELECT COUNT(*) AS delivered_orders, COUNT(DISTINCT codigo) AS satisfied_clients FROM tortilla_orders WHERE estado='Despachado'")) {
    $row = $result->fetch_assoc();
    if ($row) {
      $tortillaDelivered = (int)($row['delivered_orders'] ?? 0);
      $tortillaClients = (int)($row['satisfied_clients'] ?? 0);
    }
    $result->free();
  }

  if ($result = $conn->query("SELECT COUNT(*) AS delivered_orders, COUNT(DISTINCT codigo) AS satisfied_clients FROM rellenito_orders WHERE estado='Despachado'")) {
    $row = $result->fetch_assoc();
    if ($row) {
      $rellenitoDelivered = (int)($row['delivered_orders'] ?? 0);
      $rellenitoClients = (int)($row['satisfied_clients'] ?? 0);
    }
    $result->free();
  }

  $deliveredOrders = $tortillaDelivered + $rellenitoDelivered;
  $satisfiedClients = $tortillaClients + $rellenitoClients;
} catch (Throwable $e) {
  $statsError = 'No se pudieron cargar las métricas en este momento.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menú de Ventas | Antojitos</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- AOS (Animate On Scroll) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />

  <!-- Shared UI helpers -->
  <link rel="stylesheet" href="assets/css/ui-enhancements.css">

  <!-- Custom styles -->
  <style>
    :root{
      --brand:#ff6b6b;
      --brand-2:#ffc371;
      --dark:#0f0f10;
      --muted:#a7a7a7;
    }

    html, body{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif;
      background:#0a0a0a;
      color:#e9ecef;
      scroll-behavior:smooth;
    }

    .navbar{
      background:rgba(10,10,10,.7);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .brand-dot{
      display:inline-block;width:10px;height:10px;border-radius:50%;
      background:linear-gradient(135deg, var(--brand), var(--brand-2));
      box-shadow:0 0 15px rgba(255,107,107,.7);
      margin-right:.5rem;
      animation:pulse 2s infinite;
    }

    @keyframes pulse{
      0%,100%{ transform:scale(1); box-shadow:0 0 10px rgba(255,107,107,.6);}
      50%{ transform:scale(1.25); box-shadow:0 0 22px rgba(255,195,113,.9);}
    }

    .hero{
      position:relative;
      min-height:75vh;
      background: url('assets/hero-reference.png') center / cover no-repeat fixed;
      display:grid; place-items:center;
      isolation:isolate;
    }

    .hero::after{
      content:''; position:absolute; inset:0;
      background: linear-gradient(180deg, rgba(0,0,0,.65), rgba(0,0,0,.75));
      z-index:0;
    }

    .hero .content{
      position:relative; z-index:2;
      text-align:center;
      padding: 2rem;
    }

    .hero-stats{
      margin-top:2.5rem;
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:1.5rem;
    }

    .hero-stat{
      min-width:180px;
      padding:1.1rem 1.6rem;
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.18);
      border-radius:1.5rem;
      box-shadow:0 18px 38px rgba(0,0,0,.45);
    }

    .hero-stat__label{
      display:block;
      font-size:.75rem;
      text-transform:uppercase;
      letter-spacing:.18em;
      color:rgba(233,236,239,.65);
      margin-bottom:.35rem;
    }

    .hero-stat__value{
      display:block;
      font-size:2.2rem;
      font-weight:700;
      color:#fff;
    }

    .hero-stats__error{
      margin-top:2rem;
      display:inline-block;
      padding:.7rem 1.4rem;
      border-radius:999px;
      background:rgba(255,107,107,.15);
      border:1px solid rgba(255,107,107,.4);
      color:#ffc371;
      font-weight:500;
    }

    .badge-soft{
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.12);
      color:#fff;
    }

    .neon{
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      text-shadow: 0 0 30px rgba(255,107,107,.35);
    }

    .cta-btn{
      border-radius: 999px;
      padding:.9rem 1.4rem;
      font-weight:700;
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
      border:none;
      box-shadow: 0 8px 24px rgba(255,107,107,.35);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .cta-btn:hover{
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(255,195,113,.5);
    }

    /* Cards */
    .menu-card{
      background: radial-gradient(100% 100% at 0% 0%, rgba(255,255,255,.06), rgba(255,255,255,.02));
      border:1px solid rgba(255,255,255,.08);
      border-radius: 1.25rem;
      padding:1.25rem;
      transition: transform .25s ease, border-color .25s ease, background .25s ease;
    }
    .menu-card:hover{
      transform: translateY(-6px);
      border-color: rgba(255,255,255,.22);
      background: radial-gradient(120% 120% at 0% 0%, rgba(255,255,255,.10), rgba(255,255,255,.03));
    }

    .menu-card .title{
      font-weight:800; letter-spacing:.2px;
    }

    .menu-card .btn-outline-light{
      border-radius: 12px;
      border:1px solid rgba(255,255,255,.28);
    }

    .divider{
      height:1px;width:100%;
      background:linear-gradient(90deg, rgba(255,255,255,.0), rgba(255,255,255,.18), rgba(255,255,255,.0));
      margin: 2rem 0;
    }

    footer{
      color:var(--muted);
      font-size:.95rem;
    }

    .shadow-soft{
      box-shadow: 0 10px 30px rgba(0,0,0,.35);
    }

    .floating-badge{
      position:fixed;
      right:1rem; bottom:1rem; z-index:999;
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.12);
      padding:.5rem 1rem;border-radius:999px;backdrop-filter: blur(6px);
    }

    #equipo{
      position:relative;
      overflow:hidden;
    }

    #equipo::before{
      content:'';
      position:absolute;
      inset:0;
      background:
        radial-gradient(110% 110% at 0% 0%, rgba(255,107,107,.18), rgba(255,255,255,0) 70%),
        radial-gradient(90% 120% at 90% 100%, rgba(255,195,113,.16), rgba(255,255,255,0) 65%);
      opacity:.75;
      pointer-events:none;
    }

    #equipo .container{
      position:relative;
      z-index:1;
    }

    .team-subtitle{
      display:inline-flex;
      align-items:center;
      gap:.55rem;
      letter-spacing:.38em;
      text-transform:uppercase;
      font-size:.75rem;
      color:rgba(255,255,255,.62);
    }

    .team-subtitle::before{
      content:'';
      width:32px;
      height:2px;
      background:linear-gradient(135deg, var(--brand), var(--brand-2));
      border-radius:999px;
    }

    .team-lead{
      max-width:460px;
      color:rgba(233,236,239,.75);
    }

    .team-metrics{
      display:flex;
      gap:1.25rem;
      flex-wrap:wrap;
      margin-top:2rem;
    }

    .team-metric{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.16);
      border-radius:1.35rem;
      padding:1.1rem 1.35rem;
      min-width:150px;
      box-shadow:0 16px 32px rgba(10,10,10,.4);
    }

    .team-metric strong{
      display:block;
      font-size:1.9rem;
      color:#fff;
      letter-spacing:.02em;
    }

    .team-metric span{
      display:block;
      font-size:.72rem;
      text-transform:uppercase;
      letter-spacing:.12em;
      color:rgba(233,236,239,.65);
    }

    .team-process{
      list-style:none;
      margin:2.25rem 0 0;
      padding:0;
      display:grid;
      gap:1.2rem;
    }

    .team-process li{
      display:flex;
      align-items:flex-start;
      gap:1rem;
    }

    .team-process .step-number{
      width:36px;
      height:36px;
      border-radius:50%;
      background:linear-gradient(135deg, rgba(255,107,107,.25), rgba(255,195,113,.25));
      border:1px solid rgba(255,255,255,.18);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      color:#fff;
      box-shadow:0 10px 25px rgba(10,10,10,.45);
    }

    .team-process h6{
      font-weight:700;
    }

    .team-process p{
      color:rgba(233,236,239,.68);
    }

    .team-card{
      position:relative;
      background:radial-gradient(140% 140% at 0% 0%, rgba(255,255,255,.14), rgba(255,255,255,.04));
      border:1px solid rgba(255,255,255,.18);
      border-radius:1.75rem;
      padding:1.6rem;
      height:100%;
      display:flex;
      flex-direction:column;
      gap:1.4rem;
      box-shadow:0 18px 42px rgba(0,0,0,.45);
      transition:transform .35s ease, border-color .35s ease, box-shadow .35s ease;
    }

    .team-card:hover{
      transform:translateY(-10px);
      border-color:rgba(255,255,255,.42);
      box-shadow:0 24px 52px rgba(0,0,0,.55);
    }

    .team-card__media{
      position:relative;
      border-radius:1.25rem;
      overflow:hidden;
    }

    .team-card__media img{
      width:100%;
      height:220px;
      object-fit:cover;
      display:block;
      filter:saturate(1.12);
      transform:scale(1.02);
      transition:transform .5s ease;
    }

    .team-card:hover .team-card__media img{
      transform:scale(1.08);
    }

    .team-card__tag{
      position:absolute;
      left:1rem;
      bottom:1rem;
      display:inline-flex;
      align-items:center;
      gap:.4rem;
      padding:.4rem .85rem;
      border-radius:999px;
      background:rgba(10,10,10,.75);
      border:1px solid rgba(255,255,255,.2);
      font-size:.75rem;
      letter-spacing:.08em;
      text-transform:uppercase;
      color:#fff;
      box-shadow:0 14px 28px rgba(0,0,0,.55);
    }

    .team-card__body{
      display:flex;
      flex-direction:column;
      gap:1rem;
    }

    .team-card__header{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:.75rem;
    }

    .team-card__role{
      font-size:.75rem;
      text-transform:uppercase;
      letter-spacing:.16em;
      color:rgba(233,236,239,.62);
    }

    .team-card__bio{
      margin-bottom:0;
      color:rgba(233,236,239,.74);
      font-size:.95rem;
      line-height:1.55;
    }

    .team-card__traits{
      list-style:none;
      padding:0;
      margin:0;
      display:grid;
      gap:.55rem;
    }

    .team-card__traits li{
      display:flex;
      align-items:center;
      gap:.55rem;
      font-size:.85rem;
      color:rgba(233,236,239,.72);
    }

    .team-card__traits i{
      color:var(--brand);
    }

    .team-card__social{
      margin-top:auto;
      display:flex;
      align-items:center;
      gap:.6rem;
    }

    .team-card__social a{
      width:38px;
      height:38px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:50%;
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.18);
      color:#fff;
      transition:background .3s ease, transform .3s ease, box-shadow .3s ease;
    }

    .team-card__social a:hover{
      transform:translateY(-3px);
      background:linear-gradient(135deg, var(--brand), var(--brand-2));
      box-shadow:0 12px 28px rgba(255,107,107,.35);
    }

    .team-callout{
      margin-top:3rem;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.16);
      border-radius:1.5rem;
      padding:1.5rem 1.8rem;
      display:flex;
      align-items:center;
      gap:1.2rem;
      box-shadow:0 20px 45px rgba(0,0,0,.45);
    }

    .team-callout i{
      font-size:2.25rem;
      color:var(--brand-2);
    }

    .team-callout p{
      margin:0;
      color:rgba(233,236,239,.76);
    }

    .team-callout strong{
      color:#fff;
    }

    @media (max-width: 991.98px){
      .team-card{
        height:auto;
      }
      .team-card__media img{
        height:260px;
      }
    }

    @media (max-width: 575.98px){
      .hero-stat{
        width:100%;
      }

      .team-card__media img{
        height:220px;
      }
      .team-callout{
        flex-direction:column;
        text-align:center;
      }
    }
  </style>
</head>

<body>
  <div id="pageLoader" class="page-loader">
    <div class="page-loader__spinner"></div>
    <div class="page-loader__progress"></div>
    <p class="page-loader__text">Cargando experiencia Antojitos...</p>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container py-2">
      <a class="navbar-brand fw-bold text-white" href="#inicio">
        <span class="brand-dot"></span> Antojitos Menu
      </a>
      <button class="navbar-toggler text-bg-dark" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link text-white-50" href="#menu">Menú</a></li>
          <li class="nav-item"><a class="nav-link text-white-50" href="#equipo">Equipo</a></li>
          <li class="nav-item"><a class="nav-link text-white-50" href="#contacto">Contacto</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header id="inicio" class="hero shadow-soft">
    <div class="content container">
      <span class="badge rounded-pill badge-soft px-3 py-2" data-aos="zoom-in" data-aos-delay="100">
        <i class="bi bi-stars me-1"></i> Tradición Guatemalteca con estilo moderno
      </span>
      <h1 class="display-4 fw-black mt-3 neon" data-aos="fade-up">Antojitos Deliciosos</h1>
      <p class="lead text-white-50 mb-4" data-aos="fade-up" data-aos-delay="150">
        Explora nuestro menú y compra con un clic. Rápido, sencillo y sabroso.
      </p>
      <a href="#menu" class="btn cta-btn" data-aos="fade-up" data-aos-delay="250">
        Ver Menú
      </a>
      <?php if ($statsError === null): ?>
        <div class="hero-stats" data-aos="fade-up" data-aos-delay="320">
          <div class="hero-stat">
            <span class="hero-stat__label">Pedidos entregados</span>
            <span class="hero-stat__value"><?= number_format($deliveredOrders, 0, ',', '.') ?></span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__label">Clientes satisfechos</span>
            <span class="hero-stat__value"><?= number_format($satisfiedClients, 0, ',', '.') ?></span>
          </div>
        </div>
      <?php else: ?>
        <p class="hero-stats__error" data-aos="fade-up" data-aos-delay="320">
          <?= htmlspecialchars($statsError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <!-- MENÚ -->
  <section id="menu" class="py-5">
    <div class="container">
      <div class="row align-items-center mb-3">
        <div class="col">
          <h2 class="fw-bold">Menú principal</h2>
          <p class="text-white-50 mb-0">Selecciona una opción para ir directamente a la venta.</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Tostadas -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
          <div class="menu-card h-100">
            <div class="d-flex align-items-center mb-2">
              <lord-icon
                src="https://cdn.lordicon.com/pbbsmkso.json"
                trigger="hover"
                style="width:42px;height:42px;margin-right:.5rem">
              </lord-icon>
              <div class="title">Venta de Tostadas</div>
            </div>
            <p class="text-white-50 small mb-3">Crujientes, recién preparadas y llenas de sabor.</p>
            <a href="Tostadas.php" class="btn btn-outline-light w-100">
              Ir a Tostadas <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>

        <!-- Rellenitos -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="50">
          <div class="menu-card h-100">
            <div class="d-flex align-items-center mb-2">
              <lord-icon
                src="https://cdn.lordicon.com/udhxbiyr.json"
                trigger="hover"
                style="width:42px;height:42px;margin-right:.5rem">
              </lord-icon>
              <div class="title">Venta de Rellenitos</div>
            </div>
            <p class="text-white-50 small mb-3">Dulces de plátano con frijol y toque de azúcar.</p>
            <a href="Rellenitos.php" class="btn btn-outline-light w-100">
              Ir a Rellenitos <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>

        <!-- Atoles -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
          <div class="menu-card h-100">
            <div class="d-flex align-items-center mb-2">
              <lord-icon
                src="https://cdn.lordicon.com/zfzufhzk.json"
                trigger="hover"
                style="width:42px;height:42px;margin-right:.5rem">
              </lord-icon>
              <div class="title">Venta de Atol</div>
            </div>
            <p class="text-white-50 small mb-3">Calientito y reconfortante, perfecto para la tarde.</p>
            <a href="Atoles.php" class="btn btn-outline-light w-100">
              Ir a Atoles <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>

        <!-- Tortillas de Harina -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="150">
          <div class="menu-card h-100">
            <div class="d-flex align-items-center mb-2">
              <lord-icon
                src="https://cdn.lordicon.com/hiqmdfkt.json"
                trigger="hover"
                style="width:42px;height:42px;margin-right:.5rem">
              </lord-icon>
              <div class="title">Venta de Tortillas de Harina</div>
            </div>
            <p class="text-white-50 small mb-3">Suaves y listas para tus mejores combinaciones.</p>
            <a href="Tortillas.php" class="btn btn-outline-light w-100">
              Ir a Tortillas <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Extras / CTA -->
      <div class="row align-items-center g-4">
        <div class="col-lg-8" data-aos="fade-right">
          <div class="p-4 rounded-4" style="background:linear-gradient(135deg, rgba(255,107,107,.12), rgba(255,195,113,.10)); border:1px solid rgba(255,255,255,.12)">
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-bag-check fs-2"></i>
              <div>
                <h5 class="mb-1">¿Listo para atender pedidos?</h5>
                <p class="mb-0 text-white-50">Este inicio funciona como menú. Personalízalo y conecta con tus páginas PHP.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end" data-aos="fade-left">
          <a href="#contacto" class="btn cta-btn">Contáctanos</a>
        </div>
      </div>
    </div>
  </section>

  <!-- EQUIPO -->
  <section id="equipo" class="py-5">
    <div class="container">
      <div class="row align-items-start g-5">
        <div class="col-lg-4">
          <span class="team-subtitle" data-aos="fade-up">Equipo en acción</span>
          <h2 class="fw-bold display-6 mt-3" data-aos="fade-up" data-aos-delay="60">Nuestro equipo</h2>
          <p class="team-lead mt-3">Detrás de cada pedido hay personas que aman la cocina guatemalteca y cuidan cada detalle: desde la masa perfecta hasta el saludo final.</p>
          <div class="team-metrics">
            <div class="team-metric">
              <strong>12</strong>
              <span>Recetas con sello propio</span>
            </div>
            <div class="team-metric">
              <strong>4.9★</strong>
              <span>Calificación de clientes</span>
            </div>
          </div>
          <ul class="team-process">
            <li>
              <span class="step-number">1</span>
              <div>
                <h6 class="mb-1 text-white">Planificación diaria</h6>
                <p class="mb-0 small">Nos reunimos cada mañana para revisar rutas, ingredientes locales y sorpresas del día.</p>
              </div>
            </li>
            <li>
              <span class="step-number">2</span>
              <div>
                <h6 class="mb-1 text-white">Preparación artesanal</h6>
                <p class="mb-0 small">Todo se prepara al momento para mantener el aroma y la textura que nos caracteriza.</p>
              </div>
            </li>
            <li>
              <span class="step-number">3</span>
              <div>
                <h6 class="mb-1 text-white">Entrega cercana</h6>
                <p class="mb-0 small">Coordinamos entregas puntuales, avisos en vivo y un servicio cálido hasta tu puerta.</p>
              </div>
            </li>
          </ul>
        </div>
        <div class="col-lg-8">
          <div class="row g-4">
            <div class="col-12 col-md-6 col-xl-4">
              <article class="team-card">
                <div class="team-card__media">
                  <img src="https://images.unsplash.com/photo-1521575107034-e0fa0b594529?q=80&w=640" class="img-fluid" loading="lazy" alt="Lucía Méndez preparando ingredientes frescos" />
                  <span class="team-card__tag"><i class="bi bi-fire"></i> Cocina de hogar</span>
                </div>
                <div class="team-card__body">
                  <div class="team-card__header">
                    <h5 class="mb-0 text-white">Lucía Méndez</h5>
                    <span class="team-card__role">Chef líder</span>
                  </div>
                  <p class="team-card__bio">Chef principal y guardiana de la sazón familiar. Supervisa cada relleno y propone nuevas combinaciones dulces y saladas.</p>
                  <ul class="team-card__traits">
                    <li><i class="bi bi-star-fill"></i> Masa fresca y rellenos equilibrados</li>
                    <li><i class="bi bi-heart-fill"></i> Capacitaciones constantes al equipo de cocina</li>
                  </ul>
                  <div class="team-card__social">
                    <a href="https://wa.me/50255555555" target="_blank" rel="noopener" aria-label="Chatear con Lucía por WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    <a href="mailto:cocina@antojitos.gt" aria-label="Enviar correo a Lucía"><i class="bi bi-envelope"></i></a>
                  </div>
                </div>
              </article>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
              <article class="team-card">
                <div class="team-card__media">
                  <img src="https://images.unsplash.com/photo-1517244683847-7456b63c5969?q=80&w=640" class="img-fluid" loading="lazy" alt="Diego Carranza atendiendo a clientes" />
                  <span class="team-card__tag"><i class="bi bi-emoji-smile"></i> Servicio atento</span>
                </div>
                <div class="team-card__body">
                  <div class="team-card__header">
                    <h5 class="mb-0 text-white">Diego Carranza</h5>
                    <span class="team-card__role">Coordinación</span>
                  </div>
                  <p class="team-card__bio">Lidera la comunicación con clientes y el seguimiento de pedidos. Es la voz que recibe tus antojos con una sonrisa.</p>
                  <ul class="team-card__traits">
                    <li><i class="bi bi-chat-dots-fill"></i> Respuestas en menos de 5 minutos</li>
                    <li><i class="bi bi-pin-map-fill"></i> Monitoreo en tiempo real de entregas</li>
                  </ul>
                  <div class="team-card__social">
                    <a href="https://m.me/antojitos" target="_blank" rel="noopener" aria-label="Enviar mensaje a Diego"><i class="bi bi-messenger"></i></a>
                    <a href="mailto:servicio@antojitos.gt" aria-label="Enviar correo a Diego"><i class="bi bi-envelope-open"></i></a>
                  </div>
                </div>
              </article>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
              <article class="team-card">
                <div class="team-card__media">
                  <img src="https://images.unsplash.com/photo-1520951272-2a5f0c4b0963?q=80&w=640" class="img-fluid" loading="lazy" alt="María Paula organizando entregas" />
                  <span class="team-card__tag"><i class="bi bi-truck"></i> Logística ágil</span>
                </div>
                <div class="team-card__body">
                  <div class="team-card__header">
                    <h5 class="mb-0 text-white">María Paula</h5>
                    <span class="team-card__role">Logística</span>
                  </div>
                  <p class="team-card__bio">Coordina tiempos de salida, rutas y alianzas con repartidores de confianza para que todo llegue caliente y puntual.</p>
                  <ul class="team-card__traits">
                    <li><i class="bi bi-clock-history"></i> Entregas puntuales y seguimiento en vivo</li>
                    <li><i class="bi bi-box-seam"></i> Empaques sostenibles y seguros</li>
                  </ul>
                  <div class="team-card__social">
                    <a href="https://wa.me/50255554444" target="_blank" rel="noopener" aria-label="Chatear con María Paula"><i class="bi bi-telephone"></i></a>
                    <a href="mailto:logistica@antojitos.gt" aria-label="Enviar correo a María Paula"><i class="bi bi-envelope"></i></a>
                  </div>
                </div>
              </article>
            </div>
          </div>
          <div class="team-callout mt-4 mt-lg-5">
            <i class="bi bi-people"></i>
            <p class="mb-0">Coordinamos más de <strong>320 entregas</strong> cada mes y cada pedido sale con el mismo entusiasmo de la primera vez.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="py-4 border-top border-opacity-25 border-white">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
      <p class="mb-0">© 2025 Antojitos. Hecho con ❤️ en Guatemala.</p>
      <div class="small text-white-50">Bootstrap · Lordicon · AOS</div>
    </div>
  </footer>

  <div class="floating-badge d-none d-md-inline-flex align-items-center gap-2">
    <lord-icon
      src="https://cdn.lordicon.com/akuwjdzh.json"
      trigger="loop-on-hover"
      delay="1500"
      style="width:28px;height:28px">
    </lord-icon>
    <span class="small">Desliza y explora el menú</span>
  </div>

  <!-- Lordicon -->
  <script src="https://cdn.lordicon.com/lordicon.js"></script>
  <!-- AOS -->
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="assets/js/ui-enhancements.js"></script>

  <script>
    AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' });
  </script>
</body>
</html>
