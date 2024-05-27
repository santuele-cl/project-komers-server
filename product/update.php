<?php
require_once('../cors.php');
require_once('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operations->updateProduct();
}
