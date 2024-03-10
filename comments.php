<?php

require_once 'database.php';

// http://localhost/php/Assignment2/comments.php


// Validation Code commented for now was geeting error so 


// function validate_input($data, $required_fields) {
//     foreach ($required_fields as $field) {
//         if (!isset($data[$field])) {
//             response(400, 'Invalid input: Missing ' . $field);
//             exit;
//         }
//     }
// }

// function validate_rating($rating) {
//     if ($rating < 1 || $rating > 5) {
//         response(400, 'Invalid input: Rating must be between 1 and 5');
//         exit;
//     }
// }

// function validate_image($image) {
//     if (!preg_match('/^data:image\/[a-zA-Z0-9]+;base64,[a-zA-Z0-9+/=]+$/', $image)) {
//         response(400, 'Invalid input: Image must be a base64-encoded image');
//         exit;
//     }
// }

// function validate_text($text) {
//     if (strlen($text) < 1 || strlen($text) > 1000) {
//         response(400, 'Invalid input: Text must be between 1 and 1000 characters');
//         exit;
//     }
// }

$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'POST') {
    if (isset($request_data['endpoint']) && $request_data['endpoint'] == 'comments') {
        $required_fields = ['product_id', 'user_id', 'rating', 'image', 'text'];
        validate_input($request_data, $required_fields);

        $product_id = $request_data['product_id'];
        $user_id = $request_data['user_id'];
        $rating = $request_data['rating'];
        $image = $request_data['image'];
        $text = $request_data['text'];

        validate_rating($rating);
        validate_image($image);
        validate_text($text);

        $stmt = $db->prepare('INSERT INTO comments (product_id, user_id, rating, image, text) VALUES (:product_id, :user_id, :rating, :image, :text)');
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':text', $text, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the created comment
        $stmt = $db->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->bindParam(':id', $db->lastInsertId(), PDO::PARAM_INT);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment) {
            response(200, 'Comment created', $comment);
        } else {
            response(400, 'Comment creation failed');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} elseif ($request_method == 'PUT') {
    if (isset($request_data['id'])) {
        $required_fields = ['id', 'rating', 'image', 'text'];
        validate_input($request_data, $required_fields);

        $id = $request_data['id'];
        $rating = $request_data['rating'];
        $image = $request_data['image'];
        $text = $request_data['text'];

        validate_rating($rating);
        validate_image($image);
        validate_text($text);

        $stmt = $db->prepare('UPDATE comments SET rating = :rating, image = :image, text =:text WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':text', $text, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the updated comment
        $stmt = $db->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment) {
            response(200, 'Comment updated', $comment);
        } else {
            response(400, 'Comment update failed');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} elseif ($request_method == 'DELETE') {
    if (isset($request_data['id'])) {
        $required_fields = ['id'];
        validate_input($request_data, $required_fields);

        $id = $request_data['id'];

        $stmt = $db->prepare('DELETE FROM comments WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            response(200, 'Comment deleted');
        } else {
            response(400, 'Comment deletion failed');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} else {
    response(400, 'Invalid request method');
}