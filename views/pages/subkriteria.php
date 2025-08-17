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

// Export PDF
if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $res = $conn->query("SELECT sc.id, c.nama AS kriteria, sc.deskripsi, sc.jumlah_bobot 
                         FROM sub_criteria AS sc 
                         INNER JOIN criteria AS c ON sc.id_kreteria = c.id 
                         ORDER BY sc.id");

    // URL logo
    $logoUrl = 'https://i.ibb.co/MxMZ73BJ/petshop-200.png';

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
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 5px;
        }
    </style>

    <div class="header">
        <img src="' . $logoUrl . '" alt="Logo Arzello Petshop" />
        <h2>Arzello Petshop</h2>
        <p>Jl. Pendowo Raya NO. 105 Kelurahan Limo, Kecamatan Limo, Depok.</p>
    </div>
    <hr>
    <h3 style="text-align:center;">Laporan Data Sub Kriteria</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kriteria</th>
                <th>Deskripsi</th>
                <th>Jumlah Bobot</th>
            </tr>
        </thead>
        <tbody>';

    $no = 1;
    while ($r = $res->fetch_assoc()) {
        $html .= "<tr>
            <td>$no</td>
            <td>" . htmlspecialchars($r['kriteria']) . "</td>
            <td>" . htmlspecialchars($r['deskripsi']) . "</td>
            <td>" . number_format($r['jumlah_bobot'], 2) . "</td>
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

    ob_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("laporan_sub_kriteria.pdf", ["Attachment" => false]);
    exit;
}

?>

<div class="card border-0 shadow-soft">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0">Master Data Sub Kriteria</h4>
    </div>

    <!-- Form Input -->
    <form method="post" class="mb-4">
      <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? intval($_GET['edit']) : ''; ?>">
      <div class="row g-2">
        <div class="col-md-3">
          <select name="id_kreteria" class="form-select" required>
            <option value="">--Pilih Kriteria--</option>
            <?php
              $crit = $conn->query("SELECT id, nama FROM criteria ORDER BY nama");
              while ($c = $crit->fetch_assoc()):
                $selected = (isset($_GET['id_kreteria']) && $_GET['id_kreteria']==$c['id']) ? 'selected' : '';
            ?>
              <option value="<?= $c['id']; ?>" <?= $selected; ?>><?= htmlspecialchars($c['nama']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" step="0.01" name="jumlah_bobot" class="form-control" placeholder="Jumlah Bobot" required>
        </div>
        <div class="col-md-4">
          <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi" required>
        </div>
        <div class="col-md-2">
          <button type="submit" name="save" class="btn btn-primary w-100">Simpan</button>
        </div>
      </div>
    </form>

    <?php
    // Create / Update
    if (isset($_POST['save'])) {
        $id           = intval($_POST['id']);
        $id_kreteria  = intval($_POST['id_kreteria']);
        $jumlah_bobot = floatval($_POST['jumlah_bobot']);
        $deskripsi    = $_POST['deskripsi'];

        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE sub_criteria SET id_kreteria = ?, jumlah_bobot = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("idsi", $id_kreteria, $jumlah_bobot, $deskripsi, $id);
            $stmt->execute();
            $stmt->close();
            echo "<div class='alert alert-success'>Sub kriteria berhasil diupdate!</div>";
        } else {
            // Create
            $stmt = $conn->prepare("INSERT INTO sub_criteria(id_kreteria, jumlah_bobot, deskripsi) VALUES(?, ?, ?)");
            $stmt->bind_param("ids", $id_kreteria, $jumlah_bobot, $deskripsi);
            $stmt->execute();
            $stmt->close();
            echo "<div class='alert alert-success'>Sub kriteria berhasil ditambahkan!</div>";
        }
    }

    // Delete
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM sub_criteria WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo "<div class='alert alert-danger'>Sub kriteria berhasil dihapus!</div>";
    }
    ?>

    <!-- Tombol Export -->
    <a href="?export=pdf" class="btn btn-outline-primary mb-3">Export PDF</a>

    <!-- Tabel Data -->
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr><th>No</th><th>Kriteria</th><th>Deskripsi</th><th>Jumlah Bobot</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php
          $no=1;
          $res = $conn->query("SELECT sc.id, c.nama AS kriteria, sc.deskripsi, sc.jumlah_bobot 
                               FROM sub_criteria AS sc 
                               INNER JOIN criteria AS c ON sc.id_kreteria = c.id 
                               ORDER BY sc.id");
          while($r = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($r['kriteria']); ?></td>
            <td><?= htmlspecialchars($r['deskripsi']); ?></td>
            <td><?= number_format($r['jumlah_bobot'], 2); ?></td>
            <td>
              <a href="?delete=<?= $r['id']; ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include "../layouts/footer.php"; ?>
