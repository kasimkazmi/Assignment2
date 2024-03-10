<?php
require_once 'database.php';

// http://localhost/php/Assignment2/orders.php

$endpoint = '/orders';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    getAllOrders();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    createOrder();
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    updateOrder();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    deleteOrder();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}

function getAllOrders()
{
    global $db;

    try {
        $stmt = $db->prepare('SELECT * FROM orders');
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        response(200, 'Success', $orders);
    } catch (PDOException $e) {
        response(500, 'Failed to retrieve orders: ' . $e->getMessage());
    }
}

function createOrder()
{
    global $db;

    $request_data = json_decode(file_get_contents('php://input'), true);

    $customerName = $request_data['customer_name'];
    $orderTotal = $request_data['order_total'];

    try {
        $stmt = $db->prepare('INSERT INTO orders (customer_name, order_total) VALUES (?, ?)');
        $stmt->execute([$customerName, $orderTotal]);
        $orderId = $db->lastInsertId();
        response(201, 'Order created successfully', ['order_id' => $orderId]);
    } catch (PDOException $e) {
        response(500, 'Failed to create order: ' . $e->getMessage());
    }
}

function updateOrder()
{
    global $db;

    $request_data = json_decode(file_get_contents('php://input'), true);
    $orderId = $request_data['order_id'];

    try {
        $stmt = $db->prepare('UPDATE orders SET ... WHERE order_id = ?');
        $stmt->execute([$orderId]);
        // Additional SQL update statements go here...
        response(200, 'Order updated successfully');
    } catch (PDOException $e) {
        response(500, 'Failed to update order: ' . $e->getMessage());
    }
}

function deleteOrder()
{
    global $db;

    $request_data = json_decode(file_get_contents('php://input'), true);
    $orderId = $request_data['order_id'];

    try {
        $stmt = $db->prepare('DELETE FROM orders WHERE order_id = ?');
        $stmt->execute([$orderId]);
        response(200, 'Order deleted successfully');
    } catch (PDOException $e) {
        response(500, 'Failed to delete order: ' . $e->getMessage());
    }
}
?>