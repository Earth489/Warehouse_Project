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
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];

    $sql = "INSERT INTO categories (category_name, description) 
            VALUES ('$category_name', '$description')";

    if ($conn->query($sql) === TRUE) {
        header("Location: categories.php");
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
  <title>เพิ่มประเภทสินค้า</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>เพิ่มประเภทสินค้า</h2>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">ชื่อประเภทสินค้า</label>
      <input type="text" name="category_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">รายละเอียด</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-success">บันทึก</button>
    <a href="categories.php" class="btn btn-secondary">ยกเลิก</a>
  </form>
</div>
</body>
</html>
