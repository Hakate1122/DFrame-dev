# CRUD bảng `users` với `pdo_mysql` theo style DFrame

Mục tiêu: làm CRUD nhanh theo đúng flow của DFrame: `Router -> Controller -> Model`.

## 1) Chuẩn bị DB `test`

```sql
CREATE DATABASE IF NOT EXISTS test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE test;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 2) Cấu hình DFrame dùng `pdo_mysql`

Sửa `config/database.php`:

```php
<?php

return [
    'db_driver' => 'pdo_mysql',
    'db_design' => 'builder', // hoặc mapper

    'db_name' => 'test',
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_user' => 'root',
    'db_pass' => '',
];
```

Và nhớ bật extension trong `php.ini`:

```ini
extension=pdo_mysql
```

## 3) Model `app/Model/Users.php`

```php
<?php
namespace App\Model;

use DFrame\Database\Traits\SoftDelete;

class Users extends Model
{
    use SoftDelete;

    protected $table = 'users';
    protected $selectable = ['id', 'name', 'email', 'created_at', 'updated_at'];
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
```

## 4) Controller CRUD `app/Controller/UserController.php`

```php
<?php

namespace App\Controller;

use App\Model\Users;
use DFrame\Application\DB;
use DFrame\Application\Validator;

class UserController extends Controller
{
    private Users $users;

    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    public function listUsers()
    {
        $users = Users::all();
        return json_encode(['data' => $users], JSON_UNESCAPED_UNICODE);
    }

    public function storeUser(Validator $validator)
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $validator->make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => 'required|string|max:100', 'email' => 'required|email|max:150', 'password' => 'required|string|min:6']
        );

        if ($validator->fails()) {
            http_response_code(422);
            return json_encode(['error' => $validator->first()], JSON_UNESCAPED_UNICODE);
        }

        $this->users->insert([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ])->execute();

        return json_encode(['message' => 'created'], JSON_UNESCAPED_UNICODE);
    }

    public function showUser($id)
    {
        $user = $this->users->where('id', $id)->first();
        return json_encode(['data' => $user], JSON_UNESCAPED_UNICODE);
    }

    public function updateUser(Validator $validator, $id)
    {
        parse_str(file_get_contents('php://input'), $payload);
        $name = $payload['name'] ?? '';
        $email = $payload['email'] ?? '';

        $validator->make(
            ['name' => $name, 'email' => $email],
            ['name' => 'required|string|max:100', 'email' => 'required|email|max:150']
        );

        if ($validator->fails()) {
            http_response_code(422);
            return json_encode(['error' => $validator->first()], JSON_UNESCAPED_UNICODE);
        }

        DB::table('users')->where('id', $id)->update([
            'name' => $name,
            'email' => $email,
        ])->execute();

        return json_encode(['message' => 'updated', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function deleteUser($id)
    {
        $this->users->where('id', $id)->delete()->execute();
        return json_encode(['message' => 'deleted', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }
}
```

## 5) Route CRUD `app/Router/web/user_basic_crud.php`

```php
<?php

use App\Controller\UserController;
use DFrame\Application\Router;

Router::group('/user')::action(function (Router $router) {
    $router->sign('GET /list', [UserController::class, 'listUsers'])->name('user.list');
    $router->sign('POST /store', [UserController::class, 'storeUser'])->name('user.store');
    $router->sign('GET /show/{id}', [UserController::class, 'showUser'])->name('user.show');
    $router->sign('PUT /edit/{id}', [UserController::class, 'updateUser'])->name('user.update');
    $router->sign('DELETE /delete/{id}', [UserController::class, 'deleteUser'])->name('user.delete');
});
```

File này đã được load trong `public/index.php` bằng:

```php
->setUpWebRoutes(ROOT_DIR . 'app/Router/web/user_basic_crud.php')
```

## 6) Test nhanh

Chạy app:

```bash
php dli -s
```

Test endpoint:

- `GET /user/list`
- `POST /user/store` (body: `name`, `email`, `password`)
- `GET /user/show/1`
- `PUT /user/edit/1` (body: `name`, `email`)
- `DELETE /user/delete/1`

## 7) Note ngắn

- Nếu form HTML thuần không gửi được `PUT/DELETE`, có thể tạm dùng `POST` cho update/delete như file hiện tại của project.
- Luôn hash password và validate input trước khi insert/update.
