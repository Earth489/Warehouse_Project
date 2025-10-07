<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: warehouse_page.php");
    exit();
}

$purchase_id = $_GET['id'];

// ดึงข้อมูลหัวบิล
$sqlHeader = "SELECT p.purchase_id, p.purchase_number, p.purchase_date, 
                      s.supplier_name, s.phone, s.address, p.total_amount
               FROM purchases p
               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
               WHERE p.purchase_id = ?";
$stmt = $conn->prepare($sqlHeader);
$stmt->bind_param("i", $purchase_id);
$stmt->execute();
$headerResult = $stmt->get_result();
$purchase = $headerResult->fetch_assoc();

// ดึงรายละเอียดสินค้าในบิล
$sqlItems = "SELECT d.product_id, pr.product_name, d.quantity, d.purchase_price, 
                    (d.quantity * d.purchase_price) AS total
             FROM purchase_details d
             LEFT JOIN products pr ON d.product_id = pr.product_id
             WHERE d.purchase_id = ?";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $purchase_id);
$stmt2->execute();
$itemsResult = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายละเอียดบิลรับสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #f8f9fa;
}
.card {
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<div class="container mt-5 mb-5">
  <div class="card">
    <div class="card-header bg-dark text-white">
      <h4 class="mb-0">รายละเอียดบิลรับสินค้า</h4>
    </div>
    <div class="card-body">
      <?php if ($purchase): ?>
        <div class="mb-3">
          <p><strong>เลขที่บิล:</strong> <?= htmlspecialchars($purchase['purchase_number']) ?></p>
          <p><strong>วันที่รับเข้า:</strong> <?= date("d/m/Y", strtotime($purchase['purchase_date'])) ?></p>
          <p><strong>ซัพพลายเออร์:</strong> <?= htmlspecialchars($purchase['supplier_name']) ?></p>
          <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($purchase['phone'] ?? '-') ?></p>
          <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($purchase['address'] ?? '-') ?></p>
        </div>

        <h5 class="mt-4"> รายการสินค้า</h5>
        <table class="table table-bordered mt-3">
          <thead class="table-light">
            <tr>
              <th>รหัสสินค้า</th>
              <th>ชื่อสินค้า</th>
              <th>จำนวน</th>
              <th>ราคาต่อหน่วย</th>
              <th>ราคารวม</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($itemsResult->num_rows > 0): ?>
              <?php while ($item = $itemsResult->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($item['product_id']) ?></td>
                  <td><?= htmlspecialchars($item['product_name']) ?></td>
                  <td><?= number_format($item['quantity'], 0) ?></td>
                  <td><?= number_format($item['purchase_price'], 2) ?></td>
                  <td><?= number_format($item['total'], 2) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted">ไม่มีรายการสินค้าในบิลนี้</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="text-end mt-3">
          <h5><strong>ยอดรวมสุทธิ:</strong> <?= number_format($purchase['total_amount'], 2) ?> บาท</h5>
        </div>

      <?php else: ?>
        <div class="alert alert-danger">ไม่พบบิลนี้ในระบบ</div>
      <?php endif; ?>

      <div class="mt-4">
        <a href="warehouse_page.php" class="btn btn-secondary">กลับ</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
