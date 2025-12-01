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

// ดึงราคาซื้อล่าสุดของสินค้านี้
$latest_purchase_price = 0; // กำหนดค่าเริ่มต้น
$sql_purchase = "SELECT pd.purchase_price 
                 FROM purchase_details pd
                 JOIN purchases p ON pd.purchase_id = p.purchase_id
                 WHERE pd.product_id = ?
                 ORDER BY p.purchase_date DESC, p.purchase_id DESC
                 LIMIT 1";
$stmt_purchase = $conn->prepare($sql_purchase);
$stmt_purchase->bind_param("i", $product_id);
$stmt_purchase->execute();
$result_purchase = $stmt_purchase->get_result();
if ($row_purchase = $result_purchase->fetch_assoc()) {
    $latest_purchase_price = $row_purchase['purchase_price'];
}

// ✅ คำนวณราคาซื้อล่าสุดต่อหน่วยย่อย
$latest_purchase_price_per_sub_unit = $latest_purchase_price; // ตั้งค่าเริ่มต้น
if ($product['unit_conversion_rate'] > 1) {
    // หารด้วยอัตราแปลงเพื่อหาราคาต่อหน่วยย่อย (ป้องกันการหารด้วยศูนย์)
    $latest_purchase_price_per_sub_unit = $latest_purchase_price / $product['unit_conversion_rate'];
}

// ดึงข้อมูลประเภทสินค้าและซัพพลายเออร์
$categories = $conn->query("SELECT * FROM categories");
$suppliers = $conn->query("SELECT * FROM suppliers");

// ถ้ามีการกดบันทึก
if (isset($_POST['update'])) {
    $name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $price = (float)$_POST['selling_price'];
    $reorder = $_POST['reorder_level'];
    $base_unit = $_POST['base_unit'];
    $sub_unit = !empty($_POST['sub_unit']) ? $_POST['sub_unit'] : null;
    $unit_conversion_rate = $_POST['unit_conversion_rate'];

    // Server-side validation: ตรวจสอบว่าราคาขายไม่ต่ำกว่าราคาซื้อล่าสุด
    if ($price < $latest_purchase_price_per_sub_unit) {
        echo "<script>alert('ข้อผิดพลาด: ราคาขายต้องไม่ต่ำกว่าราคาซื้อล่าสุดต่อหน่วยย่อย (" . number_format($latest_purchase_price_per_sub_unit, 2) . " บาท)'); window.history.back();</script>";
        exit();
    }

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
               SET product_name=?, category_id=?, selling_price=?, 
                   reorder_level=?, image_path=?, base_unit=?, sub_unit=?, unit_conversion_rate=?
               WHERE product_id=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sidssssdi", $name, $category_id, $price, $reorder, $image_path, $base_unit, $sub_unit, $unit_conversion_rate, $product_id);
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
    <h3>แก้ไขข้อมูลสินค้า</h3>
    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('คุณต้องการบันทึกการแก้ไขใช่หรือไม่?');">
        <div class="mb-3">
            <label>ชื่อสินค้า</label>
            <textarea name="product_name" class="form-control" rows="3" required><?= htmlspecialchars($product['product_name']) ?></textarea>
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
                <label for="base_unit" class="form-label">หน่วยหลัก (เช่น กระสอบ, กล่อง)</label>
                <input type="text" class="form-control" id="base_unit" name="base_unit" value="<?= htmlspecialchars($product['base_unit'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="sub_unit" class="form-label">หน่วยย่อย (ถ้ามี เช่น กก., ชิ้น)</label>
                <input type="text" class="form-control" id="sub_unit" name="sub_unit" value="<?= htmlspecialchars($product['sub_unit'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="unit_conversion_rate" class="form-label">จำนวนหน่วยย่อยต่อ 1 หน่วยหลัก (1 หน่วยหลัก = ? หน่วยย่อย)</label>
                <input type="number" class="form-control" id="unit_conversion_rate" name="unit_conversion_rate" value="<?= $product['unit_conversion_rate'] ?? 1 ?>" step="0.01" required>
                <div class="form-text">ถ้าไม่มีหน่วยย่อย ให้ใส่ 1</div>
            </div>
            <div class="col-md-4 mb-3">
                <label>ราคาขาย (ต่อหน่วยย่อย)</label>
                <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>">
                <div id="price-warning" class="form-text text-danger" style="display: none;">
                    ราคาขายต้องไม่ต่ำกว่าราคาซื้อล่าสุด (ต่อหน่วยย่อย): <?= number_format($latest_purchase_price_per_sub_unit, 2) ?> บาท
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label>จุดสั่งซื้อใหม่ (ในหน่วยย่อย)</label>
                <input type="number" name="reorder_level" class="form-control" value="<?= $product['reorder_level'] ?>">
            </div>
        </div>

        <div class="mb-3">
            <label>จำนวนในสต็อก</label>
            <?php
                // จัดการแสดงผลสต็อก
                $stockDisplay = '';
                if ($product['unit_conversion_rate'] > 1 && !empty($product['sub_unit'])) {
                    $baseUnitStock = floor($product['stock_in_sub_unit'] / $product['unit_conversion_rate']);
                    $subUnitStock = fmod($product['stock_in_sub_unit'], $product['unit_conversion_rate']);
                    $stockDisplay = "{$baseUnitStock} " . htmlspecialchars($product['base_unit']) . " / {$subUnitStock} " . htmlspecialchars($product['sub_unit']);
                } else {
                    $stockDisplay = "{$product['stock_in_sub_unit']} " . htmlspecialchars($product['base_unit']);
                }
            ?>
            <input type="text" class="form-control" value="<?= $stockDisplay ?>" readonly>
        </div>

        <div class="mb-3">
            <label>รูปสินค้า</label><br>
            <?php if ($product['image_path']): ?>
                <img src="<?= $product['image_path'] ?>" width="100" class="mb-2"><br>
            <?php endif; ?>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" name="update" id="update-btn" class="btn btn-success">บันทึกการแก้ไข</button>
        <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sellingPriceInput = document.getElementById('selling_price');
    const priceWarning = document.getElementById('price-warning');
    const updateBtn = document.getElementById('update-btn');
    const latestPurchasePricePerSubUnit = <?= $latest_purchase_price_per_sub_unit ?>;

    function validatePrice() {
        const sellingPrice = parseFloat(sellingPriceInput.value);
        if (sellingPrice < latestPurchasePricePerSubUnit) {
            priceWarning.style.display = 'block';
            updateBtn.disabled = true;
        } else {
            priceWarning.style.display = 'none';
            updateBtn.disabled = false;
        }
    }

    sellingPriceInput.addEventListener('input', validatePrice);
    validatePrice(); // ตรวจสอบราคาเมื่อโหลดหน้าเว็บครั้งแรก
});
</script>

</body>
</html>
