<?php
include 'database.php';

// http://localhost/php/Assignment2/cart.php

// Check if the user_id is being passed correctly
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
} else {
    response(400, 'Invalid user_id');
    exit();
}

// Check if the user_id exists in the database
$stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // User_id exists in the database
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user_id is of the correct data type
    if (is_int($user_id)) {

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Retrieve user details
            response(200, $user);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            // Update user details
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("UPDATE users SET name = :name, email = :email WHERE id = :user_id");
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            response(200, 'User details updated successfully');
        } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            // Delete user
            $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            response(200, 'User deleted successfully');
        } else {
            response(400, 'Invalid request method');
        }

    } else {
        response(400, 'Invalid user_id');
        exit();
    }
} else {
    response(400, 'Invalid user_id');
    exit();
}
?>