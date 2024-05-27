<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header('Access-Control-Max-Age: 86400');


// <?php
// header("Access-Control-Allow-Origin: http://localhost:5173");

// header("Access-Control-Allow-Headers: Content-Type, Authorization");

// header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// header("Access-Control-Allow-Credentials: true");

// header("Content-Type: application/json");

// // Allow preflight requests
// // if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
// header('Access-Control-Allow-Origin: http://localhost:5173');
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type');
// header("Access-Control-Allow-Credentials: true");
// header('Access-Control-Max-Age: 86400');
// http_response_code(200);
//     // exit;
// // }

// // Set the appropriate headers for CORS
// header('Access-Control-Allow-Origin: http://localhost:5173');
// header('Access-Control-Allow-Headers: Content-Type');
