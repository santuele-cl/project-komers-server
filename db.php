<?php

class MySite
{

    private $server = "mysql:host=localhost;dbname=komers";
    private $user = "root";
    private $password = "";
    private $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC); //Code for fetching data from database using PHP PDO
    private $con;

    //Function for opening database connection
    public function openConnection()
    {

        try {
            //establishing connection to the database server
            date_default_timezone_set("Asia/Manila");
            $this->con = new PDO($this->server, $this->user, $this->password, $this->options);
            return $this->con;
        } catch (PDOException $e) {

            //showing if there is not error/problem connecting to the database server
            echo "There is some problem in connection: " . $e->getMessage();
        }
    }

    //Function for closing database connection
    public function closeConnection()
    {

        $this->con = null;
    }

    /**
     * Logs in a user by verifying their credentials.
     *
     * @return void
     */

    public function setUserSession($user)
    {
        if (!isset($_SESSION)) {
            session_start();
        }


        $_SESSION['userdata'] = [
            'user_id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
    }

    public function getUserSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION["userdata"]) && !empty($_SESSION["userdata"])) {
            echo json_encode(array(
                "status" => 1,
                "message" => "Success",
                "data" => $_SESSION
            ));
        } else {
            echo json_encode(array(
                "status" => 0,
                "message" => "Failed",
            ));
        }
    }

    public function getSession()
    {


        // echo json_encode($_SESSION);
        return $_SESSION;
    }

    function logout()
    {
        // Start the session if it's not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Redirect the user to the login page or any other page
        header("Location: ../index.php");
        exit; // Ensure that no further code is executed after redirection
    }

    public function login($email, $password)
    {

        // Check if the login form has been submitted
        if (isset($email) && isset($password)) {
            // User input
            $hashedPassword = md5($password);

            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data based on email and password
            $sql = "SELECT * FROM `users` WHERE `email` = :email AND `password` = :password";

            $query = $connection->prepare($sql);

            $query->bindParam(':email', $email);
            $query->bindParam(':password', $hashedPassword);

            $query->execute();

            $user = $query->fetch(); // Fetching single data from the server and it will return an array

            $total = $query->rowCount();

            // // If user credentials are found
            if ($total > 0) {
                $this->setUserSession($user);

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Login Successful",
                    "data" => $_SESSION
                ));
            } else {

                echo json_encode(array(
                    "status" => 0,
                    "message" => "Login unsuccessful",
                ));
            }
        } else {

            echo json_encode(array(
                "status" => 0,
                "message" => "Missing credentials",
            ));
        }
    }

    public function signup($email, $password)
    {

        // Check if the login form has been submitted
        if (isset($email) && isset($password)) {
            // User input
            $userId = uniqid();
            $hashedPassword = md5($password);

            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data based on email and password
            $sql = "INSERT INTO users (id,email, password)
            VALUES (:id, :email, :password)";

            $query = $connection->prepare($sql);

            $query->bindParam(':id', $userId);
            $query->bindParam(':email', $email);
            $query->bindParam(':password', $hashedPassword);

            $query->execute();

            if ($query) {
                echo json_encode(array(
                    "status" => 1,
                    "message" => "Signup successful",
                ));
            } else {
                echo json_encode(array(
                    "status" => 0,
                    "message" => "Signup failed",
                ));
            }

            // $user = $query->fetch(); // Fetching single data from the server and it will return an array

            // $total = $query->rowCount();

            // // If user credentials are found
            // if ($total > 0) {
            //     $this->setUserSession($user);

            //     echo json_encode(array(
            //         "status" => 1,
            //         "message" => "Login Successful",
            //         "data" => $_SESSION
            //     ));
            // } else {

            //     echo json_encode(array(
            //         "status" => 0,
            //         "message" => "Login unsuccessful",
            //     ));
            // }
        } else {

            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function addUser($first_name, $middle_name, $last_name, $contact_num, $role, $house_number, $street, $barangay, $city, $province, $region, $country, $zipcode, $email, $password)
    {
        // Check if all required data is provided
        $isValidInput = isset($first_name, $last_name, $contact_num, $role, $house_number, $street, $barangay, $city, $province, $region, $country, $zipcode, $email, $password) &&
            !empty($first_name) && !empty($last_name) && !empty($contact_num) &&
            !empty($role) && !empty($house_number) && !empty($street) &&
            !empty($barangay) && !empty($city) && !empty($province) &&
            !empty($region) && !empty($country) && !empty($zipcode) &&
            !empty($email) && !empty($password);

        // Check if the input data is valid
        if ($isValidInput) {
            try {
                // Open a database connection
                $connection = $this->openConnection();

                // Check if email already exists
                $sqlCheckEmail = "SELECT COUNT(*) FROM users WHERE email = :email";
                $queryCheckEmail = $connection->prepare($sqlCheckEmail);
                $queryCheckEmail->bindParam(':email', $email);
                $queryCheckEmail->execute();
                $emailExists = $queryCheckEmail->fetchColumn();

                if ($emailExists) {
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Email already exists",
                    ));
                    return;
                }

                // Start a transaction to ensure atomicity
                $connection->beginTransaction();

                // Insert user data
                $userId = uniqid();
                $hashedPassword = md5($password);
                $addressId = uniqid();

                // Insert address data
                $sqlAddressInsert = "INSERT INTO addresses (id, house_number, street, barangay, city, province, region, country, zipcode) 
                     VALUES (:addressId, :houseNumber, :street, :barangay, :city, :province, :region, :country, :zipcode)";
                $queryAddressInsert = $connection->prepare($sqlAddressInsert);
                $queryAddressInsert->bindParam(':addressId', $addressId);
                $queryAddressInsert->bindParam(':houseNumber', $house_number);
                $queryAddressInsert->bindParam(':street', $street);
                $queryAddressInsert->bindParam(':barangay', $barangay);
                $queryAddressInsert->bindParam(':city', $city);
                $queryAddressInsert->bindParam(':province', $province);
                $queryAddressInsert->bindParam(':region', $region);
                $queryAddressInsert->bindParam(':country', $country);
                $queryAddressInsert->bindParam(':zipcode', $zipcode);
                $queryAddressInsert->execute();

                $sqlUserInsert = "INSERT INTO users (id, first_name, middle_name, last_name, contact_num, role, email, password, address_id) 
                              VALUES (:userId, :firstName, :middleName, :lastName, :contactNum, :role, :email, :password, :addressId)";
                $queryUserInsert = $connection->prepare($sqlUserInsert);
                $queryUserInsert->bindParam(':userId', $userId);
                $queryUserInsert->bindParam(':firstName', $first_name);
                $queryUserInsert->bindParam(':middleName', $middle_name);
                $queryUserInsert->bindParam(':lastName', $last_name);
                $queryUserInsert->bindParam(':contactNum', $contact_num);
                $queryUserInsert->bindParam(':role', $role);
                $queryUserInsert->bindParam(':email', $email);
                $queryUserInsert->bindParam(':password', $hashedPassword);
                $queryUserInsert->bindParam(':addressId', $addressId);
                $queryUserInsert->execute();

                $connection->commit();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "User added successfully"
                ));
            } catch (PDOException $e) {
                // Rollback the transaction and handle PDOException
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Rollback the transaction and handle other exceptions
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            // Handle missing data
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function getAllUsers()
    {
        try {
            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data
            $sql = "SELECT 
                        u.id, 
                        u.first_name, 
                        u.middle_name, 
                        u.last_name, 
                        u.address_id, 
                        u.email, 
                        u.contact_num, 
                        u.role, 
                        u.isDeactivated, 
                        u.createdAt, 
                        u.updatedAt, 
                        a.house_number, 
                        a.street, 
                        a.barangay, 
                        a.city, 
                        a.province, 
                        a.region, 
                        a.country, 
                        a.zipcode
                    FROM 
                        users u
                    LEFT JOIN 
                        addresses a ON u.address_id = a.id";


            $query = $connection->prepare($sql);
            $query->execute();

            $users = $query->fetchAll(); // Fetching data from the server and it will return an array

            echo json_encode(array(
                "status" => 1,
                "message" => "Users fetched successfully",
                "data" => $users
            ));
        } catch (PDOException $e) {
            echo json_encode(array(
                "status" => 0,
                "message" => "An error occurred: " . $e->getMessage(),
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                "status" => 0,
                "message" => "An unexpected error occurred: " . $e->getMessage(),
            ));
        }
    }

    public function addProduct($name, $price, $description, $stock, $brand, $image)
    {
        $isInputValid = isset($name) && !empty($name) && isset($price) && !empty($price) && isset($description) && !empty($description) && isset($stock) && !empty($stock) && isset($brand) && !empty($brand) && isset($image) && !empty($image);

        // Check if the login form has been submitted
        if ($isInputValid) {
            $productId = uniqid();
            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data based on email and password
            $sql = "INSERT INTO products (id, name, price, description, stock, brand)
                    VALUES (:id, :name, :price, :description, :stock, :brand)";

            $query = $connection->prepare($sql);
            $query->bindParam(':id', $productId);
            $query->bindParam(':name', $name);
            $query->bindParam(':price', $price);
            $query->bindParam(':description', $description);
            $query->bindParam(':stock', $stock);
            $query->bindParam(':brand', $brand);
            $query->execute();

            $lastInsertedId = $connection->lastInsertId();

            $targetDir = "images/products/"; // Change this to your desired upload directory
            $uploadOk = 1;

            // Loop through each uploaded file
            foreach ($image["tmp_name"] as $key => $tmp_name) {
                if ($_FILES["image"]["error"][$key] == 0) {
                    $file_name = $image["name"][$key];
                    $file_size = $image["size"][$key];
                    $file_tmp = $image["tmp_name"][$key];
                    $file_type = $image["type"][$key];

                    // Check if file is an actual image
                    $check = getimagesize($file_tmp);
                    if ($check === false) {
                        echo "File $file_name is not an image.<br>";
                        $uploadOk = 0;
                    }

                    // Check if file already exists
                    if (file_exists($targetDir . $file_name)) {
                        echo "File $file_name already exists.<br>";
                        $uploadOk = 0;
                    }

                    // Check file size
                    if ($file_size > 50000000) {
                        echo "File $file_name is too large.<br>";
                        $uploadOk = 0;
                    }

                    // Allow only certain file formats
                    $allowedExtensions = array("jpg", "jpeg", "png");
                    $fileExtension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (!in_array($fileExtension, $allowedExtensions)) {
                        echo "File $file_name is not allowed. Only JPG, JPEG, PNG, and GIF files are allowed.<br>";
                        $uploadOk = 0;
                    }

                    // If everything is ok, try to upload file
                    if ($uploadOk == 1) {

                        $newFileName =  uniqid() . '_' . basename($image["name"][$key]);
                        $targetFile = $targetDir . $newFileName;

                        if (move_uploaded_file($file_tmp, "../" . $targetFile)) {

                            $result = $this->uploadProductImage($targetFile, $productId);
                            if ($result) {
                                echo "File $file_name has been uploaded successfully.\n";
                            }
                        } else {
                            echo $file_tmp . "Error uploading file $file_name.<br>";
                        }
                    }
                } else {
                    echo "Error uploading file: " . $image["name"][$key] . "<br>";
                }
            }

            if ($query) {
                echo json_encode(array(
                    "status" => 1,
                    "message" => $lastInsertedId . $targetFile . "  " . $file_name . "  " . $newFileName . " " .  "Product added successfully",
                ));
            } else {
                echo json_encode(array(
                    "status" => 0,
                    "message" => "Error. Product post failed",
                ));
            }
        } else {

            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }
    public function updateProduct()
    {
        try {
            $requestPayload = file_get_contents('php://input');
            $decodedRequestPayload = json_decode($requestPayload, true);

            $isInputValid =  isset(
                $decodedRequestPayload["productId"],
                $decodedRequestPayload["name"],
                $decodedRequestPayload["price"],
                $decodedRequestPayload["description"],
                $decodedRequestPayload["stock"],
                $decodedRequestPayload["brand"],
            ) &&
                !empty($decodedRequestPayload["productId"]) && !empty($decodedRequestPayload["name"]) &&
                !empty($decodedRequestPayload["price"]) && !empty($decodedRequestPayload["description"]) &&
                !empty($decodedRequestPayload["stock"]) && !empty($decodedRequestPayload["brand"]);

            // Check if the input data is valid
            if ($isInputValid) {
                $productId = $decodedRequestPayload["productId"];
                $name = $decodedRequestPayload["name"];
                $price = $decodedRequestPayload["price"];
                $description = $decodedRequestPayload["description"];
                $stock = $decodedRequestPayload["stock"];
                $brand = $decodedRequestPayload["brand"];

                // Open a database connection
                $connection = $this->openConnection();

                // Start a transaction to ensure atomicity
                $connection->beginTransaction();

                // Prepare and execute the SQL query to update the product
                $sql = "UPDATE products
                    SET name = :name, price = :price, description = :description, stock = :stock, brand = :brand
                    WHERE id = :productId";

                $query = $connection->prepare($sql);
                $query->bindParam(':productId', $productId);
                $query->bindParam(':name', $name);
                $query->bindParam(':price', $price);
                $query->bindParam(':description', $description);
                $query->bindParam(':stock', $stock);
                $query->bindParam(':brand', $brand);
                $query->execute();

                // Update product images (if any)

                // Clean up existing product images (delete previous images, if any)

                // Upload new product images (if any)

                // Commit the transaction
                $connection->commit();

                // Check if the query was successful
                if ($query) {
                    echo json_encode(array(
                        "status" => 1,
                        "message" => "Product updated successfully",
                    ));
                } else {
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Error. Product update failed",
                    ));
                }
            } else {
                // Handle missing data
                echo json_encode(array(
                    "status" => 0,
                    "message" => "Missing data",
                ));
            }
        } catch (PDOException $e) {
            // Rollback the transaction and handle PDOException
            $connection->rollBack();
            echo json_encode(array(
                "status" => 0,
                "message" => "An error occurred: " . $e->getMessage(),
            ));
        } catch (Exception $e) {
            // Rollback the transaction and handle other exceptions
            $connection->rollBack();
            echo json_encode(array(
                "status" => 0,
                "message" => "An unexpected error occurred: " . $e->getMessage(),
            ));
        }
    }

    public function uploadProductImage($targetPath, $productId)
    {
        $imageId = uniqid();
        // Open a database connection
        $connection = $this->openConnection();

        // Insert the image into the images table
        $sql = "INSERT INTO products_images (id,image, product_id) VALUES (:id,:image, :product_id)";
        $query = $connection->prepare($sql);
        $query->bindParam(':id', $imageId);
        $query->bindParam(':image', $targetPath);
        $query->bindParam(':product_id', $productId);
        $result = $query->execute();

        return $result;
    }

    public function getProducts()
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        // $sql = "SELECT * FROM `products`";
        $sql = "SELECT p.*, GROUP_CONCAT(pi.image) AS products_images
                FROM products p 
                LEFT JOIN products_images pi ON p.id = pi.product_id
                GROUP BY p.id";

        $query = $connection->prepare($sql);

        $query->execute();

        $products = $query->fetchAll(); // Fetching single data from the server and it will return an array
        foreach ($products as &$product) {
            if ($product['products_images']) {
                $product['products_images'] = explode(',', $product['products_images']);
            } else {
                $product['products_images'] = [];
            }
        }

        echo json_encode(array(
            "status" => 1,
            "message" => "Products fetched successfully",
            "data" => $products
        ));
    }

    public function deleteProduct($product_id)
    {
        if (isset($product_id)) {
            try {
                $connection = $this->openConnection();

                // Prepare and execute the SQL query to delete the product
                $sql = "DELETE FROM products WHERE id = :product_id";
                $query = $connection->prepare($sql);
                $query->bindParam(':product_id', $product_id);

                // Execute the query
                $query->execute();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Product deleted successfully",
                ));
            } catch (PDOException $e) {
                // Handle PDOException
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Handle other exceptions
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }


    public function addOrder($productId, $quantity)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $isInputValid = isset($productId) && !empty($productId) && isset($quantity) && !empty($quantity);

        // Check if the login form has been submitted
        if ($isInputValid) {
            try {
                // Open a database connection
                $connection = $this->openConnection();

                // Prepare and execute the SQL query to retrieve user data based on email and password
                $sql = "INSERT INTO orders (id,product_id, quantity, user_id)
                    VALUES (:id,:product_id, :quantity, :user_id)";

                $orderId = uniqid();

                $query = $connection->prepare($sql);
                $query->bindParam(':id', $orderId);
                $query->bindParam(':product_id', $productId);
                $query->bindParam(':quantity', $quantity);
                $query->bindParam(':user_id', $_SESSION['userdata']['user_id']);
                $query->execute();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Order successfully place",
                ));
            } catch (PDOException $e) {
                // Handle PDOException
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Handle other exceptions
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {

            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }


    public function cartToOrder($cartId)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $isInputValid = isset($cartId) && !empty($cartId);

        // Check if the input data is valid
        if ($isInputValid) {
            try {
                // Open a database connection
                $connection = $this->openConnection();

                // Start a transaction to ensure atomicity
                $connection->beginTransaction();

                // Prepare and execute the SQL query to insert cart items into the order table
                $sqlInsert = "INSERT INTO orders (id, product_id, quantity, user_id, total_price)
                SELECT :id as id, product_id, quantity, user_id, total_price
                FROM carts
                WHERE id = :cartId";

                $newId = uniqid();

                $queryInsert = $connection->prepare($sqlInsert);
                $queryInsert->bindParam(':id', $newId);
                $queryInsert->bindParam(':cartId', $cartId);
                $queryInsert->execute();

                // Prepare and execute the SQL query to delete the transferred item from the cart table
                $sqlDelete = "DELETE FROM carts WHERE id = :cartId";

                $queryDelete = $connection->prepare($sqlDelete);
                $queryDelete->bindParam(':cartId', $cartId);
                $queryDelete->execute();

                // Commit the transaction
                $connection->commit();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Order successfully placed",
                ));
            } catch (PDOException $e) {
                // Rollback the transaction and handle PDOException
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Rollback the transaction and handle other exceptions
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            // Handle missing data
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $isInputValid = isset($orderId, $newStatus) && !empty($orderId) && !empty($newStatus);

        // Check if the input data is valid
        if ($isInputValid) {
            try {
                // Open a database connection
                $connection = $this->openConnection();

                // Start a transaction to ensure atomicity
                $connection->beginTransaction();

                // Retrieve the quantity and product ID of the order
                $sqlOrder = "SELECT product_id, status, quantity FROM orders WHERE id = :orderId";
                $queryOrder = $connection->prepare($sqlOrder);
                $queryOrder->bindParam(':orderId', $orderId);
                $queryOrder->execute();
                $order = $queryOrder->fetch();

                if ($order) {


                    // Update the order status
                    $sqlUpdateOrder = "UPDATE orders SET status = :newStatus WHERE id = :orderId";
                    $queryUpdateOrder = $connection->prepare($sqlUpdateOrder);
                    $queryUpdateOrder->bindParam(':orderId', $orderId);
                    $queryUpdateOrder->bindParam(':newStatus', $newStatus);
                    $queryUpdateOrder->execute();

                    // If the new status is 'cancelled', add the order quantity back to the product stock
                    // if ($newStatus === 'cancelled' || $newStatus === 'failed-transaction') {
                    if (
                        (
                            $newStatus === 'cancelled' && ($order["status"] === "pending" || $order["status"] === "packed")) ||
                        $newStatus === 'failed-transaction'
                    ) {
                        $sqlUpdateStock = "UPDATE products SET stock = stock + :quantity WHERE id = :productId";
                        $queryUpdateStock = $connection->prepare($sqlUpdateStock);
                        $queryUpdateStock->bindParam(':quantity', $order['quantity']);
                        $queryUpdateStock->bindParam(':productId', $order['product_id']);
                        $queryUpdateStock->execute();
                    }
                    // }

                    // Commit the transaction
                    $connection->commit();

                    echo json_encode(array(
                        "status" => 1,
                        "message" => "Order status successfully updated to $newStatus",
                    ));
                } else {
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Order not found or already cancelled",
                    ));
                }
            } catch (PDOException $e) {
                // Rollback the transaction and handle PDOException
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Rollback the transaction and handle other exceptions
                $connection->rollBack();
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            // Handle missing data
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function deleteOrder($orderId)
    {
        if (isset($orderId)) {
            try {
                $connection = $this->openConnection();

                // Prepare and execute the SQL query to delete the product
                $sql = "DELETE FROM orders WHERE id = :orderId";
                $query = $connection->prepare($sql);
                $query->bindParam(':orderId', $orderId);

                // Execute the query
                $query->execute();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Order deleted successfully",
                ));
            } catch (PDOException $e) {
                // Handle PDOException
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Handle other exceptions
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function getAllOrders()
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        // $sql = "SELECT * FROM `products`";
        // $sql = "SELECT o.*, GROUP_CONCAT(pi.image) AS products_images
        //         FROM orders o
        //         LEFT JOIN products_images pi ON p.id = pi.product_id
        //         GROUP BY p.id";

        // $sql = "SELECT 
        //             orders.*,
        //             -- CONCAT(users.first_name, ' ', users.middle_name, ' ', users.last_name) AS user_name,
        //             products.id AS product_id,
        //             products.name AS product_name,
        //             products.price AS product_price,
        //         FROM 
        //             orders
        //         -- JOIN 
        //         --     users ON orders.user_id = users.id
        //         JOIN 
        //             products ON orders.product_id = products.id";

        $sql = "SELECT 
                    o.*, 
                    GROUP_CONCAT(pi.image) AS images,
                    CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS customer_name,
                    p.name,
                    p.price,
                    p.description,
                    p.stock,
                    p.brand
                FROM orders o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN products_images pi ON pi.product_id = p.id
                GROUP BY o.id";

        // $sql = "SELECT * from `orders`";

        $query = $connection->prepare($sql);

        $query->execute();

        $orders = $query->fetchAll(); // Fetching single data from the server and it will return an array

        foreach ($orders as &$order) {
            if ($order['images']) {
                $order['images'] = explode(',', $order['images']);
            } else {
                $order['images'] = [];
            }
        }
        // foreach ($products as &$product) {
        //     if ($product['products_images']) {
        //         $product['products_images'] = explode(',', $product['products_images']);
        //     } else {
        //         $product['products_images'] = [];
        //     }
        // }

        echo json_encode(array(
            "status" => 1,
            "message" => "Products fetched successfully",
            "data" => $orders
        ));
    }

    public function getMyOrders()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        // $sql = "SELECT * FROM `products`";
        // $sql = "SELECT o.*, GROUP_CONCAT(pi.image) AS products_images
        //         FROM orders o
        //         LEFT JOIN products_images pi ON p.id = pi.product_id
        //         GROUP BY p.id";

        // $sql = "SELECT 
        //             orders.*,
        //             -- CONCAT(users.first_name, ' ', users.middle_name, ' ', users.last_name) AS user_name,
        //             products.id AS product_id,
        //             products.name AS product_name,
        //             products.price AS product_price,
        //         FROM 
        //             orders
        //         -- JOIN 
        //         --     users ON orders.user_id = users.id
        //         JOIN 
        //             products ON orders.product_id = products.id";

        $sql = "SELECT 
                    o.*, 
                    GROUP_CONCAT(pi.image) AS images,
                    CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS customer_name,
                    p.name,
                    p.price,
                    p.description,
                    p.stock,
                    p.brand
                FROM orders o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN products_images pi ON pi.product_id = p.id
                WHERE o.user_id = :user_id
                GROUP BY o.id";

        // $sql = "SELECT * from `orders`";

        $query = $connection->prepare($sql);
        $query->bindParam(':user_id', $_SESSION["userdata"]["user_id"]);
        $query->execute();

        $orders = $query->fetchAll(); // Fetching single data from the server and it will return an array

        foreach ($orders as &$order) {
            if ($order['images']) {
                $order['images'] = explode(',', $order['images']);
            } else {
                $order['images'] = [];
            }
        }
        // foreach ($products as &$product) {
        //     if ($product['products_images']) {
        //         $product['products_images'] = explode(',', $product['products_images']);
        //     } else {
        //         $product['products_images'] = [];
        //     }
        // }

        echo json_encode(array(
            "status" => 1,
            "message" => "Products fetched successfully",
            "data" => $orders
        ));
    }


    public function addToCart($productId, $quantity)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $isInputValid = isset($productId) && !empty($productId) && isset($quantity) && !empty($quantity) && isset($_SESSION['userdata']['user_id']) && !empty($_SESSION['userdata']['user_id']);

        // Check if the login form has been submitted
        if ($isInputValid) {
            try {
                // Open a database connection
                $connection = $this->openConnection();

                // Prepare and execute the SQL query to retrieve user data based on email and password
                $sql = "INSERT INTO carts (id, product_id, quantity, user_id)
                    VALUES (:id, :product_id, :quantity, :user_id)";

                $orderId = uniqid();

                $query = $connection->prepare($sql);
                $query->bindParam(':id', $orderId);
                $query->bindParam(':product_id', $productId);
                $query->bindParam(':quantity', $quantity);
                $query->bindParam(':user_id', $_SESSION['userdata']['user_id']);
                $query->execute();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Added to cart.",
                ));
            } catch (PDOException $e) {
                // Handle PDOException
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Handle other exceptions
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {

            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }

    public function getCart()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password

        $sql = "SELECT 
                    c.*, 
                    GROUP_CONCAT(pi.image) AS images,
                    CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS customer_name,
                    p.name,
                    p.price,
                    p.description,
                    p.stock,
                    p.brand
                FROM carts c
                LEFT JOIN products p ON c.product_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN products_images pi ON pi.product_id = p.id
                WHERE c.user_id = :user_id
                GROUP BY c.id";
        $query = $connection->prepare($sql);
        $query->bindParam(':user_id', $_SESSION["userdata"]["user_id"]);

        $query->execute();

        $carts = $query->fetchAll(); // Fetching single data from the server and it will return an array

        // foreach ($orders as &$order) {
        //     if ($order['images']) {
        //         $order['images'] = explode(',', $order['images']);
        //     } else {
        //         $order['images'] = [];
        //     }
        // }
        foreach ($carts as &$cart) {
            if ($cart['images']) {
                $cart['images'] = explode(',', $cart['images']);
            } else {
                $cart['images'] = [];
            }
        }

        echo json_encode(array(
            "status" => 1,
            "message" => "Products fetched successfully",
            "data" => $carts
        ));
    }

    public function deleteCart($cartId)
    {
        $isValidInput = isset($cartId) && !empty($cartId);

        if ($isValidInput) {
            try {
                $connection = $this->openConnection();

                // Prepare and execute the SQL query to delete the product
                $sql = "DELETE FROM carts WHERE id = :cartId";

                $query = $connection->prepare($sql);
                $query->bindParam(':cartId', $cartId);
                $query->execute();
                // Execute the query
                $query->execute();

                echo json_encode(array(
                    "status" => 1,
                    "message" => "Cart deleted ",
                ));
            } catch (PDOException $e) {
                // Handle PDOException
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An error occurred: " . $e->getMessage(),
                ));
            } catch (Exception $e) {
                // Handle other exceptions
                echo json_encode(array(
                    "status" => 0,
                    "message" => "An unexpected error occurred: " . $e->getMessage(),
                ));
            }
        } else {
            echo json_encode(array(
                "status" => 0,
                "message" => "Missing data",
            ));
        }
    }


    /**
     * Sets session data for the logged-in user.
     *
     * @param array $userData The user data to be stored in session.
     * @return void
     */



    //Getting the userdata on our session
    public function getUserData()
    {

        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['userdata'])) {

            return $_SESSION['userdata'];
        } else {

            header("Location: ../index.php");
        }
    }
}

$operations = new MySite();
