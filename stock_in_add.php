<?php
// stock_in_add.php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

// ดึงสินค้า + ข้อมูลที่ต้องการใส่เป็น data-* ใน option
$sqlProducts = "SELECT p.product_id, p.product_name, p.unit, p.selling_price,
                       p.supplier_id, IFNULL(s.supplier_name,'-') AS supplier_name,
                       p.category_id, IFNULL(c.category_name,'-') AS category_name
                FROM products p
                LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.product_name ASC";
$prodResult = $conn->query($sqlProducts);
$products = [];
while ($r = $prodResult->fetch_assoc()) $products[] = $r;

// ดึงรายชื่อ suppliers (ถ้าต้องการแสดงเลือก)
$supRes = $conn->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

// ข้อความแจ้งผล
$errors = [];
$success = '';

// เมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูล arrays
    $product_ids = $_POST['product'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    // ตรวจสอบข้อมูลพื้นฐาน
    if (!is_array($product_ids) || count($product_ids) == 0) {
        $errors[] = "กรุณาเลือกสินค้าอย่างน้อย 1 รายการ";
    } else {
        // sanitize and cast
        $items = [];
        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = isset($quantities[$i]) ? (int)$quantities[$i] : 0;
            if ($pid <= 0 || $qty <= 0) {
                $errors[] = "พบข้อมูลสินค้าที่ไม่ถูกต้อง (รหัสหรือจำนวน) ในแถวที่ " . ($i+1);
                break;
            }
            $items[] = ['product_id'=>$pid, 'qty'=>$qty];
        }
    }

    if (empty($errors)) {
        // ดึง supplier_id และ price ของสินค้าที่เกี่ยวข้อง (เพื่อความปลอดภัยอย่าเชื่อ client)
        $ids = array_column($items, 'product_id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $sqlFetch = "SELECT product_id, supplier_id, selling_price FROM products WHERE product_id IN ($placeholders)";
        $stmt = $conn->prepare($sqlFetch);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();
        $map = [];
        while ($row = $res->fetch_assoc()) {
            $map[$row['product_id']] = $row;
        }
        $stmt->close();

        // ตรวจสอบว่าครบทุก product และ supplier เดียวกันหรือไม่
        $firstSupplier = null;
        foreach ($items as $it) {
            $pid = $it['product_id'];
            if (!isset($map[$pid])) {
                $errors[] = "ไม่พบข้อมูลสินค้า ID: $pid";
                break;
            }
            $supp = $map[$pid]['supplier_id'];
            if ($firstSupplier === null) $firstSupplier = $supp;
            if ($supp != $firstSupplier) {
                $errors[] = "รายการสินค้าในบิลต้องมาจากผู้จำหน่ายเดียวกัน กรุณาตรวจสอบ";
                break;
            }
        }

        if (empty($errors)) {
            // คำนวณ total_amount (ใช้ selling_price เป็นค่า purchase_price ตามที่ต้องการ)
            $total_amount = 0.0;
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = $it['qty'];
                $price = (float)$map[$pid]['selling_price'];
                $total_amount += $qty * $price;
            }

            // ทำ transaction: insert purchases, purchase_details, update products
            try {
                $conn->begin_transaction();

                $ins = $conn->prepare("INSERT INTO purchases (user_id, supplier_id, total_amount) VALUES (?, ?, ?)");
                $ins->bind_param("iid", $uid, $firstSupplier, $total_amount);
                $ins->execute();
                $purchase_id = $ins->insert_id;
                $ins->close();

                $insDet = $conn->prepare("INSERT INTO purchase_details (purchase_id, product_id, quantity, purchase_price) VALUES (?, ?, ?, ?)");
                $upd = $conn->prepare("UPDATE products SET stock_qty = stock_qty + ?, status = 'active' WHERE product_id = ?");

                foreach ($items as $it) {
                    $pid = $it['product_id'];
                    $qty = $it['qty'];
                    $price = (float)$map[$pid]['selling_price'];

                    $insDet->bind_param("iiid", $purchase_id, $pid, $qty, $price);
                    $insDet->execute();

                    $upd->bind_param("ii", $qty, $pid);
                    $upd->execute();
                }

                $insDet->close();
                $upd->close();

                $conn->commit();
                $success = "บันทึกการรับเข้าสินค้าเรียบร้อยแล้ว";
                // redirect ไปหน้า warehouse หรือแสดง success
                header("Location: warehouse_page.php?msg=stockin_ok");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "เกิดข้อผิดพลาดขณะบันทึก: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รับสินค้าเข้าคลัง</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  .table td, .table th { vertical-align: middle; }
  .small-input { max-width: 120px; }
</style>
</head>
<body>
<div class="container mt-4">
  <h2>รับสินค้าเข้าคลัง (Stock In)</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $err) echo "<div>".htmlspecialchars($err)."</div>"; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <form method="post" id="stockInForm">
    <div class="mb-3">
      <small class="text-muted">เลือกสินค้าแล้วกรอกจำนวนที่รับเข้า (ราคา/ประเภท/ซัพพลายเออร์จะแสดงอัตโนมัติ)</small>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered" id="itemsTable">
        <thead class="table-dark text-center">
          <tr>
            <th style="width:32%">สินค้า</th>
            <th style="width:16%">ประเภท</th>
            <th style="width:18%">ซัพพลายเออร์</th>
            <th style="width:8%">หน่วย</th>
            <th style="width:12%">ราคา (บาท)</th>
            <th style="width:8%">จำนวน</th>
            <th style="width:6%"></th>
          </tr>
        </thead>
        <tbody>
          <tr class="item-row">
            <td>
              <select name="product[]" class="form-select product-select" required>
                <option value="">-- เลือกสินค้า --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['product_id'] ?>"
                    data-cat="<?= htmlspecialchars($p['category_name']) ?>"
                    data-supp-id="<?= (int)$p['supplier_id'] ?>"
                    data-supp-name="<?= htmlspecialchars($p['supplier_name']) ?>"
                    data-unit="<?= htmlspecialchars($p['unit']) ?>"
                    data-price="<?= htmlspecialchars($p['selling_price']) ?>"
                  >
                    <?= htmlspecialchars($p['product_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="text" class="form-control form-control-sm cat" readonly></td>
            <td>
              <input type="text" class="form-control form-control-sm supp" readonly>
              <input type="hidden" class="supp-id" name="supplier_id_row[]" value="">
            </td>
            <td><input type="text" class="form-control form-control-sm unit" readonly></td>
            <td><input type="text" class="form-control form-control-sm price text-end" readonly></td>
            <td><input type="number" name="quantity[]" class="form-control form-control-sm text-center small-input" min="1" required></td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-danger btn-remove">-</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mb-3 d-flex justify-content-between">
      <div>
        <button type="button" id="btnAddRow" class="btn btn-secondary">+ เพิ่มแถว</button>
      </div>
      <div>
        <button type="submit" class="btn btn-primary">บันทึก</button>
        <a href="warehouse_page.php" class="btn btn-outline-secondary">ยกเลิก</a>
      </div>
    </div>
  </form>
</div>

<script>
// ฟังก์ชันอัปเดตช่องข้อมูลแถวเมื่อเลือกสินค้า
function updateRow(selectEl) {
  const opt = selectEl.options[selectEl.selectedIndex];
  const row = selectEl.closest('tr');
  const cat = row.querySelector('.cat');
  const supp = row.querySelector('.supp');
  const suppIdInput = row.querySelector('.supp-id');
  const unit = row.querySelector('.unit');
  const price = row.querySelector('.price');

  if (!opt || !opt.value) {
    cat.value = supp.value = unit.value = price.value = '';
    suppIdInput.value = '';
    return;
  }
  cat.value = opt.dataset.cat || '';
  supp.value = opt.dataset.suppName || '';
  suppIdInput.value = opt.dataset.suppId || '';
  unit.value = opt.dataset.unit || '';
  price.value = parseFloat(opt.dataset.price || 0).toFixed(2);

  // ถ้าเป็นแถวแรก ให้ตั้ง supplier หลักในฟอร์ม (และตรวจสอบแถวต่อ ๆ ไปต้องตรงกับ supplier เดียวกัน)
  enforceSameSupplier();
}

// เพิ่มแถวใหม่
document.getElementById('btnAddRow').addEventListener('click', function() {
  const tbody = document.querySelector('#itemsTable tbody');
  const firstRow = document.querySelector('.item-row');
  const newRow = firstRow.cloneNode(true);

  // เคลียร์ค่า
  newRow.querySelectorAll('input').forEach(i => i.value = '');
  newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

  tbody.appendChild(newRow);
  attachRowListeners(newRow);
});

// ลบแถว
function attachRowListeners(row) {
  row.querySelectorAll('.product-select').forEach(sel => {
    sel.addEventListener('change', function(){ updateRow(this); });
  });
  row.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', function(){
      const tbody = document.querySelector('#itemsTable tbody');
      if (tbody.children.length > 1) {
        this.closest('tr').remove();
        enforceSameSupplier();
      } else {
        // เคลียร์แถวสุดท้ายแทนลบ
        const row = this.closest('tr');
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
      }
    });
  });
}

// ตรวจสอบว่า supplier ของทุกแถวเหมือนกัน ถ้าไม่เหมือน ให้เตือนและล้างการเลือกผิด
function enforceSameSupplier() {
  const rows = document.querySelectorAll('#itemsTable tbody tr');
  let mainSupp = null;
  for (const row of rows) {
    const suppId = row.querySelector('.supp-id').value;
    if (!suppId) continue;
    if (mainSupp === null) mainSupp = suppId;
    else if (mainSupp !== suppId) {
      alert('รายการทั้งหมดในบิลต้องมาจากผู้จำหน่ายคนเดียวกัน กรุณาเลือกสินค้าใหม่หรือแยกเป็นบิลอื่น');
      // ล้างแถวที่เพิ่งเลือกไม่ตรง (หาทางที่เพิ่งต่างจาก mainSupp)
      for (const r of rows) {
        const s = r.querySelector('.supp-id').value;
        if (s && s !== mainSupp) {
          r.querySelector('.product-select').selectedIndex = 0;
          updateRow(r.querySelector('.product-select'));
        }
      }
      break;
    }
  }
}

// attach listeners ให้แถวเริ่มต้น
document.querySelectorAll('.item-row').forEach(r => attachRowListeners(r));

// ถ้าบันทึก ให้ตรวจสอบก่อน submit (optional)
document.getElementById('stockInForm').addEventListener('submit', function(e){
  // ตรวจสอบอย่างน้อย 1 แถวมี product เลือก
  const rows = document.querySelectorAll('#itemsTable tbody tr');
  let has = false;
  for (const row of rows) {
    const sel = row.querySelector('.product-select');
    const q = row.querySelector('input[name="quantity[]"]').value;
    if (sel && sel.value && q && parseInt(q) > 0) { has = true; break; }
  }
  if (!has) {
    e.preventDefault();
    alert('กรุณาเลือกสินค้าและกรอกจำนวนอย่างน้อย 1 รายการ');
  }
});
</script>
</body>
</html>
