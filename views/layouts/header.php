<?php

// kalau user klik logout
if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header("Location:  views/pages/login.php");
  exit;
}

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
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img style="width: 80px; height: auto;" src="https://i.ibb.co/MxMZ73BJ/petshop-200.png" alt="Logo Petshop" height="40" class="me-2" />
      <span class="fw-bold text-dark">Arzello Petshop</span>
    </a>

    <div class="d-flex align-items-center">
      <span class="me-3 fw-bold text-dark"><?= $_SESSION['name']; ?></span>
      <!-- Logout langsung panggil index.php?logout=1 -->
      <a href="index.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <!-- Content mulai di sini -->
  <h3>Selamat datang, <?= $_SESSION['name']; ?> 👋</h3>
  <p class="text-muted">Ini adalah halaman dashboard SPK Supplier Petshop.</p>
</div>

</body>
</html>
