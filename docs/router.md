# DFrame Router & Route - Documentation

## 1. Giới thiệu

Router trong DFrame là lớp quản lý các route (đường dẫn) cho ứng dụng web hoặc API. Nó hỗ trợ đăng ký route thông qua code hoặc sử dụng attribute (tag) trực tiếp trên controller, giúp việc quản lý và bảo trì route trở nên dễ dàng hơn.

Hai thành phần chính:
- **Router**: Lớp quản lý đăng ký, quét, xử lý các route cho ứng dụng web/API. Hỗ trợ đăng ký qua code hoặc qua attribute (tag) trên controller.
- **Route**: Attribute (tag) kế thừa từ Router, dùng để khai báo route trực tiếp trên controller method, giúp code ngắn gọn, dễ bảo trì.

---

## 2. Các files/class liên quan

- `src/Application/Router.php` – Lớp chính quản lý route, đăng ký, quét, xử lý request.
- `src/Application/Route.php` – Attribute kế thừa Router, dùng để khai báo route trên controller.
- `app/Controller/DemoController.php` – Ví dụ controller sử dụng Router/Route attribute.
- `app/Router/web.php` – Đăng ký route web, quét attribute, khởi động router.
- `app/Router/api.php` – Đăng ký route API qua code.
- `src/Application/View.php` – Hỗ trợ render view cho response.

---

## 3. Khởi tạo và sử dụng

### Đăng ký route qua code

```php
use DFrame\Application\Router;

$router = new Router();
$router->get('/', fn() => 'Home')->name('home');
$router->post('/login', 'App\Controller\AuthController@login')->name('login');
$router->default(fn() => '404 Not Found');
```

### Đăng ký route qua attribute (tag) trên controller

```php
use DFrame\Application\Router;
use DFrame\Application\Route;

class DemoController {
    #[Router(path: '/haha', method: 'GET', isApi: false, name: 'demo.haha')]
    public function demo() { return "oke"; }

    #[Route(path: '/captcha', method: 'GET', name: 'show.captcha')]
    public function captcha() { ... }

    #[Route(path: '/api/verify-captcha', method: 'POST', isApi: true, name: 'verify.captcha')]
    public function verifyCaptcha() { ... }
}
```

### Quét và khởi động router

```php
// app/Router/web.php
$router = new DFrame\Application\Router();
$router->scanControllerAttributes([
    '\App\Controller\DemoController',
    '\App\Controller\AnotherController',
]);
$router->runInstance();
```

---

## 4. Cách hỗ trợ khác

- **Middleware**: Đăng ký qua attribute `middleware: ['Auth']` hoặc qua code `$router->addMiddleware($mw);`
- **Đặt tên route**: Dùng `->name('route.name')` hoặc attribute `name: 'route.name'`
- **Sinh URL**: `Router::route('route.name', ['param1'])`
- **API Route**: Đăng ký qua code `$router->apiGet('/api/xxx', ...)` hoặc attribute `isApi: true`
- **Handler mặc định (404)**: `$router->default(fn() => '404 Not Found');`
- **Render view**: Dùng `View::render('viewname', ['data' => $value])`

---

**Tham khảo thêm:**  
- [DemoController.php](../app/Controller/DemoController.php)
- [Router.php](../src/Application/Router.php)
- [Route.php](../src/Application/Route.php)