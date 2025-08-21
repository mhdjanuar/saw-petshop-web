<?php
session_start();

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/pages/login.php");
    exit;
}

require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include "../../config/database.php";

if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    $options = new Options();
    $options->set('isRemoteEnabled', true); 
    $dompdf = new Dompdf($options);

    // Query langsung ke tabel employees
    $sql = "SELECT * FROM employees ORDER BY id DESC";
    $res = $conn->query($sql); 

    // Logo
    $logoUri = 'https://i.postimg.cc/FRHxD9R4/petshop-200.png';

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
    // contoh: Sabtu, 16 Agustus 2025

    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; }
        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; }
        .sub-header { font-size: 12px; }
        hr { border: 1px solid #000; margin: 10px 0; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            padding: 8px;
        }
        td {
            text-align: center;
            padding: 8px;
        }
        .footer {
            position: absolute;
            bottom: 20px;
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
        <h3>Laporan Supplier</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Supplier</th>
                <th>Nama</th>
            </tr>
        </thead>
        <tbody>';

    while ($r = $res->fetch_assoc()) {
        $html .= "
            <tr>
                <td>{$r['id']}</td>
                <td>{$r['name']}</td>
            </tr>";
    }

    $html .= '
        </tbody>
    </table>

    <div class="footer">
        Depok, ' . $today . '<br><br><br>
        <strong>HRD</strong><br>
        Arya Arindita
    </div>';

    ob_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("laporan_supplier.pdf", ["Attachment" => false]);
    exit;
}

// ==== JIKA TAMPIL BIASA (HTML) ====
include "../layouts/header.php";

// CREATE
if (isset($_POST['action']) && $_POST['action'] == "create") {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO employees (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    header("Location: supplier.php");
    exit;
}

// UPDATE
if (isset($_POST['action']) && $_POST['action'] == "update") {
    $id   = $_POST['id'];
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE employees SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    header("Location: supplier.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: supplier.php");
    exit;
}
?>

<div class="card border-0 shadow-soft">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0">Master Data Supplier</h4>
      <div>
        <a href="?export=pdf" target="_blank" class="btn btn-danger btn-sm">📄 Export PDF</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah Supplier</button>
      </div>
    </div>

    <!-- Tabel Employees -->
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Supplier</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $no=1;
          $sql = "SELECT * FROM employees ORDER BY id DESC";
          $res = $conn->query($sql);
          while($r = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td>
              <button class="btn btn-sm btn-warning" 
                onclick="editForm('<?php echo $r['id']; ?>','<?php echo htmlspecialchars($r['name']); ?>')">
                Edit
              </button>
              <a href="?delete=<?php echo $r['id']; ?>" 
                 class="btn btn-sm btn-danger" 
                 onclick="return confirm('Hapus data ini?')">
                Hapus
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
          <label>Nama Supplier</label>
          <input type="text" name="name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit_id">
        <div class="mb-3">
          <label>Nama Supplier</label>
          <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function editForm(id, name){
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_name").value = name;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include "../layouts/footer.php"; ?>
