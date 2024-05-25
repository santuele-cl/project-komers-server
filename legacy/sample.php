<?php
require_once('../php/DBOperations.php');

if (isset($_POST)) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Content-Type: application/json");

    $users = $operations->testGetUser();
    $myObj  = ["lakjsdf", "alksdjf"];
    // $myObj->name = "John";
    // $myObj->age = 30;
    // $myObj->city = "New York";

    $myJSON = json_encode($users, true);

    if ($myJSON) {
        echo $myJSON;
    }
}
