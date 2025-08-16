<?php 
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

    // SAW QUERY
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

    // HTML TEMPLATE
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
    <h3 style="text-align:center;">Laporan Hasil Perankingan (SAW)</h3>

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
