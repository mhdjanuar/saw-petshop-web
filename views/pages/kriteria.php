<?php 
session_start();

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/pages/login.php");
    exit;
}
?>

<?php include "../../config/database.php"; ?>
<?php include "../layouts/header.php"; ?>

<?php
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Export PDF jika diminta
if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $res = $conn->query("SELECT * FROM criteria ORDER BY id");

    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            position: absolute;
            bottom: 30px;
            right: 50px;
            font-size: 12px;
            text-align: right;
        }
    </style>

    <h2 style="text-align:center;">Arzello Petshop</h2>
    <p style="text-align:center;">Jl. Pendowo Raya NO. 105 Kelurahan Limo, Kecamatan Limo, Depok.</p>
    <hr>
    <h3 style="text-align:center;">Laporan Data Kriteria</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Bobot</th>
                <th>Tipe</th>
            </tr>
        </thead>
        <tbody>';

    $no = 1;
    while ($r = $res->fetch_assoc()) {
        $type = htmlspecialchars($r['type']);
        $bobot = number_format($r['bobot'], 2);
        $html .= "<tr>
            <td>$no</td>
            <td>".htmlspecialchars($r['nama'])."</td>
            <td>$bobot</td>
            <td>$type</td>
        </tr>";
        $no++;
    }

    $html .= '
        </tbody>
    </table>

    <div class="footer">
        Jakarta, Sabtu, 16 Agustus 2025<br><br><br>
        <strong>HRD</strong><br>
        Arya Arindita
    </div>';

    ob_clean(); // bersihkan buffer sebelum render
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("laporan_kriteria.pdf", ["Attachment" => false]);
    exit;
}
?>

<div class="card border-0 shadow-soft">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0">Master Data Kriteria</h4>
    </div>

    <!-- Form Input -->
    <form method="post" class="mb-4">
      <div class="row g-2">
        <div class="col-md-4">
          <input type="text" name="nama" class="form-control" placeholder="Nama Kriteria" required>
        </div>
        <div class="col-md-3">
          <input type="number" step="0.01" name="bobot" class="form-control" placeholder="Bobot" required>
        </div>
        <div class="col-md-3">
          <select name="type" class="form-select" required>
            <option value="">--Pilih Tipe--</option>
            <option value="Benefit">Benefit</option>
            <option value="Cost">Cost</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" name="save" class="btn btn-primary w-100">Tambah</button>
        </div>
      </div>
    </form>

    <?php
    // Proses Simpan
    if (isset($_POST['save'])) {
        $nama  = $_POST['nama'];
        $bobot = floatval($_POST['bobot']);
        $type  = $_POST['type'];

        $stmt = $conn->prepare("INSERT INTO criteria(nama, type, bobot) VALUES(?, ?, ?)");
        $stmt->bind_param("ssd", $nama, $type, $bobot);
        $stmt->execute();
        $stmt->close();

        echo "<div class='alert alert-success'>Kriteria berhasil ditambahkan!</div>";
    }

    // Proses Hapus
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM criteria WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        echo "<div class='alert alert-danger'>Kriteria berhasil dihapus!</div>";
    }
    ?>

    <!-- Tombol Export -->
    <a href="?export=pdf" class="btn btn-outline-primary mb-3">Export PDF</a>

    <!-- Tabel Data -->
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr><th>No</th><th>Nama</th><th>Bobot</th><th>Tipe</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php
          $no=1;
          $res = $conn->query("SELECT * FROM criteria ORDER BY id");
          while($r = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($r['nama']); ?></td>
            <td><?= number_format($r['bobot'], 2); ?></td>
            <td>
              <span class="badge text-bg-<?= strtolower($r['type'])=='cost'?'danger':'success'; ?>">
                <?= htmlspecialchars($r['type']); ?>
              </span>
            </td>
            <td>
              <a href="?delete=<?= $r['id']; ?>" 
                 onclick="return confirm('Yakin hapus kriteria ini?')" 
                 class="btn btn-sm btn-danger">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include "../layouts/footer.php"; ?>
