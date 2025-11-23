<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();  
}

// ดึงข้อมูลสำหรับ Filter
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

// รับค่าจากฟอร์มค้นหา
$search_term = $_GET['search_term'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';

// ดึงข้อมูลสินค้า + ประเภท + ซัพพลายเออร์
$sql = "SELECT p.product_id, p.product_name, c.category_name, s.supplier_name,
               p.base_unit, p.sub_unit, p.unit_conversion_rate,
               p.selling_price, p.stock_in_sub_unit, p.reorder_level, p.image_path
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id";

$conditions = [];
$params = [];
$types = '';

if (!empty($search_term)) {
    $conditions[] = "p.product_name LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= 's';
}
if (!empty($category_id)) {
    $conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}
if (!empty($supplier_id)) {
    $conditions[] = "p.supplier_id = ?";
    $params[] = $supplier_id;
    $types .= 'i';
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY p.product_id ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ระบบจัดการคลังสินค้า - ร้านวัสดุก่อสร้าง</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <style>
    .table td, .table th {
      height: 70px;            /* กำหนดความสูงทุกบรรทัด */
      vertical-align: middle;  /* จัดให้อยู่กึ่งกลางแนวตั้ง */
    }
    /* ลดขนาดฟอนต์หัวตาราง */
    .table thead.table-dark th {
      font-size: 0.9rem;
      font-weight: 500;
    }
    /* จัดการข้อความยาวในคอลัมน์ชื่อสินค้า */
    .product-name-col {
      max-width: 250px; /* กำหนดความกว้างสูงสุดของคอลัมน์ */
      word-wrap: break-word; /* สำหรับเบราว์เซอร์เก่า */
      overflow-wrap: break-word; /* มาตรฐานใหม่ */
      white-space: normal !important; /* ทำให้ข้อความตัดขึ้นบรรทัดใหม่ได้ */
    }
    /* เน้นแถวสินค้าที่ใกล้หมด */
    .table-danger-light { background-color: #f8d7da !important; }
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
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>


<div class="container-fluid mt-4 position-relative">
  <h2>สินค้า</h2>

  <!-- ปุ่ม + เพิ่มสินค้าใหม่ -->
  <a href="add_product.php" class="btn btn-primary mb-3">+ เพิ่มสินค้าใหม่</a>

  <!-- ฟอร์มค้นหา -->
  <form method="get" class="card card-body mb-4">
    <div class="row g-3">
      <div class="col-md-4">
        <input type="text" name="search_term" class="form-control" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search_term) ?>">
      </div>
      <div class="col-md-3">
        <select name="category_id" class="form-select">
          <option value=""> ทุกประเภท </option>
          <?php mysqli_data_seek($categories, 0); while($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['category_id'] ?>" <?= ($category_id == $c['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="supplier_id" class="form-select">
          <option value=""> ทุกซัพพลายเออร์ </option>
          <?php mysqli_data_seek($suppliers, 0); while($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['supplier_id'] ?>" <?= ($supplier_id == $s['supplier_id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['supplier_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-info w-100">ค้นหา</button>
        <a href="products.php" class="btn btn-secondary w-100">ล้างค่า</a>
      </div>
    </div>
  </form>

</div>

<div class="container-fluid mt-4">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>รหัส</th>
        <th>ชื่อสินค้า</th>
        <th>ประเภทสินค้า</th>
        <th>ซัพพลายเออร์</th>
        <th>หน่วยนับ (หลัก/ย่อย)</th>
        <th>ราคาขายต่อหน่วย(บาท)</th>
        <th>จำนวนคงเหลือ</th>
        <th>รูปสินค้า</th>
        <th>การจัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              // จัดการแสดงผลหน่วย
              $unitDisplay = $row['base_unit'];
              if ($row['unit_conversion_rate'] > 1 && !empty($row['sub_unit'])) {
                $unitDisplay .= " ({$row['unit_conversion_rate']} {$row['sub_unit']})";
              }

              // จัดการแสดงผลสต็อก
              $stockDisplay = '';
              if ($row['unit_conversion_rate'] > 1 && !empty($row['sub_unit'])) {
                  $baseUnitStock = floor($row['stock_in_sub_unit'] / $row['unit_conversion_rate']);
                  $subUnitStock = fmod($row['stock_in_sub_unit'], $row['unit_conversion_rate']);
                  $stockDisplay = "{$baseUnitStock} {$row['base_unit']} / {$subUnitStock} {$row['sub_unit']}";
              } else {
                  $stockDisplay = "{$row['stock_in_sub_unit']} {$row['base_unit']}";
              }

              // ตรวจสอบสต็อกเพื่อใส่สีให้แถว
              $row_class = '';
              if ($row['stock_in_sub_unit'] <= $row['reorder_level']) {
                  $row_class = 'table-danger-light';
              }

              echo "<tr class='{$row_class}'>
                      <td>{$row['product_id']}</td>
                      <td class='product-name-col'>" . htmlspecialchars($row['product_name']) . "</td>
                      <td>" . htmlspecialchars($row['category_name'] ?? '-') . "</td>
                      <td>" . htmlspecialchars($row['supplier_name'] ?? '-') . "</td>
                      <td>{$unitDisplay}</td>
                      <td>" . number_format($row['selling_price'], 2) . "</td>
                      <td>{$stockDisplay}</td>
                      <td>";
              if (!empty($row['image_path'])) {
                  echo "<img src='{$row['image_path']}' alt='{$row['product_name']}' width='60'>";
              } else {
                  echo "<span class='text-muted'>ไม่มีรูป</span>";
              }
              echo "</td>
                        <td class='align-middle'>
                            <div class='d-flex justify-content-center gap-2'>
                                <a href='product_edit.php?id={$row['product_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                                <a href='product_delete.php?id={$row['product_id']}' 
                                onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?');\" 
                                class='btn btn-danger btn-sm'>ลบ</a>
                            </div>
                        </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='9' class='text-center text-muted'>ไม่มีสินค้าในระบบ</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>