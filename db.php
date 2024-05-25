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
     * Checks if the provided email exists in the database.
     *
     * @param string $email The email to check.
     * @return array|false Returns an array of users with the provided email if found, or false if not found.
     */
    public function verifyEmail($email)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve users with the provided email
        $query = $connection->prepare("SELECT * FROM `users` WHERE email = ?");
        $query->execute([$email]);

        // Fetch all rows returned by the query
        $users = $query->fetchAll();

        // Get the total number of rows returned by the query
        $total = $query->rowCount();

        // If there are users with the provided email, return the array of users
        // Otherwise, return false indicating that the email doesn't exist
        if ($total > 0) {
            return $users;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the total number of users from the database.
     *
     * @return int The total number of users.
     */
    public function getNumberOfUser()
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to count the number of users
        $query = $connection->prepare("SELECT * FROM `users`");
        $query->execute();

        // Get the total number of rows returned by the query
        $total = $query->rowCount();

        // Return the total number of users
        return $total;
    }

    // Function to delete user and related records

    public function deleteUser($delete_id)
    {
        $connection = $this->openConnection();

        $delete_user_convo_query = "DELETE FROM user_convo WHERE sender_id = ?";
        $stmt_convo = $connection->prepare($delete_user_convo_query);
        $stmt_convo->execute([$delete_id]);
        if ($stmt_convo->errorInfo()[0] !== '00000') {
            echo "Error deleting user_convo records: " . $stmt_convo->errorInfo()[2];
        }

        $delete_user_messages_query = "DELETE FROM user_messages WHERE sender_id = ?";
        $stmt_messages = $connection->prepare($delete_user_messages_query);
        $stmt_messages->execute([$delete_id]);
        if ($stmt_messages->errorInfo()[0] !== '00000') {
            echo "Error deleting user_messages records: " . $stmt_messages->errorInfo()[2];
        }

        $delete_user_skills_query = "DELETE FROM user_skills WHERE user_id = ?";
        $stmt_skills = $connection->prepare($delete_user_skills_query);
        $stmt_skills->execute([$delete_id]);
        if ($stmt_skills->errorInfo()[0] !== '00000') {
            echo "Error deleting user_skills records: " . $stmt_skills->errorInfo()[2];
        }

        $delete_user_tags_query = "DELETE FROM user_tags WHERE tag_user_id = ?";
        $stmt_tags = $connection->prepare($delete_user_tags_query);
        $stmt_tags->execute([$delete_id]);
        if ($stmt_tags->errorInfo()[0] !== '00000') {
            echo "Error deleting user_tags records: " . $stmt_tags->errorInfo()[2];
        }

        $delete_query = "DELETE FROM users WHERE unique_id = ?";
        $stmt_delete = $connection->prepare($delete_query);
        $stmt_delete->execute([$delete_id]);
        if ($stmt_delete->errorInfo()[0] !== '00000') {
            echo "Error deleting record: " . $stmt_delete->errorInfo()[2];
        }

        // Close the connection
        $this->closeConnection();
    }

    public function deleteArtwork($delete_id)
    {
        $connection = $this->openConnection();


        $delete_query = "DELETE FROM artworks WHERE artwork_unique_id = ?";
        $stmt = $connection->prepare($delete_query);
        $stmt->execute([$delete_id]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Record deleted successfully');</script>";
            echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit;
        } else {
            echo "Error deleting record: No record found with ID $delete_id";
        }

        // No need to close connection explicitly
    }



    public function getAllArtworks()
    {
        $connection = $this->openConnection();

        $query = "SELECT a.*, i.image_name as image_name FROM artworks a INNER JOIN artworks_images i ON a.artwork_unique_id = i.artwork_mother_id ORDER BY a.date_created DESC";


        $result = $connection->query($query);

        // No need to close connection explicitly

        return $result;
    }




    // Function to get all users
    public function getAllUsers()
    {
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to all users 
        $query = $connection->prepare("SELECT * FROM `users`");
        $query->execute();
        $users = $query->fetchAll();

        return $users;
    }

    public function testGetUser()
    {
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to all users 
        $query = $connection->prepare("SELECT * FROM `users` WHERE `email` = ? AND `password` = ?");
        $query->execute(["papisss05@gmail.com",  md5("Fr@ncis0405")]);
        $users = $query->fetch();

        return $users;
    }

    /**
     * Verifies if the given user ID exists in the database.
     *
     * @param string $userID The unique identifier of the user.
     * @return int The number of rows found with the given user ID.
     */
    public function verifyUserID($userID)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to check if the user ID exists
        $query = $connection->prepare("SELECT * FROM `users` WHERE `unique_id` = ?");
        $query->execute([$userID]);

        // Get the total number of rows returned by the query
        $total = $query->rowCount();

        // Return the total number of rows found with the given user ID
        return $total;
    }


    /**
     * Registers a new user by inserting their data into the database.
     *
     * @param string $unique_id The unique identifier for the user.
     * @param string $user_type The type of user.
     * @param string $password The hashed password of the user.
     * @param string $email The email address of the user.
     * @param string $contact_number The contact number of the user.
     * @param string $first_name The first name of the user.
     * @param string $last_name The last name of the user.
     * @param int $verification_status The verification status of the user.
     * @return bool True if the user registration is successful, false otherwise.
     */
    public function registerUser($unique_id, $user_type, $password, $email, $contact_number, $first_name, $last_name, $verification_status)
    {
        $active_status = "Online";
        $profile_photo = "defaultProfilePicture.png";
        $cover_photo = "defaultBannerPicture.png";

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert user data into the database
        $query = $connection->prepare("INSERT INTO `users`(`unique_id`, `user_type`, `password`,`email`, 
                                    `contact_number`, `first_name`, `last_name`, `verification_status`, `active_status`, `profile_photo`, `cover_photo`) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $query->execute([$unique_id, $user_type, $password, $email, $contact_number, $first_name, $last_name, $verification_status, $active_status, $profile_photo, $cover_photo]);

        // Return true if the registration is successful, false otherwise
        return $result;
    }


    public function registerAdmin($username, $first_name, $last_name, $password)
    {
        $active_status = "Online";
        $user_type = "admin";
        $verification_status = "Verified";
        $profile_photo = "defaultProfilePicture.png";
        $cover_photo = "defaultBannerPicture.png";
        $encpassword = md5($password);

        // Open a database connection
        $connection = $this->openConnection();
        $userCount = $this->getNumberOfUser();

        // Generate random letters
        $randomLetters = '';
        for ($i = 0; $i < 3; $i++) {
            $randomLetters .= chr(rand(65, 90)); // ASCII codes for uppercase letters (A-Z)
        }

        // Generate random 4-digit number
        $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Combine random letters and number
        $randomCombination = $randomLetters . $randomNumber;

        //We'll verify first if the generated random number is already taken or not
        do {
            $generatedUserID = $randomCombination . "" . $userCount; //creating random id for user;

            $verifyUserIDResult = $this->verifyUserID($generatedUserID);
        } while ($verifyUserIDResult != 0);

        // Prepare and execute the SQL query to insert user data into the database
        $query = $connection->prepare("INSERT INTO `users`(`unique_id`, `user_type`, `username`, `password`, 
                                     `first_name`, `last_name`, `verification_status`, `active_status`, `profile_photo`, `cover_photo`) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $query->execute([$generatedUserID, $user_type, $username, $encpassword, $first_name, $last_name, $verification_status, $active_status, $profile_photo, $cover_photo]);
        header("Location: ../admin/manageaccounts.php");
        exit();
        // Return true if the registration is successful, false otherwise
        return $result;
    }


    /**
     * Generates and inserts an OTP (One-Time Password) into the database.
     *
     * @param string $otp_email The email address associated with the OTP.
     * @param string $otp The generated OTP.
     * @param string $otp_type The type of OTP.
     * @return bool True if the OTP generation and insertion is successful, false otherwise.
     */
    public function generateOTP($otp_email, $otp, $otp_type)
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert OTP data into the database
        $query = $connection->prepare("INSERT INTO `otp`(`otp_email`, `otp`, `otp_type`) 
                                    VALUES (?, ?, ?)");
        $result = $query->execute([$otp_email, $otp, $otp_type]);

        // Return true if the OTP generation and insertion is successful, false otherwise
        return $result;
    }


    /**
     * Deletes an OTP (One-Time Password) from the database.
     *
     * @param string $otp_email The email address associated with the OTP to be deleted.
     * @return bool True if the OTP deletion is successful, false otherwise.
     */
    public function deleteOTP($otp_email)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to delete OTP data from the database
        $query = $connection->prepare("DELETE FROM `otp` WHERE `otp_email` = ?");
        $result = $query->execute([$otp_email]);

        // Return true if the OTP deletion is successful, false otherwise
        return $result;
    }


    /**
     * Inserts tags selected by the user into the database.
     *
     * @param string $user_id The ID of the user who selected the tags.
     * @param string $tag_desc The description of the tag selected by the user.
     * @return bool True if the tag insertion is successful, false otherwise.
     */
    public function insertTags($user_id, $tag_desc)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("INSERT INTO `user_tags`(`tag_user_id`, `tag_desc`) VALUES (?, ?)");
        $result = $query->execute([$user_id, $tag_desc]);

        // Return true if the tag insertion is successful, false otherwise
        return $result;
    }


    /**
     * Validates the OTP (One-Time Password) provided by the user.
     *
     * @param string $otp The OTP provided by the user.
     * @param string $email The email associated with the OTP.
     * @return int The number of OTP records found in the database matching the provided OTP and email.
     */
    public function validateOTP($otp, $email)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to validate the OTP
        $query = $connection->prepare("SELECT * FROM `otp` WHERE `otp_email` = ? AND `otp` = ?");
        $query->execute([$email, $otp]);

        // Get the total number of OTP records found
        $total = $query->rowCount();

        // Return the total number of OTP records found
        return $total;
    }


    /**
     * Updates the verification status of a user in the database.
     *
     * @param string $verification_status The new verification status to be set for the user.
     * @param string $email The email of the user whose verification status is to be updated.
     */
    public function updateVerificationStatus($verification_status, $email)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to update the verification status
        $query = $connection->prepare("UPDATE `users` SET `verification_status` = ? WHERE `email` = ?");
        $result = $query->execute([$verification_status, $email]);

        // Return true if the update operation was successful, false otherwise
        if ($result) {
            $this->getUserDataByEmail($email);
        } else {
            echo "Something Went Wrong";
        }
    }

    public function getUserDataByEmail($email)
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        $query = $connection->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $query->execute([$email]);
        $user = $query->fetch(); // Fetching single data from the server and it will return an array
        $total = $query->rowCount();

        // If user credentials are found
        if ($total > 0) {
            $this->setUserSession($user);

            return true;
        } else {
            // Display error message if credentials are invalid
            return false;
        }
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

    public function loginUser()
    {

        // Check if the login form has been submitted
        if (isset($_POST['login'])) {
            $email = strtolower($_POST['email']);
            $password = md5($_POST['password']);

            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data based on email and password
            $query = $connection->prepare("SELECT * FROM `users` WHERE `email` = ? AND `password` = ?");
            $query->execute([$email, $password]);
            $user = $query->fetch(); // Fetching single data from the server and it will return an array
            $total = $query->rowCount();

            // If user credentials are found
            if ($total > 0) {
                $this->setUserSession($user);

                // Redirect based on user verification status
                if ($user['verification_status'] == "Verified") {
                    header("Location: pages/home.php");
                } else {
                    header("Location: pages/otpvalidation.php");
                }
            } else {
                // Display error message if credentials are invalid
                echo "<script>
                    alert('INVALID CREDENTIAL');
                </script>";
            }
        }
    }
    //function for logout

    //function for admin login

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
            $hashedPassword = md5($password);

            // Open a database connection
            $connection = $this->openConnection();

            // Prepare and execute the SQL query to retrieve user data based on email and password
            $sql = "INSERT INTO users (email, password)
            VALUES (:email, :password)";

            $query = $connection->prepare($sql);

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

    public function getOrders()
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

    public function loginAdminUser()
    {
        if (isset($_POST['login'])) {
            $username = strtolower($_POST['username']);
            $password = md5($_POST['password']);

            $connection = $this->openConnection();
            $query = $connection->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
            $query->execute([$username, $password]);

            $user = $query->fetch();
            $total = $query->rowCount();

            if ($total > 0) {
                $this->setUserSession($user);

                if ($user['verification_status'] == "Verified" && $user['user_type'] == "admin") {
                    $_SESSION['userdata'] = $user;
                    header("Location: dashboard.php");
                } else {
                    header("Location: admin_login.php");
                }
            } else {
                echo "<script>alert('User is not an Admin or INVALID CREDENTIAL');</script>";
            }
        }
    }
    public function logoutAdmin()
    {
        // Start the session if it's not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }


        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Redirect the user to the admin login page
        header("Location: ../admin/admin_login.php");
        exit; // Ensure that no further code is executed after redirection
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
