<?php
include 'connection.php';
session_start();

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['sale_id'])) {
    echo "ไม่พบบิลที่ต้องการดู";
    exit;
}

$sale_id = $_GET['sale_id'];

// ดึงข้อมูลบิลขาย (ไม่ผูกกับ user)
$sql_sale = "SELECT sale_id, sale_number, sale_date, total_amount 
             FROM sales
             WHERE sale_id = ?";
$stmt = $conn->prepare($sql_sale);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result_sale = $stmt->get_result();

if ($result_sale->num_rows == 0) {
    echo "ไม่พบบิลที่ต้องการดู";
    exit;
}

$sale = $result_sale->fetch_assoc();

// ดึงรายละเอียดสินค้าในบิล
$sql_detail = "SELECT sd.*, p.product_name, p.unit 
               FROM sale_details sd 
               JOIN products p ON sd.product_id = p.product_id
               WHERE sd.sale_id = ?";
$stmt2 = $conn->prepare($sql_detail);
$stmt2->bind_param("i", $sale_id);
$stmt2->execute();
$result_detail = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดบิลขาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link active" href="warehouse_page.php">รายการบิลสินค้า</a></li>
         <!-- <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-5 mb-5">
  <div class="card shadow">
    <div class="card-header bg-dark text-white">
      <h4 class="mb-0">รายละเอียดบิลขาย</h4>
    </div>
    <div class="card-body">
      <p><strong>เลขที่บิล:</strong> <?= htmlspecialchars($sale['sale_number']) ?></p>
      <p><strong>วันที่ขาย:</strong> <?= htmlspecialchars($sale['sale_date']) ?></p>
      <p><strong>ยอดรวม:</strong> <?= number_format($sale['total_amount'], 2) ?> บาท</p>

      <h5 class="mt-4">รายการสินค้า</h5>
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>ชื่อสินค้า</th>
            <th>จำนวน</th>
            <th>หน่วย</th>
            <th>ราคาขาย</th>
            <th>รวม</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total = 0;
        while ($row = $result_detail->fetch_assoc()) {
            $sum = $row['quantity'] * $row['sale_price'];
            $total += $sum;
            echo "<tr>
                    <td>{$row['product_name']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['unit']}</td>
                    <td>" . number_format($row['sale_price'], 2) . "</td>
                    <td>" . number_format($sum, 2) . "</td>
                  </tr>";
        }
        ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">รวมทั้งหมด</th>
            <th><?= number_format($total, 2) ?></th>
          </tr>
        </tfoot>
      </table>

      <a href="warehouse_page.php" class="btn btn-secondary mt-3">กลับ</a>
    </div>
  </div>
</div>
</body>
</html>
