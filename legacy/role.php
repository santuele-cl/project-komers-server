<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if (!isset($_SESSION)) {
    session_start();
}

// echo json_encode($_SESSION["userdata"]["user_type"]);

$role = $_SESSION["userdata"]["user_type"];

if ($role == "admin") {
    echo json_encode($_SESSION["userdata"]["user_type"]);
} else {
    echo  json_encode("not admin");
}
