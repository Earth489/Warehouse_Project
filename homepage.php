<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();  
}

// ───────────────────────────────
// ดึงข้อมูลสรุปจากฐานข้อมูล (ไม่ใช้ status แล้ว)
// ───────────────────────────────

// สินค้าทั้งหมด
$totalProducts = $conn->query("
    SELECT COUNT(*) AS total FROM products
")->fetch_assoc()['total'];

// สินค้าที่มีในสต็อก (stock_qty > 0)
$totalStock = $conn->query("
    SELECT COUNT(*) AS total 
    FROM products 
    WHERE stock_qty > 0
")->fetch_assoc()['total'];

// สินค้าใกล้หมด (stock_qty > 0 และ stock_qty <= reorder_level)
$lowStock = $conn->query("
    SELECT COUNT(*) AS total 
    FROM products 
    WHERE stock_qty > 0 AND stock_qty <= reorder_level
")->fetch_assoc()['total'];

// สินค้าหมด (stock_qty = 0)
$outStock = $conn->query("
    SELECT COUNT(*) AS total 
    FROM products 
    WHERE stock_qty = 0
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ระบบจัดการคลังสินค้า - ร้านวัสดุก่อสร้าง</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>

  <!-- แถบเมนูด้านบน -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 Warehouse System</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">คลังสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- เนื้อหาหลัก -->
  <div class="container my-5">
    <h1 class="mb-4">ระบบจัดการคลังสินค้า</h1>
    <p>ยินดีต้อนรับสู่ระบบจัดการคลังสินค้าของร้านวัสดุก่อสร้าง  
      ที่หน้านี้คุณสามารถดูภาพรวมของระบบได้</p>

    <!-- การ์ดสรุปยอด -->
    <div class="row text-center">
      <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
          <div class="card-body">
            <h4><?php echo $totalProducts; ?></h4>
            <p>สินค้าทั้งหมด</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white mb-3">
          <div class="card-body">
            <h4><?php echo $totalStock; ?></h4>
            <p>สินค้าในสต็อก (มีของ)</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-dark mb-3">
          <div class="card-body">
            <h4><?php echo $lowStock; ?></h4>
            <p>สินค้าใกล้หมด (ต่ำกว่าจุดสั่งซื้อใหม่)</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-danger text-white mb-3">
          <div class="card-body">
            <h4><?php echo $outStock; ?></h4>
            <p>สินค้า Out of Stock (หมด)</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ตารางสินค้าใกล้หมด -->
    <h3 class="mt-5">สินค้าใกล้หมด</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>รหัสสินค้า</th>
          <th>ชื่อสินค้า</th>
          <th>จำนวนคงเหลือ</th>
          <th>ซัพพลายเออร์</th>
        </tr>
      </thead>
      <tbody>
        <?php
      $sql = "
        SELECT p.product_id, p.product_name, p.stock_qty, 
              s.supplier_name
        FROM products p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.stock_qty <= p.reorder_level
        ORDER BY p.stock_qty ASC
      ";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['product_id']}</td>
                      <td>{$row['product_name']}</td>
                      <td>{$row['stock_qty']}</td>
                      <td>{$row['supplier_name']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='4' class='text-center text-muted'>ไม่มีสินค้าใกล้หมด</td></tr>";
      }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center p-3 mt-5">
    © 2025 ระบบจัดการคลังสินค้า - ร้านวัสดุก่อสร้าง
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
