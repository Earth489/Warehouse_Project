<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลหมวดหมู่สินค้าสำหรับ Filter
$categories_result = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");

// รับค่าจากฟอร์มค้นหา
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$category_id = $_GET['category_id'] ?? 'all';

$params = [];
$types = '';

// สร้าง SQL Query หลัก
$sql = "
    SELECT
        p.product_id,
        p.product_name,
        p.stock_qty,
        COALESCE(purchase_summary.total_quantity_purchased, 0) AS total_quantity_purchased,
        COALESCE(purchase_summary.total_purchase_amount, 0) AS total_purchase_amount,
        COALESCE(sales_summary.total_quantity_sold, 0) AS total_quantity_sold,
        COALESCE(sales_summary.total_sales_amount, 0) AS total_sales_amount
    FROM
        products p
    LEFT JOIN (
        SELECT
            pd.product_id,
            SUM(pd.quantity) AS total_quantity_purchased,
            SUM(pd.quantity * pd.purchase_price) AS total_purchase_amount
        FROM
            purchase_details pd
        JOIN
            purchases pu ON pd.purchase_id = pu.purchase_id
        WHERE pu.purchase_date BETWEEN ? AND ?
        GROUP BY pd.product_id
    ) AS purchase_summary ON p.product_id = purchase_summary.product_id
    LEFT JOIN (
        SELECT
            sd.product_id,
            SUM(sd.quantity) AS total_quantity_sold,
            SUM(sd.quantity * sd.sale_price) AS total_sales_amount
        FROM
            sale_details sd
        JOIN
            sales s ON sd.sale_id = s.sale_id
        WHERE s.sale_date BETWEEN ? AND ?
        GROUP BY sd.product_id
    ) AS sales_summary ON p.product_id = sales_summary.product_id
    WHERE 1=1
";

$params[] = $start_date;
$params[] = $end_date;
$params[] = $start_date;
$params[] = $end_date;
$types .= 'ssss';

if ($category_id != 'all') {
    $sql .= " AND p.category_id = ?";
    $params[] = (int)$category_id;
    $types .= 'i';
}

$sql .= " ORDER BY p.product_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$report_result = $stmt->get_result();

$report_data = [];
$chart_labels = [];
$chart_purchase_data = [];
$chart_sales_data = [];
$total_stock = 0;
$total_purchased_qty = 0;
$total_purchase_value = 0;
$total_sold_qty = 0;
$total_sales_value = 0;

while ($row = $report_result->fetch_assoc()) {
    $report_data[] = $row;
    $chart_labels[] = $row['product_name'];
    $chart_purchase_data[] = $row['total_purchase_amount'];
    $chart_sales_data[] = $row['total_sales_amount'];
    $total_stock += $row['stock_qty'];
    $total_purchased_qty += $row['total_quantity_purchased'];
    $total_purchase_value += $row['total_purchase_amount'];
    $total_sold_qty += $row['total_quantity_sold'];
    $total_sales_value += $row['total_sales_amount'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

<!-- เมนูบน -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏠 Warehouse System</a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="history.php">ประวัติ</a></li>
        <li class="nav-item"><a class="nav-link active" href="report.php">รายงาน</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">รายงานสรุปสินค้า</h2>

    <!-- ฟอร์มค้นหา -->
    <form method="GET" class="card card-body mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">ตั้งแต่วันที่</label><input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>"></div>
            <div class="col-md-3"><label class="form-label">ถึงวันที่</label><input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>"></div>
            <div class="col-md-4">
                <label class="form-label">หมวดหมู่สินค้า</label>
                <select name="category_id" class="form-select">
                    <option value="all">ทั้งหมด</option>
                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= ($category_id == $cat['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">แสดงรายงาน</button></div>
        </div>
    </form>

    <!-- ปุ่ม Export -->
    <div class="d-flex justify-content-end mb-3 gap-2">
        <button id="exportExcel" class="btn btn-success">ดาวน์โหลด Excel</button>
        <button id="exportPdf" class="btn btn-danger">ดาวน์โหลด PDF</button>
    </div>

    <!-- กราฟ -->
    <div class="card card-body mb-4">
        <h5 class="fw-bold">กราฟสรุปยอดซื้อ-ขาย</h5>
        <canvas id="salesChart"></canvas>
    </div>

    <!-- ตารางแสดงผล -->
    <table class="table table-bordered table-striped" id="reportTable">
        <thead class="table-dark">
            <tr>
                <th>ชื่อสินค้า</th>
                <th>จำนวนคงเหลือ</th>
                <th>จำนวนที่ซื้อ</th>
                <th>ยอดซื้อรวม (บาท)</th>
                <th>จำนวนที่ขายได้</th>
                <th>ยอดขายรวม (บาท)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php foreach ($report_data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td class="text-end"><?= number_format($row['stock_qty']) ?></td>
                        <td class="text-end"><?= number_format($row['total_quantity_purchased']) ?></td>
                        <td class="text-end"><?= number_format($row['total_purchase_amount'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['total_quantity_sold']) ?></td>
                        <td class="text-end"><?= number_format($row['total_sales_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="table-group-divider fw-bold">
            <tr>
                <td class="text-end">รวมทั้งหมด</td>
                <td class="text-end"><?= number_format($total_stock) ?></td>
                <td class="text-end"><?= number_format($total_purchased_qty) ?></td>
                <td class="text-end"><?= number_format($total_purchase_value, 2) ?></td>
                <td class="text-end"><?= number_format($total_sold_qty) ?></td>
                <td class="text-end"><?= number_format($total_sales_value, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Chart.js
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [
                {
                    label: 'ยอดซื้อ (บาท)',
                    data: <?= json_encode($chart_purchase_data) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                },
                {
                    label: 'ยอดขาย (บาท)',
                    data: <?= json_encode($chart_sales_data) ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            responsive: true
        }
    });

    // Export to Excel
    document.getElementById('exportExcel').addEventListener('click', function () {
        const table = document.getElementById('reportTable');
        const wb = XLSX.utils.table_to_book(table, { sheet: "Product Report" });
        XLSX.writeFile(wb, "product_report_<?= date('Y-m-d') ?>.xlsx");
    });

    // Export to PDF
    document.getElementById('exportPdf').addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // เพิ่ม Font ภาษาไทย
        // หมายเหตุ: คุณต้องมีไฟล์ font .ttf ในโปรเจกต์ของคุณ
        // doc.addFileToVFS('THSarabunNew.ttf', font_base64_string);
        // doc.addFont('THSarabunNew.ttf', 'THSarabunNew', 'normal');
        // doc.setFont('THSarabunNew');

        doc.text("รายงานสรุปสินค้า", 14, 16);
        doc.autoTable({
            html: '#reportTable',
            startY: 20,
            theme: 'grid',
            // styles: { font: 'THSarabunNew', fontSize: 12 } // เปิดใช้งานถ้ามี font
        });

        doc.save('product_report_<?= date('Y-m-d') ?>.pdf');
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
