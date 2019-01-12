<?php
  require_once 'auth.php';
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>TinyBlog Admin Page</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>TinyBlog - Post Management</h1>
  </header>
<?php
  // get the post
  if (isset($_GET['id'])) {
    // Existing post
    $id = $_GET['id'];

    //  Open the sqlite3 database
    $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READONLY);
    $db->enableExceptions(TRUE);

    $stmt = $db->prepare('SELECT date, title, post FROM posts WHERE id=:id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    $num_rows = 0;
    while ($row = $result->fetchArray(SQLITE3_NUM)) {
      $date = $row[0];
      $title = $row[1];
      $post = $row[2];
      $num_rows ++;
    }
    $result->finalize();
    $stmt->close();
    $db->close();

    if (! $num_rows) {
      throw new RuntimeException("id '$id' did not match any posts in database");
    }
  } else {
    // new post
    $date = time();
    $title = '';
    $post = '';
  }
?>
  <main>
   <article>
    <header>
     <h2>Post Management</h2>
    </header>
    <form action="do_post.php" method="POST">
<?php if (isset($id)) { echo '<input type="hidden" name="id" value="', $id, '">'; } ?>
     <label for="date">Date</label>
     <input type="datetime-local" step="1" name="date" value="<?php echo date('Y-m-d\TH:i:s', $date) ?>">
     <br>
     <label for="title">Title</label>
     <input name="title" value="<?php echo htmlspecialchars($title, ENT_HTML5) ?>">
     <br>
     <label for="post">Post</label>
     <br>
     <textarea name="post"><?php echo htmlspecialchars($post, ENT_HTML5) ?></textarea>
     <button>Save</button>
     <a href="index.php">Cancel</a>
    </form>
   </article>
  </main>
 </body>
</html>
