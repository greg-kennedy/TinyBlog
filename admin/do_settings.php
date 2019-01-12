<?php
  // user must be logged in...
  require_once 'auth.php';

  // SETTINGS structure
  require_once 'inc.settings.php';

  // functions to update HTML files
  require_once 'update.php';

  /* New settings */
  //  Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READWRITE);
  $db->enableExceptions(TRUE);

  // Delete any old settings
  //  This is kind of a hack: we construct a WHERE clause by concatenating '?' parameters together
  $query = 'DELETE FROM settings WHERE key NOT IN (' . implode(',', array_fill(0, count(SETTINGS), '?')) . ')';
  $stmt = $db->prepare($query);
  // Bind each SETTING name to the prepared statement
  for ($i = 0; $i < count(SETTINGS); $i ++)
  {
    $stmt->bindValue($i + 1, SETTINGS[$i][0], SQLITE3_TEXT);
  }
  $stmt->bindParam(':value', $value);
  $stmt->execute();
  $stmt->close();

  // Retrieve each setting from the DB.
  $stmt = $db->prepare('REPLACE INTO settings(key, value) VALUES (:key, :value)');
  $stmt->bindParam(':key', $key);
  $stmt->bindParam(':value', $value);

  // updates
  // Changed rows counter to determine if any changes happened
  $changed_rows = 0;
  foreach (SETTINGS as $setting) {
    $key = $setting[0];

    $type = $setting[2];

    $value = $_POST[$key] ?? '';

    if ($type == TYPE_PASSWORD) {
      // don't replace password if nothing was entered
      if ($value == '') {
        continue;
      }
      $value = password_hash($value, PASSWORD_DEFAULT);
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
    // ...and the atom feed
    create_atom($db);
  }

  // all done with the db
  $db->close();

  // redirect to settings again
  header('Location: index.php');
  exit;
?>
