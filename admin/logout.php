<?php
  session_start();

  // Destroying the session clears the $_SESSION variable, thus "logging" the user
  //  out. This also happens automatically when the browser is closed
  session_destroy();

  // go back to the main blog index.
  header("Location: ../index.html");
  exit;
?>
