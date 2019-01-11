<?php
  require_once('auth.php');

  require_once('inc.settings.php');
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>TinyBlog Admin Page</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner"><h1>TinyBlog Admin Menu</h1></header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Navigation</h2>
     </header>
     <ul>
      <li><a href="../index.html">Blog Index</a></li>
      <li><a href="logout.php">Log Out</a></li>
     </ul>
    </nav>
   </aside>
   <main>
    <article>
     <header>Post Management</header>
     <table>
      <tr><th>Date</th><th>Title</th><th colspan="2">Action</th></tr>
      <tr><td>&nbsp;</td><td><i>New Post</i></td><td colspan="2"><a href="edit.php">Create</a></td></tr>
<?php
  // Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READONLY);
  $db->enableExceptions(TRUE);

  // Fill the Posts table with all posts in the db, newest first
  $result = $db->query('SELECT id, date, title FROM posts ORDER BY date DESC');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    echo '<tr><td>', date(DATE_RFC2822, $row[1]), '</td><td>', $row[2], '</td><td><a href="edit.php?id=', $row[0], '">Edit</a></td><td><a href="do_delete.php?id=', $row[0], '">Delete</a></td></tr>';
  }
  $result->finalize();
?>
     </table>
    </article>
    <article>
     <header>Blog Stylesheet</header>
     <form action="do_style.php" method="post">
      <textarea name="style"><?php readfile('../style.css') ?></textarea>
      <button>Save</button>
     </form>
    </article>
    <article>
     <header>Site Settings</header>
     <form action="do_settings.php" method="post">
      <table>
       <tr><th>Key</th><th>Value</th></tr>
<?php
  // Site Settings: read all key/value pairs from DB, and use to fill the table.
  $settings = settings_load($db);

  // create table, using SETTINGS constant for order and other info
  foreach (SETTINGS as $setting) {
    $name = $setting[0];
    $description = $setting[1];
    $type = $setting[2];

    if ($type == TYPE_PASSWORD) {
      echo '<tr><td><label for="', $name, '"><b>', $description, '</b></label></td><td>',
        '<input type="password" name="', $name, "\"></td></tr>\n";
    } else {
      $value = $settings[$name];
      echo '<tr><td><label for="', $name, '">', $description, '</label></td><td>',
        '<input type="text" name="', $name, '" value="', $value, "\"</td></tr>\n";
    }
  }

  // all done with the db, close it.
  $db->close();
?>
      </table>
      <button>Save</button>
     </form>
    </article>
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
