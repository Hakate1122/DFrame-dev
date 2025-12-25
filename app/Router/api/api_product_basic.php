<?php

$router = new DFrame\Application\Router();

$router->signApi('GET /products', function(){
    $products = new App\Model\Products();
    $allProducts = $products->fetchAll();
    return ([
        'ok' => true,
        'products' => $allProducts
    ]);
});

$router->signApi('GET /products/{id}', function($id){
    $products = new App\Model\Products();
    $product = $products->where('id', $id)->first();
    if(!$product){
        http_response_code(404);
        return ([
            'ok' => false,
            'message' => 'Product not found'
        ]);
    }
    return ([
        'ok' => true,
        'product' => $product
    ]);
});

$router->signApi('POST /products', function(){
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $data = (stripos($contentType, 'application/json') !== false) 
    ? json_decode(file_get_contents('php://input'), true) 
    : $_POST;


    if (!is_array($data)) {
        return [
            'ok' => false,
            'message' => 'Invalid input data'
        ];
    }

    $products = new App\Model\Products();
    $newProductId = $products->insert($data)->execute();
    return [
        'ok' => true,
        'message' => 'Product created',
        'product_id' => $newProductId
    ];
});

$router->signApi('PUT /products/{id}', function($id){
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
        parse_str(file_get_contents('php://input'), $data);
    } else {
        $data = [];
    }

    // dd($data); // kiểm tra dữ liệu

    if (!is_array($data) || empty($data)) {
        return [
            'ok' => false,
            'message' => 'Invalid or empty input data'
        ];
    }

    // Loại bỏ trường id khỏi $data để tránh update id
    unset($data['id']);

    $products = new App\Model\Products();
    $existingProduct = $products->where('id', $id)->first();
    if(!$existingProduct){
        http_response_code(404);
        return [
            'ok' => false,
            'message' => 'Product not found'
        ];
    }
    
    $test = $products->where('id', $id)->update($data);
    $test->execute();
    return [
        'ok' => true,
        'message' => 'Product updated'
    ];
});

$router->signApi('DELETE /products/{id}', function($id){
    $products = new App\Model\Products();
    $existingProduct = $products->where('id', $id)->first();
    if(!$existingProduct){
        http_response_code(404);
        return [
            'ok' => false,
            'message' => 'Product not found'
        ];
    }
    $products->softDelete()->where('id', $id)->execute();
    return [
        'ok' => true,
        'message' => 'Product deleted'
    ];
});