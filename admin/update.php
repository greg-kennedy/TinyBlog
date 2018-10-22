<?php

/* TinyBlog Update Script
    Re-renders all HTML documents in the parent folder. */

/* Re-creates ALL posts */
function create_all_posts($db)
{
  /* Retrieve settings */
  $result = $db->query('SELECT id FROM posts');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    create_post($db, $row[0]);
  }
  $result->finalize();
}

/* Bakes a new post at a specified URL. */
function create_post($db, $id)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];

  /* get the blog post */
  $stmt = $db->prepare('SELECT date, title, post FROM posts WHERE id=:id');
  $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $date = date(DATE_RFC2822, $row[0]);
    $title = $row[1];
    $post = $row[2];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

  if (! $num_rows) {
    throw new Exception("id '$id' did not match any posts in database");
  }

  // bake the post
  $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>$title - $blog_name</title>
  <link rel="stylesheet" type="text/css" href="../style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="../archive.html">Blog Archive</a></li>
      <li><a href="../admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
    <article>
     <header class="title">
      <h2>$title</h2>
      <p>$date</p>
     </header>
     <section>
      <p>$post</p>
     </section>
    </article>
   </main>
  </div>
  <footer>TinyBlog by Greg Kennedy</footer>
 </body>
</html>
HTML;

  // write to disk
  $file = fopen('../post/' . $id . '.html', 'w');
  fwrite($file, $html);
  fclose($file);
}

/* Bakes a new Index page. */
function create_index($db)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];

  /* get the five most recent blog posts */
  $stmt = $db->prepare('SELECT id, date, title, post FROM posts ORDER BY date desc LIMIT 5');

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $id[] = $row[0];
    $date[] = date(DATE_RFC2822, $row[1]);
    $title[] = $row[2];
    $post[] = $row[3];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

  if (! $num_rows) {
    // TODO: empty blog, replace with boilerplate
    throw new Exception('Cannot create index.html: no posts in database');
  }

  // bake the post
  $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>$title - $blog_name</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="archive.html">Blog Archive</a></li>
      <li><a href="admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
HTML;

  for ($i = 0; $i < $num_rows; $i ++) {
    $html .= <<<HTML
    <article>
     <header class="title">
      <h2><a href="post/$id[$i].html">$title[$i]</a></h2>
      <p>$date[$i]</p>
     </header>
     <section>
      <p>$post[$i]</p>
     </section>
    </article>
HTML;
  }

  $html .= <<<HTML
   </main>
  </div>
  <footer>TinyBlog by Greg Kennedy</footer>
 </body>
</html>
HTML;

  // write to disk
  $file = fopen('../index.html', 'w');
  fwrite($file, $html);
  fclose($file);
}

function create_archive($db)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];

  /* get all the blog posts */
  $stmt = $db->prepare('SELECT id, date, title FROM posts ORDER BY date desc');

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $id[] = $row[0];
    $date[] = date(DATE_RFC2822, $row[1]);
    $title[] = $row[2];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

/*
  if (! $num_rows) {
    // TODO: empty blog, replace with boilerplate
    throw new Exception("id '$id' did not match any posts in database");
  }
*/

  // bake the post
  $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>Archive - $blog_name</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="archive.html">Blog Archive</a></li>
      <li><a href="admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
    <article>
     <header class="title">
      <h2>Blog Archive</h2>
      <p>Complete list of posts:</p>
     </header>
     <section>
      <table>
       <tr>
        <th>Date Posted</th>
        <th>Title</th>
       </tr>
HTML;
  for ($i = 0; $i < $num_rows; $i ++) {
    $html .= <<<HTML
       <tr>
        <td>$date[$i]</td>
        <td><a href="post/$id[$i].html">$title[$i]</a></td>
       </tr>
HTML;
  }
  $html .= <<<HTML
      </table>
     </section>
    </article>
   </main>
  </div>
  <footer>TinyBlog by Greg Kennedy</footer>
 </body>
</html>
HTML;

  // write to disk
  $file = fopen('../archive.html', 'w');
  fwrite($file, $html);
  fclose($file);
}
