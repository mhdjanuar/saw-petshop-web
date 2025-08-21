<?php 

session_start();

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/pages/login.php");
    exit;
}

include "../../config/database.php"; 
include "../layouts/header.php"; 
require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ==== EXPORT PDF ====
if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // URL logo
    // === Logo dari local (base64) ===
    $logoPath = __DIR__ . '/../../assets/img/petshop-200.png';

    if (!file_exists($logoPath)) {
        die("Logo tidak ditemukan di: $logoPath");
    }

    $logoData = base64_encode(file_get_contents($logoPath));
    $logoUri  = 'data:image/png;base64,' . $logoData;

    // QUERY SAW Ranking
    $query = "
        SELECT 
            p.name AS name,
            ROUND(SUM(
                CASE 
                    WHEN c.type = 'benefit' THEN (sc.jumlah_bobot / pembagi_table.pembagi) * (c.bobot / total_weight.total)
                    WHEN c.type = 'cost' THEN (pembagi_table.pembagi / sc.jumlah_bobot) * (c.bobot / total_weight.total)
                END
            ), 4) AS score,
            RANK() OVER (
                ORDER BY SUM(
                    CASE 
                        WHEN c.type = 'benefit' THEN (sc.jumlah_bobot / pembagi_table.pembagi) * (c.bobot / total_weight.total)
                        WHEN c.type = 'cost' THEN (pembagi_table.pembagi / sc.jumlah_bobot) * (c.bobot / total_weight.total)
                    END
                ) DESC
            ) AS ranking
        FROM alternatif AS a
        JOIN employees AS p ON a.id_employee = p.id
        JOIN sub_criteria AS sc ON a.id_sub_kreteria = sc.id
        JOIN criteria AS c ON sc.id_kreteria = c.id
        JOIN (
            SELECT 
                c.id AS id_kriteria,
                CASE 
                    WHEN c.type = 'benefit' THEN MAX(sc.jumlah_bobot)
                    WHEN c.type = 'cost' THEN MIN(sc.jumlah_bobot)
                END AS pembagi
            FROM alternatif AS a
            JOIN sub_criteria AS sc ON a.id_sub_kreteria = sc.id
            JOIN criteria AS c ON sc.id_kreteria = c.id
            GROUP BY c.id, c.type
        ) AS pembagi_table ON pembagi_table.id_kriteria = c.id
        JOIN (SELECT SUM(bobot) AS total FROM criteria) AS total_weight
        GROUP BY p.name
        ORDER BY score DESC;
    ";

    $result = mysqli_query($conn, $query);

    // TEMPLATE HTML
    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 30px; }
        .header { text-align: center; }
        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; }
        .sub-header { font-size: 11px; margin-bottom: 10px; }
        hr { border: 0; border-top: 1px solid #000; margin: 10px 0; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: right;
        }
    </style>

    <div class="header">
        <img src="' . $logoUri . '" alt="Logo Arzello Petshop" />
        <h2>Arzello Petshop</h2>
        <div class="sub-header">Jl. Pendowo Raya NO. 105, Kelurahan Limo, Kecamatan Limo, Depok.</div>
        <hr>
        <h3>Laporan Hasil Perankingan</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Alternatif</th>
                <th>Skor</th>
            </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr>
            <td>{$row['ranking']}</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . number_format($row['score'], 4) . "</td>
        </tr>";
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
    $dompdf->stream("laporan_perankingan.pdf", ["Attachment" => false]);
    exit;
}
?>

<!-- Normal HTML Display -->
<div class="card border-0 shadow-soft">
  <div class="card-body">
    <h4 class="mb-3">Hasil Perankingan (SAW)</h4>

    <a href="?export=pdf" class="btn btn-outline-primary mb-3">Export PDF</a>

    <?php
    // Run the same query for browser view
    $query = "
        SELECT 
            p.name AS name,
            ROUND(SUM(
                CASE 
                    WHEN c.type = 'benefit' THEN (sc.jumlah_bobot / pembagi_table.pembagi) * (c.bobot / total_weight.total)
                    WHEN c.type = 'cost' THEN (pembagi_table.pembagi / sc.jumlah_bobot) * (c.bobot / total_weight.total)
                END
            ), 4) AS score,
            RANK() OVER (
                ORDER BY SUM(
                    CASE 
                        WHEN c.type = 'benefit' THEN (sc.jumlah_bobot / pembagi_table.pembagi) * (c.bobot / total_weight.total)
                        WHEN c.type = 'cost' THEN (pembagi_table.pembagi / sc.jumlah_bobot) * (c.bobot / total_weight.total)
                    END
                ) DESC
            ) AS ranking
        FROM alternatif AS a
        JOIN employees AS p ON a.id_employee = p.id
        JOIN sub_criteria AS sc ON a.id_sub_kreteria = sc.id
        JOIN criteria AS c ON sc.id_kreteria = c.id
        JOIN (
            SELECT 
                c.id AS id_kriteria,
                CASE 
                    WHEN c.type = 'benefit' THEN MAX(sc.jumlah_bobot)
                    WHEN c.type = 'cost' THEN MIN(sc.jumlah_bobot)
                END AS pembagi
            FROM alternatif AS a
            JOIN sub_criteria AS sc ON a.id_sub_kreteria = sc.id
            JOIN criteria AS c ON sc.id_kreteria = c.id
            GROUP BY c.id, c.type
        ) AS pembagi_table ON pembagi_table.id_kriteria = c.id
        JOIN (SELECT SUM(bobot) AS total FROM criteria) AS total_weight
        GROUP BY p.name
        ORDER BY score DESC;
    ";

    $result = mysqli_query($conn, $query);
    ?>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Peringkat</th>
            <th>Alternatif</th>
            <th>Skor</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?php echo $row['ranking']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo number_format($row['score'], 4); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
