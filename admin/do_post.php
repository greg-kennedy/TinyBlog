<?php

  // user must be logged in...
  require_once 'auth.php';

  // functions to update HTML files
  require_once 'update.php';

  /* Commits a new post to the db */
  // get post values
  $date = $_POST['date'];
  $title = $_POST['title'];
  $post = $_POST['post'];

  // convert date to timestamp
  $date = DateTime::createFromFormat('Y-m-d\TH:i:s', $date)->getTimestamp();

  // standardize post to LF-only line endings
  $post = str_replace("\r", '', $post);

  //  Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READWRITE);
  $db->enableExceptions(TRUE);

  // get the post ID
  if (isset($_POST['id'])) {
    // Existing post, needs update query
    $id = $_POST['id'];

    $stmt = $db->prepare('UPDATE posts SET date=:date, title=:title, post=:post WHERE id=:id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':date', $date, SQLITE3_INTEGER);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':post', $post, SQLITE3_TEXT);

    $stmt->execute()->finalize();
  } else {
    // new post
    $stmt = $db->prepare('INSERT INTO posts (date,title,post) VALUES(:date,:title,:post)');
    $stmt->bindValue(':date', $date, SQLITE3_INTEGER);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':post', $post, SQLITE3_TEXT);

    $stmt->execute()->finalize();

    $id = $db->lastInsertRowId();
  }

  if ($db->changes()) {
    // bake new post
    create_post($db,$id);
    // update the index page
    create_index($db);
    // update the archive page
    create_archive($db);
    // ...and the atom feed
    create_atom($db);

    // close the db handle, all done
    $db->close();

    // redirect to post
    header('Location: ../post/' . $id . '.html');
    exit;
  } else {
    // Something went wrong.
    $db->close();
  }
?>
