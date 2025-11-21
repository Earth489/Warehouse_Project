<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();  
}

// ดึงข้อมูลสรุปสำหรับหน้าแรก
// จำนวนสินค้าทั้งหมด
$total_products = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];

// จำนวนประเภทสินค้าทั้งหมด
$total_categories = $conn->query("SELECT COUNT(*) AS count FROM categories")->fetch_assoc()['count'];

// จำนวนซัพพลายเออร์ทั้งหมด
$total_suppliers = $conn->query("SELECT COUNT(*) AS count FROM suppliers")->fetch_assoc()['count'];

// ยอดขายรวมของเดือนปัจจุบัน
$current_month_sales = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM sales WHERE DATE_FORMAT(sale_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetch_assoc()['total'];

// ยอดซื้อรวมของเดือนปัจจุบัน
$current_month_purchases = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM purchases WHERE DATE_FORMAT(purchase_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetch_assoc()['total'];

// เดือนปัจจุบันสำหรับแสดงผล
$current_month_thai = date('m/Y');


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
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- เนื้อหาหลัก -->
  <div class="container my-5">
    <h1 class="mb-4">ระบบจัดการคลังสินค้า</h1>

    <!-- ส่วนสรุปข้อมูล (Summary Cards) -->
    <div class="row mb-5">
      <div class="col-md-4 mb-3">
        <div class="card bg-dark text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">สินค้าทั้งหมด</h5>
            <p class="card-text fs-3"><?= number_format($total_products) ?> รายการ</p>
            <a href="products.php" class="text-white text-decoration-none">ดูรายละเอียด &raquo;</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-dark text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">ประเภทสินค้าทั้งหมด</h5>
            <p class="card-text fs-3"><?= number_format($total_categories) ?> ประเภท</p>
            <a href="categories.php" class="text-white text-decoration-none">ดูรายละเอียด &raquo;</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-dark text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">ซัพพลายเออร์ทั้งหมด</h5>
            <p class="card-text fs-3"><?= number_format($total_suppliers) ?> ราย</p>
            <a href="suppliers.php" class="text-white text-decoration-none">ดูรายละเอียด &raquo;</a>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-5">
      <div class="col-md-6 mb-3">
        <div class="card bg-success text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">ยอดขายรวมเดือน <?= $current_month_thai ?></h5>
            <p class="card-text fs-3"><?= number_format($current_month_sales, 2) ?> บาท</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card bg-danger text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">ยอดซื้อรวมเดือน <?= $current_month_thai ?></h5>
            <p class="card-text fs-3"><?= number_format($current_month_purchases, 2) ?> บาท</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ตารางสินค้าใกล้หมด -->
    <h3 class="mt-4">สินค้าใกล้หมด</h3>
    <table class="table table-striped">
      <thead>
        <tr>
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
                      <td>{$row['product_name']}</td>
                      <td>{$row['stock_qty']}</td>
                      <td>{$row['supplier_name']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='3' class='text-center text-muted'>ไม่มีสินค้าใกล้หมด</td></tr>";
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
