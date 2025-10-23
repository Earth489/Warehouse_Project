<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงรายการสินค้าในสต็อก
$sql = "SELECT * FROM products ORDER BY product_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มบิลขายสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="fw-bold mb-4">🚚 เพิ่มบิลขายสินค้า</h2>
  <form action="stock_out_save.php" method="POST">

    <div class="mb-3">
      <label for="sale_date" class="form-label">วันที่ขาย</label>
      <input type="date" id="sale_date" name="sale_date" class="form-control" required>
    </div>

    <table class="table table-bordered">
      <thead class="table-secondary">
        <tr>
          <th>เลือก</th>
          <th>ชื่อสินค้า</th>
          <th>จำนวนคงเหลือ</th>
          <th>ราคาขายต่อหน่วย</th>
          <th>จำนวนที่ขาย</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><input type="checkbox" name="product_id[]" value="<?= $row['product_id'] ?>"></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= $row['stock_qty'] ?></td>
            <td><?= number_format($row['selling_price'], 2) ?></td>
            <td><input type="number" name="quantity[<?= $row['product_id'] ?>]" min="1" class="form-control" style="width:100px;"></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <button type="submit" class="btn btn-success mt-3">บันทึกการขาย</button>
    <a href="warehouse_page.php" class="btn btn-secondary mt-3">กลับ</a>
  </form>
</div>
</body>
</html>
