<?php
// ======================================
// 1. session 啟動與安全檢查
// ======================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';
$member_id = $_SESSION['member_id'] ?? null;

if (!$member_id) {
    die("請先登入會員");
}
$connect->set_charset("utf8mb4");

// ======================================
// 2. 取得 POST 結帳資料
// ======================================
$payment_method = $_POST['payment_method'] ?? '信用卡付款';
$selected_items = $_POST['selected_items'] ?? []; // 這是從前端傳過來被勾選的商品自增 id (p.id)

if (empty($selected_items)) {
    die("請至少選擇一項商品");
}

// ======================================
// 3. 取得該會員的 cart_id
// ======================================
$stmt = $connect->prepare("SELECT id FROM carts WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$cart = $result->fetch_assoc();
$stmt->close();

if (!$cart) {
    die("找不到購物車");
}
$cart_id = $cart['id'];

// ======================================
// 4. 查詢購物車內被選中商品的詳細資料與庫存
// ======================================
// 建立動態佔位符 ?,?,?
$placeholders = implode(',', array_fill(0, count($selected_items), '?'));

$sql = "
    SELECT
        ci.product_id, -- 這對應的是 products 表的 id
        ci.qty,        -- 購物車購買數量
        ci.price,      -- 購買單價
        p.name,        -- 商品名稱
        p.image,       -- 商品圖片
        p.qty AS stock_qty -- 商品目前的實際庫存量
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = ? AND ci.product_id IN ($placeholders)
";

$stmt = $connect->prepare($sql);

// 動態綁定參數 (第一個是 cart_id, 後面全部是商品的自增 id)
$types = "i" . str_repeat("i", count($selected_items));
$params = array_merge([$cart_id], $selected_items);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    // 即時庫存檢查
    if ($row['stock_qty'] < $row['qty']) {
        die("商品【{$row['name']}】庫存不足，目前僅剩 {$row['stock_qty']} 件");
    }
    $row['subtotal'] = $row['price'] * $row['qty'];
    $total_amount += $row['subtotal'];
    $items[] = $row;
}
$stmt->close();

if (empty($items)) {
   header("Location: index.php?route=cart");
   exit;
}

// ======================================
// 5. 計算最終結帳金額
// ======================================
$discount_amount = 200;
$final_amount = $total_amount - $discount_amount;
if ($final_amount < 0) {
    $final_amount = 0;
}

// ======================================
// 6. 開始交易 寫入訂單主表, 訂單明細表
// ======================================
$connect->begin_transaction();

try {
    // ─── A. 寫入訂單主表 (orders) ───
    $order_num = "QR" . date("Ymd") . "-" . rand(1000, 9999);

    $stmt = $connect->prepare("
        INSERT INTO orders 
        (order_num, member_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, ?, '已付款', '待出貨')
    ");

    $stmt->bind_param("siddds", $order_num, $member_id, $total_amount, $discount_amount, $final_amount, $payment_method);
    
    if (!$stmt->execute()) {
        throw new Exception("訂單主表寫入失敗: " . $stmt->error);
    }
    $order_id = $stmt->insert_id; // 取得剛產生的訂單流水號
    $stmt->close();

    // ─── B. 寫入訂單明細表 (order_items) ───
    $stmt_item = $connect->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, product_image, price, qty, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    // ─── C. 準備扣庫存的 SQL (對齊你的 products.id) ───
    $stmt_stock = $connect->prepare("
        UPDATE products 
        SET qty = qty - ? 
        WHERE id = ?
    ");

    // ─── D. 準備刪除購物車內項目的 SQL ───
    $stmt_delete = $connect->prepare("
        DELETE FROM cart_items 
        WHERE cart_id = ? AND product_id = ?
    ");

    // ─── E. 跑迴圈集體處裡所有購買商品 ───
    foreach ($items as $item) {
        
        // 1. 寫入明細
        $stmt_item->bind_param(
            "iissdid",
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['image'],
            $item['price'],
            $item['qty'],
            $item['subtotal']
        );
        if (!$stmt_item->execute()) {
            throw new Exception("明細寫入失敗: " . $stmt_item->error);
        }

        // 2. 正確扣除 products 資料表的庫存 (qty = qty - 購買數量 WHERE id = 商品自增id)
        $stmt_stock->bind_param("ii", $item['qty'], $item['product_id']);
        if (!$stmt_stock->execute()) {
            throw new Exception("庫存扣除失敗: " . $stmt_stock->error);
        }

        // 3. 從 cart_items 中移除已經結帳的商品
        $stmt_delete->bind_param("ii", $cart_id, $item['product_id']);
        if (!$stmt_delete->execute()) {
            throw new Exception("購物車商品移除失敗: " . $stmt_delete->error);
        }
    }

    // 關閉所有 Statement
    $stmt_item->close();
    $stmt_stock->close();
    $stmt_delete->close();

    // ─── F. 更新 Session 購物車導覽列數字 ───
    $stmt_count = $connect->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
    $stmt_count->bind_param("i", $cart_id);
    $stmt_count->execute();
    $stmt_count->bind_result($new_cart_count);
    $stmt_count->fetch();
    $stmt_count->close();

    $_SESSION['cart_count'] = $new_cart_count;
    $_SESSION['buy_success'] = [
        'order_num' => $order_num,
        'final_amount' => $final_amount
    ];

    // ======================================
    // 7. 提交所有交易 (確認無誤，真正寫入雲端 RDS)
    // ======================================
    $connect->commit();

    header("Location: index.php?route=buy_success");
    exit;

} catch (Exception $e) {
    // 如果中途有任何一間商品庫存扣失敗、或任何一個步驟出錯，全部退回原始狀態
    $connect->rollback();
    die("訂單交易失敗，原因：" . $e->getMessage());
}
?>
