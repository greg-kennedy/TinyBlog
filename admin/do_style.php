<?php
  // user must be logged in...
  require_once('auth.php');

  /* Replace style.css in parent folder with the one from POST */
  // get post values
  if (isset($_POST['style'])) {
    file_put_contents('../style.css', $_POST['style']);
  }

  // redirect back to index
  header('Location: index.php');
  exit();
?>
