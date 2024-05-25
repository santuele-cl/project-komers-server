<?php
require_once('cors.php');
require_once('db.php');

$requestPayload = file_get_contents('php://input');
$decodedRequestPayload = json_decode($requestPayload, true);


$operations->login($decodedRequestPayload["email"], $decodedRequestPayload["password"]);

// echo json_encode($decodedRequestPayload);

// if (!isset($_SESSION)) {
//     session_start();
// }
