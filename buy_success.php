<?php

    if (!isset($_SESSION['buy_success'])) {
        header("Location:index.php");
        exit;
    }

    $data = $_SESSION['buy_success'];
    $order_num = $data['order_num'];
    $final_amount = $data['final_amount'];

    // 用完即刪
    unset($_SESSION['buy_success']);
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

    .btn01{
        background:#4284f5;
        color:#fff;
        border:none;
        padding:12px 20px;
        text-decoration:none;
        border-radius:5px;
    }

    .money{
        color:red;
        font-weight:bold;
    }

</style>

<div class="abc" style="width:350px; margin:auto; margin-top:30px;">
    <strong>訂單交易成功</strong>
</div>

<br>

<div style="border:4px solid blue; width:400px; margin:auto; padding:20px;">

    <div style="font-size:22px;">
        訂單編號：
        <span class="money"><?= $order_num ?></span>
    </div>

    <br>

    <div style="font-size:22px;">
        付款金額：
        <span class="money">
            $<?= number_format($final_amount) ?>
        </span>
    </div>

    <br><br>

    <a href="index.php?route=order" class="btn01">
        查看我的訂單
    </a>

</div>

<br><br>