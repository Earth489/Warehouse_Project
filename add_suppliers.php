<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ถ้ามีการกด submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = $_POST['supplier_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $sql = "INSERT INTO suppliers (supplier_name, address, phone) 
            VALUES ('$supplier_name', '$address', '$phone')";

    if ($conn->query($sql) === TRUE) {
        header("Location: suppliers.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เพิ่มซัพพลายเออร์</title>
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
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-5">
  <h2>เพิ่มซัพพลายเออร์</h2>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">ชื่อซัพพลายเออร์</label>
      <input type="text" name="supplier_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">ที่อยู่</label>
      <textarea name="address" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">เบอร์โทร</label>
      <input type="text" name="phone" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">บันทึก</button>
    <a href="suppliers.php" class="btn btn-secondary">ยกเลิก</a>
  </form>
</div>
</body>
</html>
