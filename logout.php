<?php
require_once('cors.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    // Destroy the session if it exists
    if (isset($_SESSION)) {
        // session_unset();
        $_SESSION = [];
        session_destroy();
    }
}
