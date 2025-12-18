<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. ดึงรายชื่อสินค้า
$product_sql = "SELECT product_id, product_name FROM products ORDER BY product_name ASC";
$product_result = $conn->query($product_sql);

// ----------------------------------------------------
// 2. Logic การค้นหา
// ----------------------------------------------------
$search_product_id = isset($_GET['search_product_id']) ? $_GET['search_product_id'] : '';

if ($search_product_id != '') {
    $safe_id = mysqli_real_escape_string($conn, $search_product_id);
    
    // เชื่อมตาราง purchases -> purchase_details ตามที่แก้ให้ถูกต้องแล้ว
    $sql = "SELECT DISTINCT s.* FROM suppliers s
            INNER JOIN purchases pu ON s.supplier_id = pu.supplier_id
            INNER JOIN purchase_details pd ON pu.purchase_id = pd.purchase_id
            WHERE pd.product_id = '$safe_id'
            ORDER BY s.supplier_id ASC";
} else {
    $sql = "SELECT * FROM suppliers ORDER BY supplier_id ASC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ซัพพลายเออร์</title>
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <style>
      body{font-family:'Prompt',sans-serif;}
      /* ปรับแต่งช่อง Select2 ให้เข้ากับ Bootstrap */
      .select2-container--bootstrap-5 .select2-selection {
          border-color: #dee2e6;
      }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 ระบบจัดการคลังสินค้า สำหรับร้านวัสดุก่อสร้าง</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>     
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>      
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class= "nav-link " htef="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
          
        </ul>
      </div>
    </div>
  </nav>
  
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>ซัพพลายเออร์</h2>
      <a href="add_suppliers.php" class="btn btn-primary">+ เพิ่มซัพพลายเออร์</a>
  </div>

  <div class="card mb-4 ">
    <div class="card-body">
      <form method="GET" id="searchForm" class="row g-3 align-items-center">
        <div class="col-md-auto">
           <label class="fw-bold"><i class="bi bi-keyboard"></i> พิมพ์ชื่อสินค้าเพื่อหาคนขาย:</label>
        </div>
        <div class="col-md-6">
           <select name="search_product_id" class="form-select my-select2">
               <option value=""> พิมพ์ชื่อสินค้า... </option>
               <?php 
               if($product_result) {
                   mysqli_data_seek($product_result, 0); 
                   while($prod = $product_result->fetch_assoc()) {
                       $selected = ($search_product_id == $prod['product_id']) ? 'selected' : '';
                       echo "<option value='{$prod['product_id']}' $selected>{$prod['product_name']}</option>";
                   }
               }
               ?>
           </select>
        </div>
        <div class="col-md-auto">
            <?php if($search_product_id != ''): ?>
                <a href="suppliers.php" class="btn btn-outline-secondary">ล้างค่า</a>
            <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <table class="table table-bordered table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th class="text-center"> ID</th>
        <th class="text-center">ชื่อซัพพลายเออร์</th>
        <th width="20%" class="text-center">ที่อยู่</th>
        <th class="text-center"> เบอร์โทร</th>
        <th class="text-center">รายละเอียด</th>
        <th class="text-center">การจัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $is_target = ($search_product_id != '') ? 'table-success' : ''; 
              echo "<tr class='$is_target'>
                      <td>{$row['supplier_id']}</td>
                      <td>
                        <span class='fw-bold'>{$row['supplier_name']}</span>";
              
              if ($search_product_id != '') {
                   echo " <span class='badge bg-primary ms-2'>เคยสั่งกับเจ้านี้</span>";
              }

              echo "  </td>
                      <td>{$row['address']}</td>
                      <td class='text-center'>{$row['phone']}</td>
                      <td>{$row['description']}</td>
                      <td class='text-center'>
                        <a href='supplier_edit.php?id={$row['supplier_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                        <a href='supplier_delete.php?id={$row['supplier_id']}' onclick=\"return confirm('ยืนยันลบ?');\" class='btn btn-danger btn-sm'>ลบ</a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6' class='text-center text-muted p-4'>";
          if ($search_product_id != '') {
              echo "<i class='bi bi-inbox fs-4'></i><br>ไม่พบประวัติการสั่งซื้อสินค้านี้<br><small>(สินค้าใหม่ หรือ ยังไม่เคยทำรายการรับเข้า)</small>";
          } else {
              echo "ไม่พบข้อมูลซัพพลายเออร์";
          }
          echo "</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.my-select2').select2({
            theme: 'bootstrap-5', // ใช้ธีม Bootstrap 5 ให้สวยงาม
            width: '100%',        // ความกว้างเต็มช่อง
            placeholder: '-- พิมพ์ชื่อสินค้า... --',
            allowClear: true
        });

        // เมื่อเลือกสินค้าเสร็จ ให้กดค้นหาอัตโนมัติ (Submit Form)
        $('.my-select2').on('change', function() {
            $('#searchForm').submit(); 
        });
    });
</script>

</body>
</html>