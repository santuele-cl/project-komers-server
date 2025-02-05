<?php
require_once('../cors.php');
require_once('../db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $category = $_POST['category'];

    $isInputValid = isset($name) && !empty($name) && isset($price) && !empty($price) && isset($description) && !empty($description) && isset($stock) && !empty($stock) && isset($brand) && !empty($brand) && isset($category) && !empty($category) && isset($_FILES['image']);

    if ($isInputValid) {
        $operations->addProduct($name, $price, $description, $stock, $brand,$category, $_FILES["image"]);
    }
}
