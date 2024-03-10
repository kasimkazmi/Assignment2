<?php

require_once 'database.php';

// http://localhost/php/Assignment2/products.php


// Parse the request data
$request_method = $_SERVER['REQUEST_METHOD'];


if ($request_method == 'GET') {
    $id = isset($request_data['id']) ? $request_data['id'] : null;

    if ($id !== null) {
        // Fetch the product from the database
        $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            response(200, 'Product fetched', $product);
        } else {
            response(404, 'Product not found');
        }
    } else {
        // Fetch all products from the database
        $stmt = $db->query('SELECT * FROM products');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        response(200, 'Products fetched', $products);
    }
} elseif ($request_method == 'POST') {
    if (isset($request_data['endpoint'])) {
        $endpoint = $request_data['endpoint'];

        if ($endpoint == 'products') {
            // Create a new product in the database
            $name = $request_data['name'];
            $description = $request_data['description'];
            $price = $request_data['price'];
            $shipping_cost = $request_data['shipping_cost'];

            $stmt = $db->prepare('INSERT INTO products (name, description, price, shipping_cost) VALUES (:name, :description, :price, :shipping_cost)');
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_INT);
            $stmt->bindParam(':shipping_cost', $shipping_cost, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the newly created product
            $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
            $stmt->bindParam(':id', $db->lastInsertId(), PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                response(200, 'Product created', $product);
            } else {
                response(400, 'Product creation failed');
            }
        } else {
            response(400, 'Invalid endpoint');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} elseif ($request_method == 'PUT') {
    $id = isset($request_data['id']) ? $request_data['id'] : null;

    if ($id !== null) {
        // Update an existing product
        $name = $request_data['name'];
        $description = $request_data['description'];
        $price = $request_data['price'];
        $shipping_cost = $request_data['shipping_cost'];

        $stmt = $db->prepare('UPDATE products SET name = :name, description = :description, price = :price, shipping_cost = :shipping_cost WHERE id = :id');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_INT);
        $stmt->bindParam(':shipping_cost', $shipping_cost, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            response(200, 'Product updated');
        } else {
            response(404, 'Product not found');
        }
    } else {
        response(400, 'Invalid product ID');
    }
} elseif ($request_method == 'DELETE') {
    $id = isset($request_data['id']) ? $request_data['id'] : null;

    if ($id !== null) {
        // Delete a product from the database
        $stmt = $db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            response(200, 'Product deleted');
        } else {
            response(404, 'Product not found');
        }
    } else {
        response(400, 'Invalid product ID');
    }
} else {
    response(400, 'Invalid request method');
}