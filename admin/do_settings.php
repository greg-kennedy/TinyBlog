<?php
  // user must be logged in...
  require_once('auth.php');

  // functions to update HTML files
  require_once('update.php');

  /* New settings */
  //  Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READWRITE);
  $db->enableExceptions(TRUE);

  // Retrieve each setting from the DB.
  $stmt = $db->prepare('REPLACE INTO settings(key, value) VALUES (:key, :value)');
  $stmt->bindParam(':key', $key);
  $stmt->bindParam(':value', $value);

  // updates
  $changed_rows = 0;
  foreach (array('name') as $key) {
    // get value for key from POST
    $value = $_POST[$key] ?? '';

    // special handler for password
    if ($key == 'password') {
      // don't replace password if nothing was entered
      if ($value == '') {
        continue;
      }
      $value = password_hash($password);
    }
    $stmt->execute();
    $changed_rows += $db->changes();
  }
  $stmt->close();

  // If any settings changed, re-create everything.
  if ($changed_rows > 0)
  {
    // bake all posts
    create_all_posts($db);
    // update the index page
    create_index($db);
    // update the archive page
    create_archive($db);
  }

  // all done with the db
  $db->close();

  // redirect to settings again
  header('Location: index.php');
  exit();
?>
