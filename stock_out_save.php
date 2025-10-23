<?php
include 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {

    $user_id = $_SESSION['user_id'];
    $sale_date = $_POST['sale_date'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    // สร้างเลขที่บิลแบบง่าย
    $sale_number = 'SO' . date('YmdHis');

    $conn->begin_transaction();

    try {
        // คำนวณยอดรวมทั้งหมด
        $total_amount = 0;
        foreach ($product_ids as $pid) {
            $qty = (int)$quantities[$pid];
            $price = $conn->query("SELECT selling_price FROM products WHERE product_id=$pid")->fetch_assoc()['selling_price'];
            $total_amount += $qty * $price;
        }

        // บันทึกลงตาราง sales
        $stmt = $conn->prepare("INSERT INTO sales (sale_number, user_id, sale_date, total_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisd", $sale_number, $user_id, $sale_date, $total_amount);
        $stmt->execute();
        $sale_id = $stmt->insert_id;

        // บันทึกสินค้าใน sale_details และอัปเดตสต็อก
        $stmt_detail = $conn->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, sale_price) VALUES (?, ?, ?, ?)");

        foreach ($product_ids as $pid) {
            $qty = (int)$quantities[$pid];

            // 🔍 ตรวจสอบจำนวนในคลังก่อนขาย
            $check = $conn->query("SELECT stock_qty, selling_price, product_name FROM products WHERE product_id=$pid");
            $data = $check->fetch_assoc();
            $current_stock = (int)$data['stock_qty'];
            $price = $data['selling_price'];
            $product_name = $data['product_name'];

            if ($qty > $current_stock) {
                // ❌ ถ้าขายเกินสต็อก ให้ยกเลิกธุรกรรมและแจ้งเตือน
                $conn->rollback();
                echo "<script>
                    alert('❌ สินค้า \"$product_name\" มีในคลังเพียง $current_stock ชิ้น ขายเกินไม่ได้!');
                    window.history.back();
                </script>";
                exit();
            }

            // ✅ ถ้าไม่เกิน ให้บันทึกรายละเอียดการขาย
            $stmt_detail->bind_param("iiid", $sale_id, $pid, $qty, $price);
            $stmt_detail->execute();

            // และอัปเดตจำนวนคงเหลือ
            $conn->query("UPDATE products SET stock_qty = stock_qty - $qty WHERE product_id = $pid");
        }

        $conn->commit();

        echo "<script>alert('✅ บันทึกการขายสินค้าเรียบร้อย'); window.location='warehouse_page.php';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "'); history.back();</script>";
    }

} else {
    echo "<script>alert('กรุณาเลือกสินค้าก่อนทำการบันทึก'); history.back();</script>";
}
?>
