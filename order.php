<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'connect.php';

    $member_id = $_SESSION['member_id'] ?? null;

    if (!$member_id) {
        die("請先登入");
    }


    // ======================================
    // 查詢 orders
    // ======================================

    $stmt = $connect->prepare("
        SELECT
            id, order_num, total_amount, discount_amount, final_amount, payment_method, order_status, created_at
        FROM orders
        WHERE member_id = ?
        ORDER BY id DESC
    ");

    if (!$stmt) {
        die("SQL prepare 錯誤：" . $connect->error);
    }

    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
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
    }
    .abc img{
        height:40px;
        width:30px;
    }
    .orders-wrapper{
        width:90%;
        margin:auto;
    }
    table{
        width:100%;
        border-collapse: collapse;
    }
    th,td{
        border:1px solid #999;
        padding:12px;
        text-align:center;
    }
    th{
        background:#333;
        color:white;
    }
    .money{
        color:red;
        font-weight:bold;
    }
    .status1{
        color:green;
        font-weight:bold;
    }
    .status2{
        color:blue;
        font-weight:bold;
    }
    .empty{
        text-align:center;
        margin-top:50px;
        font-size:22px;
    }
</style>

<div class="orders-wrapper">

    <center>
        <div class="alert abc w-25 mt-3 mx-auto">
            <strong>
                <img src="images/main/logo.png">&nbsp;&nbsp;我的訂單
            </strong>
        </div>
    </center>

    <?php if(empty($orders)): ?>

        <div class="empty">目前尚無交易訂單</div>

    <?php else: ?>

    <table>
        <tr>
            <th>訂單編號</th>
            <th>訂單日期</th>
            <th>訂單金額</th>
            <th>折扣</th>
            <th>付款金額</th>
            <th>付款方式</th>
            <th>訂單狀態</th>
            <th>查看明細</th>
        </tr>

        <?php foreach($orders as $order): ?>
        <tr>
            <td>
                <?= htmlspecialchars($order['order_num']) ?>
            </td>
            <td>
                <?= $order['created_at'] ?>
            </td>
            <td class="money">
                $<?= number_format($order['total_amount']) ?>
            </td>
            <td class="money">
                -$<?= number_format($order['discount_amount']) ?>
            </td>
            <td class="money">
                $<?= number_format($order['final_amount']) ?>
            </td>
            <td class="status1">
                <?= $order['payment_method'] ?>
            </td>
            <td class="status2">
                <?= $order['order_status'] ?>
            </td>
            <td>
                <a href="index.php?route=order_items&id=<?= $order['id'] ?>">
                    查看訂單明細
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

</div><br><br>

<?php
    $connect->close();
?>