<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'connect.php';

    $member_id = $_SESSION['member_id'] ?? null;

    $order_id = $_GET['id'] ?? 0;

    if (!$member_id) {
        die("請先登入");
    }


    // ======================================
    // 查詢 orders
    // ======================================

    $stmt = $connect->prepare("
        SELECT * FROM orders
        WHERE id = ?
        AND member_id = ?
    ");

    $stmt->bind_param("ii", $order_id, $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    $stmt->close();

    if (!$order) {
        die("查無交易訂單");
    }


    // ======================================
    // 查詢 order_items
    // ======================================

    $stmt = $connect->prepare("
        SELECT * FROM order_items
        WHERE order_id = ?
    ");

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];

    while($row = $result->fetch_assoc()){
        $items[] = $row;
    }

    $stmt->close();

?>

<style>
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
    .abc strong{
        font-size:22px;
        color:#fff;
    }
    table{
        width:80%;
        margin:auto;
        border-collapse: collapse;
    }
    th,td{
        border:2px solid #14d334;
        padding:10px;
        text-align:center;
        background-color: #ffcccc;
    }
    .btn03 {
        background-color: #f59595;
        border: 2px solid #f59595;
    }
    .money{
        color:red;
        font-weight:bold;
    }
</style>

    <div class="abc" style="width:350px; margin:auto; margin-top:30px;">
        <strong>訂單明細</strong>
    </div><br><br>

    <center>
    <div class="text-center" style="width:50%;">
        <h4>
            訂單編號： <?= $order['order_num'] ?> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="money">
                付款金額：$<?= number_format($order['final_amount']) ?>
            </span>
        </h4>
        <hr style="width:100%; height:5px; background:green; border:none;"><br>
        <p style="text-align:left;">
            建立時間： <?= $order['created_at'] ?><br>
            付款方式： <?= $order['payment_status'] ?> <br>
            訂單狀態：<?= $order['order_status'] ?>
        </p>
    </div>
    </center>
    <table style="width:50%;">
        <tr>
            <th>商品名稱</th>
            <th>圖片</th>
            <th>單價</th>
            <th>數量</th>
            <th>小計</th>
        </tr>

        <?php foreach($items as $item): ?>
        <tr>
            <td>
                <?= $item['product_name'] ?>
            </td>
            <td>
                <img src="<?= $item['product_image'] ?>" width="100">
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

    <div class='d-flex gap-2 justify-content-center'>
        <a href='index.php?route=main' class='btn btn-success w-25'>返回首頁</a>
        <a href="index.php?route=order" class="btn btn-warning w-25">返回訂單列表</a>
    </div>
