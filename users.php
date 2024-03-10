<?php
require_once 'database.php';

// http://localhost/php/Assignment2/users.php

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests for users
if ($request_method == 'GET') {
    // Fetch all users
    $stmt = $db->prepare('SELECT * FROM users');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($users) {
        response(200, 'Users fetched', $users);
    } else {
        response(400, 'No users found');
    }
} elseif ($request_method == 'POST') {
    // Handle POST requests for users
    $name = $request_data['name'];
    $email = $request_data['email'];
    $password = $request_data['password'];

    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the created user
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindParam(':id', $db->lastInsertId(), PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        response(201, 'User created', $user);
    } else {
        response(400, 'User creation failed');
    }
} elseif ($request_method == 'PUT') {
    // Handle PUT requests for users
    $id = isset($request_data['id']) ? $request_data['id'] : null;

    if ($id !== null) {
        $name = $request_data['name'];
        $email = $request_data['email'];
        $password = $request_data['password'];

        // Update the user in the database
        $stmt = $db->prepare('UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the updated user
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            response(200, 'User updated', $user);
        } else {
            response(400, 'User update failed');
        }
    } else {
        response(400, 'Invalid user ID');
    }
} elseif ($request_method == 'DELETE') {
    // Handle DELETE requests for users
    $id = isset($request_data['id']) ? $request_data['id'] : null;

    if ($id !== null) {
        // Delete the user from the database
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            response(200, 'User deleted');
        } else {
            response(400, 'User deletion failed');
        }
    } else {
        response(400, 'Invalid user ID');
    }
}