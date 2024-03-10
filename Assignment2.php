<?php


// http://localhost/Assignment2.php?endpoint=users

// http://localhost/Assignment2.php?endpoint=products


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'assignment2db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Function for responding with JSON
function response($status, $message, $data = []) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode(['message' => $message, 'data' => $data]);
    exit;
}

// Function for logging
function logMessage($message) {
    // Modify this function to log the message to a file or other logging mechanism
    echo $message . "\n";
}

// Connect to the database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    logMessage('Database connection successful');
} catch (PDOException $e) {
    response(500, 'Database connection failed: ' . $e->getMessage());
    exit();
}

// Get the request method and data
$request_method = $_SERVER["REQUEST_METHOD"];
$request_data = json_decode(file_get_contents("php://input"), true);
$endpoint = null; // Define $endpoint here

if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;
}

// Handle the request based on the method and endpoint
if ($request_method == 'GET') {
    if ($endpoint == 'products') {
        // Handle the products endpoint
        if ($id !== null) {
            // Get a specific product by ID
            $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                response(200, 'Product found', $product);
            } else {
                response(404, 'Product not found');
            }
        } else {
            // Get all products
            $stmt = $db->query('SELECT * FROM products');
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            response(200, 'Products retrieved', $products);
        }

      }  elseif ($endpoint == 'users') {
            // Handle the users endpoint
            if ($request_method == 'GET') {
                // Retrieve all users
                $stmt = $db->prepare('SELECT * FROM users');
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                if ($users) {
                    foreach ($users as &$user) {
                        unset($user['password']); // Remove password from each user in the response
                    }
                    response(200, 'Users retrieved', $users);
                } else {
                    response(404, 'No users found');
                }
            } else {
                response(405, 'Method not allowed');
            }
        } else {
            response(400, 'Invalid endpoint');
        }

        // This code is get user by ID  Commented for now 

    //     } elseif ($endpoint == 'users') {
    //     // Handle the users endpoint
    //     if ($request_method == 'GET') {
    //         if ($id !== null) {
    //             // Retrieve a specific user by ID
    //             $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    //             $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    //             $stmt->execute();
    //             $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //             if ($user) {
    //                 // Verify the password
    //                 if (password_verify($request_data['password'], $user['password'])) {
    //                     unset($user['password']); // Remove password from response
    //                     response(200, 'User retrieved', $user);
    //                 } else {
    //                     response(401, 'Invalid password');
    //                 }
    //             } else {
    //                 response(404, 'User not found');
    //             }
    //         } else {
    //             response(400, 'Missing required fields');
    //         }
    //     } else {
    //         response(405, 'Method not allowed');
    //     }
    // } else {
    //     response(400, 'Invalid endpoint');
    // }
} elseif ($request_method == 'POST') {
    if (isset($request_data['endpoint'])) {
        $endpoint = $request_data['endpoint'];
        if ($endpoint == 'products') {
    // Handle the products endpoint
    $description = $request_data['description'];
    $image = $request_data['image'];
    $pricing = $request_data['pricing'];
    $shipping_cost = $request_data['shipping_cost'];

    // Insert the new product into the database
    $stmt = $db->prepare('INSERT INTO products (description, image, pricing, shipping_cost) VALUES (:description, :image, :pricing, :shipping_cost)');
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':image', $image, PDO::PARAM_STR);
    $stmt->bindParam(':pricing', $pricing, PDO::PARAM_INT);
    $stmt->bindParam(':shipping_cost', $shipping_cost, PDO::PARAM_INT);
    $stmt->execute();

    $product_id = $db->lastInsertId();

    // Fetch the newly created product
    $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        response(201, 'Product created', $product);
    } else {
        response(500, 'Failed to create product');
    }}}
 elseif ($endpoint == 'users') {
    
    // Handle the users endpoint
    if ($request_method == 'POST') {
        // Validate input
        if (!isset($request_data['email']) || !isset($request_data['password']) || !isset($request_data['username'])) {
            response(400, 'Missing required fields');
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($request_data['password'], PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $db->prepare('INSERT INTO users (email, password, username) VALUES (:email, :password, :username)');
        $stmt->bindParam(':email', $request_data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':username', $request_data['username'], PDO::PARAM_STR);
        $stmt->execute();

        $user_id = $db->lastInsertId();

        // Fetch the newly created user
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            response(201, 'User created', $user);
        } else {
            response(500, 'Failed to create user');
        }
    } else {
        response(405, 'Method not allowed');
    }
} else {
    response(400, 'Invalid endpoint');
}
} elseif ($request_method == 'PUT') {
    if (isset($request_data['endpoint'])) {
        $endpoint = $request_data['endpoint'];
        $id = isset($request_data['id']) ? $request_data['id'] : null;
        if ($endpoint == 'products') {
           // Handle the products endpoint
           if ($id !== null) {
            // Update an existing product
            $description = $request_data['description'];
            $image = $request_data['image'];
            $pricing = $request_data['pricing'];
            $shipping_cost = $request_data['shipping_cost'];

            // Update the product in the database
            $stmt = $db->prepare('UPDATE products SET description = :description, image = :image, pricing = :pricing, shipping_cost = :shipping_cost WHERE id = :id');
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
            $stmt->bindParam(':pricing', $pricing, PDO::PARAM_INT);
            $stmt->bindParam(':shipping_cost', $shipping_cost, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the updated product
            $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                response(200, 'Product updated', $product);
            } else {
                response(404, 'Product not found');
            }

            } else {
                response(400, 'Invalid product ID');
            }


        } elseif ($endpoint == 'users') {
            // Handle the users endpoint
             if ($id !== null) {
                // Update an existing user
                $name = $request_data['name'];
                $email = $request_data['email'];

                // Update the user in the database
                $stmt = $db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
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
                    response(404, 'User not found');
                }
            } else {
                response(400, 'Invalid user ID');
            }

        } else {
            response(400, 'Invalid endpoint');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} elseif ($request_method == 'DELETE') {
    if (isset($request_data['endpoint'])) {
        $endpoint = $request_data['endpoint'];
        $id = isset($request_data['id']) ? $request_data['id'] : null;
        if ($endpoint == 'products') {
      // Handle the products endpoint
       if ($id !== null) {
    // Delete the product from the database
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

        } elseif ($endpoint == 'users') {

 // Handle the users endpoint
if ($id !== null) {
    // Delete the user from the database
    $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        response(200, 'User deleted');
    } else {
        response(404, 'User not found');
    }
} else {
    response(400, 'Invalid user ID');
}        } else {
            response(400, 'Invalid endpoint');
        }
    } else {
        response(400, 'Invalid endpoint');
    }
} else {
    response(405, 'Method not allowed');
}

// Close the database connection
$db = null;
?>