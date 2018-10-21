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
  $db = new SQLite3('tinycms.db', SQLITE3_OPEN_READONLY);
  $db->enableExceptions(TRUE);
?>
  <div id="wrap">
   <aside>&nbsp;</aside>
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
  $result = $db->query('SELECT key, description, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    echo '<tr><td><label for="', $row[0], '">', $row[1], '</label></td><td>',
      '<input type="text" name="', $row[0], '" value="', $row[2], "\"</td></tr>\n";
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
  <footer>TinyBlog by Greg Kennedy</footer>
 </body>
</html>
