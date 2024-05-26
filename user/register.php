<?php
require_once('../cors.php');
require_once('../db.php');

$requestPayload = file_get_contents('php://input');
$decodedRequestPayload = json_decode($requestPayload, true);

$role = "customer";

if (isset($decodedRequestPayload["role"]) && !empty($decodedRequestPayload["role"])) {
    $role = $decodedRequestPayload["role"];
}

$operations->addUser(
    $decodedRequestPayload["first_name"],
    $decodedRequestPayload["middle_name"],
    $decodedRequestPayload["last_name"],
    $decodedRequestPayload["contact_num"],
    $role,
    $decodedRequestPayload["house_number"],
    $decodedRequestPayload["street"],
    $decodedRequestPayload["barangay"],
    $decodedRequestPayload["city"],
    $decodedRequestPayload["province"],
    $decodedRequestPayload["region"],
    $decodedRequestPayload["country"],
    $decodedRequestPayload["zipcode"],
    $decodedRequestPayload["email"],
    $decodedRequestPayload["password"]
);
