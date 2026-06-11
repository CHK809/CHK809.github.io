<?php

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
    // 1. 取得 cart_id
    // ======================================

    $stmt = $connect->prepare("
        SELECT id
        FROM carts
        WHERE member_id = ?
    ");

    $stmt->bind_param("i", $member_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $cart = $result->fetch_assoc();

    if (!$cart) {
        die("購物車不存在");
    }

    $cart_id = $cart['id'];

    $stmt->close();


    // ======================================
    // 2. 取得購物車商品, 我是 p.product_num
    // ======================================

    $stmt = $connect->prepare("
        SELECT
            ci.product_id,
            ci.qty,
            ci.price,
            p.name,
            p.image,
            p.product_id, 
            p.qty AS stock_qty

        FROM cart_items ci

        JOIN products p
        ON ci.product_id = p.id

        WHERE ci.cart_id = ?
    ");

    $stmt->bind_param("i", $cart_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $items = [];

    $total_amount = 0;

    while ($row = $result->fetch_assoc()) {

        // 庫存檢查
        if ($row['stock_qty'] < $row['qty']) {
            die("商品 {$row['name']} 庫存不足");
        }

        $row['subtotal'] = $row['price'] * $row['qty'];
        $total_amount += $row['subtotal'];
        $items[] = $row;
    }

    $stmt->close();

    if (empty($items)) {
        die("購物車是空的");
    }


    // ======================================
    // 3. 計算金額
    // ======================================

    $discount_amount = 200;

    $final_amount = $total_amount - $discount_amount;

    if ($final_amount < 0) {
        $final_amount = 0;
    }

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">

<title>交易確認</title>

<style>
    body{
        font-family:微軟正黑體;
    }
    .abc{
        height:60px;
        background-image: linear-gradient(
            to left,
            #d08adc,
            #ff82a2,
            #fd9968,
            #ceba53,
            #82d47d
        );

        display:flex;
        justify-content:center;
        align-items:center;
    }
    .abc img{
        height:40px;
        width:30px;
    }
    .abc strong{
        display:flex;
        align-items:center;
        gap:8px;
        font-size:20px;
    }
    table{
        border-collapse: collapse;
    }
    th, td{
        text-align:center;
        vertical-align:middle;
        border:1px solid #999;
        padding:10px;
    }
    .money{
        color:red;
        font-weight:bold;
    }
    .confirmed{
        transform:scale(2);
    }
    .btn{
        padding:10px 20px;
        border:none;
        cursor:pointer;
        color:#fff;
        font-size:16px;
    }
    .btn-success{
        background:green;
    }
    .btn-back{
        background:#360277;
    }
</style>

</head>

<body>

<div class="text-center">

    <div class="abc" style="width:300px; margin:auto; margin-top:20px;">
        <strong><img src="images/main/logo.png">結帳確認</strong>
    </div>
    <br>

    <!-- 顯示訂單 -->
    <form method="post" action="index.php?route=buy">

        <!-- 訂單主表 -->
        <table width="40%" align="center">
            <tr>
                <th>訂單金額</th>
                <td class="money" id="total_amount">
                    $<?= number_format($total_amount) ?>
                </td>
            </tr>
            <tr>
                <th>折扣</th>
                <td class="money">
                    -$<?= number_format($discount_amount) ?>
                </td>
            </tr>
            <tr>
                <th>付款金額</th>
                <td class="money" id="final_amount">
                    $<?= number_format($final_amount) ?>
                </td>
            </tr>
        </table>

        <br><br>

        <!-- 訂單明細表 -->
        <div class="abc" style="width:600px; margin:auto;">
            <strong>訂單明細</strong>
        </div>
        <br>

        <table width="80%" align="center">
            <tr>
                <th>確認</th>
                <th>商品/商品編號</th>
                <th>圖片</th>
                <th>單價</th>
                <th>數量</th>
                <th>小計</th>
            </tr>

            <?php foreach($items as $item): ?>
            <tr>
                <td>
                    <!-- checkbox -->
                    <input type="checkbox" class="confirmed" name="selected_items[]" value="<?= $item['product_id'] ?>" 
                    data-subtotal="<?= $item['subtotal'] ?>" checked>
                </td>

                <td>
                    <?= $item['name'] ?><br><?= $item['product_id'] ?>
                </td>

                <td>
                    <img src="<?= $item['image'] ?>" width="100">
                </td>

                <td>
                    $<?= number_format($item['price']) ?>
                </td>

                <td>
                    <?= $item['qty'] ?>
                </td>

                <td class="money">
                    $<?= number_format($item['subtotal']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br><br>

        <!-- 選擇付款方式 & 結帳 -->
        <div style="border:4px solid blue; width:400px; margin:auto; padding:20px;">
            <div style="text-align:left; margin-bottom:15px;">
                <label>
                    <input type="radio" name="payment_method" value="信用卡付款" checked>
                    信用卡付款
                </label>
            </div>
            <div style="text-align:left; margin-bottom:15px;">
                <label>
                    <input type="radio" name="payment_method" value="貨到付款">
                    貨到付款
                </label>
            </div>
            <div style="text-align:left; margin-bottom:20px;">
                <label>
                    <input type="radio" name="payment_method" value="Line Pay">
                    Line Pay
                </label>
            </div>
            <div style="display:flex; justify-content:center; gap:20px;">
                <a href="javascript:history.back()" class="btn btn-back" style="text-decoration:none;">
                    回購物車
                </a>
                <input type="submit" value="確定結帳" class="btn btn-success">
            </div>
        </div>
    </form>
</div>
<br><br>

<script>

// ================================
// 即時更新金額
// ================================

const checkboxes = document.querySelectorAll('.confirmed');
const totalAmountEl = document.getElementById('total_amount');
const finalAmountEl = document.getElementById('final_amount');
const discount = <?= $discount_amount ?>;

function updateAmount(){

    let total = 0;
    checkboxes.forEach(cb => {

        if(cb.checked){
            total += Number(cb.dataset.subtotal);
        }

    });

    let final = total - discount;
    if(final < 0){
        final = 0;
    }

    totalAmountEl.innerHTML =
        '$' + total.toLocaleString();

    finalAmountEl.innerHTML =
        '$' + final.toLocaleString();
}


// checkbox change
checkboxes.forEach(cb => {
    cb.addEventListener('change', updateAmount);

});

</script>

</body>
</html>
