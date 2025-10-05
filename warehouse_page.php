<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลเฉพาะสินค้าที่อยู่ในคลัง (active และ stock_qty > 0)
$sql = "
  SELECT p.product_id, p.product_name, c.category_name, s.supplier_name,
         p.unit, p.selling_price, p.stock_qty, p.image_path
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.category_id
  LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
  WHERE p.stock_qty > 0
  ORDER BY p.product_id ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>คลังสินค้า - ร้านวัสดุก่อสร้าง</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    .table td, .table th {
      height: 70px;            /* ความสูงเท่ากันทุกแถว */
      vertical-align: middle;
    }
    .img-thumb { width:60px; height:60px; object-fit:cover; border-radius:4px; }
    .top-actions { display:flex; gap:.5rem; justify-content:flex-end; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 Warehouse System</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="warehouse_page.php">คลังสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
      <h2 class="me-auto">คลังสินค้า</h2>
      <div class="top-actions">
        <a href="stock_in_add.php" class="btn btn-primary">รับเข้า (ซื้อสินค้า)</a>
        <a href="stock_out_add.php" class="btn btn-danger">เบิก/ขายสินค้า</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
          <tr>
            <th style="width:6%;">รหัส</th>
            <th style="width:26%;">ชื่อสินค้า</th>
            <th style="width:14%;">ประเภท</th>
            <th style="width:14%;">ซัพพลายเออร์</th>
            <th style="width:8%;">หน่วย</th>
            <th style="width:10%;">ราคา (บาท)</th>
            <th style="width:10%;">จำนวนคงเหลือ</th>
            <th style="width:6%;">รูป</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="text-center"><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['category_name'] ?: '-'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['supplier_name'] ?: '-'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['unit'] ?: '-'); ?></td>
                <td class="text-end"><?php echo number_format($row['selling_price'],2); ?></td>
                <td class="text-center"><?php echo (int)$row['stock_qty']; ?></td>
                <td class="text-center">
                  <?php if (!empty($row['image_path'])): ?>
                    <img class="img-thumb" src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                  <?php else: ?>
                    <span class="text-muted">ไม่มีรูป</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">ไม่มีสินค้าที่อยู่ในคลัง (stock)</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
