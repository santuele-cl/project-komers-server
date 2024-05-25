<?php
require_once('cors.php');

session_start();

// Destroy the session if it exists
if (isset($_SESSION)) {
    // session_unset();
    $_SESSION = [];
    session_destroy();
}
