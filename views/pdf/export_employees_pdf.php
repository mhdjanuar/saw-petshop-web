<?php
require_once '../../dompdf/autoload.inc.php'; // arahkan ke folder dompdf
include "../../config/database.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Atur opsi
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Query data employees
$sql = "SELECT * FROM employees ORDER BY id DESC";
$res = $conn->query($sql);

// Buat HTML
$html = '
<h3 style="text-align:center;">Laporan Data Employees</h3>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <thead>
    <tr>
      <th style="text-align:center;">No</th>
      <th>Nama Employee</th>
    </tr>
  </thead>
  <tbody>';
$no = 1;
while ($r = $res->fetch_assoc()) {
    $html .= "
    <tr>
      <td style='text-align:center;'>$no</td>
      <td>".htmlspecialchars($r['name'])."</td>
    </tr>";
    $no++;
}
$html .= '</tbody></table>';

// Generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_employees.pdf", ["Attachment" => false]);
