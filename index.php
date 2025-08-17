<?php
session_start();

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/pages/login.php");
    exit;
}

include __DIR__ . "/config/database.php";
include __DIR__ . "/views/layouts/header.php";
?>

<style>
  .dashboard-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
    background: #ffffff;
  }

  .dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  }

  .dashboard-icon {
    font-size: 36px;
    color: #4e73df;
    margin-bottom: 10px;
  }

  .dashboard-header img {
    width: 100px;
    height: auto;
    border-radius: 50%;
    margin-bottom: 10px;
  }

  .btn-dark {
    background-color: #343a40;
    border: none;
  }

  .btn-dark:hover {
    background-color: #23272b;
  }

  .btn-success {
    border-radius: 20px;
  }

  .footer-text {
    font-size: 14px;
    color: #777;
  }
</style>

<div class="row dashboard-header text-center mb-4">
  <div class="col-12">
    <img style="width: 150px; height: auto;" src="assets/img/dasboard-petshop.jpg" alt="Logo Petshop">
    <h2 class="fw-bold">Sistem Pendukung Keputusan</h2>
    <p class="text-muted">Pemilihan Supplier Petshop dengan Metode SAW</p>
  </div>
</div>

<div class="row g-4 justify-content-center">
  <!-- Supplier -->
  <div class="col-sm-6 col-md-3">
    <div class="card dashboard-card text-center p-3">
      <div class="card-body">
        <div class="dashboard-icon"><i class="fas fa-store"></i></div>
        <h5 class="fw-bold">Supplier</h5>
        <p class="text-muted">Master data supplier</p>
        <a href="views/pages/supplier.php" class="btn btn-dark w-100">Kelola</a>
      </div>
    </div>
  </div>

  <!-- Kriteria -->
  <div class="col-sm-6 col-md-3">
    <div class="card dashboard-card text-center p-3">
      <div class="card-body">
        <div class="dashboard-icon"><i class="fas fa-balance-scale"></i></div>
        <h5 class="fw-bold">Kriteria</h5>
        <p class="text-muted">Bobot & tipe (benefit/cost)</p>
        <a href="views/pages/kriteria.php" class="btn btn-dark w-100">Kelola</a>
      </div>
    </div>
  </div>

  <!-- Sub Kriteria -->
  <div class="col-sm-6 col-md-3">
    <div class="card dashboard-card text-center p-3">
      <div class="card-body">
        <div class="dashboard-icon"><i class="fas fa-sliders-h"></i></div>
        <h5 class="fw-bold">Sub Kriteria</h5>
        <p class="text-muted">Master data sub kriteria</p>
        <a href="views/pages/subkriteria.php" class="btn btn-dark w-100">Kelola</a>
      </div>
    </div>
  </div>

  <!-- Penilaian -->
  <div class="col-sm-6 col-md-3">
    <div class="card dashboard-card text-center p-3">
      <div class="card-body">
        <div class="dashboard-icon"><i class="fas fa-edit"></i></div>
        <h5 class="fw-bold">Penilaian</h5>
        <p class="text-muted">Nilai alternatif per kriteria</p>
        <a href="views/pages/penilaian.php" class="btn btn-dark w-100">Input</a>
      </div>
    </div>
  </div>
</div>

<!-- Hasil Ranking -->
<div class="row mt-4 justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-soft text-center">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-3">Hasil Perankingan</h5>
        <a href="views/pages/hasil.php" class="btn btn-success btn-lg">Lihat Hasil</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/views/layouts/footer.php"; ?>
