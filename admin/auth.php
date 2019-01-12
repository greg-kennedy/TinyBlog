<?php

require_once 'set_error_handler.php';

// Include this anywhere you need to test for login.

session_start( ['read_and_close' => true] );

// Redirect user to the "login" page if their session is not initialized.
if (! isset($_SESSION['authorized'])) {
  header("Location: login.php");
  exit;
}

?>
