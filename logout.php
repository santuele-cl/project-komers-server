<?php
require_once('db.php');
require_once('cors.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operations->logout();
}
