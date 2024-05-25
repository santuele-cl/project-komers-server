<?php

class MySite
{

    private $server = "mysql:host=localhost;dbname=artfinds";
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
            $this->setUserData($user);

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

    public function setUserData($user)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['userdata'] = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'user_type' => $user['user_type']
        ];
    }

    public function getUserSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['userdata'])) {
            if ($_SESSION['userdata']['user_type'] == "admin") {
                // Redirect admin to the admin dashboard
                header("Location: ./../admin/dashboard.php");
            } else {
                // Redirect non-admin users to the home page
                header("Location: ./pages/home.php");
            }
            // exit; // Ensure script stops after the redirect
        } else {
            // Return an error message if no user session is found
            return "No user session found.";
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
                $this->setUserData($user);

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

    public function testLoginUser()
    {

        // Check if the login form has been submitted

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        $query = $connection->prepare("SELECT * FROM `users` WHERE `email` = ? AND `password` = ?");
        $email = "papisss05@gmail.com";
        $password = md5("Fr@ncis0405");
        $query->execute([$email, $password]);
        $user = $query->fetch(); // Fetching single data from the server and it will return an array
        $total = $query->rowCount();

        // return $user;

        // // If user credentials are found
        if ($total > 0) {
            $this->setUserData($user);
            return $_SESSION;
            // return "success";
        }
        // else {
        //     return ;
        //     // Display error message if credentials are invalid
        //     // echo "<script>
        //     //         alert('INVALID CREDENTIAL');
        //     //     </script>";
        // }
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
                $this->setUserData($user);

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

    //Show all selected tags by user
    public function getUserTags($userID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_tags` WHERE `tag_user_id`= ?");
        $query->execute([$userID]);
        $tags = $query->fetchAll();

        $output = '';

        foreach ($tags as $tag) {

            $output .= '<span class="badge rounded-pill text-bg-dark">' . $tag['tag_desc'] . '</span>  ';
        }

        return $output;
    }

    //Get user's working experience
    public function getUserExperience($userID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `experience` WHERE `user_experience_id`= ? ORDER BY `end_date` DESC LIMIT 1");
        $query->execute([$userID]);
        $experience = $query->fetch();
        $output = '';

        if ($experience) {

            $output .= <<<HTML
                <div class="mt-3 d-flex justify-content-start w-100 ms-4">
                    <p class="fs-7 fw-bolder text-muted mb-0">WORK EXPERIENCE</p>
                </div>
                <div class="mt-3 d-flex justify-content-start w-100 ms-4">
                    <p class="fs-7 fw-bold text-dark mb-0">{$experience['job_title']}</p>
                    <p class="fs-7 text-dark">—— {$experience['previous_company']}</p> 
                </div>   
            HTML;

            return $output;
        } else {

            return $this->getUserEducation($userID);
        }
    }

    //Get user's educational background
    public function getUserEducation($userID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `education` WHERE `user_education_id`= ? ORDER BY `year_graduated` DESC LIMIT 1");
        $query->execute([$userID]);
        $education = $query->fetch();
        $output = '';

        if ($education) {


            $output .= <<<HTML
                <div class="mt-3 d-flex justify-content-start w-100 ms-4">
                    <p class="fs-7 fw-bolder text-muted mb-0">EDUCATIONAL BACKGROUND</p>
                </div>
                <div class="mt-1 justify-content-start w-100 ms-4">
                    <p class="fs-7 fw-bold text-dark mb-0">{$education['major']}</p>
                    <p class="fs-7 text-dark">—— {$education['school']}</p>    
                </div>
            HTML;

            return $output;
        } else {

            $output .= '<div class="mt-3 d-flex justify-content-start w-100 ms-4">
                            <p class="fs-7 fw-bolder text-muted mb-0">WORK EXPERIENCE</p>
                        </div>
                        <div class="mt-1 justify-content-start w-100 ms-4">
                            <p class="fs-7 fw-bold text-dark mb-0">No Record!</p>
                            <p class="fs-7 text-dark">—— User is currently unemployed</p>    
                        </div>';

            return $output;
        }
    }
    //Function to create task
    public function createTask($userID, $taskDue, $task)
    {

        $task_status = "In Progress";

        $taskDueDate = DateTime::createFromFormat('Y-m-d H:i:s', $taskDue);

        //Converting task due to a format acceptable by the database
        if ($taskDueDate !== false) {
            //format the data coming from backend to mysql datetime format
            $mySqlDateTime = $taskDueDate->format('Y-m-d H:i:s');

            $connection = $this->openConnection();
            $query = $connection->prepare("INSERT INTO `tasks`(`user_task_id`, `task`, `task_status`, `due_date`) VALUES (?, ?, ?, ?)");
            $result = $query->execute([$userID, $task, $task_status, $mySqlDateTime]);

            return $result;
        } else {
            return "Invalid datetime format";
        }
    }

    public function updateTask($taskID, $taskDue, $task)
    {
        $currentDateTime = new DateTime();
        $currentDateTimeMysql = $currentDateTime->format("Y-m-d H:i:s");

        $taskDueDate = DateTime::createFromFormat('Y-m-d H:i:s', $taskDue);

        // Check if taskDueDate is valid
        if ($taskDueDate !== false) {
            // Format the due date for MySQL
            $mySqlDateTime = $taskDueDate->format('Y-m-d H:i:s');

            // Open database connection
            $connection = $this->openConnection();

            // Prepare SQL statement with placeholders
            $query = $connection->prepare("UPDATE `tasks` SET `task` = ?, `due_date` = ?, `date_created` = ? WHERE `task_id` = ?");

            // Bind parameters and execute query
            $result = $query->execute([$task, $mySqlDateTime, $currentDateTimeMysql, $taskID]);

            // Return the result of the execution
            return $result;
        } else {
            // Return an error message if the datetime format is invalid
            return "Invalid datetime format";
        }
    }


    /**
     * Updates the progress of a task to "In Progress" status.
     *
     * @param int $taskID The ID of the task to update.
     * @return bool True on success, false on failure.
     */
    //Function to update task progress
    public function updateTaskProgress($taskID, $task_status)
    {

        $currentDateTime = new DateTime();
        $currentDateTimeMysql = $currentDateTime->format("Y-m-d H:i:s");

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `tasks` SET `task_status` = ? , `date_created` = ? WHERE task_id = ?");
        $result = $query->execute([$task_status, $currentDateTimeMysql, $taskID]);

        return $result;
    }


    /**
     * Retrieves and displays all tasks for a given user.
     *
     * @param int $userID The ID of the user whose tasks are to be fetched.
     * @return string HTML markup containing the list of tasks.
     */
    public function getAllTasks($userID)
    {

        // Open database connection
        $connection = $this->openConnection();

        // Prepare SQL query to fetch tasks for the specified user
        $query = $connection->prepare("SELECT * FROM `tasks` WHERE `user_task_id` = ? ORDER BY `due_date` ASC");

        // Execute the query with the user ID parameter
        $query->execute([$userID]);

        // Fetch all tasks
        $tasks = $query->fetchAll();

        // Initialize output string
        $output = '';

        // Iterate over each task and format its HTML representation
        foreach ($tasks as $task) {
            // Convert due date to 12-hour format
            $dueDate12hFormat = date('Y-m-d h:i A', strtotime($task['due_date']));
            $taskDescription = str_replace("<br>", "\n", $task['task']);

            $task_description = str_replace('<br>', ' ', $task['task']);
            $max_length = 25; // Maximum length for the task description


            // Check if the length of the task description exceeds the maximum length
            if (strlen($task_description) > $max_length) {
                // Truncate the task description and append three dots
                $task_description = substr($task_description, 0, $max_length - 3) . '...';
            }



            // Set color based on task status
            $color = '';
            //Set a strikethrough effect to a word if it's done
            $strikeThroughText = '';
            if ($task['task_status'] === "In Progress") {
                $color = '<p class="change status badge bg-primary rounded-pill m-0 fst-normal pointer" onclick="updateStatusBtn(' . $task['task_id'] . ',\'' . $task['task_status'] . '\')">' . $task['task_status'] . '</p>';
                $strikeThroughText = '<p data-html="true" class="fw-bold mb-0" data-bs-toggle="tooltip" title="' . $taskDescription . '" data-bs-placement="right">' . $task_description . '</p>';
            } else {
                $color = '<p class="change status badge bg-success rounded-pill m-0 fst-normal pointer" onclick="updateStatusBtn(' . $task['task_id'] . ',\'' . $task['task_status'] . '\')">' . $task['task_status'] . '</p>';
                $strikeThroughText = '<p class="fw-bold mb-0 pointer" data-bs-toggle="tooltip" title="' . $taskDescription . '" data-bs-placement="right" style="text-decoration: line-through;">' . $task_description . '</p>';
            }

            // Append task HTML markup to the output string using heredoc syntax
            $output .= <<<HTML
                <!-- Task -->
                <li class="list-group-item">
                    <div class="ms-2 me-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            {$strikeThroughText}
                            <!-- Edit -->
                            <i class="fas fa-ellipsis-h postTaskMenu" type="button" id="postTaskMenu" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <!-- Edit menu -->
                            <ul class="dropdown-menu border-0 shadow" aria-labelledby="postTaskMenu">
                                <li class="d-flex align-items-center" onclick="getUpdateTaskID('{$task['task_id']}','{$task['due_date']}','{$task['task']}')">
                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7 editTaskBtn" id="editTaskBtn" data-bs-toggle="modal" data-bs-target="#EditTask">Edit Post</a>
                                </li>
                                <li class="d-flex align-items-center" onclick="getDeleteTaskID('{$task['task_id']}')">
                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7">Delete Post</a>
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-alarm fs-6 text-primary me-1"></i>
                                <p class="fs-7 text-muted mb-0">{$dueDate12hFormat}</p>
                            </div>
                            {$color}
                        </div>
                    </div>
                    <hr>
                </li>
            HTML;
        }

        // Return the HTML markup of all tasks
        return $output;
    }

    /**
     * Retrieves and displays all tasks for a given user based on their status (Pending or Completed).
     *
     * @param int $userID The ID of the user whose tasks are to be fetched.
     * @param string $userStatus The status of the tasks to retrieve (Pending or Completed).
     * @return string HTML markup containing the list of tasks.
     */
    public function getAllPendingAndCompleteTasks($userID, $userStatus)
    {

        // Open database connection
        $connection = $this->openConnection();

        // Prepare SQL query to fetch tasks for the specified user with the given status
        $query = $connection->prepare("SELECT * FROM `tasks` WHERE `user_task_id` = ? AND `task_status` = ? ORDER BY `due_date` ASC");

        // Execute the query with the user ID and status parameters
        $query->execute([$userID, $userStatus]);

        // Fetch all tasks with the specified status
        $tasks = $query->fetchAll();

        // Initialize output string
        $output = '';

        // Iterate over each task and format its HTML representation
        foreach ($tasks as $task) {
            // Convert due date to 12-hour format
            $dueDate12hFormat = date('Y-m-d h:i A', strtotime($task['due_date']));
            $taskDescription = str_replace("<br>", "\n", $task['task']);

            $task_description = str_replace('<br>', ' ', $task['task']);
            $max_length = 25; // Maximum length for the task description


            // Check if the length of the task description exceeds the maximum length
            if (strlen($task_description) > $max_length) {
                // Truncate the task description and append three dots
                $task_description = substr($task_description, 0, $max_length - 3) . '...';
            }

            // Set color based on task status
            $color = '';
            //Set a strikethrough effect to a word if it's done
            $strikeThroughText = '';
            if ($task['task_status'] === "In Progress") {
                $color = '<p class="change status badge bg-primary rounded-pill m-0 fst-normal pointer" onclick="updateStatusBtn(' . $task['task_id'] . ',\'' . $task['task_status'] . '\')">' . $task['task_status'] . '</p>';
                $strikeThroughText = '<p data-html="true" class="fw-bold mb-0" data-bs-toggle="tooltip" title="' . $taskDescription . '" data-bs-placement="right">' . $task_description . '</p>';
            } else {
                $color = '<p class="change status badge bg-success rounded-pill m-0 fst-normal pointer" onclick="updateStatusBtn(' . $task['task_id'] . ',\'' . $task['task_status'] . '\')">' . $task['task_status'] . '</p>';
                $strikeThroughText = '<p class="fw-bold mb-0 pointer" data-bs-toggle="tooltip" title="' . $taskDescription . '" data-bs-placement="right" style="text-decoration: line-through;">' . $task_description . '</p>';
            }

            // Append task HTML markup to the output string using heredoc syntax
            $output .= <<<HTML
                <!-- Task -->
                <li class="list-group-item">
                    <div class="ms-2 me-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            {$strikeThroughText}
                            <!-- Edit -->
                            <i class="fas fa-ellipsis-h postTaskMenu" type="button" id="postTaskMenu" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <!-- Edit menu -->
                            <ul class="dropdown-menu border-0 shadow" aria-labelledby="postTaskMenu">
                                <li class="d-flex align-items-center" onclick="getUpdateTaskID('{$task['task_id']}','{$task['due_date']}','{$task['task']}')">
                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7 editTaskBtn" id="editTaskBtn" data-bs-toggle="modal" data-bs-target="#EditTask">Edit Post</a>
                                </li>
                                <li class="d-flex align-items-center" onclick="getDeleteTaskID('{$task['task_id']}')">
                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7">Delete Post</a>
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-alarm fs-6 text-primary me-1"></i>
                                <p class="fs-7 text-muted mb-0">{$dueDate12hFormat}</p>
                            </div>
                            {$color}
                        </div>
                    </div>
                    <hr>
                </li>
            HTML;
        }

        // Return the HTML markup of all tasks with the specified status
        return $output;
    }

    //Function to delete selected Task
    public function deleteSelectedTask($taskID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `tasks` WHERE `task_id` = ?");
        $result = $query->execute([$taskID]);

        return $result;
    }

    //upload image
    public function uploadImages($userID, $image_name, $artwork_mother_id)
    {

        $connection = $this->openConnection();
        // Prepare SQL query to fetch tasks for the specified user with the given status
        $query = $connection->prepare("INSERT INTO `artworks_images`(`artwork_mother_id`, `artist_id`, `image_name`) 
                                        VALUES (?, ?, ?)");
        // Execute the query with the user ID and status parameters
        $result = $query->execute([$artwork_mother_id, $userID, $image_name]);

        return $result;
    }

    //Get the total number of post
    public function getNumberOfArtworks()
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to count the number of users
        $query = $connection->prepare("SELECT * FROM `artworks`");
        $query->execute();

        // Get the total number of rows returned by the query
        $total = $query->rowCount();

        // Return the total number of users
        return $total;
    }

    public function createArtwork($artwork_unique_id, $creator_user_id, $caption, $artwork_tags, $creator_first_name, $creator_last_name, $creator_profile_photo, $artwork_status, $price)
    {
        // Open a database connection
        $currentDateTime = date('Y-m-d H:i:s');
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert artwork
        $query = $connection->prepare("INSERT INTO `artworks`(`artwork_unique_id`, `creator_user_id`, `caption`, `artwork_tags` , `creator_first_name`,
                                    `creator_last_name`, `creator_profile_photo` ,`artwork_status`, `date_created`,`price`) 
                                        VALUES (:artwork_unique_id, :creator_user_id, :caption, :artwork_tags, :creator_first_name, :creator_last_name, :creator_profile_photo, :artwork_status, :date_created, :price)");

        // Bind parameters
        $query->bindParam(':artwork_unique_id', $artwork_unique_id);
        $query->bindParam(':creator_user_id', $creator_user_id);
        $query->bindParam(':caption', $caption);
        $query->bindParam(':artwork_tags', $artwork_tags);
        $query->bindParam(':creator_first_name', $creator_first_name);
        $query->bindParam(':creator_last_name', $creator_last_name);
        $query->bindParam(':creator_profile_photo', $creator_profile_photo);
        $query->bindParam(':artwork_status', $artwork_status);
        $query->bindParam(':date_created', $currentDateTime);
        $query->bindParam(':price', $price);

        // Execute query
        $result = $query->execute();

        return $result;
    }
    /**
     * Inserts tags selected by the user into the database.
     *
     * @param string $user_id The ID of the user who selected the tags.
     * @param string $tag_desc The description of the tag selected by the user.
     * @return bool True if the tag insertion is successful, false otherwise.
     */
    public function insertArtworkTags($artwork_mother_id, $artwork_user_id, $tag)
    {

        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("INSERT INTO `artworks_tags`(`artwork_mother_id`, `artwork_user_id`, `tag`) 
                                    VALUES (?,?,?)");
        $result = $query->execute([$artwork_mother_id, $artwork_user_id, $tag]);

        // Return true if the tag insertion is successful, false otherwise
        return $result;
    }

    public function getArtWorksTimeline($current_user_id)
    {

        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        $query = $connection->prepare("SELECT * FROM (
                                        SELECT a.*
                                        FROM followers f
                                        JOIN artworks a ON f.user_following_id = a.creator_user_id
                                        WHERE f.user_follower_id = :current_user_id
                                        UNION
                                        SELECT a.*
                                        FROM artworks a
                                        JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
                                        WHERE t.tag_user_id = :current_user_id
                                        UNION
                                        SELECT *
                                        FROM artworks
                                        WHERE creator_user_id = :current_user_id
                                    ) AS combined_results
                                    ORDER BY date_created DESC;");

        // Bind parameters
        $query->bindParam(':current_user_id', $current_user_id);

        // Execute the query
        $query->execute();

        // Fetch all results
        $artWorks = $query->fetchAll();

        $posts = '';

        // Iterate through each artwork
        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $current_user_id); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $likeStatusBool = '';
            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }

            //like status display data
            if ($likeStatus) {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3 text-primary" ></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'true';
            } else {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3"></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'false';
            }

            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }


            if ($creatorInfo['profile_photo']) {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/' . $creatorInfo['profile_photo'] . '" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            } else {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/defaultProfilePicture.png" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            }


            if ($creatorInfo['unique_id'] === $current_user_id) {
                $editPost .= <<<HTML
                                <i class="fas fa-ellipsis-h" type="button" id="post1Menu" data-bs-toggle="dropdown"
                                    aria-expanded="false"></i>
                                <!-- edit menu -->
                                <ul class="dropdown-menu border-0 shadow" aria-labelledby="post1Menu">
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="editArtwork('{$artWork['artwork_unique_id']}','{$artWork['caption']}','{$artWork['artwork_tags']}')">Edit Post</a>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            href="#" onclick="deleteArtwork('{$artWork['artwork_unique_id']}')">Delete Post</a>
                                    </li>
                                </ul>
                            HTML;
            }

            if ($creatorInfo['unique_id'] !== $current_user_id) {
                $hireMeStatus .= <<<HTML
                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                    data-bs-toggle="collapse" onclick="hireMe('{$creatorInfo['unique_id']}')">
                                    <i class="fas fa-briefcase me-3"></i>
                                    <p class="m-0">Hire Me</p>
                                </div>
                            HTML;
            }


            // Append task HTML markup to the output string using heredoc syntax
            $posts .= <<<HTML
                <!-- p 1 -->
                <div class="bg-white p-4 rounded mt-3">
                            <!-- author -->
                            <div class="d-flex justify-content-between">
                                <!-- avatar -->
                                <div class="d-flex">
                                    {$profilePicture}
                                    
                                    <div>
                                        <p class="m-0 fw-bold">{$posterFullName}</p>
                                        <span class="text-muted fs-7">{$posterDate}</span>
                                    </div>
                                </div>
                                <!-- edit -->
                                {$editPost}
                            </div>
                            <!-- post content -->
                            <div class="mt-3">
                                <!-- content -->
                                <div>
                                    <p>
                                        {$artWork['caption']}
                                    </p>
                                    <img src="../images/artworks/$artWorkCover" alt="post image"
                                        class="img-fluid rounded" data-bs-toggle="modal" data-bs-target="#viewPostModal" 
                                        onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                        '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                </div>
                                <!-- likes & comments -->
                                <div class="post__comment mt-3 position-relative">
                                    <!-- likes -->
                                    <div class="d-flex align-items-center top-0 start-0 position-absolute"
                                        style="height: 50px; z-index: 1">
                                        <div class="me-2">
                                            <i class="text-primary fas fa-thumbs-up"></i>
                                        </div>
                                        <p class="m-0 text-muted fs-7" id="$likeCountID">{$likeCount}</p>
                                    </div>
                                    <!-- comments start-->
                                    <div class="accordion" id="accordionExample">
                                        <div class="accordion-item border-0">
                                            <!-- comment collapse -->
                                            <h2 class="accordion-header" id="headingTwo">
                                                <div class="accordion-button collapsed pointer d-flex justify-content-end"
                                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                                    onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                                    '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}')">
                                                    <p class="m-0" id="$commentCountID">{$commentCount}</p> 
                                                </div>
                                            </h2>
                                            <hr />
                                            <!-- comment & like bar -->
                                            <div class="d-flex justify-content-around">
                                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1" onclick="verifyLikeStatus('{$artWork['artwork_unique_id']}','{$artWork['creator_user_id']}')" id="$likeBtnID">
                                                    {$likeStatusDisplay}
                                                </div>
                                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                                    onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                                    '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}')">
                                                    <i class="fas fa-comment-alt me-3"></i>
                                                    <p class="m-0">Comment</p>
                                                </div>
                                                {$hireMeStatus}
                                            </div>
                                            <!-- comment expand -->
                                            <div id="collapsePost1" class="accordion-collapse collapse"
                                                aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                <hr />
                                                <div class="accordion-body">
                                                    <!-- comment 1 -->
                                                    <div class="d-flex align-items-center my-1">
                                                        <!-- avatar -->
                                                        <img src="https://source.unsplash.com/collection/happy-people"
                                                            alt="avatar" class="rounded-circle me-2"
                                                            style="width: 38px; height: 38px; object-fit: cover;" />
                                                        <!-- comment text -->
                                                        <div class="p-2 rounded comment__input w-100">
                                                            <!-- comment menu of author -->
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <p class="fw-bold m-0">Jhezryl</p>
                                                                <div class="d-flex justify-content-end">
                                                                    <!-- icon -->
                                                                    <i class="fas fa-ellipsis-h text-blue pointer"
                                                                        id="post1CommentMenuButton"
                                                                        data-bs-toggle="dropdown" aria-expanded="false"></i>
                                                                    <!-- menu -->
                                                                    <ul class="dropdown-menu border-0 shadow"
                                                                        aria-labelledby="post1CommentMenuButton">
                                                                        <li class="d-flex align-items-center">
                                                                            <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                                                href="#">Edit Comment</a>
                                                                        </li>
                                                                        <li class="d-flex align-items-center">
                                                                            <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                                                href="#">Delete Comment</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <p class="m-0 fs-7 bg-gray p-2 rounded">
                                                                Lorem ipsum dolor sit amet, consectetur
                                                                adipiscing elit.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <!-- comment 2 -->
                                                    <div class="d-flex align-items-center my-1">
                                                        <!-- avatar -->
                                                        <img src="https://source.unsplash.com/random/2" alt="avatar"
                                                            class="rounded-circle me-2"
                                                            style="width: 38px; height: 38px;object-fit: cover;" />
                                                        <!-- comment text -->
                                                        <div class="p-3 rounded comment__input w-100">
                                                            <p class="fw-bold m-0">Darold</p>
                                                            <p class="m-0 fs-7 bg-gray p-2 rounded">
                                                                Lorem ipsum dolor sit amet, consectetur
                                                                adipiscing elit.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <!-- create comment -->
                                                    <form class="d-flex my-1">
                                                        <!-- avatar -->
                                                        <div>
                                                            <img src="https://source.unsplash.com/collection/happy-people"
                                                                alt="avatar" class="rounded-circle me-2"
                                                                style="width: 38px; height: 38px; object-fit: cover;" />
                                                        </div>
                                                        <!-- input -->
                                                        <input type="text"
                                                            class="form-control border-0 rounded-pill bg-gray"
                                                            placeholder="Write a comment" />
                                                    </form>
                                                    <!-- end -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end -->
                                </div>
                            </div>
                        </div>
                <!-- end of p1-->
                HTML;
        }

        return $posts;
    }

    public function getArtWorksTimelineB($current_user_id, $search_query)
    {

        $searchQuerySegment = explode(",", $search_query);

        $graphicDesignCondition = in_array("Graphic Design", $searchQuerySegment) ? "Graphic Design" : "lkjskldjfksjdf";
        $photographyCondition = in_array("Photography", $searchQuerySegment) ? "Photography" : "lkjskldjfksjdf";
        $fineArtsCondition = in_array("Fine Arts", $searchQuerySegment) ? "Fine Arts" : "lkjskldjfksjdf";
        $interiorDesignCondition = in_array("Interior Design", $searchQuerySegment) ? "Interior Design" : "lkjskldjfksjdf";
        $iconDesignCondition = in_array("Icon Design", $searchQuerySegment) ? "Icon Design" : "lkjskldjfksjdf";
        $streetArtCondition = in_array("Street Art", $searchQuerySegment) ? "Street Art" : "lkjskldjfksjdf";
        $uiUxCondition = in_array("UI/UX", $searchQuerySegment) ? "UI/UX" : "lkjskldjfksjdf";
        $typographyCondition = in_array("Typography", $searchQuerySegment) ? "Typography" : "lkjskldjfksjdf";
        $wigStyleCondition = in_array("Wig Style", $searchQuerySegment) ? "Wig Style" : "lkjskldjfksjdf";
        $photoManipulationCondition = in_array("Photo Manipulation", $searchQuerySegment) ? "Photo Manipulation" : "lkjskldjfksjdf";
        $makeUpCondition = in_array("Makeup", $searchQuerySegment) ? "Makeup" : "lkjskldjfksjdf";
        $digitalArtsCondition = in_array("Digital Arts", $searchQuerySegment) ? "Digital Arts" : "lkjskldjfksjdf";

        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        $query = $connection->prepare("SELECT * FROM (
            SELECT a.*
            FROM followers f
            JOIN artworks a ON f.user_following_id = a.creator_user_id
            WHERE f.user_follower_id = :current_user_id 
            AND 
                (a.artwork_tags LIKE '%$graphicDesignCondition%' OR 
                a.artwork_tags LIKE '%$photographyCondition%' OR
                a.artwork_tags LIKE '%$fineArtsCondition%'  OR
                a.artwork_tags LIKE '%$interiorDesignCondition%'  OR
                a.artwork_tags LIKE '%$iconDesignCondition%'  OR
                a.artwork_tags LIKE '%$digitalArtsCondition%'  OR
                a.artwork_tags LIKE '%$makeUpCondition%'  OR
                a.artwork_tags LIKE '%$photoManipulationCondition%'  OR
                a.artwork_tags LIKE '%$wigStyleCondition%'  OR
                a.artwork_tags LIKE '%$typographyCondition%'  OR
                a.artwork_tags LIKE '%$streetArtCondition%'  OR
                a.artwork_tags LIKE '%$uiUxCondition%' )
            UNION
            SELECT a.*
            FROM artworks a
            JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
            WHERE t.tag_user_id = :current_user_id 
            AND 
                (a.artwork_tags LIKE '%$graphicDesignCondition%' OR 
                a.artwork_tags LIKE '%$photographyCondition%' OR
                a.artwork_tags LIKE '%$fineArtsCondition%'  OR
                a.artwork_tags LIKE '%$interiorDesignCondition%'  OR
                a.artwork_tags LIKE '%$iconDesignCondition%'  OR
                a.artwork_tags LIKE '%$digitalArtsCondition%'  OR
                a.artwork_tags LIKE '%$makeUpCondition%'  OR
                a.artwork_tags LIKE '%$photoManipulationCondition%'  OR
                a.artwork_tags LIKE '%$wigStyleCondition%'  OR
                a.artwork_tags LIKE '%$streetArtCondition%'  OR
                a.artwork_tags LIKE '%$typographyCondition%'  OR
                a.artwork_tags LIKE '%$uiUxCondition%' )
            UNION
            SELECT *
            FROM artworks
            WHERE creator_user_id = :current_user_id 
            AND 
            (
                artwork_tags LIKE '%$graphicDesignCondition%' OR 
                artwork_tags LIKE '%$photographyCondition%' OR
                artwork_tags LIKE '%$fineArtsCondition%'  OR
                artwork_tags LIKE '%$interiorDesignCondition%'  OR
                artwork_tags LIKE '%$iconDesignCondition%'  OR
                artwork_tags LIKE '%$digitalArtsCondition%'  OR
                artwork_tags LIKE '%$makeUpCondition%'  OR
                artwork_tags LIKE '%$photoManipulationCondition%'  OR
                artwork_tags LIKE '%$wigStyleCondition%'  OR
                artwork_tags LIKE '%$typographyCondition%'  OR
                artwork_tags LIKE '%$streetArtCondition%'  OR
                artwork_tags LIKE '%$uiUxCondition%' )
        ) AS combined_results
        ORDER BY date_created DESC;");
        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        // $query = $connection->prepare("SELECT * FROM (
        //                                 SELECT a.*
        //                                 FROM followers f
        //                                 JOIN artworks a ON f.user_following_id = a.creator_user_id
        //                                 WHERE f.user_follower_id = :current_user_id
        //                                 UNION
        //                                 SELECT a.*
        //                                 FROM artworks a
        //                                 JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
        //                                 WHERE t.tag_user_id = :current_user_id
        //                                 UNION
        //                                 SELECT *
        //                                 FROM artworks
        //                                 WHERE creator_user_id = :current_user_id
        //                             ) AS combined_results
        //                             ORDER BY date_created DESC;");

        // // Bind parameters
        $query->bindParam(':current_user_id', $current_user_id);

        // Execute the query
        $query->execute();

        // Fetch all results
        $artWorks = $query->fetchAll();

        $posts = '';

        // Iterate through each artwork
        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $current_user_id); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $likeStatusBool = '';
            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }

            //like status display data
            if ($likeStatus) {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3 text-primary" ></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'true';
            } else {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3"></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'false';
            }

            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }


            if ($creatorInfo['profile_photo']) {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/' . $creatorInfo['profile_photo'] . '" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            } else {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/defaultProfilePicture.png" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            }


            if ($creatorInfo['unique_id'] === $current_user_id) {
                $editPost .= <<<HTML
                                <i class="fas fa-ellipsis-h" type="button" id="post1Menu" data-bs-toggle="dropdown"
                                    aria-expanded="false"></i>
                                <!-- edit menu -->
                                <ul class="dropdown-menu border-0 shadow" aria-labelledby="post1Menu">
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="editArtwork('{$artWork['artwork_unique_id']}','{$artWork['caption']}','{$artWork['artwork_tags']}')">Edit Post</a>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            href="#" onclick="deleteArtwork('{$artWork['artwork_unique_id']}')">Delete Post</a>
                                    </li>
                                </ul>
                            HTML;
            }

            if ($creatorInfo['unique_id'] !== $current_user_id) {
                $hireMeStatus .= <<<HTML
                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                    data-bs-toggle="collapse" onclick="hireMe('{$creatorInfo['unique_id']}')">
                                    <i class="fas fa-briefcase me-3"></i>
                                    <p class="m-0">Hire Me</p>
                                </div>
                            HTML;
            }


            // Append task HTML markup to the output string using heredoc syntax
            $posts .= <<<HTML
                <!-- p 1 -->
                <div class="bg-white p-4 rounded mt-3">
                            <!-- author -->
                            <div class="d-flex justify-content-between">
                                <!-- avatar -->
                                <div class="d-flex">
                                    {$profilePicture}
                                    
                                    <div>
                                        <p class="m-0 fw-bold">{$posterFullName}</p>
                                        <span class="text-muted fs-7">{$posterDate}</span>
                                    </div>
                                </div>
                                <!-- edit -->
                                {$editPost}
                            </div>
                            <!-- post content -->
                            <div class="mt-3">
                                <!-- content -->
                                <div>
                                    <p>
                                        {$artWork['caption']}
                                    </p>
                                    <img src="../images/artworks/$artWorkCover" alt="post image"
                                        class="img-fluid rounded" data-bs-toggle="modal" data-bs-target="#viewPostModal" 
                                        onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                        '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                </div>
                                <!-- likes & comments -->
                                <div class="post__comment mt-3 position-relative">
                                    <!-- likes -->
                                    <div class="d-flex align-items-center top-0 start-0 position-absolute"
                                        style="height: 50px; z-index: 1">
                                        <div class="me-2">
                                            <i class="text-primary fas fa-thumbs-up"></i>
                                        </div>
                                        <p class="m-0 text-muted fs-7" id="$likeCountID">{$likeCount}</p>
                                    </div>
                                    <!-- comments start-->
                                    <div class="accordion" id="accordionExample">
                                        <div class="accordion-item border-0">
                                            <!-- comment collapse -->
                                            <h2 class="accordion-header" id="headingTwo">
                                                <div class="accordion-button collapsed pointer d-flex justify-content-end"
                                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                                    onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                                    '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}')">
                                                    <p class="m-0" id="$commentCountID">{$commentCount}</p> 
                                                </div>
                                            </h2>
                                            <hr />
                                            <!-- comment & like bar -->
                                            <div class="d-flex justify-content-around">
                                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1" onclick="verifyLikeStatus('{$artWork['artwork_unique_id']}','{$artWork['creator_user_id']}')" id="$likeBtnID">
                                                    {$likeStatusDisplay}
                                                </div>
                                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                                    onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                                    '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}')">
                                                    <i class="fas fa-comment-alt me-3"></i>
                                                    <p class="m-0">Comment</p>
                                                </div>
                                                {$hireMeStatus}
                                            </div>
                                            <!-- comment expand -->
                                            <div id="collapsePost1" class="accordion-collapse collapse"
                                                aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                <hr />
                                                <div class="accordion-body">
                                                    <!-- comment 1 -->
                                                    <div class="d-flex align-items-center my-1">
                                                        <!-- avatar -->
                                                        <img src="https://source.unsplash.com/collection/happy-people"
                                                            alt="avatar" class="rounded-circle me-2"
                                                            style="width: 38px; height: 38px; object-fit: cover;" />
                                                        <!-- comment text -->
                                                        <div class="p-2 rounded comment__input w-100">
                                                            <!-- comment menu of author -->
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <p class="fw-bold m-0">Jhezryl</p>
                                                                <div class="d-flex justify-content-end">
                                                                    <!-- icon -->
                                                                    <i class="fas fa-ellipsis-h text-blue pointer"
                                                                        id="post1CommentMenuButton"
                                                                        data-bs-toggle="dropdown" aria-expanded="false"></i>
                                                                    <!-- menu -->
                                                                    <ul class="dropdown-menu border-0 shadow"
                                                                        aria-labelledby="post1CommentMenuButton">
                                                                        <li class="d-flex align-items-center">
                                                                            <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                                                href="#">Edit Comment</a>
                                                                        </li>
                                                                        <li class="d-flex align-items-center">
                                                                            <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                                                href="#">Delete Comment</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <p class="m-0 fs-7 bg-gray p-2 rounded">
                                                                Lorem ipsum dolor sit amet, consectetur
                                                                adipiscing elit.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <!-- comment 2 -->
                                                    <div class="d-flex align-items-center my-1">
                                                        <!-- avatar -->
                                                        <img src="https://source.unsplash.com/random/2" alt="avatar"
                                                            class="rounded-circle me-2"
                                                            style="width: 38px; height: 38px;object-fit: cover;" />
                                                        <!-- comment text -->
                                                        <div class="p-3 rounded comment__input w-100">
                                                            <p class="fw-bold m-0">Darold</p>
                                                            <p class="m-0 fs-7 bg-gray p-2 rounded">
                                                                Lorem ipsum dolor sit amet, consectetur
                                                                adipiscing elit.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <!-- create comment -->
                                                    <form class="d-flex my-1">
                                                        <!-- avatar -->
                                                        <div>
                                                            <img src="https://source.unsplash.com/collection/happy-people"
                                                                alt="avatar" class="rounded-circle me-2"
                                                                style="width: 38px; height: 38px; object-fit: cover;" />
                                                        </div>
                                                        <!-- input -->
                                                        <input type="text"
                                                            class="form-control border-0 rounded-pill bg-gray"
                                                            placeholder="Write a comment" />
                                                    </form>
                                                    <!-- end -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end -->
                                </div>
                            </div>
                        </div>
                <!-- end of p1-->
                HTML;
        }

        return $posts;
    }

    public function getAllForSaleArtWorksTimeline($current_user_id, $search_query)
    {

        $searchQuerySegment = explode(",", $search_query);

        $graphicDesignCondition = in_array("Graphic Design", $searchQuerySegment) ? "Graphic Design" : "lkjskldjfksjdf";
        $photographyCondition = in_array("Photography", $searchQuerySegment) ? "Photography" : "lkjskldjfksjdf";
        $fineArtsCondition = in_array("Fine Arts", $searchQuerySegment) ? "Fine Arts" : "lkjskldjfksjdf";
        $interiorDesignCondition = in_array("Interior Design", $searchQuerySegment) ? "Interior Design" : "lkjskldjfksjdf";
        $iconDesignCondition = in_array("Icon Design", $searchQuerySegment) ? "Icon Design" : "lkjskldjfksjdf";
        $streetArtCondition = in_array("Street Art", $searchQuerySegment) ? "Street Art" : "lkjskldjfksjdf";
        $uiUxCondition = in_array("UI/UX", $searchQuerySegment) ? "UI/UX" : "lkjskldjfksjdf";
        $typographyCondition = in_array("Typography", $searchQuerySegment) ? "Typography" : "lkjskldjfksjdf";
        $wigStyleCondition = in_array("Wig Style", $searchQuerySegment) ? "Wig Style" : "lkjskldjfksjdf";
        $photoManipulationCondition = in_array("Photo Manipulation", $searchQuerySegment) ? "Photo Manipulation" : "lkjskldjfksjdf";
        $makeUpCondition = in_array("Makeup", $searchQuerySegment) ? "Makeup" : "lkjskldjfksjdf";
        $digitalArtsCondition = in_array("Digital Arts", $searchQuerySegment) ? "Digital Arts" : "lkjskldjfksjdf";

        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        $query = $connection->prepare("SELECT * FROM (
            SELECT a.*
            FROM followers f
            JOIN artworks a ON f.user_following_id = a.creator_user_id
            WHERE f.user_follower_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            AND 
                (a.artwork_tags LIKE '%$graphicDesignCondition%' OR 
                a.artwork_tags LIKE '%$photographyCondition%' OR
                a.artwork_tags LIKE '%$fineArtsCondition%'  OR
                a.artwork_tags LIKE '%$interiorDesignCondition%'  OR
                a.artwork_tags LIKE '%$iconDesignCondition%'  OR
                a.artwork_tags LIKE '%$digitalArtsCondition%'  OR
                a.artwork_tags LIKE '%$makeUpCondition%'  OR
                a.artwork_tags LIKE '%$photoManipulationCondition%'  OR
                a.artwork_tags LIKE '%$wigStyleCondition%'  OR
                a.artwork_tags LIKE '%$typographyCondition%'  OR
                a.artwork_tags LIKE '%$streetArtCondition%'  OR
                a.artwork_tags LIKE '%$uiUxCondition%' )
            UNION
            SELECT a.*
            FROM artworks a
            JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
            WHERE t.tag_user_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            AND 
                (a.artwork_tags LIKE '%$graphicDesignCondition%' OR 
                a.artwork_tags LIKE '%$photographyCondition%' OR
                a.artwork_tags LIKE '%$fineArtsCondition%'  OR
                a.artwork_tags LIKE '%$interiorDesignCondition%'  OR
                a.artwork_tags LIKE '%$iconDesignCondition%'  OR
                a.artwork_tags LIKE '%$digitalArtsCondition%'  OR
                a.artwork_tags LIKE '%$makeUpCondition%'  OR
                a.artwork_tags LIKE '%$photoManipulationCondition%'  OR
                a.artwork_tags LIKE '%$wigStyleCondition%'  OR
                a.artwork_tags LIKE '%$streetArtCondition%'  OR
                a.artwork_tags LIKE '%$typographyCondition%'  OR
                a.artwork_tags LIKE '%$uiUxCondition%' )
            UNION
            SELECT *
            FROM artworks
            WHERE creator_user_id = :current_user_id 
            AND artwork_status = 'sale' -- Filter by artwork_status
            AND 
            (
                artwork_tags LIKE '%$graphicDesignCondition%' OR 
                artwork_tags LIKE '%$photographyCondition%' OR
                artwork_tags LIKE '%$fineArtsCondition%'  OR
                artwork_tags LIKE '%$interiorDesignCondition%'  OR
                artwork_tags LIKE '%$iconDesignCondition%'  OR
                artwork_tags LIKE '%$digitalArtsCondition%'  OR
                artwork_tags LIKE '%$makeUpCondition%'  OR
                artwork_tags LIKE '%$photoManipulationCondition%'  OR
                artwork_tags LIKE '%$wigStyleCondition%'  OR
                artwork_tags LIKE '%$typographyCondition%'  OR
                artwork_tags LIKE '%$streetArtCondition%'  OR
                artwork_tags LIKE '%$uiUxCondition%' )
        ) AS combined_results
        ORDER BY date_created DESC;");

        // $query = $connection->prepare("SELECT * FROM artworks WHERE artwork_status = `sale`
        // ORDER BY date_created DESC;");

        // Bind parameters
        $query->bindParam(':current_user_id', $current_user_id);
        // $query->bindParam(':someCondition', $someCondition);
        // $query->bindParam(':someCondition2', $someCondition2);

        // Execute the query
        $query->execute();

        // Fetch all results
        $artWorks = $query->fetchAll();

        $posts = '';

        // Iterate through each artwork
        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artworkPrice = $artWork['price'];
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $current_user_id); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $likeStatusBool = '';
            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }

            //like status display data
            if ($likeStatus) {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3 text-primary" ></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'true';
            } else {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3"></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'false';
            }

            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }


            if ($creatorInfo['profile_photo']) {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/' . $creatorInfo['profile_photo'] . '" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            } else {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/defaultProfilePicture.png" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            }


            if ($creatorInfo['unique_id'] === $current_user_id) {
                $editPost .= <<<HTML
                                <i class="fas fa-ellipsis-h" type="button" id="post1Menu" data-bs-toggle="dropdown"
                                    aria-expanded="false"></i>
                                <!-- edit menu -->
                                <ul class="dropdown-menu border-0 shadow" aria-labelledby="post1Menu">
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="editArtwork('{$artWork['artwork_unique_id']}','{$artWork['caption']}','{$artWork['artwork_tags']}')">Edit Post</a>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            href="#" onclick="deleteArtwork('{$artWork['artwork_unique_id']}')">Delete Post</a>
                                    </li>
                                </ul>
                            HTML;
            }

            if ($creatorInfo['unique_id'] !== $current_user_id) {
                $hireMeStatus .= <<<HTML
                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                    data-bs-toggle="collapse" onclick="hireMe('{$creatorInfo['unique_id']}')">
                                    <i class="fas fa-briefcase me-3"></i>
                                    <p class="m-0">Hire Me</p>
                                </div>
                            HTML;
            }


            // Append task HTML markup to the output string using heredoc syntax
            // Append task HTML markup to the output string using heredoc syntax
            $posts .= <<<HTML
             <div class="artbotique-img-container" style="width: 450px; height: auto; position: relative;">
                 <img class="artbotique-img" style="transition: all 0.3s ease; max-width: 100%;max-height: 100%; margin-bottom: 10px; border-radius: 10px" src="../images/artworks/$artWorkCover" alt="post image"
                                        data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                         onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                         '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                         <p class="artbotique-img-hovered-text" style="font-size: 2.25rem; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)">  $artworkPrice </p>
             </div>
                             
             HTML;
        }

        return $posts;
    }

    public function getAllForSaleArtWorksTimelineV2($current_user_id)
    {

        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        $query = $connection->prepare("SELECT * FROM (
            SELECT a.*
            FROM followers f
            JOIN artworks a ON f.user_following_id = a.creator_user_id
            WHERE f.user_follower_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            UNION
            SELECT a.*
            FROM artworks a
            JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
            WHERE t.tag_user_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            UNION
            SELECT *
            FROM artworks
            WHERE creator_user_id = :current_user_id 
            AND artwork_status = 'sale' -- Filter by artwork_status
        ) AS combined_results
        ORDER BY date_created DESC;");

        // Bind parameters
        $query->bindParam(':current_user_id', $current_user_id);

        // Execute the query
        $query->execute();

        // Fetch all results
        $artWorks = $query->fetchAll();

        $posts = '';

        // Iterate through each artwork
        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artworkPrice = $artWork['price'];
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $current_user_id); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $likeStatusBool = '';
            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }

            //like status display data
            if ($likeStatus) {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3 text-primary" ></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'true';
            } else {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3"></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'false';
            }

            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }


            if ($creatorInfo['profile_photo']) {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/' . $creatorInfo['profile_photo'] . '" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            } else {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/defaultProfilePicture.png" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            }


            if ($creatorInfo['unique_id'] === $current_user_id) {
                $editPost .= <<<HTML
                                <i class="fas fa-ellipsis-h" type="button" id="post1Menu" data-bs-toggle="dropdown"
                                    aria-expanded="false"></i>
                                <!-- edit menu -->
                                <ul class="dropdown-menu border-0 shadow" aria-labelledby="post1Menu">
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="editArtwork('{$artWork['artwork_unique_id']}','{$artWork['caption']}','{$artWork['artwork_tags']}')">Edit Post</a>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            href="#" onclick="deleteArtwork('{$artWork['artwork_unique_id']}')">Delete Post</a>
                                    </li>
                                </ul>
                            HTML;
            }

            if ($creatorInfo['unique_id'] !== $current_user_id) {
                $hireMeStatus .= <<<HTML
                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                    data-bs-toggle="collapse" onclick="hireMe('{$creatorInfo['unique_id']}')">
                                    <i class="fas fa-briefcase me-3"></i>
                                    <p class="m-0">Hire Me</p>
                                </div>
                            HTML;
            }


            // Append task HTML markup to the output string using heredoc syntax
            // Append task HTML markup to the output string using heredoc syntax
            $posts .= <<<HTML
             <div class="artbotique-img-container" style="width: 450px; height: auto; position: relative;">
                 <img class="artbotique-img" style="transition: all 0.3s ease; max-width: 100%;max-height: 100%; margin-bottom: 10px; border-radius: 10px" src="../images/artworks/$artWorkCover" alt="post image"
                                        data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                         onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                         '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                         <p class="artbotique-img-hovered-text" style="font-size: 2.25rem; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)">  $artworkPrice </p>
             </div>
                             
             HTML;
        }

        return $posts;
    }

    public function getAllTagsElement($current_user_id)
    {

        // Open database connection
        $connection = $this->openConnection();

        // Prepare the SQL query
        $query = $connection->prepare("SELECT * FROM (
            SELECT a.*
            FROM followers f
            JOIN artworks a ON f.user_following_id = a.creator_user_id
            WHERE f.user_follower_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            -- AND a.artwork_tags =  'Graphic Design'
            UNION
            SELECT a.*
            FROM artworks a
            JOIN user_tags t ON FIND_IN_SET(t.tag_desc, a.artwork_tags) > 0
            WHERE t.tag_user_id = :current_user_id 
            AND a.artwork_status = 'sale' -- Filter by artwork_status
            -- AND a.artwork_tags =  'Graphic Design'
            UNION
            SELECT *
            FROM artworks
            WHERE creator_user_id = :current_user_id 
            AND artwork_status = 'sale' -- Filter by artwork_status
            -- AND a.artwork_tags =  'Graphic Design'
        ) AS combined_results
        ORDER BY date_created DESC;");

        // $query = $connection->prepare("SELECT * FROM artworks WHERE artwork_status = `sale`
        // ORDER BY date_created DESC;");

        // Bind parameters
        $query->bindParam(':current_user_id', $current_user_id);

        // Execute the query
        $query->execute();

        // Fetch all results
        $artWorks = $query->fetchAll();

        $posts = '';

        // Iterate through each artwork
        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artworkPrice = $artWork['price'];
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $current_user_id); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $likeStatusBool = '';
            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }

            //like status display data
            if ($likeStatus) {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3 text-primary" ></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'true';
            } else {
                $likeStatusDisplay .= '<i class="fas fa-thumbs-up me-3"></i> <p class="m-0">Like</p>';
                $likeStatusBool = 'false';
            }

            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }


            if ($creatorInfo['profile_photo']) {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/' . $creatorInfo['profile_photo'] . '" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            } else {
                $profilePicture .= '<a href="profile.php?U=' . $creatorInfo['unique_id'] . '">' .
                    '<img src="../images/profilePicture/defaultProfilePicture.png" ' .
                    'alt="avatar" class="rounded-circle me-2" ' .
                    'style="width: 38px; height: 38px; object-fit: cover;" />' .
                    '</a>';
            }


            if ($creatorInfo['unique_id'] === $current_user_id) {
                $editPost .= <<<HTML
                                <i class="fas fa-ellipsis-h" type="button" id="post1Menu" data-bs-toggle="dropdown"
                                    aria-expanded="false"></i>
                                <!-- edit menu -->
                                <ul class="dropdown-menu border-0 shadow" aria-labelledby="post1Menu">
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            data-bs-toggle="modal" data-bs-target="#editPostModal" onclick="editArtwork('{$artWork['artwork_unique_id']}','{$artWork['caption']}','{$artWork['artwork_tags']}')">Edit Post</a>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                            href="#" onclick="deleteArtwork('{$artWork['artwork_unique_id']}')">Delete Post</a>
                                    </li>
                                </ul>
                            HTML;
            }

            if ($creatorInfo['unique_id'] !== $current_user_id) {
                $hireMeStatus .= <<<HTML
                                <div class="dropdown-item rounded d-flex justify-content-center align-items-center pointer text-mutedp-1"
                                    data-bs-toggle="collapse" onclick="hireMe('{$creatorInfo['unique_id']}')">
                                    <i class="fas fa-briefcase me-3"></i>
                                    <p class="m-0">Hire Me</p>
                                </div>
                            HTML;
            }


            // Append task HTML markup to the output string using heredoc syntax
            // Append task HTML markup to the output string using heredoc syntax
            $posts .= <<<HTML
             <div class="artbotique-img-container" style="width: 450px; height: auto; position: relative;">
                 <img class="artbotique-img" style="transition: all 0.3s ease; max-width: 100%;max-height: 100%; margin-bottom: 10px; border-radius: 10px" src="../images/artworks/$artWorkCover" alt="post image"
                                        data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                         onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}',
                                         '{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                         <p class="artbotique-img-hovered-text" style="font-size: 2.25rem; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)">  $artworkPrice </p>
             </div>
                             
             HTML;
        }

        return $posts;
    }
    //get the time difference
    public function calculateTimeDifference($artwork_date_created)
    {
        // Convert artwork_date_created to a Unix timestamp
        $artwork_timestamp = strtotime($artwork_date_created);

        // Get current Unix timestamp
        $current_timestamp = time();

        // Calculate the difference in seconds
        $difference = $current_timestamp - $artwork_timestamp;

        // If the difference is less than or equal to 4 days (345600 seconds)
        if ($difference <= 345600) {
            // If the difference is less than a minute
            if ($difference < 60) {
                return $difference . "s ago";
            }
            // If the difference is less than an hour
            elseif ($difference < 3600) {
                $minutes = floor($difference / 60);
                return $minutes . "m ago";
            }
            // If the difference is less than a day
            elseif ($difference < 86400) {
                $hours = floor($difference / 3600);
                return $hours . "h ago";
            }
            // If the difference is less than 2 days
            elseif ($difference < 172800) {
                return "1d ago";
            }
            // If the difference is less than 3 days
            elseif ($difference < 259200) {
                return "2d ago";
            }
            // If the difference is less than or equal to 4 days
            else {
                return "3d ago";
            }
        } else {
            // If the difference is more than 4 days
            $rawDateData = new DateTime($artwork_date_created);
            $posterDate = $rawDateData->format('F j \a\t g:i a');

            return $posterDate;
        }
    }

    public function getCreatorInfo($creatorID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `users` WHERE `unique_id`= ?");
        $query->execute([$creatorID]);
        $commenter = $query->fetch();

        return $commenter;
    }
    //function for number of likes
    public function getNumberOfProjectLikes($user_id)
    {
        // Open a database connection
        $connection = $this->openConnection();
        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("SELECT * FROM `artworks_likes` WHERE `creator_user_id`= ?");
        $query->execute([$user_id]);
        $noOfLikes = $query->rowCount();
        // Return true if the tag insertion is successful, false otherwise
        return $noOfLikes;
    }

    //Function to get the first comment of the artwork
    public function getFirstComment($artwork_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks_comments` WHERE `artwork_mother_id`= ? ORDER BY `date_created` DESC");
        $query->execute([$artwork_id]);
        $comment = $query->fetch();

        return $comment;
    }

    //Function to get the cover image of the artwork
    public function getArtworkCoverPhoto($artwork_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM artworks_images WHERE artwork_mother_id = ?  ORDER BY artwork_image_id LIMIT 1;");
        $query->execute([$artwork_id]);
        $coverImage = $query->fetch();

        return $coverImage;
    }

    //Function to get the price of the artwork
    public function getArtworkPrice($artwork_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM artworks_images WHERE artwork_mother_id = ?  ORDER BY artwork_image_id LIMIT 1;");
        $query->execute([$artwork_id]);
        $coverImage = $query->fetch();

        return $coverImage;
    }


    //Function to get the commenter infoi
    public function getOtherUserInfo($otherUser)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `users` WHERE `unique_id`= ?");
        $query->execute([$otherUser]);
        $commenter = $query->fetch();

        return $commenter;
    }

    //Function to get all ArtWork Images
    public function getArtWorkImages($artwork_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks_images` WHERE `artwork_mother_id`= ?");
        $query->execute([$artwork_id]);
        $artWorkImages = $query->fetchAll();
        $imagesOutput = '';
        $counter = 0;
        foreach ($artWorkImages as $artWorkImage) {
            $image = $artWorkImage['image_name'];
            if ($counter === 0) {
                $imagesOutput .= <<<HTML
                            <div class="carousel-item active">
                                <div class="d-flex  justify-content-center align-items-center">
                                    <img src="../images/artworks/$image" class="img-fluid" alt="avatar" style="max-width: 1800px; max-height: 800px; object-fit: cover;">
                                </div>
                            </div>
                        HTML;
            } else {
                $imagesOutput .= <<<HTML
                            <div class="carousel-item">
                                <div class="d-flex  justify-content-center align-items-center">
                                    <img src="../images/artworks/$image" class="img-fluid" alt="avatar" style="max-width: 1800px; max-height: 800px; object-fit: cover;">
                                </div>
                            </div>
                        HTML;
            }
            $counter++;
        }

        return $imagesOutput;
    }

    //function to delete artworks images
    public function deleteArtWorkImages($artwork_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `artworks_images` WHERE `artwork_mother_id` = ?");
        $result = $query->execute([$artwork_id]);

        return $result;
    }

    //function to update the existing artworks
    public function updateArtWork($artwork_id, $caption, $artwork_tags)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `artworks` SET `caption`= ? ,`artwork_tags`= ?, `date_created` = CURRENT_TIMESTAMP() WHERE `artwork_unique_id`= ?");
        $result = $query->execute([$caption, $artwork_tags, $artwork_id]);

        return $result;
    }
    //function to delete Artworks
    public function deleteArtWorks($artwork_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `artworks` WHERE `artwork_unique_id` = ?");
        $result = $query->execute([$artwork_id]);

        $deleteImages = $this->deleteArtWorkImages($artwork_id);
        $deleteComments = $this->deleteComment($artwork_id);
        $deleteLikes = $this->deleteLikes($artwork_id);
        return $result;
    }

    public function deleteLikes($artwork_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `artworks_likes` WHERE `artwork_mother_id`= ?");
        $result = $query->execute([$artwork_id]);

        return $result;
    }

    //Function to get all ArtWork Images
    public function getArtWorkComments($artwork_id, $currentUser_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks_comments` WHERE `artwork_mother_id`= ? ORDER BY `comment_id` DESC");
        $query->execute([$artwork_id]);
        $artWorkComments = $query->fetchAll();
        $commentOutputs = '';


        if (!$artWorkComments) {
            $commentOutputs .= "No Comments Available";
        } else {
            foreach ($artWorkComments as $artWorkComment) {

                $commenterInfo = $this->getOtherUserInfo($artWorkComment['commenter_id']);
                $commenterProfilepic = '';
                $commenterFirstName = ucfirst($commenterInfo['first_name']);
                $commentID = $artWorkComment['comment_id'];
                $comment = $artWorkComment['comment'];

                if ($commenterInfo['profile_photo']) {
                    $commenterProfilepic .= '<a href="profile.php?U=' . $commenterInfo['unique_id'] . '">' .
                        '<img src="../images/profilePicture/' . $commenterInfo['profile_photo'] . '" ' .
                        'alt="avatar" class="rounded-circle me-2" ' .
                        'style="width: 38px; height: 38px; object-fit: cover;" />' .
                        '</a>';
                } else {
                    $commenterProfilepic .= '<a href="profile.php?U=' . $commenterInfo['unique_id'] . '">' .
                        '<img src="../images/profilePicture/defaultProfilePicture.png"' .
                        'alt="avatar" class="rounded-circle me-2" ' .
                        'style="width: 38px; height: 38px; object-fit: cover;" />' .
                        '</a>';
                }

                //Check if the comment is the current user 
                if ($artWorkComment['commenter_id'] === $currentUser_id) {

                    $commentOutputs .= <<<HTML
                        <div class="chat-item p-1 mt-2 rounded">
                            <div class="d-flex justify-content-start">
                                <!-- avatar -->
                                {$commenterProfilepic}
                                <!-- comment menu of author -->
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <p class="fw-bold m-0">{$commenterFirstName}</p>
                                        <input type="hidden" id="commentID" value="$commentID">
                                        
                                        <div class="d-flex justify-content-end">
                                            <!-- icon -->
                                            <i class="fas fa-ellipsis-h text-blue pointer"
                                                id="post1CommentMenuButton"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"></i>
                                            <!-- menu -->
                                            <ul class="dropdown-menu border-0 shadow"
                                                aria-labelledby="post1CommentMenuButton">
                                                <li
                                                    class="d-flex align-items-center">
                                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                        onclick="setEditComment('$commentID', '$comment')">Edit Comment</a>
                                                </li>
                                                <li
                                                    class="d-flex align-items-center">
                                                    <a class="dropdown-item d-flex justify-content-around align-items-center fs-7"
                                                        onclick="deleteComment('$commentID')">Delete Comment</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="text-muted bg-gray p-2 rounded" style="font-size: 14px; max-width: 330px; word-wrap: break-word;">
                                    {$comment}
                                    </p>
                                </div>
                            </div>
                        </div>  
                    HTML;
                } else {
                    $commentOutputs .= <<<HTML
                                <div class="chat-item p-1 mt-2 rounded">
                                    <div class="d-flex justify-content-start">
                                        <!-- avatar -->
                                        {$commenterProfilepic}
                                        <!-- comment text -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="fw-bold m-0">{$commenterFirstName}</p>
                                                <input type="hidden" id="commentID" value="$commentID">

                                            </div>
                                            <p class="text-muted bg-gray p-2 rounded" style="font-size: 14px; max-width: 330px; word-wrap: break-word;">
                                            {$artWorkComment['comment']}
                                            </p>
                                        <div>
                                    </div>
                                </div>  
                                HTML;
                }
            }
        }



        return $commentOutputs;
    }


    //================================================ LIKES FUNCTION ===============================================\\

    //function to get the like count of a post
    public function getNumberOfLikes($artwork_mother_id)
    {

        // Open a database connection
        $connection = $this->openConnection();
        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("SELECT * FROM `artworks_likes` WHERE `artwork_mother_id`= ?");
        $query->execute([$artwork_mother_id]);
        $noOfLikes = $query->rowCount();
        // Return true if the tag insertion is successful, false otherwise
        return $noOfLikes;
    }

    //function to follow a user
    public function likeArtWork($artwork_mother_id, $creator_user_id, $liker_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `artworks_likes`(`artwork_mother_id`, `creator_user_id`, `liker_id`) 
                                        VALUES (?, ?, ?)");
        $result = $query->execute([$artwork_mother_id, $creator_user_id, $liker_id]);

        return $result;
    }

    //function to unfollow a user 
    public function unlikeArtWork($artwork_mother_id, $liker_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `artworks_likes` WHERE `artwork_mother_id`= ? AND `liker_id`= ?");
        $result = $query->execute([$artwork_mother_id, $liker_id]);

        return $result;
    }

    //check if the artwork is like by the current user 
    public function artWorkLikeStatus($artwork_mother_id, $liker_id)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks_likes` WHERE `artwork_mother_id`= ? AND `liker_id` = ?");
        $query->execute([$artwork_mother_id, $liker_id]);
        $followStatus = $query->rowCount();

        if ($followStatus > 0) {
            return true;
        } else {
            return false;
        }
    }
    //================================================ END LIKES FUNCTION ===============================================\\

    //================================================ COMMENTS FUNCTION ===============================================\\

    //Insert comments to the database
    public function insertComments($currentUser_id, $artwork_mother_id, $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture)
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("INSERT INTO `artworks_comments`(`artwork_mother_id`, `comment`, `commenter_id`, `commenter_firstName`, `commenter_lastName`, `commenter_profilePicture`) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
        $result = $query->execute([$artwork_mother_id, $comment, $currentUser_id, $commenter_firstName, $commenter_lastName, $commenter_profilePicture]);

        // Return true if the tag insertion is successful, false otherwise
        return $result;
    }

    //Function to get the number of comments in a post
    public function getNumberOfComments($artwork_mother_id)
    {

        // Open a database connection
        $connection = $this->openConnection();
        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("SELECT * FROM `artworks_comments` WHERE `artwork_mother_id`= ?");
        $query->execute([$artwork_mother_id]);
        $noOfComment = $query->rowCount();
        // Return true if the tag insertion is successful, false otherwise
        return $noOfComment;
    }

    //function to update/edit the comment
    public function editComment($comment_id, $comment)
    {

        // Open a database connection
        $connection = $this->openConnection();
        // Prepare and execute the SQL query to insert tags into the database
        $query = $connection->prepare("UPDATE `artworks_comments` SET `comment`= ? WHERE `comment_id`= ?");
        $result = $query->execute([$comment, $comment_id]);
        // Return true if the tag insertion is successful, false otherwise
        return $result;
    }

    //Function to delete a specific comments
    public function deleteComment($commentID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `artworks_comments` WHERE `comment_id` = ?");
        $result = $query->execute([$commentID]);

        return $result;
    }
    //================================================ END COMMENTS FUNCTION ===============================================\\

    //================================================ FOLLOW FUNCTION ===============================================\\
    //function to follow a user
    public function followUser($followingID, $followerID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `followers`(`user_follower_id`, `user_following_id`) VALUES (?, ?)");
        $result = $query->execute([$followerID, $followingID]);

        return $result;
    }

    //function to unfollow a user 
    public function unfollowUser($followingID, $followerID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `followers` WHERE `user_follower_id`= ? AND `user_following_id`= ?");
        $result = $query->execute([$followerID, $followingID]);

        return $result;
    }

    //check if the current user is a follower of the user he is viewing
    public function checkFollowStatus($followingID, $followerID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `followers` WHERE `user_follower_id`= ? AND `user_following_id` = ?");
        $query->execute([$followerID, $followingID]);
        $followStatus = $query->rowCount();

        if ($followStatus > 0) {
            return true;
        } else {
            return false;
        }
    }
    //number of user's follower
    public function getNumberOfFollowers($userID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `followers` WHERE `user_following_id` = ?");
        $query->execute([$userID]);
        $followerCount = $query->rowCount();

        return $followerCount;
    }

    //================================================ END FOLLOW FUNCTION ===============================================\\
    public function getFirstConvo($currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_convo` WHERE `sender_id`= ? OR `receiver_id` = ? ORDER BY `recent_read_time` DESC LIMIT 1");
        $query->execute([$currentUserID, $currentUserID]);
        $convoHead = $query->fetch();

        return $convoHead;
    }
    //get all the convo that the user have
    public function getAllConvo($currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_convo` WHERE `sender_id`= ? OR `receiver_id` = ? ORDER BY `recent_read_time` DESC");
        $query->execute([$currentUserID, $currentUserID]);
        $convoHeads = $query->fetchAll();
        $conversationList = '';

        foreach ($convoHeads as $convoHead) {
            $chatMateName = '';
            $chatMateFname = '';
            $chatMateStatus = '';
            $statusBadgeUnread = '';
            $statusBadgeRead = '';
            $chatMateID = '';
            $chatMatePhoto = '';
            if ($convoHead['sender_id'] == $currentUserID) {
                $convoHead['recent_read_status'] = "Read";
                $chatMateInfo = $this->getOtherUserInfo($convoHead['receiver_id']);
                if ($chatMateInfo) {
                    $chatMateID = $convoHead['receiver_id'];
                    $chatMateName = ucfirst($chatMateInfo['first_name']) . " " . ucfirst($chatMateInfo['last_name']);
                    $chatMateFname = ucfirst($chatMateInfo['first_name']);
                }
            } else {

                $chatMateInfo = $this->getOtherUserInfo($convoHead['sender_id']);
                if ($chatMateInfo) {
                    $chatMateID = $convoHead['sender_id'];
                    $chatMateName = ucfirst($chatMateInfo['first_name']) . " " . ucfirst($chatMateInfo['last_name']);
                    $chatMateFname = ucfirst($chatMateInfo['first_name']);
                }
            }



            if ($chatMateInfo) {
                $chatMatePhoto = $chatMateInfo['profile_photo'];
            }


            //Active Status
            if ($chatMateInfo) {
                $chatMateStatus = $chatMateInfo['active_status'];
            }


            //Recene Message Display
            $recentMessage = $this->getRecentMessage($convoHead['convo_id']);
            if ($recentMessage) {
                $recentMessageSentTime = $recentMessage['date_created'];
                $timeSent = $this->getTimeDetails($recentMessageSentTime);
            } else {
                $timeSent = '';
                $recentMessage = '';
            }

            $pattern = '/\.(jpg|jpeg|png|gif)$/i';
            if ($recentMessage) {
                $messageContent = $recentMessage['message_content'];


                if ($recentMessage['receiver_id'] == $currentUserID) {


                    if (preg_match($pattern, $messageContent)) {

                        $messageContent = $chatMateFname . " has sent you an image.";
                    }

                    if ($recentMessage['receiver_read_status'] == "Unread") {
                        $statusBadgeUnread .= ' <p class="badge text-bg-danger fw-normal fs-7">New</p>';
                    } else {
                        $statusBadgeRead .= '<p class="m-0 fs-7 text-muted mb-0">' . $timeSent . '</p>';
                    }
                } else {
                    if (preg_match($pattern, $messageContent)) {

                        $messageContent = "You sent you an image.";
                    }
                    $statusBadgeRead .= '<p class="m-0 fs-7 text-muted mb-0">' . $timeSent . '</p>';
                }
            } else {
                $messageContent = '';
            }



            //Profile Picture
            if (!$chatMatePhoto) {
                $chatMatePhoto = 'defaultProfilePicture.png';
            }

            $conversationList .= <<<HTML
                                <div class="chat-item p-1 mt-2 rounded" onclick="openConvo('{$convoHead['convo_id']}','{$chatMateID}','$currentUserID')">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                            <img src="../images/profilePicture/$chatMatePhoto" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/>
                                            <div>
                                                <p class="m-0 fw-bold mb-0">{$chatMateName}</p>
                                                <span class="text-muted fs-7">{$chatMateStatus}</span>
                                            </div>
                                        </div>
                                        <div>
                                           {$statusBadgeUnread}
                                        </div>
                                            {$statusBadgeRead}
                                    </div>
                                    <div class="mt-1 mx-3"> 
                                        <span class="text-muted" style="font-size: 14px;">{$messageContent}</span>
                                    </div>
                                </div>
                            HTML;
        }

        return $conversationList;
    }

    public function getTimeDetails($time)
    {
        $now = new DateTime();
        $saved = new DateTime($time);
        $interval = $now->diff($saved);

        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' min' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
    //get the recent message of the
    public function getRecentMessage($convo_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_messages` WHERE `convo_id` = ?  ORDER BY `message_id` DESC LIMIT 1;");
        $query->execute([$convo_id]);
        $coverImage = $query->fetch();

        return $coverImage;
    }


    //create conversation
    public function createConversation($sender_id, $receiver_id, $recent_read_status)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `user_convo`(`sender_id`, `receiver_id`, `recent_read_status`, `recent_read_time`) 
                                        VALUES (?, ?, ?, CURRENT_TIMESTAMP())");
        $query->execute([$sender_id, $receiver_id, $recent_read_status]);
        $convoID = $connection->lastInsertId();

        return $this->openConversation($convoID, $receiver_id, $sender_id);
    }

    public function createConversationWithNew($sender_id, $receiver_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `user_convo`(`sender_id`, `receiver_id`, `recent_read_time`) 
                                        VALUES (?, ?, CURRENT_TIMESTAMP())");
        $query->execute([$sender_id, $receiver_id]);
        $convoID = $connection->lastInsertId();

        return $convoID;
    }

    //get number of conversation the user have
    public function getNumberOfConvo($currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_convo` WHERE `sender_id`= ? OR `receiver_id` = ? ORDER BY `recent_read_time` DESC");
        $query->execute([$currentUserID, $currentUserID]);
        $convoCount = $query->rowCount();

        return $convoCount;
    }

    //check for convo duplication
    public function checkConvoDuplication($sender_id, $receiver_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_convo` WHERE (`sender_id`= ? AND `receiver_id` = ?) OR (`sender_id`= ? AND `receiver_id` = ?)");
        $query->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
        $convoCount = $query->rowCount();

        if ($convoCount > 0) {

            return true;
        } else {

            return false;
        }
    }

    //function to input data on list of messages
    public function searchUser($userInput, $currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT *
                                        FROM `users`
                                        WHERE `first_name` LIKE CONCAT('%', :value, '%')
                                        OR `last_name` LIKE CONCAT('%', :value, '%')
                                        OR `email` LIKE CONCAT('%', :value, '%')
                                        OR CONCAT(first_name, ' ', last_name) LIKE CONCAT('%', :value, '%');");
        $query->bindValue(':value', $userInput, PDO::PARAM_STR); // Bind the parameter value
        $query->execute();
        $results = $query->fetchAll();
        $conversationList = '';


        foreach ($results as $result) {
            $chatMatePhoto = $result['profile_photo'];
            $chatMateName = ucfirst($result['first_name']) . " " . ucfirst($result['last_name']);
            $convoID = '';
            $convoInfo = $this->getConvoInfo($result['unique_id'], $currentUserID);

            if ($convoInfo) {
                $convoID = $convoInfo['convo_id'];
            } else {
                $convoID = "Unregistered";
            }

            //Profile Picture
            if (!$chatMatePhoto) {
                $chatMatePhoto = 'defaultProfilePicture.png';
            }


            $conversationList .= <<<HTML
                                <div class="chat-item p-1 mt-2 rounded" onclick="openConvo('{$convoID}','{$result['unique_id']}')">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                            <img src="../images/profilePicture/$chatMatePhoto" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/>
                                            <div>
                                                <p class="m-0 fw-bold mb-0">{$chatMateName}</p> <!-- name -->
                                                
                                            </div>
                                        </div>
                                        <div>
                                           
                                        </div>
                                            
                                    </div>
                                    <div class="mt-1 mx-3"> 
                                        <span class="text-muted" style="font-size: 14px;"></span>
                                    </div>
                                </div>
                            HTML;
        }

        return $conversationList;
    }
    //To get the info the chat mate
    public function getConvoInfo($chatMateID, $currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_convo` WHERE (`sender_id`= ? AND `receiver_id` = ?) OR (`sender_id`= ? AND `receiver_id` = ?)");
        $query->execute([$chatMateID, $currentUserID, $currentUserID, $chatMateID]);
        $convoInfo = $query->fetch();

        return $convoInfo;
    }
    //function for opening conversation
    public function openConversation($convo_id, $chatMate, $currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_messages` WHERE `convo_id` = ? ORDER BY `message_id` ASC");
        $query->execute([$convo_id]);
        $messages = $query->fetchAll();
        $convoMessages = '';
        $chatMateUserInfo = $this->getOtherUserInfo($chatMate);
        $chatMateFullName = ucfirst($chatMateUserInfo['first_name']) . ' ' . ucfirst($chatMateUserInfo['last_name']);
        $chatMatePicture = $chatMateUserInfo['profile_photo'];


        if (!$chatMatePicture) {
            $chatMatePicture = 'defaultProfilePicture.png';
        }

        foreach ($messages as $message) {

            $chatMessage = $message['message_content'];
            $pattern = '/\.(jpg|jpeg|png|gif)$/i';
            if (!next($messages)) {
                if ($message['receiver_id'] == $currentUserID) {
                    $this->setReadStatus($convo_id);
                }
            }



            if ($message['sender_id'] === $currentUserID) {

                if (preg_match($pattern, $chatMessage)) {
                    $convoMessages .= <<<HTML
                                    <!-- =================== Chat Item You =================== -->
                                    <div class="chat-item p-1 mt-2 rounded">
                                        <div class="d-flex justify-content-end">
                                            <div>                                        
                                                <img src="../images/messagePicture/$chatMessage" alt="avatar" class="rounded me-2" style="max-width: 200px; max-height: 350px; object-fit: cover"/>
                                            </div>     
                                        </div>
                                    </div>
                                    <!-- =================== /Chat Item You =================== -->
                                    HTML;
                } else {
                    $convoMessages .= <<<HTML
                                    <!-- =================== Chat Item You =================== -->
                                    <div class="chat-item p-1 mt-2 rounded">
                                        <div class="d-flex justify-content-end">
                                            <div>                                        
                                                <p class="text-bg-dark py-2 px-2 chat-bubble-user-1" style="font-size: 14px; display: inline-block; max-width: 600px; word-wrap: break-word;">{$chatMessage}</p>
                                            </div>     
                                        </div>
                                    </div>
                                    <!-- =================== /Chat Item You =================== -->
                                    HTML;
                }
            } else {

                // Check if the message content ends with one of the specified file extensions
                if (preg_match($pattern, $chatMessage)) {
                    $convoMessages .= <<<HTML
                                    <!-- =================== Chat Item Other User =================== -->
                                     <!-- =================== Chat Item Other User =================== -->
                                    <div class="chat-item p-1 mt-2 rounded">
                                        <div class="d-flex justify-content-start">
                                            <img src="../images/profilePicture/$chatMatePicture" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/>
                                            <div style="width: 600px;">
                                                <p class="m-0 fw-bold mb-2">{$chatMateFullName}</p>
                                                <img src="../images/messagePicture/$chatMessage" alt="avatar" class="rounded me-2" style="max-width: 200px; max-height: 350px; object-fit: cover"/>    
                                            </div>
                                        </div>
                                    </div>
                                    <!-- =================== /Chat Item Other User =================== -->
                                    HTML;
                } else {
                    $convoMessages .= <<<HTML
                                    <!-- =================== Chat Item Other User =================== -->
                                    <div class="chat-item p-1 mt-2 rounded">
                                        <div class="d-flex justify-content-start">
                                            <img src="../images/profilePicture/$chatMatePicture" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/>
                                            <div>
                                                <p class="m-0 fw-bold mb-2">{$chatMateFullName}</p>
                                                <p class="text-muted bg-gray p-2 chat-bubble-user-2" style="font-size: 14px; max-width: 600px; word-wrap: break-word;">{$chatMessage}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- =================== /Chat Item Other User =================== -->
                                    HTML;
                }
            }
        }

        return $convoMessages;
    }
    //set read receupt to updated
    public function setReadStatus($convo_id)
    {
        $read_status = "Read";

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `user_messages` SET `receiver_read_status`= ? ,`read_datetime`= CURRENT_TIMESTAMP() WHERE `convo_id` = ?");
        $query->execute([$read_status, $convo_id]);
    }

    ///Set the convo's read status to read 
    public function setReadStatusConvo($convo_id)
    {
        $read_status = "Read";

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `user_convo` SET `recent_read_status`= ? ,`recent_read_time`= CURRENT_TIMESTAMP() WHERE `convo_id` = ?");
        $query->execute([$read_status, $convo_id]);
    }

    //sent Message
    public function sendMessage($convo_id, $sender_id, $receiver_id, $message_content, $sender_read_status, $receiver_read_status)
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `user_messages`(`convo_id`, `sender_id`, `receiver_id`, `message_content`, `sender_read_status`, `receiver_read_status` ,`date_created`, `read_datetime`) 
                                        VALUES (?, ?, ?, ?, ? ,?, ?, ?)");
        $result = $query->execute([$convo_id, $sender_id, $receiver_id, $message_content, $sender_read_status, $receiver_read_status, $currentDateTime, $currentDateTime]);
        $this->updateRecentReadtime($convo_id);
        return $result;
    }

    public function updateRecentReadtime($convo_ID)
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `user_convo` SET `recent_read_time`= ? WHERE `convo_id` = ?");
        $query->execute([$currentDateTime, $convo_ID]);
    }

    public function hireMeFunction($sender_id, $receiver_id)
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `user_convo`(`sender_id`, `receiver_id`,`recent_read_time`) 
                                        VALUES (?, ?, ?)");
        $result = $query->execute([$sender_id, $receiver_id, $currentDateTime]);
        return $result;
    }


    //-------------------------------------------------------------------------------------------------------------------------------------//
    //retrieve all artwork images uploaded by a specific user ID
    function getArtWorkImagesByUserID($artist_id)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT *
                                    FROM artworks_images 
                                    WHERE artist_id = ?
                                    ORDER BY date_created DESC");
        $query->execute([$artist_id]);
        $artWorkImages = $query->fetchAll();
        $imagesOutput = '';

        // Check if there are images
        if (!empty($artWorkImages)) {
            $imagesOutput .= '<div class="row mt-2">';
            $rowCount = 0; // Initialize row counter

            foreach ($artWorkImages as $index => $artWorkImage) {
                // Start a new row for every three images
                if ($index > 0 && $index % 3 === 0) {
                    $imagesOutput .= '</div>';
                    $rowCount++;

                    // Limit to three rows
                    if ($rowCount >= 3) {
                        break;
                    }

                    $imagesOutput .= '<div class="row mt-2">';
                }

                $imagePath = "../images/artworks/" . $artWorkImage['image_name'];

                // Check if the file exists
                if (file_exists($imagePath)) {
                    $imagesOutput .= '<div class="col-md-4 col-lg-4 col-sm-4 mt-2 pointer px-1">
                                            <img src="' . $imagePath . '" class="rounded img-fluid" alt="avatar" style="width: 100%; height: 120px; object-fit: cover;" >
                                        </div>';
                } else {
                    $imagesOutput .= '<div class="col-md-4 col-lg-4 col-sm-4 mt-2 pointer px-1">
                                        <p>Image not found: ' . $imagePath . '</p>
                                    </div>';
                }
            }

            $imagesOutput .= '</div>';
        }

        return $imagesOutput;
    }

    public function getProfileUserTags($userID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_tags` WHERE `tag_user_id`= ?");
        $query->execute([$userID]);
        $tags = $query->fetchAll();

        $output = '';

        foreach ($tags as $tag) {

            $output .= '<span class="badge text-bg-dark fw-normal fs-6 my-1 ms-1 pe-3">' . $tag['tag_desc'] . '</span>  ';
        }

        return $output;
    }

    public function addBio($userID, $bio)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `users`(`bio`) VALUES (?)");
        $result = $query->execute([$bio]);

        return $result;
    }


    //-------------------------------------------------------------------------------------------------------------------------------------//


    public function updateActiveStatus($currentUserID)
    {
        $active_status = "Online";
        // Open a database connection
        $connection = $this->openConnection();
        // Prepare and execute the SQL query to update the verification status
        $query = $connection->prepare("UPDATE `users` SET `active_status` = ? , `last_activeTime`= CURRENT_TIMESTAMP() WHERE `unique_id` = ?");
        $result = $query->execute([$active_status, $currentUserID]);

        return $result;
    }

    public function searhOtherUserProfile($userInput)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT *
                                        FROM `users`
                                        WHERE `first_name` LIKE CONCAT('%', :value, '%')
                                        OR `last_name` LIKE CONCAT('%', :value, '%')
                                        OR `email` LIKE CONCAT('%', :value, '%')
                                        OR CONCAT(first_name, ' ', last_name) LIKE CONCAT('%', :value, '%');");
        $query->bindValue(':value', $userInput, PDO::PARAM_STR); // Bind the parameter value
        $query->execute();
        $users = $query->fetchAll();
        $usersList = '';

        foreach ($users as $user) {
            $userProfile = '';
            $userFullName = ucfirst($user['first_name']) . " " . ucfirst($user['last_name']);
            $userID = $user['unique_id'];
            if ($user['profile_photo']) {
                $userProfile = $user['profile_photo'];
            } else {
                $userProfile = 'defaultProfilePicture.png';
            }

            $usersList .= <<<HTML
                            <!-- search item -->
                            <li class="my-4">
                                <div class="alert fade show dropdown-item p-1 m-0 d-flex align-items-center justify-content-between" role="alert">
                                <a href="profile.php?U=$userID" style="text-decoration: none; color: inherit;">
                                    <div class="d-flex align-items-center">
                                        <img src="../images/profilePicture/$userProfile" alt="avatar" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover"/>
                                        <p class="m-0">{$userFullName}</p>
                                    </div>
                                </a>
                                    <button type="button" class="btn-close p-0 m-0" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </li>
                            <!-- search item -->
                            HTML;
        }

        return $usersList;
    }


    public function deleteConvo($convoID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `user_convo` WHERE `convo_id`= ?");
        $result = $query->execute([$convoID]);

        return $result;
    }

    public function deleteMessages($convoID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `user_messages` WHERE `convo_id`= ?");
        $result = $query->execute([$convoID]);

        return $result;
    }

    public function changePassword($email, $newPassword)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `password` = ? WHERE `email` = ?");
        $result = $query->execute([$newPassword, $email]);

        return $result;
    }

    public function setUserSkills($currentUserID, $currentUserSkill)
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `user_skills`(`user_id`, `skill`, `date_created`) VALUES (?, ?, ?)");
        $result = $query->execute([$currentUserID, $currentUserSkill, $currentDateTime]);
        return $result;
    }

    public function deleteUserSkills($currentUserID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `user_skills` WHERE `user_id`= ?");
        $result = $query->execute([$currentUserID]);

        return $result;
    }

    public function deleteUserTags($currentUserID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `user_tags` WHERE `tag_user_id`= ?");
        $result = $query->execute([$currentUserID]);

        return $result;
    }

    public function updateUserBioProfile($currentUserID, $userBio)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `bio` = ? WHERE `unique_id` = ?");
        $result = $query->execute([$userBio, $currentUserID]);

        if ($result) {

            $this->getUserDataByID($currentUserID);
        }
    }

    public function getUserDataByID($currentUserID)
    {
        // Open a database connection
        $connection = $this->openConnection();

        // Prepare and execute the SQL query to retrieve user data based on email and password
        $query = $connection->prepare("SELECT * FROM `users` WHERE `user_id` = ?");
        $query->execute([$currentUserID]);
        $user = $query->fetch(); // Fetching single data from the server and it will return an array
        $total = $query->rowCount();

        // If user credentials are found
        if ($total > 0) {
            $this->setUserData($user);

            return true;
        } else {
            // Display error message if credentials are invalid
            return false;
        }
    }

    public function getUserSkills($userID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `user_skills` WHERE `user_id`= ?");
        $query->execute([$userID]);
        $skills = $query->fetchAll();

        $output = '';

        foreach ($skills as $skill) {

            $output .= '<span class="badge text-bg-dark fw-normal fs-6 my-1 ms-1 pe-3">' . $skill['skill'] . '</span>  ';
        }

        return $output;
    }

    public function getUserArtworks($userID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks` WHERE `creator_user_id`= ?");
        $query->execute([$userID]);
        $artWorks = $query->fetchAll();
        $output = '';

        foreach ($artWorks as $artWork) {

            $posterDate = $this->calculateTimeDifference($artWork['date_created']);
            $rawCoverImage = $this->getArtworkCoverPhoto($artWork['artwork_unique_id']);
            $artWorkCover = $rawCoverImage['image_name'];
            $creatorInfo = $this->getCreatorInfo($artWork['creator_user_id']);
            $posterFullName = ucfirst($creatorInfo['first_name']) . ' ' . ucfirst($creatorInfo['last_name']);
            $commentCount = $this->getNumberOfComments($artWork['artwork_unique_id']);
            $likeCount = $this->getNumberOfLikes($artWork['artwork_unique_id']);
            $likeStatus = $this->artWorkLikeStatus($artWork['artwork_unique_id'], $userID); //$artwork_mother_id, $liker_id
            $commentCountID = "commentCount" . $artWork['artwork_unique_id'];
            $likeCountID = "likeCount" . $artWork['artwork_unique_id'];
            $likeBtnID = "likeBtn" . $artWork['artwork_unique_id'];
            $countryOrigin = '';
            $profilePicture = '';
            $likeStatusDisplay = '';
            $editPost = '';
            $hireMeStatus = '';
            $creatorProfilePhoto = '';
            $artWorkCaption = $artWork['caption'];
            $artWorkCaptionLimited = strlen($artWorkCaption) > 25 ? substr($artWorkCaption, 0, 15) . "..." : $artWorkCaption;



            if ($creatorInfo['country']) {
                $countryOrigin = $creatorInfo['country'];
            } else {
                $countryOrigin = 'Earth';
            }

            if ($creatorInfo['profile_photo']) {
                $creatorProfilePhoto = '../images/profilePicture/' . $creatorInfo['profile_photo'] . '';
            } else {
                $creatorProfilePhoto = '../images/profilePicture/defaultProfilePicture.png';
            }


            //Comment count display data
            if (!$commentCount) {
                $commentCount = "0 Comment";
            } else {
                if ($commentCount == 1) {
                    $commentCount .= " Comment";
                } else {

                    $commentCount .= " Comments";
                }
            }

            if (!$likeCount) {
                $likeCount = "0 Like";
            } else {
                if ($likeCount == 1) {
                    $likeCount .= " Like";
                } else {

                    $likeCount .= " Likes";
                }
            }



            $output .= <<<HTML
                            <div class="col-md-3 col-sm-6 mt-3 pointer" data-bs-toggle="modal" data-bs-target="#checkImageModal" >
                            <img src="../images/artworks/$artWorkCover" alt="post image" style="width: 450px; height: 250px; object-fit: cover;"
                                class="img-fluid rounded" data-bs-toggle="modal" data-bs-target="#viewPostModal" 
                                onclick="viewPost('{$artWork['artwork_unique_id']}','{$posterFullName}','{$artWork['caption']}','{$artWork['date_created']}','{$artWork['artwork_tags']}', '{$artWork['creator_user_id']}', '{$countryOrigin}', '{$creatorProfilePhoto}')"/>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <p class="fs-7 mb-0 fw-bold text-dark">{$artWorkCaptionLimited}</p>    
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-thumbs-up text-muted fs-7 me-1"></i>
                                        <p class="fs-7 mb-0 fw-bold text-dark me-2">{$likeCount}</p>
                                        <i class="fas fa-comment-alt text-muted fs-7 me-1"></i>
                                        <p class="fs-7 mb-0 fw-bold text-dark">{$commentCount}</p>  
                                    </div>                                                          
                                </div>
                            </div>  
                    HTML;
        }

        return $output;
    }

    public function updateUserProfile($currentUserID, $userFirstName, $userLastName, $userBirthdate, $userGender)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `first_name` = ?, `last_name` = ?, `birthdate` = ?, `gender` = ? WHERE `unique_id` = ?");
        $query->execute([$userFirstName, $userLastName, $userBirthdate, $userGender, $currentUserID]);
    }

    public function updateUserAccountInfo($currentUserID, $current_job, $contact_number, $country)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `current_job` = ?, `contact_number` = ?, `country` = ?  WHERE `unique_id` = ?");
        $query->execute([$current_job, $contact_number, $country, $currentUserID]);

        // session_reset();
        // $this->getUserDataByID($currentUserID);

    }

    public function insertExperience($currentUserID, $previous_company, $job_title, $start_date, $end_date)
    {

        $currentDateTime = date('Y-m-d H:i:s');

        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `experience`(`user_experience_id` ,`previous_company`, `job_title`, `date_created`, `start_date`, `end_date`) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $query->execute([$currentUserID, $previous_company, $job_title, $currentDateTime, $start_date, $end_date]);
        return $result;
    }

    public function insertEducation($currentUserID, $school, $major, $year_graduated, $year_started)
    {

        $currentDateTime = date('Y-m-d H:i:s');

        $connection = $this->openConnection();
        $query = $connection->prepare("INSERT INTO `education`(`user_education_id`, `school`, `major`, `date_created`, `year_graduated`, `year_started`) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $query->execute([$currentUserID, $school, $major, $currentDateTime, $year_graduated, $year_started]);
        return $result;
    }


    public function showAllExperience($currentUserID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `experience` WHERE `user_experience_id` = ? ORDER BY `end_date` DESC");
        $query->execute([$currentUserID]);
        $experiences = $query->fetchAll();
        $output = '';

        foreach ($experiences as $experience) {
            $company = ucfirst($experience['previous_company']);
            $job = ucfirst($experience['job_title']);
            $serviceYear = $experience['start_date'] . "-" . $experience['end_date'];

            $output .= <<<HTML
                           <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Experience" onclick="viewExperience('{$company}','{$job}','{$experience['start_date']}','{$experience['end_date']}','{$experience['experience_id']}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-briefcase fs-5 me-2"></i>
                                        <p class="mb-0 fs-6 fw-bold">{$company}</p>
                                    </div>   
                                    <p class="fs-7 mb-0 text-muted">{$serviceYear}</p>                                                            
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-graduation-cap fs-5 me-2 invisible"></i>
                                        <p class="mb-0 fs-7 fw-medium">{$job}</p>
                                    </div>   
                                    <!-- <p class="fs-7 mb-0 text-muted">Makati, NCR</p> -->                                                            
                                </div>
                            </div>
                            <hr class="">  
                    HTML;
        }

        return $output;
    }
    public function showAllEducation($currentUserID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `education` WHERE `user_education_id` = ? ORDER BY `year_graduated` DESC");
        $query->execute([$currentUserID]);
        $educations = $query->fetchAll();
        $output = '';

        foreach ($educations as $experience) {
            $company = ucfirst($experience['school']);
            $job = ucfirst($experience['major']);
            $serviceYear = $experience['year_started'] . "-" . $experience['year_graduated'];

            $output .= <<<HTML
                                <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Education" onclick="viewEducation('{$company}','{$job}','{$experience['year_started']}','{$experience['year_graduated']}','{$experience['education_id']}')">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-graduation-cap fs-5 me-2"></i>
                                            <p class="mb-0 fs-6 fw-bold">{$company}</p>
                                        </div>   
                                        <p class="fs-7 mb-0 text-muted">{$serviceYear}</p>                                                            
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-graduation-cap fs-5 me-2 invisible"></i>
                                            <p class="mb-0 fs-7 fw-medium">{$job}</p>
                                        </div>   
                                       <!-- <p class="fs-7 mb-0 text-muted">4 years</p> -->                                                            
                                    </div>
                                </div>
                                <hr class="">
                    HTML;
        }

        return $output;
    }

    public function updateExperience($expID, $previous_company, $job_title, $start_date, $end_date)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `experience` SET `previous_company` = ?, `job_title` = ?, `start_date` = ?, `end_date` = ?  WHERE `experience_id` = ?");
        $result = $query->execute([$previous_company, $job_title, $start_date, $end_date, $expID]);
        return $result;
    }

    public function updateEducation($education_id, $school, $major, $year_graduated, $year_started)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `education` SET `school` = ?, `major` = ?, `year_graduated` = ?, `year_started` = ?  WHERE `education_id` = ?");
        $result = $query->execute([$school, $major, $year_graduated, $year_started, $education_id]);
        return $result;
    }

    public function getAllUsersArtworks($currentUserID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `artworks_images` WHERE `artist_id` = ? ORDER BY `date_created` DESC LIMIT 12");
        $query->execute([$currentUserID]);
        $artWorks = $query->fetchAll();
        $output = '';

        foreach ($artWorks as $artWork) {
            $artWorkCover = $artWork['image_name'];

            $output .= <<<HTML
                                <div class="col-md-4 col-lg-4 col-sm-4 mt-2 pointer px-1">
                                    <img src="../images/artworks/$artWorkCover" class="rounded img-fluid" alt="avatar" style="width: 100%; height: 120px; object-fit: cover;" />
                                </div>
                        HTML;
        }

        return $output;
    }

    public function updateProfilePicture($currentUser, $profilePicture)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `profile_photo` = ?  WHERE `unique_id` = ?");
        $result = $query->execute([$profilePicture, $currentUser]);

        return $result;
    }

    public function updateCoverPicture($currentUser, $cover_photo)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE `users` SET `cover_photo` = ?  WHERE `unique_id` = ?");
        $result = $query->execute([$cover_photo, $currentUser]);

        return $result;
    }



    public function getFollowersList($currentUserID)
    {

        $connection = $this->openConnection();
        $query = $connection->prepare("SELECT * FROM `followers` WHERE `user_following_id` = ? ORDER BY `date_created` DESC");
        $query->execute([$currentUserID]);
        $followers = $query->fetchAll();
        $output = '';

        foreach ($followers as $follower) {

            $followerInfo = $this->getOtherUserInfo($follower['user_follower_id']);
            $followerPhoto = $followerInfo['profile_photo'];
            $followerFullname = ucfirst($followerInfo['first_name']) . " " . ucfirst($followerInfo['last_name']);
            $followerEmail = $followerInfo['email'];
            $followerID = $followerInfo['unique_id'];

            $output .= <<<HTML
                                <div class="d-flex align-content-center justify-content-between mx-3">
                                    <div class="d-flex w-100">
                                    <a href="profile.php?U=$followerID"><img src="../images/profilePicture/$followerPhoto" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/></a>
                                        <div>
                                            <p class="fw-bold align-self-end mb-0 me-3">{$followerFullname}</p>                                       
                                            <p class="text-muted fs-7" >{$followerEmail}</p>
                                        </div>
                                    </div>
                                </div>
                        HTML;
        }
        return $output;
    }

    public function deleteExperience($experienceID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `experience` WHERE `experience_id`= ?");
        $result = $query->execute([$experienceID]);

        return $result;
    }
    public function deleteEducation($educationID)
    {
        $connection = $this->openConnection();
        $query = $connection->prepare("DELETE FROM `education` WHERE `education_id` = ?");
        $result = $query->execute([$educationID]);

        return $result;
    }


    public function autoActiveUpdateStatus()
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $connection = $this->openConnection();
        $query = $connection->prepare("UPDATE users 
                                    SET active_status = 'Offline' 
                                    WHERE last_activeTime < DATE_SUB(?, INTERVAL 5 MINUTE)");
        $result = $query->execute([$currentDateTime]);

        return $result . " : " . $currentDateTime;
    }
}

$operations = new MySite();
