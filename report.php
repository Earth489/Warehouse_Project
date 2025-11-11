<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$bill_type = $_GET['bill_type'] ?? 'all';

// ดึงข้อมูลบิล
if ($bill_type === 'บิลซื้อ (Purchase)') {
    $sql = "
        SELECT 
            p.purchase_id AS bill_id, 
            p.purchase_number AS bill_number, 
            p.purchase_date AS bill_date, 
            s.supplier_name AS party_name, 
            p.total_amount, 
            'บิลซื้อ (Purchase)' AS bill_type
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.purchase_date BETWEEN ? AND ?
        ORDER BY p.purchase_date DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
} elseif ($bill_type === 'บิลขาย (Sale)') {
    $sql = "
        SELECT 
            s.sale_id AS bill_id, 
            s.sale_number AS bill_number, 
            s.sale_date AS bill_date, 
            'ลูกค้าทั่วไป' AS party_name, 
            s.total_amount, 
            'บิลขาย (Sale)' AS bill_type
        FROM sales s
        WHERE s.sale_date BETWEEN ? AND ?
        ORDER BY s.sale_date DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $sql = "
        SELECT 
            p.purchase_id AS bill_id, 
            p.purchase_number AS bill_number, 
            p.purchase_date AS bill_date, 
            s.supplier_name AS party_name, 
            p.total_amount, 
            'บิลซื้อ (Purchase)' AS bill_type
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.purchase_date BETWEEN ? AND ?

        UNION ALL

        SELECT 
            s.sale_id AS bill_id, 
            s.sale_number AS bill_number, 
            s.sale_date AS bill_date, 
            'ลูกค้าทั่วไป' AS party_name, 
            s.total_amount, 
            'บิลขาย (Sale)' AS bill_type
        FROM sales s
        WHERE s.sale_date BETWEEN ? AND ?

        ORDER BY bill_date DESC, bill_type
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานสรุปสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@media print {
    .no-print { display: none; }
    body { background: white; }
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
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link active" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">รายงานสรุปสินค้า</h2>

    <!-- ฟอร์มเลือกวันที่ -->
    <form method="get" class="card card-body mb-4 no-print">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">ประเภทบิล</label>
                <select name="bill_type" class="form-select">
                    <option value="all" <?= $bill_type == 'all' ? 'selected' : '' ?>>บิลทั้งหมด</option>
                    <option value="บิลซื้อ (Purchase)" <?= $bill_type == 'บิลซื้อ (Purchase)' ? 'selected' : '' ?>>บิลรับเข้า</option>
                    <option value="บิลขาย (Sale)" <?= $bill_type == 'บิลขาย (Sale)' ? 'selected' : '' ?>>บิลขายออก</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">แสดงรายงาน</button>
                <button type="button" class="btn btn-danger flex-fill" onclick="window.print()">พิมพ์รายงาน (PDF)</button>
            </div>
        </div>
    </form>

    <!-- ตารางรายงาน -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>วันที่:</strong> <?= date("d/m/Y", strtotime($row['bill_date'])) ?><br>
                        <strong>เลขที่บิล:</strong> <?= htmlspecialchars($row['bill_number']) ?><br>
                        <strong>คู่ค้า:</strong> <?= htmlspecialchars($row['party_name']) ?>
                    </div>
                    <span class="badge <?= ($row['bill_type'] == 'บิลซื้อ (Purchase)') ? 'bg-success' : 'bg-danger' ?>">
                        <?= htmlspecialchars($row['bill_type']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php
                    if ($row['bill_type'] == 'บิลซื้อ (Purchase)') {
                        $detail_sql = "
                            SELECT pd.quantity, pd.purchase_price AS price, p.product_name, p.unit
                            FROM purchase_details pd
                            JOIN products p ON pd.product_id = p.product_id
                            WHERE pd.purchase_id = ?
                        ";
                    } else {
                        $detail_sql = "
                            SELECT sd.quantity, sd.sale_price AS price, p.product_name, p.unit
                            FROM sale_details sd
                            JOIN products p ON sd.product_id = p.product_id
                            WHERE sd.sale_id = ?
                        ";
                    }
                    $stmt2 = $conn->prepare($detail_sql);
                    $stmt2->bind_param("i", $row['bill_id']);
                    $stmt2->execute();
                    $details = $stmt2->get_result();
                    if ($details->num_rows > 0): ?>
                        <table class="table table-bordered mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th>สินค้า</th>
                                    <th>จำนวน</th>
                                    <th>หน่วย</th>
                                    <th>ราคา/หน่วย</th>
                                    <th>รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sum = 0;
                                while ($d = $details->fetch_assoc()):
                                    $line_total = $d['quantity'] * $d['price'];
                                    $sum += $line_total; ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['product_name']) ?></td>
                                        <td><?= $d['quantity'] ?></td>
                                        <td><?= htmlspecialchars($d['unit']) ?></td>
                                        <td><?= number_format($d['price'], 2) ?></td>
                                        <td><?= number_format($line_total, 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-secondary">
                                    <td colspan="4" class="text-end fw-bold">รวมทั้งหมด</td>
                                    <td class="fw-bold"><?= number_format($sum, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted mb-0">ไม่มีรายละเอียดสินค้าในบิลนี้</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">ไม่พบบิลในช่วงวันที่ที่เลือก</div>
    <?php endif; ?>
</div>
</body>
</html>
