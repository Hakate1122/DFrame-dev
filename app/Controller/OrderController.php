<?php
namespace App\Controller;

class OrderController extends Controller{

    public function getOrders() {
        return ["status" => "success"];
        // Logic to retrieve orders
    }

    public function createOrder() {
        return ["status" => "success"];
        // Logic to create a new order
    }
}
