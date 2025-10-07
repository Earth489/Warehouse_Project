<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลจากตาราง suppliers
$sql = "SELECT supplier_id, supplier_name, address, phone FROM suppliers ORDER BY supplier_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ซัพพลายเออร์ - ระบบคลังสินค้า</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏠 Warehouse System</a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>ซัพพลายเออร์</h2>
  <a href="add_suppliers.php" class="btn btn-primary mb-3">+ เพิ่มซัพพลายเออร์</a>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>ชื่อซัพพลายเออร์</th>
        <th>ที่อยู่</th>
        <th>เบอร์โทร</th>
        <th>การจัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['supplier_id']}</td>
                      <td>{$row['supplier_name']}</td>
                      <td>{$row['address']}</td>
                      <td>{$row['phone']}</td>
                      <td class='text-center'>
                        <a href='supplier_edit.php?id={$row['supplier_id']}' class='btn btn-warning btn-sm me-2'>แก้ไข</a>
                        <a href='supplier_delete.php?id={$row['supplier_id']}' onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบซัพพลายเออร์นี้?');\" class='btn btn-danger btn-sm'>ลบ</a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='5' class='text-center text-muted'>ไม่มีข้อมูลซัพพลายเออร์</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</body>
</html>
