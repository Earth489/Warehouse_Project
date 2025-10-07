<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();  
}

//สินค้าขายดีประจำเดือนล่าสุด

// หาวันที่เดือนล่าสุดที่มีการขาย
$latest_month = $conn->query("
  SELECT DATE_FORMAT(MAX(sale_date), '%Y-%m') AS latest_month 
  FROM sales
")->fetch_assoc()['latest_month'];

$topProducts = [];
if ($latest_month) {
    $sql = "
        SELECT p.product_name, SUM(sd.quantity) AS total_sold
        FROM sale_details sd
        JOIN sales s ON sd.sale_id = s.sale_id
        JOIN products p ON sd.product_id = p.product_id
        WHERE DATE_FORMAT(s.sale_date, '%Y-%m') = '$latest_month'
        GROUP BY sd.product_id
        ORDER BY total_sold DESC
        LIMIT 4
    ";
    $topProducts = $conn->query($sql);
}
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
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
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

    <div class="row text-center">
  <h3 class="mb-4">🔥 สินค้าขายดีประจำเดือน 
    <?php echo $latest_month ? date("m/Y", strtotime($latest_month . "-01")) : ""; ?>
  </h3>

  <?php if ($topProducts && $topProducts->num_rows > 0): ?>
    <?php 
      $colors = ['primary', 'success', 'warning', 'danger'];
      $i = 0;
      while ($row = $topProducts->fetch_assoc()): 
    ?>
      <div class="col-md-3">
        <div class="card bg-<?php echo $colors[$i % 4]; ?> text-white mb-3">
          <div class="card-body">
            <h4><?php echo number_format($row['total_sold']); ?></h4>
            <p><?php echo htmlspecialchars($row['product_name']); ?></p>
          </div>
        </div>
      </div>
    <?php $i++; endwhile; ?>
  <?php else: ?>
    <div class="text-center text-muted mt-3">
      <p>ไม่มีข้อมูลการขายในเดือนล่าสุด</p>
    </div>
  <?php endif; ?>
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

  <!-- Footer 
 <footer class="bg-dark text-white text-center p-3 mt-5">
    © 2025 ระบบจัดการคลังสินค้า - ร้านวัสดุก่อสร้าง
    </footer>
-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
