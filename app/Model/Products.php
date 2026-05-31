<?php

declare(strict_types=1);

namespace App\Model;

use DLight\Application\DB;
use DLight\Database\Traits\SoftDelete;

class Products extends DB{
    use SoftDelete;
    protected $table = "products";
}
