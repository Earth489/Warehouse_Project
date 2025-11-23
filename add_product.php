<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ถ้ากดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name   = $_POST['product_name'];
    $category_id    = $_POST['category_id'];
    $selling_price  = $_POST['selling_price'];
    $reorder_level  = $_POST['reorder_level'];
    // รับค่าใหม่สำหรับหน่วย
    $base_unit = $_POST['base_unit'];
    $sub_unit = !empty($_POST['sub_unit']) ? $_POST['sub_unit'] : null;
    $unit_conversion_rate = $_POST['unit_conversion_rate'];
    
    // อัพโหลดรูป
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image_path = $targetFilePath;
        }
    }

    // บันทึกลง DB ด้วยคอลัมน์ใหม่
    $stmt = $conn->prepare("INSERT INTO products 
        (product_name, category_id, selling_price, reorder_level, image_path, base_unit, sub_unit, unit_conversion_rate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidssssd", 
        $product_name, $category_id, $selling_price, $reorder_level, $image_path, $base_unit, $sub_unit, $unit_conversion_rate
    );

    if ($stmt->execute()) {
        header("Location: products.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เพิ่มสินค้าใหม่</title>
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
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link active" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-5">
  <h2 class="mb-4">เพิ่มสินค้าใหม่</h2>
  <form method="post" enctype="multipart/form-data">
    
    <div class="mb-3">
      <label class="form-label">ชื่อสินค้า</label>
      <input type="text" name="product_name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">หมวดหมู่</label>
      <select name="category_id" class="form-select" required>
        <option value="">-- เลือกหมวดหมู่ --</option>
        <?php
        $cat = $conn->query("SELECT category_id, category_name FROM categories");
        while ($row = $cat->fetch_assoc()) {
            echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="base_unit" class="form-label">หน่วยหลัก (เช่น กระสอบ, กล่อง)</label>
        <input type="text" class="form-control" id="base_unit" name="base_unit" required>
      </div>
      <div class="col-md-4 mb-3">
        <label for="sub_unit" class="form-label">หน่วยย่อย (ถ้ามี เช่น กก., ชิ้น)</label>
        <input type="text" class="form-control" id="sub_unit" name="sub_unit">
      </div>
      <div class="col-md-4 mb-3">
        <label for="unit_conversion_rate" class="form-label">อัตราแปลง (1 หน่วยหลัก = ? หน่วยย่อย)</label>
        <input type="number" class="form-control" id="unit_conversion_rate" name="unit_conversion_rate" value="1" step="0.01" required>
        <div class="form-text">ถ้าไม่มีหน่วยย่อย ให้ใส่ 1</div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">ราคาขาย (ต่อหน่วยย่อย)</label>
      <input type="number" step="0.01" name="selling_price" class="form-control" required>
      <div class="form-text">เช่น ราคาขายต่อ 1 กิโลกรัม หรือราคาขายต่อ 1 ชิ้น</div>
    </div>

    <div class="mb-3">
      <label class="form-label">จุดสั่งซื้อใหม่ (ในหน่วยย่อย)</label>
      <input type="number" name="reorder_level" class="form-control" required>
      <div class="form-text">เช่น ถ้าต้องการให้แจ้งเตือนเมื่อปูนเหลือน้อยกว่า 2 กระสอบ (กระสอบละ 20 กก.) ให้กรอก 40</div>
    </div>

    <div class="mb-3">
      <label class="form-label">รูปสินค้า</label>
      <input type="file" name="image" class="form-control">
    </div>

    <button type="submit" class="btn btn-success">บันทึก</button>
    <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
  </form>
</div>
</body>
</html>