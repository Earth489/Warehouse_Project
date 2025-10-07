<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ ดึงข้อมูลบิลรับสินค้าเข้า
$sql_in = "SELECT p.purchase_id, p.purchase_number, p.purchase_date, 
                  s.supplier_name, p.total_amount
           FROM purchases p
           LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
           ORDER BY p.purchase_date DESC";
$result_in = $conn->query($sql_in);

// ✅ ดึงข้อมูลบิลขายสินค้าออก
$sql_out = "SELECT sale_id, sale_number, sale_date, total_amount 
            FROM sales
            ORDER BY sale_date DESC";
$result_out = $conn->query($sql_out);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายการบิลสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.card-bill {
  margin-top: 20px;
  transition: transform 0.2s, box-shadow 0.2s;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.card-bill:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}
.card-header {
  background: linear-gradient(#23231A);
  color: white;
  font-weight: 600;
  border-radius: 15px 15px 0 0;
}
.badge-supplier, .badge-customer { background-color: #6c757d; }
</style>
</head>
<body>

<!-- เมนูบน -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏠 Warehouse System</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
        <li class="nav-item"><a class="nav-link  active" href="warehouse_page.php">รายการบิลสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
        <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
  <h2 class="fw-bold mb-4"> รายการบิลสินค้า</h2>

  <!-- แท็บเลือกดู -->
  <ul class="nav nav-tabs" id="billTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="in-tab" data-bs-toggle="tab" data-bs-target="#in" type="button" role="tab">
         บิลรับสินค้าเข้า
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="out-tab" data-bs-toggle="tab" data-bs-target="#out" type="button" role="tab">
         บิลขายสินค้าออก
      </button>
    </li>
  </ul>

  <div class="tab-content mt-3" id="billTabsContent">

    <!-- ✅ แท็บ: บิลรับสินค้าเข้า -->
    <div class="tab-pane fade show active" id="in" role="tabpanel">
      <a href="stock_in_add.php" class="btn btn-primary mb-3">+ เพิ่มบิลรับสินค้า</a>
      <div class="row g-4">
        <?php if ($result_in && $result_in->num_rows > 0): ?>
          <?php while ($row = $result_in->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card card-bill">
                <div class="card-header d-flex justify-content-between">
                  <span>เลขที่บิล: <?= htmlspecialchars($row['purchase_number']) ?></span>
                  <span><?= date("d/m/Y", strtotime($row['purchase_date'])) ?></span>
                </div>
                <div class="card-body">
                  <p><strong>ซัพพลายเออร์:</strong> 
                     <span class="badge badge-supplier"><?= htmlspecialchars($row['supplier_name']) ?: '-' ?></span></p>
                  <p><strong>ยอดรวม:</strong> 
                     <span class="text-success fw-bold"><?= number_format($row['total_amount'], 2) ?> บาท</span></p>
                  <div class="text-end">
                    <a href="purchase_detail.php?id=<?= $row['purchase_id'] ?>" 
                       class="btn btn-outline-primary btn-sm">ดูรายละเอียด</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-center text-muted mt-4">
            <h5>ยังไม่มีบิลรับสินค้า</h5>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ✅ แท็บ: บิลขายสินค้าออก -->
    <div class="tab-pane fade" id="out" role="tabpanel">
      <a href="stock_out_add.php" class="btn btn-warning mb-3 text-dark">+ เพิ่มบิลขายสินค้า</a>
      <div class="row g-4">
        <?php if ($result_out && $result_out->num_rows > 0): ?>
          <?php while ($row = $result_out->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card card-bill">
                <div class="card-header d-flex justify-content-between">
                  <span>เลขที่บิล: <?= htmlspecialchars($row['sale_number']) ?></span>
                  <span><?= date("d/m/Y", strtotime($row['sale_date'])) ?></span>
                </div>
                <div class="card-body">
                  <p><strong>ลูกค้า:</strong> 
                    <span class="badge badge-customer">ลูกค้าทั่วไป</span></p>
                  <p><strong>ยอดรวม:</strong> 
                     <span class="text-danger fw-bold"><?= number_format($row['total_amount'], 2) ?> บาท</span></p>
                  <div class="text-end">
                    <a href="sale_detail.php?id=<?= $row['sale_id'] ?>" 
                       class="btn btn-outline-danger btn-sm">ดูรายละเอียด</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-center text-muted mt-4">
            <h5>ยังไม่มีบิลขายสินค้า</h5>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
