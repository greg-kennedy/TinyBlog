<?php

// On re-entry to this page
if ( isset( $_POST['password'] ) ) {
  // Getting submitted user data from database
  //  Open the sqlite3 database
  $db = new SQLite3('tinyblog.db', SQLITE3_OPEN_READONLY);
  $db->enableExceptions(TRUE);
  
  // Retrieve settings
  $result = $db->query('SELECT value FROM settings WHERE key="password"');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $db_password = $row[0];
  }
  $result->finalize();
  $db->close();

  // Verify user password and set $_SESSION
  if ( password_verify( $_POST['password'], $db_password ) ) {
    session_start();
    $_SESSION['authorized'] = true;
    session_write_close();

    // login succeeded so go to the index.
    //  otherwise it falls through to the login page again.
    header('Location: index.php');
  }
}

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
  <div id="wrap">
   <main>
    <article>
     <header>Login</header>
     <form method="POST">
      <label for="password">Password:</label>
      <input type="password" name="password">
      <button>Submit</button>
     </form>
    </article>
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
