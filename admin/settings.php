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
    // check the db
    $value = $_POST[$key] ?? '';
    $stmt->execute();
    $changed_rows += $db->changes();
  }
  $stmt->close();

  // get list of all posts we need to go re-create
/*
  $result = $db->query('SELECT id FROM posts');
  $num_rows = 0;

  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $ids[] = $row[0];
  }
  $result->finalize();
*/

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
