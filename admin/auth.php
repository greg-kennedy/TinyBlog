<?php

// Include this anywhere you need to test for login.
session_start( ['read_and_close' => true] );

if (! isset($_SESSION['authorized'])) {
  header("Location: login.php");
  exit;
}

?>
