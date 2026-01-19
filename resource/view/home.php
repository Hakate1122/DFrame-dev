<?php
function formatBytes($bytes) {
    return round($bytes / 1024 / 1024, 2) . ' MB';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
</head>

<body>
    <h1>Chúc mừng! DFrame Framework đã khởi chạy thành công.</h1>

    <h3>Thông tin máy chủ</h3>

    <ul>
        <li>Phiên bản PHP: <?= $phpVersion ?></li>
        <li>Phiên bản DFrame: <?= $dframeVersion ?></li>
        <li>Hệ điều hành: <?= $os ?></li>
        <li>Máy chủ web: <?= $server ?></li>
        <li>Bộ nhớ sử dụng: <?= formatBytes($memory) ?> / <?= $memoryLimit ?></li>

    </ul>

    <p>
        Đây là trang chào mừng mặc định. Bạn có thể chỉnh sửa hoặc thay thế bằng cách sửa
        <code>resource/view/home.php</code>.
    </p>

    <p>Controller: <code>src/Controller/HomeController.php</code></p>
    <p>Route: <code>src/Route/web/web.php</code></p>

    <p>Vui lòng tham khảo tài liệu chính thức để biết thêm về DFrame.</p>
</body>

</html>