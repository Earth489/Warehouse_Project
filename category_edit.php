<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$category_id = $_GET['id'];

// ดึงข้อมูลประเภทสินค้าจากฐานข้อมูล
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ไม่พบข้อมูลประเภทสินค้า'); window.location='categories.php';</script>";
    exit();
}

$category = $result->fetch_assoc();

// เมื่อกดปุ่มบันทึก
if (isset($_POST['update'])) {
    $name = $_POST['category_name'];
    $desc = $_POST['description'];

    $sql_update = "UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssi", $name, $desc, $category_id);

    if ($stmt->execute()) {
        echo "<script>alert('อัปเดตข้อมูลเรียบร้อย'); window.location='categories.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แก้ไขประเภทสินค้า</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card">
    <div class="card-header bg-warning text-white">
      <h4>แก้ไขประเภทสินค้า</h4>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">ชื่อประเภทสินค้า</label>
          <input type="text" name="category_name" class="form-control" value="<?= $category['category_name'] ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">รายละเอียด</label>
          <textarea name="description" class="form-control" rows="3"><?= $category['description'] ?></textarea>
        </div>

        <button type="submit" name="update" class="btn btn-success">บันทึกการแก้ไข</button>
        <a href="categories.php" class="btn btn-secondary">ยกเลิก</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>
