# DFrame View - Documentation

## 1. Giới thiệu
DFrame View là một phần của DFrame, cung cấp khả năng hiển thị dữ liệu một cách linh hoạt và mạnh mẽ. Với DFrame View, bạn có thể dễ dàng tạo ra các giao diện người dùng tương tác và trực quan cho ứng dụng của mình.

Thành phần chính:
- **View**: Lớp chính hỗ trợ render view, cho phép bạn truyền dữ liệu từ controller đến giao diện người dùng một cách dễ dàng.

Các drivers hỗ trợ:

Nằm trong thư mục `src/Application/View/Driver/`, bao gồm:
- `Blade.php`: Hỗ trợ template engine Blade.
- `Twig.php`: Hỗ trợ template engine Twig.
Hoặc bạn có thể tự tạo driver riêng cho mình bằng cách kế thừa từ lớp `DFrame\Application\View\Driver\BaseDriver`.

## 2. Các files/class liên quan
- `src/Application/View.php` – Lớp chính hỗ trợ render view.

## 3. Sử dụng DFrame View(php thuần nếu không sử dụng template engine)

### Cách sử dụng cơ bản
```php
use DFrame\Application\View;
$view = new View('path/to/view/file.php', ['key' => 'value']);
echo $view->render();
```

## 4. Sử dụng với template engine
**Lưu ý**: Đảm bảo bạn đã cài đặt các package cần thiết cho template engine bạn muốn sử dụng (Blade hoặc Twig).
Demo sử dụng với BladeOne:

- Cài đặt package BladeOne:
```bash
composer require eftec/bladeone
```

- Bật hỗ trợ View engine trong .env:
```
SUPPORT_VIEW_ENGINE=enabled
VIEW_ENGINE=bladeone
```

- Cấu hình trong `config/view.php`:
```php
<?php
return [
    'view_path' => ROOT_DIR . 'resource/view/',
    'engine' => 'bladeone',
    'drives' => [
        // BladeOne view drive configuration
        'bladeone' => [
            'class' => \DFrame\Application\Drive\View\BladeOneDrive::class,
            'options' => [
                'compiled_path' => INDEX_DIR . 'cache/view/compiled/',
                'cache' => true,
                'debug' => true,
            ],
        ],
        // You can add more view drives here
    ]
];
```
Cấu hình bao gồm đường dẫn view, engine sử dụng và các tùy chọn cho driver.

-Sử dụng trong controller:
```php
use DFrame\Application\View;

class DemoController {
    public function show() {
        $view = new View('demo/show', ['name' => 'DFrame']); // 'demo/show' là đường dẫn tới file view (ví dụ: resource/view/demo/show.blade.php)
        return $view->render();
    }
}
```

- Đăng ký route để sử dụng controller:
```php
use DFrame\Routing\Route;

Route::get('/demo/show', [DemoController::class, 'show'])->name('demo.show');
```

Xem kết quả bằng cách truy cập vào đường dẫn `/demo/show` trên trình duyệt của bạn. Bạn sẽ thấy nội dung được render từ file view với dữ liệu truyền từ controller.