<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();  
}

// ดึงข้อมูลสินค้า + ประเภท + ซัพพลายเออร์
$sql = "SELECT p.product_id, p.product_name, c.category_name, s.supplier_name, 
               p.unit, p.selling_price, p.stock_qty, p.reorder_level, p.image_path
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        ORDER BY p.product_id ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ระบบจัดการคลังสินค้า - ร้านวัสดุก่อสร้าง</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <style>
    .table td, 
.table th {
  height: 70px;            /* กำหนดความสูงทุกบรรทัด */
  vertical-align: middle;  /* จัดให้อยู่กึ่งกลางแนวตั้ง */
}
  </style>
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
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link active" href="products.php">สินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>


<div class="container-sm mt-4 position-relative">
  <h2>สินค้า</h2>

  <!-- ปุ่ม + เพิ่มสินค้าใหม่ -->
<a href="add_product.php" class="btn btn-primary mb-3">+ เพิ่มสินค้าใหม่</a>



</div>

<div class="container mt-4">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>รหัส</th>
        <th>ชื่อสินค้า</th>
        <th>ประเภทสินค้า</th>
        <th>ซัพพลายเออร์</th>
        <th>หน่วยนับ</th>
        <th>ราคา (บาท)</th>
        <th>จำนวนคงเหลือ</th> <!-- ✅ เพิ่ม -->
        <th>ระดับแจ้งเตือน</th>
        <th>รูปสินค้า</th>
        <th>การจัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['product_id']}</td>
                      <td>{$row['product_name']}</td>
                      <td>{$row['category_name']}</td>
                      <td>{$row['supplier_name']}</td>
                      <td>{$row['unit']}</td>
                      <td>{$row['selling_price']}</td>
                      <td>{$row['stock_qty']}</td> <!-- ✅ แสดงจำนวน -->
                      <td>{$row['reorder_level']}</td>
                      <td>";
              if (!empty($row['image_path'])) {
                  echo "<img src='{$row['image_path']}' alt='{$row['product_name']}' width='60'>";
              } else {
                  echo "<span class='text-muted'>ไม่มีรูป</span>";
              }
              echo "</td>
                        <td class='text-center align-middle'>
                            <a href='product_edit.php?id={$row['product_id']}' class='btn btn-warning btn-sm me-2'>แก้ไข</a>
                            <a href='product_delete.php?id={$row['product_id']}' 
                            onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?');\" 
                            class='btn btn-danger btn-sm'>ลบ</a>
                        </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='11' class='text-center text-muted'>ไม่มีสินค้าในระบบ</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>