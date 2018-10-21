<?php
  require_once('update.php');

  /* Commits a new post to the db */
  // get post values
  $date = $_POST['date'];
  $title = $_POST['title'];
  $post = $_POST['post'];

  //  Open the sqlite3 database
  $db = new SQLite3('tinycms.db', SQLITE3_OPEN_READWRITE);
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

  //echo $db->changes(), " rows changed.";
  $db->close();

  // bake new post
  create_post($id);
  // update the index page
  create_index();
  // update the archive page
  create_archive();

  // redirect to post
  header('Location: ../post/' . $id . '.html');
  exit();
?>
