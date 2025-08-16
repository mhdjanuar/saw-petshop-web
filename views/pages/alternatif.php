<?php include "../../config/database.php"; ?>
<?php include "../layouts/header.php"; ?>

<div class="card border-0 shadow-soft">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0">Master Data Alternatif</h4>
    </div>

    <?php
    // CREATE
    if (isset($_POST['save'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $conn->query("INSERT INTO alternatif (name) VALUES ('$name')");
        echo "<div class='alert alert-success'>Data berhasil ditambahkan!</div>";
    }

    // UPDATE
    if (isset($_POST['update'])) {
        $id   = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $conn->query("UPDATE alternatif SET name='$name' WHERE id=$id");
        echo "<div class='alert alert-info'>Data berhasil diupdate!</div>";
    }

    // DELETE
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $conn->query("DELETE FROM alternatif WHERE id=$id");
        echo "<div class='alert alert-danger'>Data berhasil dihapus!</div>";
    }
    ?>

    <!-- Form Tambah Alternatif -->
    <form method="post" class="mb-4">
      <div class="row g-2">
        <div class="col-md-8">
          <input type="text" name="name" class="form-control" placeholder="Nama Alternatif" required>
        </div>
        <div class="col-md-4">
          <button type="submit" name="save" class="btn btn-primary w-100">Tambah</button>
        </div>
      </div>
    </form>

    <!-- Tabel Data Alternatif -->
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Supplier</th>
            <th>Nama Kriteria</th>
            <th>Bobot Alternatif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $no=1;
          $sql = "
              SELECT 
                  a.id,
                  p.name AS employee_name, 
                  c.id AS id_kreteria, 
                  c.nama AS nama_kriteria, 
                  sc.jumlah_bobot AS bobot_alternatif
              FROM alternatif AS a
              LEFT JOIN employees AS p ON a.id_employee = p.id
              LEFT JOIN sub_criteria AS sc ON a.id_sub_kreteria = sc.id
              LEFT JOIN criteria AS c ON sc.id_kreteria  = c.id
              ORDER BY a.id
          ";
          $res = $conn->query($sql);
          while($r = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($r['employee_name']); ?></td>
            <td><?php echo htmlspecialchars($r['nama_kriteria']); ?></td>
            <td><?php echo htmlspecialchars($r['bobot_alternatif']); ?></td>
            <td>
              <!-- Tombol Delete -->
              <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-sm btn-danger" 
                onclick="return confirm('Yakin hapus data ini?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Form Update (Hidden, muncul saat klik edit) -->
    <div id="editForm" style="display:none;" class="mt-4">
      <h5>Edit Alternatif</h5>
      <form method="post">
        <input type="hidden" name="id" id="edit_id">
        <div class="row g-2">
          <div class="col-md-8">
            <input type="text" name="name" id="edit_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <button type="submit" name="update" class="btn btn-success w-100">Update</button>
          </div>
        </div>
      </form>
    </div>

  </div>
</div>

<?php include "../layouts/footer.php"; ?>
