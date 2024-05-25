<?php
require_once('../cors.php');
require_once('../db.php');

// $image = $_FILES["image"]["name"];

// echo json_encode($image);

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // $image = $_FILES["image"];

    // echo json_encode($image);
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];

    $isInputValid = isset($name) && !empty($name) && isset($price) && !empty($price) && isset($description) && !empty($description) && isset($stock) && !empty($stock) && isset($brand) && !empty($brand) && isset($_FILES['image']);

    if ($isInputValid) {
        $operations->addProduct($name, $price, $description, $stock, $brand, $_FILES["image"]);
    }
}