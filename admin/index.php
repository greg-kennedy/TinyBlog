<?php
  require_once('auth.php');
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>TinyBlog Admin Page</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">TinyBlog Admin Menu</header>
<?php
  // Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READONLY);
  $db->enableExceptions(TRUE);
?>
  <div id="wrap">
<!--   <aside>&nbsp;</aside> -->
   <main>
    <article>
     <header>Post Management</header>
     <table>
      <tr><th>Date</th><th>Title</th><th>Action</th></tr>
      <tr><td>&nbsp;</td><td><i>New Post</i></td><td><a href="edit.php">Edit</a></td></tr>
<?php
  $result = $db->query('SELECT id, date, title FROM posts');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    echo '<tr><td>', $row[1], '</td><td>', $row[2], '</td><td><a href="edit.php?id=', $row[0], '">Edit</a></td></tr>';
  }
  $result->finalize();
?>
     </table>
    </article>
    <article>
     <header>Site Settings</header>
     <form action="settings.php" method="post">
      <table>
       <tr><th>Key</th><th>Value</th></tr>
<?php
  $descriptions = [
    'name' => 'Blog Name',
    'password' => 'Blog Password'
  ];

  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    echo '<tr><td><label for="', $row[0], '">', $descriptions[$row[0]], '</label></td><td>',
      '<input type="text" name="', $row[0], '" value="', $row[1], "\"</td></tr>\n";
  }
  $result->finalize();
?>
      </table>
      <button>Save</button>
     </form>
    </article>
   </main>
  </div>
<?php
  $db->close();
?>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
