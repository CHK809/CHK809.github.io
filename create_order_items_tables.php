<?php

    $db_host = "放自己的rds endpoint";
    $db_username = "admin";
    $db_password = "12345678";

    $connect = new mysqli($db_host, $db_username, $db_password);

    echo "<br><br><div style='border:2px solid green; width:50%; margin:auto; padding:20px;'>";

	if ($connect->connect_errno) {
		die("RDS shopsql 伺服器連線失敗：" . $connect->connect_error);
	} else {
       echo "<p style='text-align:center;color:green;'>連線到 RDS shopsql 伺服器 成功</p>";
    }

	// 使用 資料庫 shopsql 
    if (!$connect->select_db("shopsql")) {
        die("選擇資料庫 shopsql 失敗：" . $connect->error);
    }
	$connect->set_charset("utf8mb4"); // 確保資料庫正確處理中文或特殊符號

    // ===== 建立 訂單主表 orders =====
    $sql_create = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_num VARCHAR(30) NOT NULL UNIQUE,
            member_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            final_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            payment_method VARCHAR(50),
            payment_status VARCHAR(30) DEFAULT '未付款',
            order_status VARCHAR(30) DEFAULT '待出貨',
            receiver_name VARCHAR(50),
            receiver_phone VARCHAR(20),
            receiver_address VARCHAR(255),
            note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
        );
    ";

    if (!$connect->query($sql_create)) {
		die("建立 訂單主表 orders 錯誤：" . $connect->error);
	} else {
       echo "<center><font color='blue'>訂單主表 orders 建立成功</font></center><br>";
    }

    // ===== 建立 訂單明細 order_items =====
    $sql_create = "
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(100),
            product_image VARCHAR(255),
            price DECIMAL(10,2) NOT NULL,
            qty INT NOT NULL DEFAULT 1,
            subtotal DECIMAL(10,2) NOT NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
        );
    ";

    if (!$connect->query($sql_create)) {
        die("建立 order_items 錯誤：" . $connect->error);
    } else {
       echo "<center><font color='green'>訂單明細 order_items 建立成功</font></center><br>";
    }

