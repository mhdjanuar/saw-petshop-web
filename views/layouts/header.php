<?php
// kalau user klik logout
if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header("Location: views/pages/login.php");
  exit;
}

$base_url = "http://localhost/spk-saw-php/";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SPK Supplier Petshop - SAW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .navbar-custom-bg {
      background-color: #f5e9d4 !important;
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-custom-bg shadow">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>index.php">
      <img style="width: 80px; height: auto;" src="https://i.postimg.cc/FRHxD9R4/petshop-200.png" alt="Logo Petshop" height="40" class="me-2" />
      <span class="fw-bold text-dark">Arzello Petshop</span>
    </a>

    <!-- tombol collapse untuk hp -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
      <!-- menu navigasi -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>index.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>views/pages/supplier.php">Supplier</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>views/pages/kriteria.php">Kriteria</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>views/pages/subkriteria.php">Sub Kriteria</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>views/pages/penilaian.php">Penilaian</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $base_url ?>views/pages/hasil.php">Hasil Ranking</a>
        </li>
      </ul>

      <!-- user + logout -->
      <div class="d-flex align-items-center">
        <span class="me-3 fw-bold text-dark"><?= $_SESSION['name']; ?></span>
        <a href="<?= $base_url ?>index.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container py-4">
  <!-- Content mulai di sini -->
  <h3>Selamat datang, <?= $_SESSION['name']; ?> 👋</h3>
  <p class="text-muted">Ini adalah halaman dashboard SPK Supplier Petshop.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
