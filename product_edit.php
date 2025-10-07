<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// ดึงข้อมูลสินค้าที่ต้องการแก้ไข
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ไม่พบข้อมูลสินค้า'); window.location='products.php';</script>";
    exit();
}

$product = $result->fetch_assoc();

// ดึงข้อมูลประเภทสินค้าและซัพพลายเออร์
$categories = $conn->query("SELECT * FROM categories");
$suppliers = $conn->query("SELECT * FROM suppliers");

// ถ้ามีการกดบันทึก
if (isset($_POST['update'])) {
    $name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $unit = $_POST['unit'];
    $price = $_POST['selling_price'];
    $reorder = $_POST['reorder_level'];
    $stock = $_POST['stock_qty'];

    // จัดการรูปภาพ (ถ้ามีอัปโหลดใหม่)
    $image_path = $product['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    }

    $sql_update = "UPDATE products 
               SET product_name=?, category_id=?, unit=?, 
                   selling_price=?, reorder_level=?, stock_qty=?, image_path=? 
               WHERE product_id=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sisdiisi", $name, $category_id, $unit, $price, $reorder, $stock, $image_path, $product_id);
    $stmt->execute();

    echo "<script>alert('อัปเดตข้อมูลสินค้าเรียบร้อย'); window.location='products.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขสินค้า</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>แก้ไขข้อมูลสินค้า</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" value="<?= $product['product_name'] ?>" required>
        </div>

        <div class="mb-3">
            <label>ประเภทสินค้า</label>
            <select name="category_id" class="form-select" required>
                <?php while($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['category_id'] ?>" <?= ($product['category_id'] == $c['category_id']) ? 'selected' : '' ?>>
                        <?= $c['category_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>หน่วยนับ</label>
                <input type="text" name="unit" class="form-control" value="<?= $product['unit'] ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>ราคา</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>ระดับแจ้งเตือน (reorder)</label>
                <input type="number" name="reorder_level" class="form-control" value="<?= $product['reorder_level'] ?>">
            </div>
        </div>

        <div class="mb-3">
            <label>จำนวนในสต็อก</label>
            <input type="number" name="stock_qty" class="form-control" value="<?= $product['stock_qty'] ?>" readonly>
        </div>

        <div class="mb-3">
            <label>รูปสินค้า</label><br>
            <?php if ($product['image_path']): ?>
                <img src="<?= $product['image_path'] ?>" width="100" class="mb-2"><br>
            <?php endif; ?>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" name="update" class="btn btn-success">บันทึกการแก้ไข</button>
        <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>

</body>
</html>
