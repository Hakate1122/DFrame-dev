<?php
$amount = $_GET['amount'] ?? 0;
if (isset($_GET['success'])) {
    echo "<script>
        localStorage.removeItem('foodCart');
        const order = JSON.parse(localStorage.getItem('pendingOrder'));
        fetch('order_api.php', {
            method: 'POST',
            body: new URLSearchParams(order)
        }).then(()=> {
            localStorage.removeItem('pendingOrder');
            location.href = '#orderDone';
            setTimeout(()=>document.getElementById('orderStatus').innerHTML='<h3>Thanh toán VNPAY thành công! Đơn hàng đang được xử lý...</h3>',1000);
        });
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>VNPAY Demo</title>
</head>
<body style="font-family:sans-serif;text-align:center;margin-top:60px;">
  <h2>Thanh toán VNPAY Demo</h2>
  <p>Số tiền: <b><?=number_format($amount)?> ₫</b></p>
  <a href="?success=1&amount=<?=$amount?>"
     style="background:#28a745;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;">Xác nhận thanh toán</a>
</body>
</html>
