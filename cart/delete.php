<?php
require_once('../cors.php');
require_once('../db.php');


$requestPayload = file_get_contents('php://input');
$decodedRequestPayload = json_decode($requestPayload, true);

if (isset($decodedRequestPayload["cartId"])) {
    $operations->deleteCart($decodedRequestPayload["cartId"]);
}
