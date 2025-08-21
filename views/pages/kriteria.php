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

// === Logo dari local (base64) ===
    $logoPath = __DIR__ . '/../../assets/img/petshop-200.png';

    if (!file_exists($logoPath)) {
        die("Logo tidak ditemukan di: $logoPath");
    }

    $logoData = base64_encode(file_get_contents($logoPath));
    $logoUri  = 'data:image/png;base64,' . $logoData;

    $res = $conn->query("SELECT * FROM criteria ORDER BY id");

    // === Tanggal Indonesia Manual ===
    $hariIndo = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    $bulanIndo = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $hari   = $hariIndo[date('l')];
    $tanggal= date('d');
    $bulan  = $bulanIndo[(int)date('m')];
    $tahun  = date('Y');

    $today = "$hari, $tanggal $bulan $tahun";

    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; }
        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; }
        .sub-header { font-size: 12px; }
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

    <div class="header">
        <img src="' . $logoUri . '" alt="Logo Arzello Petshop" />
        <h2>Arzello Petshop</h2>
        <div class="sub-header">Jl. Pendowo Raya NO. 105 Kelurahan Limo, Kecamatan Limo, Depok.</div>
        <hr>
        <h3>Laporan Kriteria</h3>
    </div>

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
        $type  = htmlspecialchars($r['type']);
        $bobot = number_format($r['bobot'], 2);
        $nama  = htmlspecialchars($r['nama']);
        $html .= "<tr>
            <td>$no</td>
            <td>$nama</td>
            <td>$bobot</td>
            <td>$type</td>
        </tr>";
        $no++;
    }

    $html .= '
        </tbody>
    </table>

    <div class="footer">
        Depok, ' . $today . '<br><br><br>
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
