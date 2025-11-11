<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงรายการสินค้าในสต็อก
$sql = "SELECT product_id, product_name, unit, selling_price, stock_qty 
        FROM products 
        WHERE stock_qty > 0
        ORDER BY product_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มบิลขายสินค้า</title>
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
          <li class="nav-item"><a class="nav-link" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">🚚 เพิ่มบิลขายสินค้า</h2>
    <form action="stock_out_save.php" method="POST" id="sale-form">

        <div class="mb-3 col-md-4">
            <label for="sale_date" class="form-label">วันที่ขาย</label>
            <input type="date" id="sale_date" name="sale_date" class="form-control" required>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark text-center">
                <tr>
                    <th>สินค้า</th>
                    <th>หน่วย</th>
                    <th>ราคาขาย</th>
                    <th>จำนวนคงเหลือ</th>
                    <th>จำนวนที่ขาย</th>
                    <th>ราคารวม</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="itemBody">
                <tr>
                    <td>
                        <select name="product_id[]" class="form-select product-select" required>
                            <option value="">-- เลือกสินค้า --</option>
                            <?php mysqli_data_seek($result, 0); ?>
                            <?php while ($p = $result->fetch_assoc()): ?>
                                <option value="<?= $p['product_id'] ?>"
                                        data-unit="<?= htmlspecialchars($p['unit']) ?>"
                                        data-price="<?= $p['selling_price'] ?>"
                                        data-stock="<?= $p['stock_qty'] ?>">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td><input type="text" class="form-control unit" readonly></td>
                    <td><input type="text" class="form-control price text-end" readonly></td>
                    <td><input type="text" class="form-control stock text-center" readonly></td>
                    <td><input type="number" name="quantity[]" class="form-control quantity text-center" min="1" required></td>
                    <td><input type="text" class="form-control row-total text-end" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-remove">-</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" id="btnAdd" class="btn btn-secondary">+ เพิ่มแถว</button>
        <button type="submit" class="btn btn-success">บันทึกการขาย</button>
        <a href="warehouse_page.php" class="btn btn-outline-secondary">ยกเลิก</a>

        <div class="mt-3 text-end">
            <h4>ราคารวมทั้งหมด: <span id="totalAmount" class="text-success">0.00</span> บาท</h4>
        </div>
    </form>
</div>

<script>
function addRowListeners(row) {
    const productSelect = row.querySelector('.product-select');
    const quantityInput = row.querySelector('.quantity');
    const removeBtn = row.querySelector('.btn-remove');

    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const tr = this.closest('tr');
        tr.querySelector('.unit').value = selectedOption.dataset.unit || '';
        tr.querySelector('.price').value = parseFloat(selectedOption.dataset.price || 0).toFixed(2);
        tr.querySelector('.stock').value = selectedOption.dataset.stock || '';
        quantityInput.max = selectedOption.dataset.stock || 1; // ตั้งค่า max ของจำนวนที่ขาย
        updateTotals();
    });

    quantityInput.addEventListener('input', updateTotals);

    removeBtn.addEventListener('click', () => {
        row.remove();
        updateTotals();
    });
}

function updateTotals() {
    let totalAmount = 0;
    document.querySelectorAll('#itemBody tr').forEach(row => {
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const quantity = parseInt(row.querySelector('.quantity').value) || 0;
        const rowTotal = price * quantity;
        row.querySelector('.row-total').value = rowTotal.toFixed(2);
        totalAmount += rowTotal;
    });
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

document.getElementById('btnAdd').addEventListener('click', () => {
    const tbody = document.getElementById('itemBody');
    const firstRow = tbody.querySelector('tr');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelector('select').selectedIndex = 0;
    tbody.appendChild(newRow);
    addRowListeners(newRow);
});

document.querySelectorAll('#itemBody tr').forEach(addRowListeners);

</script>
</body>
</html>
