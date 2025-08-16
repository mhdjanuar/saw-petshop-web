<?php 
include "../../config/database.php"; 
include "../layouts/header.php"; 

// proses insert ke tabel alternatif
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_employee = $_POST['id_employee'];
    $sub_kriteria = $_POST['sub_kriteria']; // array: [id_kriteria => id_sub_kriteria]

    foreach ($sub_kriteria as $id_kriteria => $id_sub) {
        $sql = "INSERT INTO alternatif (id_employee, id_sub_kreteria) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id_employee, $id_sub);
        mysqli_stmt_execute($stmt);
    }
}
?>

<div class="card border-0 shadow-soft">
  <div class="card-body">
    <h4>Data Alternatif</h4>

    <!-- Form Input -->
    <form method="POST" action="">
      <div class="form-group">
        <label>Supplier:</label>
        <select name="id_employee" class="form-control" required>
          <option value="">Pilih Supplier</option>
          <?php
            $suppliers = mysqli_query($conn, "SELECT id, name FROM employees");
            while($s = mysqli_fetch_assoc($suppliers)){
              echo "<option value='".$s['id']."'>".$s['name']."</option>";
            }
          ?>
        </select>
      </div>

      <?php
        $criteria = mysqli_query($conn, "SELECT * FROM criteria");
        while($c = mysqli_fetch_assoc($criteria)){
          echo "<div class='form-group'>";
          echo "<label>".$c['nama'].":</label>";
          echo "<select name='sub_kriteria[".$c['id']."]' class='form-control' required>";
          echo "<option value=''>-- Pilih --</option>";
          $subs = mysqli_query($conn, "SELECT * FROM sub_criteria WHERE id_kreteria='".$c['id']."'");
          while($sc = mysqli_fetch_assoc($subs)){
            echo "<option value='".$sc['id']."'>".$sc['deskripsi']."</option>";
          }
          echo "</select>";
          echo "</div>";
        }
      ?>

      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <!-- Tabel Alternatif -->
    <div class="table-responsive mt-4">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Nama Supplier</th>
            <th>Nama Kriteria</th>
            <th>Bobot Alternatif</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $query = "SELECT p.name AS nama_supplier, c.nama AS nama_kriteria, sc.jumlah_bobot 
                      FROM alternatif a
                      INNER JOIN employees p ON a.id_employee = p.id
                      INNER JOIN sub_criteria sc ON a.id_sub_kreteria = sc.id
                      INNER JOIN criteria c ON sc.id_kreteria = c.id";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_assoc($result)){
              echo "<tr>";
              echo "<td>".$row['nama_supplier']."</td>";
              echo "<td>".$row['nama_kriteria']."</td>";
              echo "<td>".$row['jumlah_bobot']."</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
