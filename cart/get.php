<?php
require_once('../cors.php');
require_once('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $operations->getCart();
}
